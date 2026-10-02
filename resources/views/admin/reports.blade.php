<x-village-link-layout title="Reports" header="Reports & Analytics">
    <form method="GET" action="{{ route('admin.reports') }}" class="vl-panel mb-6 grid gap-4 md:grid-cols-5">
        <div><label class="mb-1 block text-xs text-white/60">From</label><input type="date" name="from" value="{{ request('from', $from->format('Y-m-d')) }}" class="vl-input"></div>
        <div><label class="mb-1 block text-xs text-white/60">To</label><input type="date" name="to" value="{{ request('to', $to->format('Y-m-d')) }}" class="vl-input"></div>
        <div>
            <label class="mb-1 block text-xs text-white/60">Status</label>
            <select name="status" class="vl-input">
                <option value="">All</option>
                @foreach (['pending_pickup', 'picked_up', 'in_transit', 'arrived_center', 'out_for_delivery', 'delivered'] as $item)
                    <option value="{{ $item }}" @selected($status === $item)>{{ ucfirst(str_replace('_', ' ', $item)) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-1 block text-xs text-white/60">Driver</label>
            <select name="driver_id" class="vl-input">
                <option value="">All</option>
                @foreach ($drivers as $driver)
                    <option value="{{ $driver->id }}" @selected((string) $driverId === (string) $driver->id)>{{ $driver->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-end gap-2">
            <button class="vl-btn-primary flex-1">Filter</button>
            <a href="{{ route('admin.reports.export', request()->query()) }}" class="vl-btn-outline">CSV</a>
        </div>
    </form>

    <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-5">
        <x-vl-stat-card label="Total Parcels" :value="$totalParcels" icon="package" />
        <x-vl-stat-card label="Delivered" :value="$deliveredParcels" icon="package-check" />
        <x-vl-stat-card label="Paid Revenue" :value="'Rs. '.number_format($revenue)" icon="wallet-cards" />
        <x-vl-stat-card label="Pending Pay" :value="$pendingPayments" icon="clock-3" />
        <x-vl-stat-card label="Open Complaints" :value="$openComplaints" icon="shield-alert" />
    </div>

    <div class="vl-table-panel mt-6">
        <div class="overflow-x-auto">
            <table class="vl-table">
                <thead>
                    <tr>
                        <th>Tracking</th>
                        <th>Customer</th>
                        <th>Driver</th>
                        <th>Status</th>
                        <th>Payment</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($parcels as $parcel)
                        <tr>
                            <td class="font-mono text-vl-peach">{{ $parcel->tracking_id }}</td>
                            <td>{{ $parcel->user?->name }}</td>
                            <td>{{ $parcel->agent?->name ?? '-' }}</td>
                            <td><x-vl-status-badge :status="$parcel->status" /></td>
                            <td>{{ ucfirst($parcel->payment_status ?? 'unpaid') }}</td>
                            <td>Rs. {{ number_format($parcel->price, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-10 text-center text-white/60">No report data for selected filters.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-village-link-layout>
