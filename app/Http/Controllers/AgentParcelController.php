<?php
namespace App\Http\Controllers;
use App\Models\ParcelStatus;


use App\Models\Parcel;
use App\Models\ParcelLocation;
use App\Notifications\ParcelStatusUpdated;
use App\Services\GeoService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AgentParcelController extends Controller
{
    public function index()
    {
        $driver = auth()->user()->load('driverProfile');
        $parcels = Parcel::where('agent_id', auth()->id())
            ->latest()
            ->get();
        $activeCount = $parcels->where('status', '!=', 'delivered')->count();
        $completedCount = $parcels->where('status', 'delivered')->count();

        return view('agent.parcels.index', compact('driver', 'parcels', 'activeCount', 'completedCount'));
    }

    public function editStatus(Parcel $parcel, GeoService $geo)
    {
        if ($parcel->agent_id !== auth()->id()) {
            abort(403);
        }

        $origin = $parcel->pickup_location ?: $parcel->pickup_address;
        $destination = $parcel->delivery_location ?: $parcel->delivery_address;
        $pickup = $geo->coordinatesFor($origin);
        $delivery = $geo->coordinatesFor($destination);
        $route = $geo->routeBetween($origin, $destination);
        $demoRoute = collect($route['points'] ?? [])
            ->whenEmpty(function () use ($pickup, $delivery) {
                return collect([$pickup, $delivery])
                    ->filter()
                    ->values()
                    ->map(fn (array $point) => ['lat' => (float) $point[0], 'lng' => (float) $point[1]]);
            })
            ->values()
            ->all();

        return view('agent.parcels.status', compact('parcel', 'demoRoute'));
    }

    public function updateStatus(Request $request, Parcel $parcel)
    {
        if ($parcel->agent_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'status' => ['required', 'in:pending_pickup,picked_up,in_transit,arrived_center,out_for_delivery,delivered'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'delivery_notes' => ['nullable', 'string', 'max:1000'],
            'delivery_proof' => [
                Rule::requiredIf($request->status === 'delivered' && ! $parcel->delivery_proof_path),
                'nullable',
                'image',
                'max:4096',
            ],
        ]);

        $proofPath = $parcel->delivery_proof_path;
        if ($request->hasFile('delivery_proof')) {
            $proofPath = $request->file('delivery_proof')->store('delivery-proofs', 'public');
        }

        $parcel->update([
            'status' => $request->status,
            'current_lat' => $request->latitude ?? $parcel->current_lat,
            'current_lng' => $request->longitude ?? $parcel->current_lng,
            'delivery_proof_path' => $proofPath,
            'delivery_notes' => $request->delivery_notes,
            'delivered_at' => $request->status === 'delivered' ? now() : $parcel->delivered_at,
        ]);

        if ($request->filled(['latitude', 'longitude'])) {
            $this->storeLocation($parcel, $request);
        }

        ParcelStatus::create([
            'parcel_id' => $parcel->id,
            'user_id' => auth()->id(),
            'status' => $request->status,
            'note' => 'Updated by delivery driver',
        ]);

        $parcel->load('user');
        if ($parcel->user) {
            $label = Str::title(str_replace('_', ' ', $request->status));
            $parcel->user->notify(new ParcelStatusUpdated($parcel, $label));
        }

        if ($request->status === 'delivered') {
            auth()->user()->driverProfile()?->update(['availability_status' => 'available']);

            return redirect()->route('agent.parcels.index')
                ->with('success', 'Parcel delivered. Customer notified.');
        }

        auth()->user()->driverProfile()?->update(['availability_status' => 'busy']);

        return redirect()->route('agent.parcels.index')
            ->with('success', 'Parcel status updated successfully.');
    }

    public function updateLocation(Request $request, Parcel $parcel)
    {
        if ($parcel->agent_id !== auth()->id()) {
            abort(403);
        }

        if ($parcel->status === 'delivered') {
            return response()->json(['message' => 'Delivered parcel location cannot be updated.'], 422);
        }

        $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'speed_kmh' => ['nullable', 'numeric', 'min:0', 'max:250'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $location = $this->storeLocation($parcel, $request);

        return response()->json([
            'message' => 'Location updated.',
            'location' => $location,
        ]);
    }

    private function storeLocation(Parcel $parcel, Request $request): ParcelLocation
    {
        $location = ParcelLocation::create([
            'parcel_id' => $parcel->id,
            'driver_id' => auth()->id(),
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'speed_kmh' => $request->speed_kmh,
            'note' => $request->note ?? 'Driver status update',
        ]);

        $parcel->update([
            'current_lat' => $request->latitude,
            'current_lng' => $request->longitude,
        ]);

        auth()->user()->driverProfile()->updateOrCreate(
            ['user_id' => auth()->id()],
            [
                'current_lat' => $request->latitude,
                'current_lng' => $request->longitude,
                'last_location_at' => now(),
                'availability_status' => 'busy',
            ],
        );

        return $location;
    }
}
