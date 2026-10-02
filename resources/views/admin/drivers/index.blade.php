<x-village-link-layout title="Drivers" header="Driver Management">
    <div class="vl-table-panel">
        <div class="overflow-x-auto">
            <table class="vl-table">
                <thead>
                    <tr>
                        <th>Driver</th>
                        <th>Vehicle</th>
                        <th>Availability</th>
                        <th>Active</th>
                        <th>Completed</th>
                        <th>Score</th>
                        <th>Rating</th>
                        <th>On Time</th>
                        <th>Last Location</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($drivers as $driver)
                        <tr>
                            <td>
                                <p class="font-medium">{{ $driver->name }}</p>
                                <p class="text-xs text-white/50">{{ $driver->email }}</p>
                            </td>
                            <td>{{ $driver->driverProfile?->vehicle_number ?? '-' }}<br><span class="text-xs text-white/50">{{ $driver->driverProfile?->vehicle_type }}</span></td>
                            <td>{{ ucfirst($driver->driverProfile?->availability_status ?? 'available') }}</td>
                            <td>{{ $driver->active_deliveries_count }}</td>
                            <td>{{ $driver->completed_deliveries_count }}</td>
                            <td>
                                <span class="inline-flex rounded-full border border-vl-peach/40 bg-vl-accent/15 px-3 py-1 text-xs font-bold text-vl-peach">
                                    {{ $driver->performance_score }}%
                                </span>
                            </td>
                            <td>{{ $driver->performance_rating ? $driver->performance_rating.'/5' : 'No ratings' }}<br><span class="text-xs text-white/50">{{ $driver->performance_complaints }} complaint(s)</span></td>
                            <td>{{ $driver->performance_on_time }}%</td>
                            <td class="text-xs">
                                @if ($driver->driverProfile?->current_lat)
                                    {{ $driver->driverProfile->current_lat }}, {{ $driver->driverProfile->current_lng }}<br>
                                    <span class="text-white/50">{{ $driver->driverProfile->last_location_at?->diffForHumans() }}</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td><a href="{{ route('admin.drivers.edit', $driver) }}" class="vl-action-link">Edit</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="10" class="py-10 text-center text-white/60">No drivers found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-village-link-layout>
