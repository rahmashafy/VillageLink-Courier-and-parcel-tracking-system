<?php

namespace App\Services;

class PricingService
{
    public function __construct(private GeoService $geo) {}

    /** Smart price: base + weight + distance + delivery type */
    public function calculate(float $weight, float $distanceKm, string $deliveryType = 'standard'): array
    {
        $base = 300;
        $weightCharge = $weight * 150;
        $distanceCharge = $distanceKm * 5;
        $typeMultiplier = match ($deliveryType) {
            'express' => 1.5,
            'same_day' => 2.0,
            default => 1.0,
        };

        $subtotal = $base + $weightCharge + $distanceCharge;
        $total = round($subtotal * $typeMultiplier, 2);

        return [
            'base' => $base,
            'weight_charge' => round($weightCharge, 2),
            'distance_charge' => round($distanceCharge, 2),
            'delivery_type' => $deliveryType,
            'total' => $total,
        ];
    }

    public function volumetricWeight(?float $lengthCm, ?float $widthCm, ?float $heightCm): float
    {
        if (! $lengthCm || ! $widthCm || ! $heightCm) {
            return 0;
        }

        return round(($lengthCm * $widthCm * $heightCm) / 5000, 2);
    }

    public function chargeableWeight(float $actualWeight, ?float $lengthCm, ?float $widthCm, ?float $heightCm): float
    {
        return max($actualWeight, $this->volumetricWeight($lengthCm, $widthCm, $heightCm));
    }

    public function estimateDistance(string $pickupLocation, string $deliveryLocation): float
    {
        $pickup = strtolower(trim($pickupLocation));
        $delivery = strtolower(trim($deliveryLocation));

        if ($pickup === $delivery) {
            return 25;
        }

        $route = $this->geo->routeBetween($pickupLocation, $deliveryLocation);

        if ($route) {
            return (float) $route['distance_km'];
        }

        $pickupCoords = $this->geo->coordinatesFor($pickupLocation);
        $deliveryCoords = $this->geo->coordinatesFor($deliveryLocation);

        if ($pickupCoords && $deliveryCoords) {
            return round($this->geo->haversineKm($pickupCoords[0], $pickupCoords[1], $deliveryCoords[0], $deliveryCoords[1]) * 1.22, 2);
        }

        return 80;
    }
}
