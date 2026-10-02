<?php

namespace App\Services;

use App\Models\Parcel;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PaymentGatewayService
{
    public function createPayHereCheckout(Parcel $parcel, User $user, array $loyalty = []): array
    {
        $payable = (float) ($loyalty['payable'] ?? $parcel->price);
        $amount = $this->formatAmount($payable);
        $currency = config('services.payhere.currency', 'LKR');
        $orderId = 'VL-'.$parcel->tracking_id.'-'.now()->format('His');

        $payment = Payment::create([
            'parcel_id' => $parcel->id,
            'user_id' => $user->id,
            'amount' => $payable,
            'currency' => $currency,
            'method' => 'online_gateway',
            'provider' => 'payhere',
            'reference' => 'PAY-'.Str::upper(Str::random(10)),
            'gateway_order_id' => $orderId,
            'status' => 'pending',
            'loyalty_points_redeemed' => (int) ($loyalty['points'] ?? 0),
            'loyalty_discount' => (float) ($loyalty['discount'] ?? 0),
        ]);

        $nameParts = preg_split('/\s+/', trim($user->name), 2);
        $merchantId = (string) config('services.payhere.merchant_id');

        $payload = [
            'merchant_id' => $merchantId,
            'return_url' => route('customer.payments.return', $payment),
            'cancel_url' => route('customer.payments.cancel', $payment),
            'notify_url' => route('payments.payhere.notify'),
            'order_id' => $orderId,
            'items' => 'Village Link courier payment '.$parcel->tracking_id,
            'currency' => $currency,
            'amount' => $amount,
            'first_name' => $nameParts[0] ?? $user->name,
            'last_name' => $nameParts[1] ?? '',
            'email' => $user->email,
            'phone' => $user->phone ?? '',
            'address' => $user->address ?? $parcel->pickup_address,
            'city' => $parcel->pickup_location ?? 'Colombo',
            'country' => 'Sri Lanka',
            'custom_1' => (string) $parcel->id,
            'custom_2' => (string) $payment->id,
            'hash' => $this->checkoutHash($merchantId, $orderId, $amount, $currency),
        ];

        $payment->update(['checkout_payload' => $payload]);

        return [$payment, $payload];
    }

    public function isPayHereConfigured(): bool
    {
        return (bool) config('services.payhere.enabled')
            && filled(config('services.payhere.merchant_id'))
            && filled(config('services.payhere.merchant_secret'));
    }

    public function checkoutUrl(): string
    {
        return (string) config('services.payhere.checkout_url');
    }

    public function verifyNotification(Request $request): bool
    {
        $merchantId = (string) $request->input('merchant_id');
        $orderId = (string) $request->input('order_id');
        $amount = $this->formatAmount((float) $request->input('payhere_amount'));
        $currency = (string) $request->input('payhere_currency');
        $statusCode = (string) $request->input('status_code');
        $md5sig = strtoupper((string) $request->input('md5sig'));

        $localSig = strtoupper(md5(
            $merchantId.
            $orderId.
            $amount.
            $currency.
            $statusCode.
            strtoupper(md5((string) config('services.payhere.merchant_secret')))
        ));

        return hash_equals($localSig, $md5sig);
    }

    private function checkoutHash(string $merchantId, string $orderId, string $amount, string $currency): string
    {
        return strtoupper(md5(
            $merchantId.
            $orderId.
            $amount.
            $currency.
            strtoupper(md5((string) config('services.payhere.merchant_secret')))
        ));
    }

    private function formatAmount(float $amount): string
    {
        return number_format($amount, 2, '.', '');
    }
}
