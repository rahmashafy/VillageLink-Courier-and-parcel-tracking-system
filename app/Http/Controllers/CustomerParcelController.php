<?php

namespace App\Http\Controllers;

use App\Models\Parcel;
use App\Models\ParcelStatus;
use App\Services\DeliveryPredictionService;
use App\Services\GeoService;
use App\Services\PricingService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CustomerParcelController extends Controller
{
    public function create()
    {
        return view('customer.parcels.create');
    }

    public function store(Request $request, PricingService $pricing, DeliveryPredictionService $prediction)
    {
        $request->validate([
            'sender_name' => ['required', 'string', 'max:255'],
            'receiver_name' => ['required', 'string', 'max:255'],
            'receiver_phone' => ['required', 'string', 'max:20'],
            'pickup_address' => ['required', 'string'],
            'delivery_address' => ['required', 'string'],
            'pickup_location' => ['required', 'string'],
            'delivery_location' => ['required', 'string'],
            'parcel_type' => ['required', 'string'],
            'delivery_type' => ['required', 'in:standard,express,same_day'],
            'weight' => ['required', 'numeric', 'min:0.1'],
            'length_cm' => ['nullable', 'numeric', 'min:1', 'max:300'],
            'width_cm' => ['nullable', 'numeric', 'min:1', 'max:300'],
            'height_cm' => ['nullable', 'numeric', 'min:1', 'max:300'],
        ]);

        $distance = $pricing->estimateDistance($request->pickup_location, $request->delivery_location);
        $volumetricWeight = $pricing->volumetricWeight($request->float('length_cm') ?: null, $request->float('width_cm') ?: null, $request->float('height_cm') ?: null);
        $chargeableWeight = $pricing->chargeableWeight((float) $request->weight, $request->float('length_cm') ?: null, $request->float('width_cm') ?: null, $request->float('height_cm') ?: null);
        $priceData = $pricing->calculate($chargeableWeight, $distance, $request->delivery_type);
        $predictionData = $prediction->predict($request->delivery_type, $distance);

        $parcel = Parcel::create([
            'tracking_id' => $this->generateTrackingId(),
            'user_id' => auth()->id(),
            'sender_name' => $request->sender_name,
            'receiver_name' => $request->receiver_name,
            'receiver_phone' => $request->receiver_phone,
            'pickup_address' => $request->pickup_address,
            'delivery_address' => $request->delivery_address,
            'pickup_location' => $request->pickup_location,
            'delivery_location' => $request->delivery_location,
            'parcel_type' => $request->parcel_type,
            'delivery_type' => $request->delivery_type,
            'weight' => $request->weight,
            'length_cm' => $request->input('length_cm'),
            'width_cm' => $request->input('width_cm'),
            'height_cm' => $request->input('height_cm'),
            'volumetric_weight' => $volumetricWeight,
            'chargeable_weight' => $chargeableWeight,
            'distance_km' => $distance,
            'original_price' => $priceData['total'],
            'price' => $priceData['total'],
            'status' => 'pending_pickup',
            'estimated_delivery_at' => $predictionData['estimated_at'],
        ]);

        ParcelStatus::create([
            'parcel_id' => $parcel->id,
            'user_id' => auth()->id(),
            'status' => 'pending_pickup',
            'note' => 'Parcel booked successfully',
        ]);

        return redirect()->route('customer.payments.pay', $parcel)
            ->with('success', 'Parcel booked successfully. Tracking ID: ' . $parcel->tracking_id);
    }
    public function trackForm()
    {
        $recentParcels = Parcel::where('user_id', auth()->id())
            ->latest()
            ->take(4)
            ->get();

        return view('customer.parcels.track', compact('recentParcels'));
    }

    public function trackResult(Request $request)
    {
        $request->validate([
            'tracking_id' => ['required', 'string', 'max:40'],
        ]);

        $trackingId = Str::upper(trim($request->tracking_id));

        $parcel = Parcel::where('tracking_id', $trackingId)
            ->where('user_id', auth()->id())
            ->first();

        if (! $parcel) {
            return back()->withInput()->with('error', 'Tracking ID not found for your account.');
        }

        return $this->trackingView($parcel);
    }

    public function show(Parcel $parcel)
    {
        if ($parcel->user_id !== auth()->id()) {
            abort(403);
        }

        return $this->trackingView($parcel);
    }

    public function index()
    {
        $parcels = Parcel::where('user_id', auth()->id())
            ->latest()
            ->get();

        return view('customer.parcels.index', compact('parcels'));
    }

    private function trackingView(Parcel $parcel)
    {
        $statusSteps = ['pending_pickup', 'picked_up', 'in_transit', 'arrived_center', 'out_for_delivery', 'delivered'];
        $parcel->load('agent.driverProfile', 'latestLocation');
        $statusHistory = ParcelStatus::where('parcel_id', $parcel->id)->oldest()->get();

        return view('customer.parcels.track-result', compact('parcel', 'statusSteps', 'statusHistory'));
    }

    public function latestLocation(Parcel $parcel, GeoService $geo)
    {
        if ($parcel->user_id !== auth()->id()) {
            abort(403);
        }

        $parcel->load('latestLocation', 'agent.driverProfile');
        $origin = $parcel->pickup_location ?: $parcel->pickup_address;
        $destination = $parcel->delivery_location ?: $parcel->delivery_address;
        $pickup = $this->mapPoint($geo->coordinatesFor($origin));
        $delivery = $this->mapPoint($geo->coordinatesFor($destination));
        $vehicle = filled($parcel->current_lat) && filled($parcel->current_lng)
            ? ['lat' => (float) $parcel->current_lat, 'lng' => (float) $parcel->current_lng]
            : null;

        $route = $geo->routeBetween($origin, $destination);
        $routePoints = $route['points'] ?? collect([$pickup, $vehicle, $delivery])->filter()->values()->all();
        $routeVehicle = $vehicle && count($routePoints) >= 2
            ? $this->snapVehicleToRoute($vehicle, $routePoints)
            : $vehicle;
        $remainingKm = $vehicle ? $this->remainingRouteKm($vehicle, $routePoints, $geo) : null;
        $speedKmh = $parcel->latestLocation?->speed_kmh;
        $liveEtaMinutes = $this->liveEtaMinutes($remainingKm, $speedKmh, $route);

        return response()->json([
            'status' => $parcel->status,
            'latitude' => $parcel->current_lat,
            'longitude' => $parcel->current_lng,
            'pickup' => $pickup,
            'delivery' => $delivery,
            'vehicle' => $vehicle,
            'route_vehicle' => $routeVehicle,
            'route_points' => $routePoints,
            'distance_km' => $route['distance_km'] ?? null,
            'remaining_km' => $remainingKm,
            'duration_minutes' => $route['duration_minutes'] ?? null,
            'live_eta_minutes' => $liveEtaMinutes,
            'speed_kmh' => $speedKmh,
            'is_live' => (bool) $parcel->latestLocation?->created_at?->gte(now()->subSeconds(30)),
            'updated_at' => $parcel->latestLocation?->created_at?->toIso8601String(),
            'driver' => $parcel->agent?->name,
            'vehicle_type' => $parcel->agent?->driverProfile?->vehicle_type,
            'vehicle_number' => $parcel->agent?->driverProfile?->vehicle_number,
            'eta' => $parcel->estimated_delivery_at?->format('M d, g:i A'),
        ]);
    }

    public function routePreview(Request $request, GeoService $geo)
    {
        $validated = $request->validate([
            'pickup_location' => ['required', 'string', 'max:255'],
            'delivery_location' => ['required', 'string', 'max:255'],
            'pickup_address' => ['nullable', 'string', 'max:500'],
            'delivery_address' => ['nullable', 'string', 'max:500'],
        ]);

        $origin = $validated['pickup_location'] ?: $validated['pickup_address'];
        $destination = $validated['delivery_location'] ?: $validated['delivery_address'];
        $pickup = $this->mapPoint($geo->coordinatesFor($origin));
        $delivery = $this->mapPoint($geo->coordinatesFor($destination));

        if (! $pickup || ! $delivery) {
            return response()->json([
                'message' => 'Could not locate one or both locations. Try a clearer city or address.',
            ], 422);
        }

        $route = $geo->routeBetween($origin, $destination);

        return response()->json([
            'pickup' => $pickup,
            'delivery' => $delivery,
            'route_points' => $route['points'] ?? [$pickup, $delivery],
            'distance_km' => $route['distance_km'] ?? null,
            'duration_minutes' => $route['duration_minutes'] ?? null,
        ]);
    }

    private function mapPoint(?array $coordinates): ?array
    {
        if (! $coordinates) {
            return null;
        }

        return ['lat' => (float) $coordinates[0], 'lng' => (float) $coordinates[1]];
    }

    private function snapVehicleToRoute(array $vehicle, array $routePoints): array
    {
        $closest = $vehicle;
        $closestDistance = PHP_FLOAT_MAX;

        foreach ($routePoints as $point) {
            if (! isset($point['lat'], $point['lng'])) {
                continue;
            }

            $distance = abs((float) $point['lat'] - (float) $vehicle['lat'])
                + abs((float) $point['lng'] - (float) $vehicle['lng']);

            if ($distance < $closestDistance) {
                $closestDistance = $distance;
                $closest = ['lat' => (float) $point['lat'], 'lng' => (float) $point['lng']];
            }
        }

        return $closest;
    }

    private function remainingRouteKm(array $vehicle, array $routePoints, GeoService $geo): ?float
    {
        if (count($routePoints) < 2) {
            return null;
        }

        $index = $this->nearestRouteIndex($vehicle, $routePoints);
        $remaining = 0.0;

        for ($i = $index; $i < count($routePoints) - 1; $i++) {
            $from = $routePoints[$i];
            $to = $routePoints[$i + 1];

            if (! isset($from['lat'], $from['lng'], $to['lat'], $to['lng'])) {
                continue;
            }

            $remaining += $geo->haversineKm(
                (float) $from['lat'],
                (float) $from['lng'],
                (float) $to['lat'],
                (float) $to['lng'],
            );
        }

        return round($remaining, 2);
    }

    private function nearestRouteIndex(array $vehicle, array $routePoints): int
    {
        $bestIndex = 0;
        $bestDistance = PHP_FLOAT_MAX;

        foreach ($routePoints as $index => $point) {
            if (! isset($point['lat'], $point['lng'])) {
                continue;
            }

            $distance = abs((float) $point['lat'] - (float) $vehicle['lat'])
                + abs((float) $point['lng'] - (float) $vehicle['lng']);

            if ($distance < $bestDistance) {
                $bestDistance = $distance;
                $bestIndex = $index;
            }
        }

        return $bestIndex;
    }

    private function liveEtaMinutes(?float $remainingKm, ?float $speedKmh, ?array $route): ?int
    {
        if (! $remainingKm) {
            return null;
        }

        if ($speedKmh && $speedKmh > 5) {
            return (int) max(1, round(($remainingKm / $speedKmh) * 60));
        }

        $totalKm = isset($route['distance_km']) ? (float) $route['distance_km'] : 0;
        $totalMinutes = isset($route['duration_minutes']) ? (int) $route['duration_minutes'] : 0;

        if ($totalKm > 0 && $totalMinutes > 0) {
            return (int) max(1, round($totalMinutes * ($remainingKm / $totalKm)));
        }

        return null;
    }

    private function generateTrackingId(): string
    {
        do {
            $trackingId = 'TRK'.now()->format('Ymd').random_int(1000, 9999);
        } while (Parcel::where('tracking_id', $trackingId)->exists());

        return $trackingId;
    }

}
