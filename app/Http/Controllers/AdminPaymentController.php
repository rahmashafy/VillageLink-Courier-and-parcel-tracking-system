<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\LoyaltyService;
use Illuminate\Http\Request;

class AdminPaymentController extends Controller
{
    public function index()
    {
        $payments = Payment::with('parcel.user')
            ->latest()
            ->get();

        return view('admin.payments.index', compact('payments'));
    }

    public function update(Request $request, Payment $payment, LoyaltyService $loyalty)
    {
        $oldStatus = $payment->status;

        $validated = $request->validate([
            'status' => ['required', 'in:pending,paid,failed,cancelled,refunded'],
            'failure_reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $payment->update([
            'status' => $validated['status'],
            'paid_at' => $validated['status'] === 'paid' ? now() : $payment->paid_at,
            'failed_at' => in_array($validated['status'], ['failed', 'cancelled', 'refunded'], true) ? now() : null,
            'failure_reason' => $validated['failure_reason'] ?? null,
        ]);

        $payment->refresh();

        if ($oldStatus !== 'paid' && $payment->status === 'paid') {
            $loyalty->awardForPayment($payment);
        }

        if ($oldStatus !== $payment->status && in_array($payment->status, ['failed', 'cancelled', 'refunded'], true)) {
            $loyalty->refundRedemption($payment);
        }

        $payment->parcel?->update([
            'payment_status' => match ($validated['status']) {
                'paid' => 'paid',
                'pending' => 'pending',
                'refunded' => 'refunded',
                default => 'unpaid',
            },
        ]);

        return back()->with('success', 'Payment status updated.');
    }
}
