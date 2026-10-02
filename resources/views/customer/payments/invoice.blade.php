<x-village-link-layout title="Invoice" header="Invoice">
    <div class="mx-auto max-w-3xl">
        <div class="vl-panel" id="invoice">
            <div class="flex flex-col gap-4 border-b border-white/10 pb-6 md:flex-row md:items-start md:justify-between">
                <div>
                    <p class="font-display text-3xl font-bold text-vl-peach">{{ __('vl.site_name') }}</p>
                    <p class="mt-1 text-sm text-white/60">Smart Courier & Parcel Tracking</p>
                </div>
                @if ($payment?->status === 'paid')
                    <span class="w-fit rounded-full border border-emerald-300/40 bg-emerald-400/15 px-3 py-1 text-sm font-semibold text-emerald-100">PAID</span>
                @elseif ($payment)
                    <span class="w-fit rounded-full border border-amber-300/40 bg-amber-400/15 px-3 py-1 text-sm font-semibold text-amber-100">{{ strtoupper($payment->status) }}</span>
                @else
                    <span class="w-fit rounded-full border border-amber-300/40 bg-amber-400/15 px-3 py-1 text-sm font-semibold text-amber-100">UNPAID</span>
                @endif
            </div>

            <dl class="my-6 grid gap-4 text-sm md:grid-cols-2">
                <div class="vl-mini-card"><dt class="text-white/60">Tracking ID</dt><dd class="mt-1 font-mono font-semibold text-vl-peach">{{ $parcel->tracking_id }}</dd></div>
                <div class="vl-mini-card"><dt class="text-white/60">Date</dt><dd class="mt-1">{{ $payment?->created_at?->format('M d, Y') ?? now()->format('M d, Y') }}</dd></div>
                <div class="vl-mini-card"><dt class="text-white/60">Receiver</dt><dd class="mt-1">{{ $parcel->receiver_name }}</dd></div>
                <div class="vl-mini-card"><dt class="text-white/60">Weight</dt><dd class="mt-1">{{ $parcel->weight }} kg</dd></div>
                @if (($parcel->chargeable_weight ?? 0) > 0)
                    <div class="vl-mini-card"><dt class="text-white/60">Chargeable Weight</dt><dd class="mt-1">{{ $parcel->chargeable_weight }} kg</dd></div>
                @endif
                <div class="vl-mini-card"><dt class="text-white/60">Loyalty Points</dt><dd class="mt-1">{{ $payment?->loyalty_points_redeemed ?? 0 }} redeemed / {{ $payment?->loyalty_points_awarded ?? 0 }} earned</dd></div>
            </dl>

            <div class="overflow-hidden rounded-2xl border border-white/10">
                <table class="w-full text-sm">
                    <tr class="border-b border-white/10"><td class="px-4 py-3">Base charge</td><td class="px-4 py-3 text-right">Rs. 300.00</td></tr>
                    <tr class="border-b border-white/10"><td class="px-4 py-3">Weight ({{ $parcel->weight }} kg x Rs. 150)</td><td class="px-4 py-3 text-right">Rs. {{ number_format($parcel->weight * 150, 2) }}</td></tr>
                    @if (($payment?->loyalty_discount ?? 0) > 0)
                        <tr class="border-b border-white/10"><td class="px-4 py-3">Loyalty discount</td><td class="px-4 py-3 text-right text-emerald-200">- Rs. {{ number_format($payment->loyalty_discount, 2) }}</td></tr>
                    @endif
                    <tr class="text-lg font-bold"><td class="px-4 py-4">Total</td><td class="px-4 py-4 text-right text-vl-peach">Rs. {{ number_format($payment?->amount ?? $parcel->price, 2) }}</td></tr>
                </table>
            </div>

            @if ($payment)
                <p class="mt-5 text-sm text-white/70">
                    Payment method: {{ ucfirst(str_replace('_', ' ', $payment->method)) }}
                    @if ($payment->reference)
                        <span class="ml-2 font-mono text-vl-peach">{{ $payment->reference }}</span>
                    @endif
                </p>
            @endif

            <div class="mt-6 flex flex-wrap gap-3">
                <a href="{{ route('customer.payments.invoice.pdf', $parcel) }}" target="_blank" class="vl-btn-primary">
                    <i data-lucide="download" class="vl-icon"></i>
                    Download PDF
                </a>
                <button type="button" onclick="window.print()" class="vl-btn-outline">
                    <i data-lucide="printer" class="vl-icon"></i>
                    Print
                </button>
                <a href="{{ route('customer.refunds.create', $parcel) }}" class="vl-btn-outline">
                    <i data-lucide="refresh-cw" class="vl-icon"></i>
                    Request Refund
                </a>
            </div>
        </div>
    </div>
</x-village-link-layout>
