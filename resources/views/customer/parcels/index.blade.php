<x-village-link-layout title="My Parcels" header="My Parcels">
    <div class="mb-5 flex justify-end">
        <a href="{{ route('customer.parcels.create') }}" class="vl-btn-primary">
            <i data-lucide="package-plus" class="vl-icon"></i>
            Book Parcel
        </a>
    </div>

    <div class="vl-table-panel">
        <div class="overflow-x-auto">
            <table class="vl-table">
                <thead>
                    <tr>
                        <th>Tracking ID</th>
                        <th>Receiver</th>
                        <th>Status</th>
                        <th>Price</th>
                        <th>Payment</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($parcels as $parcel)
                        <tr>
                            <td class="font-mono text-vl-peach">{{ $parcel->tracking_id }}</td>
                            <td>{{ $parcel->receiver_name }}</td>
                            <td><x-vl-status-badge :status="$parcel->status" /></td>
                            <td>Rs. {{ number_format($parcel->price, 2) }}</td>
                            <td>
                                <span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold {{ ($parcel->payment_status ?? 'unpaid') === 'paid' ? 'border-emerald-300/40 bg-emerald-400/15 text-emerald-100' : 'border-amber-300/40 bg-amber-400/15 text-amber-100' }}">
                                    {{ ucfirst($parcel->payment_status ?? 'unpaid') }}
                                </span>
                            </td>
                            <td>
                                <div class="flex flex-wrap gap-2">
                                    <a href="{{ route('customer.parcels.show', $parcel) }}" class="vl-action-link">Track</a>
                                    @if (($parcel->payment_status ?? 'unpaid') === 'unpaid')
                                        <a href="{{ route('customer.payments.pay', $parcel) }}" class="vl-action-link">Pay</a>
                                    @else
                                        <a href="{{ route('customer.payments.invoice', $parcel) }}" class="vl-action-link">Invoice</a>
                                    @endif
                                    <a href="{{ route('customer.refunds.create', $parcel) }}" class="vl-action-link">Request</a>
                                    @if ($parcel->status === 'delivered')
                                        <a href="{{ route('customer.ratings.create', $parcel) }}" class="vl-action-link">Rate</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-10 text-center text-white/60">No parcels found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-village-link-layout>
