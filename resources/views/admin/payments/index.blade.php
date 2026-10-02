<x-village-link-layout title="Payments" header="Payment Verification">
    <div class="vl-table-panel">
        <div class="overflow-x-auto">
            <table class="vl-table">
                <thead>
                    <tr>
                        <th>Tracking</th>
                        <th>Customer</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Reference</th>
                        <th>Status</th>
                        <th>Update</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($payments as $payment)
                        <tr class="align-top">
                            <td class="font-mono text-vl-peach">{{ $payment->parcel?->tracking_id ?? '-' }}</td>
                            <td>{{ $payment->parcel?->user?->name ?? '-' }}</td>
                            <td>{{ $payment->currency ?? 'LKR' }} {{ number_format($payment->amount, 2) }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $payment->method)) }}<br><span class="text-xs text-white/50">{{ ucfirst($payment->provider ?? 'manual') }}</span></td>
                            <td class="font-mono text-xs">{{ $payment->reference ?? $payment->gateway_order_id ?? '-' }}</td>
                            <td><x-vl-status-badge :status="$payment->status" /></td>
                            <td>
                                <form method="POST" action="{{ route('admin.payments.update', $payment) }}" class="flex flex-wrap gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" class="rounded-lg border border-white/15 bg-white/10 px-3 py-2 text-xs">
                                        @foreach (['pending', 'paid', 'failed', 'cancelled', 'refunded'] as $status)
                                            <option value="{{ $status }}" @selected($payment->status === $status)>{{ ucfirst($status) }}</option>
                                        @endforeach
                                    </select>
                                    <input name="failure_reason" placeholder="Reason" class="rounded-lg border border-white/15 bg-white/10 px-3 py-2 text-xs">
                                    <button type="submit" class="vl-action-link">Save</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-10 text-center text-white/60">No payments found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-village-link-layout>
