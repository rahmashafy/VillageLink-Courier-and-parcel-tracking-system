<x-village-link-layout title="My Deliveries" header="My Deliveries">
    <div class="mb-6 grid gap-4 md:grid-cols-3">
        <div class="vl-mini-card">
            <p class="text-xs uppercase tracking-[0.2em] text-white/45">Logged in driver</p>
            <p class="mt-2 font-display text-xl font-semibold text-vl-cream">{{ $driver->name }}</p>
            <p class="mt-1 text-sm text-white/55">{{ $driver->email }}</p>
        </div>
        <div class="vl-mini-card">
            <p class="text-xs uppercase tracking-[0.2em] text-white/45">Assigned to you</p>
            <p class="mt-2 text-3xl font-bold text-vl-peach">{{ $parcels->count() }}</p>
            <p class="mt-1 text-sm text-white/55">Only admin-assigned parcels show here.</p>
        </div>
        <div class="vl-mini-card">
            <p class="text-xs uppercase tracking-[0.2em] text-white/45">Current work</p>
            <p class="mt-2 text-3xl font-bold text-vl-peach">{{ $activeCount }}</p>
            <p class="mt-1 text-sm text-white/55">{{ $completedCount }} completed delivery/deliveries.</p>
        </div>
    </div>

    <div class="mb-6 rounded-2xl border border-vl-peach/25 bg-vl-peach/10 p-4 text-sm text-white/70">
        New bookings will appear on this page only after the admin assigns that parcel to
        <span class="font-semibold text-vl-peach">{{ $driver->name }}</span>.
        If a parcel is assigned to another driver, it will be visible only in that driver's workspace.
    </div>

    <div class="vl-table-panel">
        <div class="overflow-x-auto">
            <table class="vl-table">
                <thead>
                    <tr>
                        <th>Tracking ID</th>
                        <th>Receiver</th>
                        <th>Address</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($parcels as $parcel)
                        <tr>
                            <td class="font-mono text-vl-peach">{{ $parcel->tracking_id }}</td>
                            <td>{{ $parcel->receiver_name }}</td>
                            <td class="max-w-xs truncate">{{ $parcel->delivery_address }}</td>
                            <td><x-vl-status-badge :status="$parcel->status" /></td>
                            <td>
                                <a href="{{ route('agent.parcels.status', $parcel) }}" class="vl-action-link">
                                    <i data-lucide="refresh-cw" class="vl-icon"></i>
                                    Update
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-10 text-center text-white/60">No deliveries assigned yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-village-link-layout>
