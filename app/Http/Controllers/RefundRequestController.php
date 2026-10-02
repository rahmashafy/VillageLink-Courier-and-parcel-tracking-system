<?php

namespace App\Http\Controllers;

use App\Models\Parcel;
use App\Models\RefundRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RefundRequestController extends Controller
{
    public function customerIndex()
    {
        $requests = RefundRequest::with('parcel.payment')
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        return view('customer.refunds.index', compact('requests'));
    }

    public function create(Parcel $parcel)
    {
        if ($parcel->user_id !== auth()->id()) {
            abort(403);
        }

        $parcel->load('payment');

        $existing = RefundRequest::where('parcel_id', $parcel->id)
            ->where('user_id', auth()->id())
            ->where('status', 'pending')
            ->first();

        return view('customer.refunds.create', compact('parcel', 'existing'));
    }

    public function store(Request $request, Parcel $parcel)
    {
        if ($parcel->user_id !== auth()->id()) {
            abort(403);
        }

        if (RefundRequest::where('parcel_id', $parcel->id)->where('user_id', auth()->id())->where('status', 'pending')->exists()) {
            return back()->withErrors(['reason' => 'You already have a pending request for this parcel.']);
        }

        $validated = $request->validate([
            'type' => ['required', Rule::in(['refund', 'cancel'])],
            'reason' => ['required', 'string', 'min:10', 'max:1000'],
        ]);

        $payment = $parcel->payment;
        $amount = $payment?->amount ?? $parcel->price;

        RefundRequest::create([
            'user_id' => auth()->id(),
            'parcel_id' => $parcel->id,
            'payment_id' => $payment?->id,
            'type' => $validated['type'],
            'requested_amount' => $amount,
            'reason' => $validated['reason'],
            'status' => 'pending',
        ]);

        return redirect()->route('customer.refunds.index')
            ->with('success', 'Request submitted. Admin will review it soon.');
    }

    public function adminIndex()
    {
        $requests = RefundRequest::with('user', 'parcel', 'payment')
            ->latest()
            ->get();

        return view('admin.refunds.index', compact('requests'));
    }

    public function adminUpdate(Request $request, RefundRequest $refundRequest)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['approved', 'rejected'])],
            'approved_amount' => ['nullable', 'numeric', 'min:0'],
            'admin_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $approved = $validated['status'] === 'approved';
        $amount = $approved ? ($validated['approved_amount'] ?? $refundRequest->requested_amount) : null;

        $refundRequest->update([
            'status' => $validated['status'],
            'approved_amount' => $amount,
            'admin_notes' => $validated['admin_notes'] ?? null,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        if ($approved && $refundRequest->payment) {
            $refundRequest->payment->update([
                'status' => 'refunded',
                'failure_reason' => $validated['admin_notes'] ?? 'Refund approved by admin.',
            ]);
            $refundRequest->parcel?->update(['payment_status' => 'refunded']);
        }

        if ($approved && $refundRequest->type === 'cancel' && $refundRequest->parcel?->status !== 'delivered') {
            $refundRequest->parcel->update(['status' => 'cancelled']);
        }

        return back()->with('success', 'Request updated successfully.');
    }
}
