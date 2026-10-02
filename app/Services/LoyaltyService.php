<?php

namespace App\Services;

use App\Models\LoyaltyTransaction;
use App\Models\Parcel;
use App\Models\Payment;
use App\Models\User;

class LoyaltyService
{
    public function redemptionFor(User $user, Parcel $parcel): array
    {
        $available = (int) ($user->loyalty_points ?? 0);
        $maxDiscount = (int) floor(((float) $parcel->price) * 0.2);
        $points = min($available, $maxDiscount);

        return [
            'available_points' => $available,
            'points' => $points,
            'discount' => (float) $points,
            'payable' => max((float) $parcel->price - $points, 0),
        ];
    }

    public function applyRedemption(Payment $payment): void
    {
        if ($payment->loyalty_points_redeemed <= 0) {
            return;
        }

        $user = $payment->user;
        if (! $user) {
            return;
        }

        $points = min((int) $payment->loyalty_points_redeemed, (int) $user->loyalty_points);
        if ($points <= 0) {
            $payment->update([
                'loyalty_points_redeemed' => 0,
                'loyalty_discount' => 0,
            ]);

            return;
        }

        $user->decrement('loyalty_points', $points);

        LoyaltyTransaction::create([
            'user_id' => $user->id,
            'payment_id' => $payment->id,
            'parcel_id' => $payment->parcel_id,
            'points' => -$points,
            'type' => 'redeemed',
            'description' => 'Redeemed for parcel '.$payment->parcel?->tracking_id,
        ]);
    }

    public function refundRedemption(Payment $payment): void
    {
        if ($payment->loyalty_points_redeemed <= 0) {
            return;
        }

        $alreadyRefunded = LoyaltyTransaction::where('payment_id', $payment->id)
            ->where('type', 'redemption_refund')
            ->exists();

        if ($alreadyRefunded) {
            return;
        }

        $points = (int) $payment->loyalty_points_redeemed;
        $payment->user?->increment('loyalty_points', $points);

        LoyaltyTransaction::create([
            'user_id' => $payment->user_id,
            'payment_id' => $payment->id,
            'parcel_id' => $payment->parcel_id,
            'points' => $points,
            'type' => 'redemption_refund',
            'description' => 'Returned points after payment cancellation or failure',
        ]);
    }

    public function awardForPayment(Payment $payment): void
    {
        if ($payment->status !== 'paid' || $payment->loyalty_points_awarded > 0) {
            return;
        }

        $points = (int) floor(((float) $payment->amount) / 100);
        if ($points <= 0) {
            return;
        }

        $payment->user?->increment('loyalty_points', $points);
        $payment->update(['loyalty_points_awarded' => $points]);

        LoyaltyTransaction::create([
            'user_id' => $payment->user_id,
            'payment_id' => $payment->id,
            'parcel_id' => $payment->parcel_id,
            'points' => $points,
            'type' => 'earned',
            'description' => 'Earned from paid parcel '.$payment->parcel?->tracking_id,
        ]);
    }
}
