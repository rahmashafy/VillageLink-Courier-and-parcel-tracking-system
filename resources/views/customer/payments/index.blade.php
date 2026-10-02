<x-village-link-layout title="Payments" header="Payment History">
    <div class="vl-table-panel">
        <div class="overflow-x-auto">
            <table class="vl-table">
                <thead>
                    <tr>
                        <th>Tracking ID</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Invoice</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($payments as $payment)
                        <tr>
                            <td class="font-mono text-vl-peach">{{ $payment->parcel->tracking_id ?? '-' }}</td>
                            <td>
                                Rs. {{ number_format($payment->amount, 2) }}
                                @if (($payment->loyalty_discount ?? 0) > 0)
                                    <p class="text-xs text-emerald-200">Saved Rs. {{ number_format($payment->loyalty_discount, 2) }}</p>
                                @endif
                            </td>
                            <td>{{ ucfirst(str_replace('_', ' ', $payment->method)) }}</td>
                            <td><x-vl-status-badge :status="$payment->status" /></td>
                            <td>{{ $payment->created_at->format('M d, Y') }}</td>
                            <td>
                                @if ($payment->parcel)
                                    <a href="{{ route('customer.payments.invoice', $payment->parcel) }}" class="vl-action-link">View</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-10 text-center text-white/60">No payments yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-village-link-layout>
