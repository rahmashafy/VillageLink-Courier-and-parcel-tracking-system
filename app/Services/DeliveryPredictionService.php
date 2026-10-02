<?php

namespace App\Services;

use Carbon\Carbon;

class DeliveryPredictionService
{
    public function predict(string $deliveryType, float $distanceKm): array
    {
        $hours = match ($deliveryType) {
            'same_day' => 8,
            'express' => 24,
            default => 48,
        };

        $hours += (int) floor($distanceKm / 100) * 6;

        $eta = Carbon::now()->addHours($hours);
        $delayRisk = $distanceKm > 200 ? 'medium' : 'low';

        return [
            'estimated_at' => $eta,
            'display' => $eta->format('M d, Y').' before '.$eta->format('g:i A'),
            'delay_risk' => $delayRisk,
            'message' => 'AI predicted delivery: '.$eta->format('l').' before '.$eta->format('g:i A'),
        ];
    }
}
