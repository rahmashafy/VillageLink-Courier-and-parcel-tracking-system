<x-village-link-layout title="Request Refund" header="Request - {{ $parcel->tracking_id }}">
    <div class="mx-auto grid max-w-4xl gap-6 md:grid-cols-[0.8fr_1.2fr]">
        <div class="vl-panel">
            <p class="text-xs uppercase tracking-[0.22em] text-white/50">Parcel</p>
            <h2 class="mt-2 font-mono text-2xl font-bold text-vl-peach">{{ $parcel->tracking_id }}</h2>
            <dl class="mt-5 space-y-3 text-sm">
                <div class="flex justify-between gap-4"><dt class="text-white/60">Status</dt><dd><x-vl-status-badge :status="$parcel->status" /></dd></div>
                <div class="flex justify-between gap-4"><dt class="text-white/60">Payment</dt><dd>{{ ucfirst($parcel->payment_status ?? 'unpaid') }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-white/60">Amount</dt><dd class="font-semibold text-vl-peach">Rs. {{ number_format($parcel->payment?->amount ?? $parcel->price, 2) }}</dd></div>
                <div class="border-t border-white/10 pt-3"><dt class="text-white/60">Receiver</dt><dd class="mt-1">{{ $parcel->receiver_name }}</dd></div>
            </dl>
        </div>

        <div class="vl-panel">
            @if ($existing)
                <div class="rounded-xl border border-amber-300/30 bg-amber-500/15 px-4 py-3 text-sm text-amber-100">
                    You already have a pending {{ $existing->type }} request for this parcel.
                </div>
            @else
                <form method="POST" action="{{ route('customer.refunds.store', $parcel) }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="mb-2 block text-sm text-white/80">Request Type</label>
                        <select name="type" required class="vl-input">
                            <option value="refund">Refund request</option>
                            @if ($parcel->status !== 'delivered')
                                <option value="cancel">Cancel parcel request</option>
                            @endif
                        </select>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm text-white/80">Reason</label>
                        <textarea name="reason" rows="5" required minlength="10" placeholder="Explain why you need refund or cancellation..." class="vl-input">{{ old('reason') }}</textarea>
                    </div>
                    @if ($errors->any())
                        <p class="rounded-xl border border-red-300/30 bg-red-500/15 px-4 py-3 text-sm text-red-100">{{ $errors->first() }}</p>
                    @endif
                    <button type="submit" class="vl-btn-primary w-full">
                        <i data-lucide="refresh-cw" class="vl-icon"></i>
                        Submit Request
                    </button>
                </form>
            @endif
        </div>
    </div>
</x-village-link-layout>
