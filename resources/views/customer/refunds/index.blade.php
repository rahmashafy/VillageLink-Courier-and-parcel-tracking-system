<x-village-link-layout title="Refunds" header="Refund / Cancel Requests">
    <div class="vl-table-panel">
        <div class="flex items-center justify-between border-b border-white/10 px-6 py-4">
            <div>
                <p class="text-xs uppercase tracking-[0.22em] text-white/50">Customer support</p>
                <h2 class="mt-1 font-display text-lg font-semibold text-vl-cream">My Requests</h2>
            </div>
            <a href="{{ route('customer.parcels.index') }}" class="vl-muted-link">Select parcel</a>
        </div>
        <div class="overflow-x-auto">
            <table class="vl-table">
                <thead>
                    <tr>
                        <th>Tracking</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Reason</th>
                        <th>Admin Note</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($requests as $request)
                        <tr>
                            <td class="font-mono text-vl-peach">{{ $request->parcel?->tracking_id }}</td>
                            <td>{{ ucfirst($request->type) }}</td>
                            <td>Rs. {{ number_format($request->requested_amount, 2) }}</td>
                            <td><x-vl-status-badge :status="$request->status" /></td>
                            <td class="max-w-xs">{{ $request->reason }}</td>
                            <td class="max-w-xs text-white/65">{{ $request->admin_notes ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-white/60">
                                No refund or cancellation requests yet. Open My Parcels and choose a parcel to request one.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-village-link-layout>
