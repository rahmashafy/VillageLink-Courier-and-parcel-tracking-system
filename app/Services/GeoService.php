<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeoService
{
    public function coordinatesFor(string|null $location): ?array
    {
        $location = trim((string) $location);

        if ($location === '') {
            return null;
        }

        if (preg_match('/(-?\d+(?:\.\d+)?)\s*,\s*(-?\d+(?:\.\d+)?)/', $location, $match)) {
            return [(float) $match[1], (float) $match[2]];
        }

        if ($this->mapsEnabled()) {
            $coords = $this->geocodeWithMaps($location);

            if ($coords) {
                return $coords;
            }
        }

        $coords = $this->fallbackCoordinates($location);

        if ($coords) {
            return $coords;
        }

        return $this->geocodeWithNominatim($location);
    }

    public function routeBetween(string $origin, string $destination): ?array
    {
        $originCoords = $this->coordinatesFor($origin);
        $destinationCoords = $this->coordinatesFor($destination);

        if (! $originCoords || ! $destinationCoords) {
            return null;
        }

        return $this->routeBetweenCoordinates($originCoords, $destinationCoords);
    }

    public function routeBetweenCoordinates(array $origin, array $destination): ?array
    {
        if ($this->mapsEnabled()) {
            $route = $this->directionsWithMaps($origin, $destination);

            if ($route) {
                return $route;
            }
        }

        $osrmRoute = $this->directionsWithOsrm($origin, $destination);

        if ($osrmRoute) {
            return $osrmRoute;
        }

        return $this->fallbackRoute($origin, $destination);
    }

    public function haversineKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    private function mapsEnabled(): bool
    {
        return filled(config('services.maps.api_key'));
    }

    private function geocodeWithMaps(string $location): ?array
    {
        $cacheKey = 'geo:geocode:'.md5(strtolower($location));

        return Cache::remember($cacheKey, now()->addDay(), function () use ($location) {
            $response = Http::timeout(10)->get('https://maps.googleapis.com/maps/api/geocode/json', [
                'address' => $location,
                'key' => config('services.maps.api_key'),
                'region' => config('services.maps.region', 'lk'),
            ]);

            if (! $response->successful()) {
                Log::warning('Maps geocoding request failed', ['location' => $location]);

                return null;
            }

            $result = $response->json('results.0.geometry.location');

            if (! is_array($result) || ! isset($result['lat'], $result['lng'])) {
                return null;
            }

            return [(float) $result['lat'], (float) $result['lng']];
        });
    }

    private function directionsWithMaps(array $origin, array $destination): ?array
    {
        $cacheKey = 'geo:route:'.md5(implode(',', $origin).':'.implode(',', $destination));

        return Cache::remember($cacheKey, now()->addHours(6), function () use ($origin, $destination) {
            $response = Http::timeout(15)->get('https://maps.googleapis.com/maps/api/directions/json', [
                'origin' => $origin[0].','.$origin[1],
                'destination' => $destination[0].','.$destination[1],
                'mode' => 'driving',
                'key' => config('services.maps.api_key'),
                'region' => config('services.maps.region', 'lk'),
            ]);

            if (! $response->successful()) {
                Log::warning('Maps directions request failed');

                return null;
            }

            $route = $response->json('routes.0');

            if (! is_array($route)) {
                return null;
            }

            $encodedPolyline = data_get($route, 'overview_polyline.points');
            $points = $encodedPolyline ? $this->decodePolyline($encodedPolyline) : [];
            $legs = $route['legs'] ?? [];
            $distanceMeters = collect($legs)->sum(fn (array $leg) => (float) ($leg['distance']['value'] ?? 0));
            $durationSeconds = collect($legs)->sum(fn (array $leg) => (float) ($leg['duration']['value'] ?? 0));

            if ($points === []) {
                $points = [
                    ['lat' => $origin[0], 'lng' => $origin[1]],
                    ['lat' => $destination[0], 'lng' => $destination[1]],
                ];
            }

            return [
                'distance_km' => round($distanceMeters / 1000, 2),
                'duration_minutes' => (int) round($durationSeconds / 60),
                'points' => $points,
            ];
        });
    }

    private function directionsWithOsrm(array $origin, array $destination): ?array
    {
        if (! config('services.maps.osrm_enabled') || app()->environment('testing')) {
            return null;
        }

        $cacheKey = 'geo:osrm:'.md5(implode(',', $origin).':'.implode(',', $destination));

        return Cache::remember($cacheKey, now()->addHours(6), function () use ($origin, $destination) {
            try {
                $response = Http::timeout(8)->get(
                    'https://router.project-osrm.org/route/v1/driving/'.
                    $origin[1].','.$origin[0].';'.$destination[1].','.$destination[0],
                    [
                        'overview' => 'full',
                        'geometries' => 'geojson',
                        'steps' => 'false',
                    ],
                );
            } catch (\Throwable $exception) {
                Log::warning('OSRM route request failed', ['error' => $exception->getMessage()]);

                return null;
            }

            if (! $response->successful()) {
                return null;
            }

            $coordinates = $response->json('routes.0.geometry.coordinates');

            if (! is_array($coordinates) || count($coordinates) < 2) {
                return null;
            }

            return [
                'distance_km' => round(((float) $response->json('routes.0.distance', 0)) / 1000, 2),
                'duration_minutes' => (int) max(1, round(((float) $response->json('routes.0.duration', 0)) / 60)),
                'points' => collect($coordinates)
                    ->filter(fn ($point) => is_array($point) && count($point) >= 2)
                    ->map(fn ($point) => ['lat' => round((float) $point[1], 7), 'lng' => round((float) $point[0], 7)])
                    ->values()
                    ->all(),
            ];
        });
    }

    private function geocodeWithNominatim(string $location): ?array
    {
        if (! config('services.maps.nominatim_enabled')) {
            return null;
        }

        $cacheKey = 'geo:nominatim:'.md5(strtolower($location));

        return Cache::remember($cacheKey, now()->addDays(7), function () use ($location) {
            try {
                $response = Http::withHeaders([
                    'User-Agent' => config('app.name', 'Village Link').' route lookup (local demo)',
                ])->timeout(10)->get('https://nominatim.openstreetmap.org/search', [
                    'q' => $location.', Sri Lanka',
                    'format' => 'jsonv2',
                    'limit' => 1,
                    'countrycodes' => 'lk',
                    'addressdetails' => 0,
                ]);
            } catch (\Throwable $exception) {
                Log::warning('Nominatim geocoding request failed', [
                    'location' => $location,
                    'error' => $exception->getMessage(),
                ]);

                return null;
            }

            if (! $response->successful()) {
                return null;
            }

            $result = $response->json('0');

            if (! is_array($result) || ! isset($result['lat'], $result['lon'])) {
                return null;
            }

            return [round((float) $result['lat'], 7), round((float) $result['lon'], 7)];
        });
    }

    private function fallbackCoordinates(string $location): ?array
    {
        $location = strtolower($location);

        $cities = [
            'colombo' => [6.9271, 79.8612],
            'fort' => [6.9344, 79.8428],
            'kotte' => [6.8905, 79.9015],
            'dehiwala' => [6.8513, 79.8656],
            'mount lavinia' => [6.8390, 79.8647],
            'moratuwa' => [6.7730, 79.8816],
            'maharagama' => [6.8480, 79.9265],
            'homagama' => [6.8440, 80.0024],
            'kaduwela' => [6.9355, 79.9842],
            'avissawella' => [6.9553, 80.2042],
            'kandy' => [7.2906, 80.6337],
            'galle' => [6.0535, 80.2210],
            'jaffna' => [9.6615, 80.0255],
            'kurunegala' => [7.4863, 80.3623],
            'mawanella' => [7.2536, 80.4469],
            'kegalle' => [7.2513, 80.3464],
            'digana' => [7.2933, 80.7359],
            'kadugannawa' => [7.2547, 80.5242],
            'peradeniya' => [7.2631, 80.5967],
            'dambulla' => [7.8731, 80.6511],
            'negombo' => [7.2083, 79.8358],
            'chilaw' => [7.5758, 79.7953],
            'puttalam' => [8.0362, 79.8283],
            'kuliyapitiya' => [7.4688, 80.0401],
            'wariyapola' => [7.6280, 80.2350],
            'nikaweratiya' => [7.7476, 80.1151],
            'matara' => [5.9549, 80.5550],
            'weligama' => [5.9750, 80.4297],
            'tangalle' => [6.0240, 80.7911],
            'hambantota' => [6.1241, 81.1185],
            'ambalangoda' => [6.2355, 80.0538],
            'hikkaduwa' => [6.1407, 80.1012],
            'anuradhapura' => [8.3114, 80.4037],
            'apuradhapura' => [8.3114, 80.4037],
            'anuradapura' => [8.3114, 80.4037],
            'mihintale' => [8.3500, 80.5167],
            'medawachchiya' => [8.5408, 80.4959],
            'polonnaruwa' => [7.9403, 81.0188],
            'hingurakgoda' => [8.0362, 80.9483],
            'minneriya' => [8.0366, 80.9034],
            'trincomalee' => [8.5874, 81.2152],
            'trinco' => [8.5874, 81.2152],
            'kantale' => [8.3651, 80.9669],
            'badulla' => [6.9934, 81.0550],
            'bandarawela' => [6.8289, 80.9914],
            'ella' => [6.8667, 81.0466],
            'haputale' => [6.7654, 80.9510],
            'welimada' => [6.9061, 80.9138],
            'mahiyanganaya' => [7.3285, 80.9883],
            'monaragala' => [6.8728, 81.3507],
            'wellawaya' => [6.7367, 81.1026],
            'kataragama' => [6.4134, 81.3326],
            'ratnapura' => [6.7056, 80.3847],
            'balangoda' => [6.6617, 80.6930],
            'embilipitiya' => [6.3439, 80.8489],
            'pelmadulla' => [6.6209, 80.5429],
            'batticaloa' => [7.7102, 81.6924],
            'kalmunai' => [7.4167, 81.8167],
            'ampara' => [7.2912, 81.6724],
            'akkaraipattu' => [7.2167, 81.8500],
            'sainthamaruthu' => [7.3833, 81.8333],
            'eravur' => [7.7782, 81.6038],
            'vavuniya' => [8.7514, 80.4971],
            'mannar' => [8.9810, 79.9044],
            'kilinochchi' => [9.3803, 80.3770],
            'mullaitivu' => [9.2671, 80.8142],
            'point pedro' => [9.8167, 80.2333],
            'chavakachcheri' => [9.6647, 80.1597],
            'nuwara eliya' => [6.9497, 80.7891],
            'nuwaraeliya' => [6.9497, 80.7891],
            'hatton' => [6.8916, 80.5955],
            'talawakele' => [6.9371, 80.6581],
            'maskeliya' => [6.8319, 80.5683],
            'gampola' => [7.1647, 80.5767],
            'matale' => [7.4675, 80.6234],
            'rattota' => [7.5220, 80.6810],
            'galewela' => [7.7594, 80.5675],
            'panadura' => [6.7132, 79.9026],
            'kalutara' => [6.5854, 79.9607],
            'horana' => [6.7159, 80.0626],
            'bandaragama' => [6.7139, 79.9897],
            'beruwala' => [6.4788, 79.9828],
            'aluthgama' => [6.4335, 80.0004],
            'gampaha' => [7.0873, 79.9990],
            'kadawatha' => [7.0015, 79.9500],
            'kiribathgoda' => [6.9788, 79.9297],
            'kelaniya' => [6.9553, 79.9220],
            'wattala' => [6.9895, 79.8897],
            'minuwangoda' => [7.1663, 79.9533],
            'veyangoda' => [7.1560, 80.0958],
            'nittambuwa' => [7.1442, 80.0965],
            'divulapitiya' => [7.2167, 80.0167],
            'rambukkana' => [7.3230, 80.3919],
            'yatiyantota' => [7.0245, 80.3008],
            'deraniyagala' => [6.9333, 80.3333],
        ];

        foreach ($cities as $city => $coords) {
            if (str_contains($location, $city)) {
                return $coords;
            }
        }

        return null;
    }

    private function fallbackRoute(array $origin, array $destination): array
    {
        $roadPoints = $this->knownRoadRoute($origin, $destination);
        $distanceKm = round($this->haversineKm($origin[0], $origin[1], $destination[0], $destination[1]) * 1.22, 2);

        return [
            'distance_km' => $distanceKm,
            'duration_minutes' => (int) max(30, round(($distanceKm / 45) * 60)),
            'points' => $roadPoints ?: $this->curvedFallbackRoute($origin, $destination),
        ];
    }

    private function knownRoadRoute(array $origin, array $destination): ?array
    {
        $from = $this->nearestKnownCity($origin);
        $to = $this->nearestKnownCity($destination);

        if (! $from || ! $to || $from === $to) {
            return null;
        }

        $routes = [
            'kurunegala:colombo' => [
                [7.4863, 80.3623],
                [7.4202, 80.3303],
                [7.3346, 80.3035],
                [7.2895, 80.2402],
                [7.2522, 80.1709],
                [7.2311, 80.1984],
                [7.1446, 80.0967],
                [7.0047, 79.9542],
                [6.9584, 79.9212],
                [6.9271, 79.8612],
            ],
            'colombo:kandy' => [
                [6.9271, 79.8612],
                [6.9584, 79.9212],
                [7.0047, 79.9542],
                [7.1446, 80.0967],
                [7.2311, 80.1984],
                [7.2536, 80.3464],
                [7.2513, 80.4469],
                [7.2628, 80.5960],
                [7.2906, 80.6337],
            ],
            'kurunegala:kandy' => [
                [7.4863, 80.3623],
                [7.4149, 80.4449],
                [7.3691, 80.5312],
                [7.3302, 80.6211],
                [7.2906, 80.6337],
            ],
            'negombo:colombo' => [
                [7.2083, 79.8358],
                [7.1644, 79.8731],
                [7.0867, 79.8951],
                [7.0047, 79.9542],
                [6.9584, 79.9212],
                [6.9271, 79.8612],
            ],
        ];

        $key = $from.':'.$to;
        $reverseKey = $to.':'.$from;
        $points = $routes[$key] ?? null;

        if (! $points && isset($routes[$reverseKey])) {
            $points = array_reverse($routes[$reverseKey]);
        }

        if (! $points) {
            return null;
        }

        $points[0] = $origin;
        $points[array_key_last($points)] = $destination;

        return collect($points)
            ->map(fn (array $point) => ['lat' => round((float) $point[0], 7), 'lng' => round((float) $point[1], 7)])
            ->values()
            ->all();
    }

    private function nearestKnownCity(array $point): ?string
    {
        $cities = [
            'colombo' => [6.9271, 79.8612],
            'kandy' => [7.2906, 80.6337],
            'kurunegala' => [7.4863, 80.3623],
            'negombo' => [7.2083, 79.8358],
        ];

        $best = null;
        $bestDistance = PHP_FLOAT_MAX;

        foreach ($cities as $city => $coords) {
            $distance = $this->haversineKm((float) $point[0], (float) $point[1], $coords[0], $coords[1]);

            if ($distance < $bestDistance) {
                $best = $city;
                $bestDistance = $distance;
            }
        }

        return $bestDistance <= 25 ? $best : null;
    }

    private function curvedFallbackRoute(array $origin, array $destination): array
    {
        $points = [];
        $steps = 8;
        $latDelta = $destination[0] - $origin[0];
        $lngDelta = $destination[1] - $origin[1];
        $length = max(sqrt($latDelta ** 2 + $lngDelta ** 2), 0.0001);
        $normalLat = -$lngDelta / $length;
        $normalLng = $latDelta / $length;

        for ($i = 0; $i <= $steps; $i++) {
            $t = $i / $steps;
            $offset = sin($t * pi()) * 0.05;
            $points[] = [
                'lat' => round($origin[0] + $latDelta * $t + $normalLat * $offset, 7),
                'lng' => round($origin[1] + $lngDelta * $t + $normalLng * $offset, 7),
            ];
        }

        return $points;
    }

    private function decodePolyline(string $encoded): array
    {
        $points = [];
        $index = 0;
        $length = strlen($encoded);
        $lat = 0;
        $lng = 0;

        while ($index < $length) {
            $shift = 0;
            $result = 0;

            do {
                $byte = ord($encoded[$index++]) - 63;
                $result |= ($byte & 0x1f) << $shift;
                $shift += 5;
            } while ($byte >= 0x20);

            $deltaLat = (($result & 1) ? ~($result >> 1) : ($result >> 1));
            $lat += $deltaLat;

            $shift = 0;
            $result = 0;

            do {
                $byte = ord($encoded[$index++]) - 63;
                $result |= ($byte & 0x1f) << $shift;
                $shift += 5;
            } while ($byte >= 0x20);

            $deltaLng = (($result & 1) ? ~($result >> 1) : ($result >> 1));
            $lng += $deltaLng;

            $points[] = [
                'lat' => round($lat / 1e5, 7),
                'lng' => round($lng / 1e5, 7),
            ];
        }

        return $points;
    }
}
