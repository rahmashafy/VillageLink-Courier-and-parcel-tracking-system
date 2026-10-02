<x-village-link-layout title="All Parcels" header="All Parcels">
    <div class="vl-table-panel">
        <div class="overflow-x-auto">
            <table class="vl-table">
                <thead>
                    <tr>
                        <th>Tracking ID</th>
                        <th>Customer</th>
                        <th>Receiver</th>
                        <th>Status</th>
                        <th>Price</th>
                        <th>Driver</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($parcels as $parcel)
                        <tr>
                            <td class="font-mono text-vl-peach">{{ $parcel->tracking_id }}</td>
                            <td>{{ $parcel->user->name ?? 'N/A' }}</td>
                            <td>{{ $parcel->receiver_name }}</td>
                            <td><x-vl-status-badge :status="$parcel->status" /></td>
                            <td>Rs. {{ number_format($parcel->price, 2) }}</td>
                            <td>{{ $parcel->agent->name ?? 'Not assigned' }}</td>
                            <td>
                                <a href="{{ route('admin.parcels.assign', $parcel) }}" class="vl-action-link">
                                    <i data-lucide="user-plus" class="vl-icon"></i>
                                    Assign Driver
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-10 text-center text-white/60">No parcels found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-village-link-layout>
