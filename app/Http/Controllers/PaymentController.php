<?php

namespace App\Http\Controllers;

use App\Models\Parcel;
use App\Models\Payment;
use App\Services\LoyaltyService;
use App\Services\PaymentGatewayService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PaymentController extends Controller
{
    public function index()
    {
        $payments = Payment::with('parcel')
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        return view('customer.payments.index', compact('payments'));
    }

    public function invoice(Parcel $parcel)
    {
        if ($parcel->user_id !== auth()->id()) {
            abort(403);
        }

        $payment = $parcel->payment;

        return view('customer.payments.invoice', compact('parcel', 'payment'));
    }

    public function downloadPdf(Parcel $parcel)
    {
        if ($parcel->user_id !== auth()->id()) {
            abort(403);
        }

        $payment = $parcel->payment;

        return Pdf::loadView('customer.payments.invoice-pdf', compact('parcel', 'payment'))
            ->setPaper('a4')
            ->download('invoice-'.$parcel->tracking_id.'.pdf');
    }

    public function payForm(Parcel $parcel, PaymentGatewayService $gateway, LoyaltyService $loyalty)
    {
        if ($parcel->user_id !== auth()->id()) {
            abort(403);
        }

        if (($parcel->payment_status ?? 'unpaid') === 'paid') {
            return redirect()->route('customer.payments.invoice', $parcel);
        }

        return view('customer.payments.pay', [
            'parcel' => $parcel,
            'gatewayReady' => $gateway->isPayHereConfigured(),
            'loyalty' => $loyalty->redemptionFor(auth()->user(), $parcel),
        ]);
    }

    public function pay(Request $request, Parcel $parcel, PaymentGatewayService $gateway, LoyaltyService $loyalty)
    {
        if ($parcel->user_id !== auth()->id()) {
            abort(403);
        }

        if (($parcel->payment_status ?? 'unpaid') === 'paid') {
            return redirect()->route('customer.payments.invoice', $parcel);
        }

        $validated = $request->validate([
            'method' => ['required', Rule::in(['payhere', 'bank_transfer', 'cash'])],
            'bank_name' => ['required_if:method,bank_transfer', 'nullable', 'string', 'max:255'],
            'transfer_reference' => ['required_if:method,bank_transfer', 'nullable', 'string', 'max:100'],
            'cash_confirm' => ['exclude_unless:method,cash', 'accepted'],
            'use_loyalty' => ['nullable', 'boolean'],
        ]);

        $loyaltyData = $request->boolean('use_loyalty')
            ? $loyalty->redemptionFor($request->user(), $parcel)
            : ['points' => 0, 'discount' => 0, 'payable' => (float) $parcel->price];

        if ($validated['method'] === 'payhere') {
            if (! $gateway->isPayHereConfigured()) {
                return back()
                    ->withErrors(['method' => 'Online payment is not available right now. Please choose Bank Transfer or Cash.'])
                    ->withInput();
            }

            [$payment, $payload] = $gateway->createPayHereCheckout($parcel, $request->user(), $loyaltyData);
            $loyalty->applyRedemption($payment);
            $parcel->update(['loyalty_discount_amount' => $loyaltyData['discount']]);

            return view('customer.payments.payhere-checkout', [
                'payment' => $payment,
                'payload' => $payload,
                'checkoutUrl' => $gateway->checkoutUrl(),
            ]);
        }

        $details = $validated['method'] === 'bank_transfer'
            ? [
                'bank_name' => $validated['bank_name'],
                'transfer_reference' => $validated['transfer_reference'],
            ]
            : ['note' => 'Cash on delivery confirmed by customer'];

        $payment = Payment::create([
            'parcel_id' => $parcel->id,
            'user_id' => auth()->id(),
            'amount' => $loyaltyData['payable'],
            'currency' => config('services.payhere.currency', 'LKR'),
            'method' => $validated['method'],
            'provider' => $validated['method'] === 'cash' ? 'cash' : 'manual',
            'reference' => $validated['method'] === 'cash' ? 'COD-'.$parcel->tracking_id : $validated['transfer_reference'],
            'payment_details' => $details,
            'status' => 'pending',
            'loyalty_points_redeemed' => $loyaltyData['points'],
            'loyalty_discount' => $loyaltyData['discount'],
        ]);

        $loyalty->applyRedemption($payment);

        $parcel->update([
            'payment_status' => 'pending',
            'loyalty_discount_amount' => $loyaltyData['discount'],
        ]);

        return redirect()->route('customer.payments.invoice', $parcel)
            ->with('success', 'Payment request recorded. Admin will confirm it after verification.');
    }

    public function gatewayReturn(Payment $payment)
    {
        if ($payment->user_id !== auth()->id()) {
            abort(403);
        }

        return redirect()->route('customer.payments.invoice', $payment->parcel)
            ->with('success', 'Payment is being verified. Your invoice will update after gateway confirmation.');
    }

    public function gatewayCancel(Payment $payment, LoyaltyService $loyalty)
    {
        if ($payment->user_id !== auth()->id()) {
            abort(403);
        }

        $payment->update([
            'status' => 'cancelled',
            'gateway_status' => 'cancelled',
            'failed_at' => now(),
            'failure_reason' => 'Customer cancelled gateway checkout.',
        ]);

        $loyalty->refundRedemption($payment);

        return redirect()->route('customer.payments.pay', $payment->parcel)
            ->withErrors(['method' => 'Payment was cancelled. You can try again or choose another method.']);
    }

    public function payHereNotify(Request $request, PaymentGatewayService $gateway, LoyaltyService $loyalty)
    {
        if (! $gateway->verifyNotification($request)) {
            return response('Invalid signature', 403);
        }

        $payment = Payment::where('gateway_order_id', $request->input('order_id'))->firstOrFail();
        $paid = (string) $request->input('status_code') === '2';

        $payment->update([
            'gateway_payment_id' => $request->input('payment_id'),
            'gateway_status' => $request->input('status_message', $request->input('status_code')),
            'status' => $paid ? 'paid' : 'failed',
            'paid_at' => $paid ? now() : null,
            'failed_at' => $paid ? null : now(),
            'failure_reason' => $paid ? null : $request->input('status_message'),
            'payment_details' => array_merge($payment->payment_details ?? [], $request->except(['md5sig'])),
        ]);

        if ($paid) {
            $loyalty->awardForPayment($payment->refresh());
        } else {
            $loyalty->refundRedemption($payment->refresh());
        }

        $payment->parcel?->update(['payment_status' => $paid ? 'paid' : 'unpaid']);

        return response('OK');
    }
}
