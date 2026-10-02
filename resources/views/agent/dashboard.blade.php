<x-village-link-layout title="Driver Dashboard" header="Hello, {{ auth()->user()->name }}">
    @php
        $profile = $driver->driverProfile;
        $availability = $profile?->availability_status ?? 'available';
        $availabilityClass = $availability === 'available'
            ? 'border-emerald-300/40 bg-emerald-400/15 text-emerald-100'
            : ($availability === 'busy'
                ? 'border-amber-300/40 bg-amber-400/15 text-amber-100'
                : 'border-red-300/40 bg-red-400/15 text-red-100');
        $lastGps = $profile?->last_location_at?->diffForHumans() ?? 'Not shared yet';
        $shiftLabel = trim(($profile?->shift_start ?: '08:00').' - '.($profile?->shift_end ?: '18:00'));
        $nextStatusLabel = $nextStatus ? ucfirst(str_replace('_', ' ', $nextStatus)) : 'Waiting';
    @endphp

    <div class="grid gap-6 xl:grid-cols-[1.45fr_0.55fr]">
        <section class="vl-panel overflow-hidden">
            <div class="flex flex-col gap-4 border-b border-white/10 pb-5 md:flex-row md:items-start md:justify-between">
                <div>
                    <p class="text-xs uppercase tracking-[0.24em] text-white/50">Current Delivery</p>
                    <h2 class="mt-2 font-display text-2xl font-bold text-vl-cream">
                        @if ($activeJob)
                            {{ $activeJob->tracking_id }}
                        @else
                            Ready for next assignment
                        @endif
                    </h2>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-white/65">
                        @if ($activeJob)
                            {{ $nextAction }} for {{ $activeJob->receiver_name }}. Keep GPS updated so the customer can follow the delivery live.
                        @else
                            You have no active delivery right now. When admin assigns a parcel, it will appear here with the next action.
                        @endif
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center rounded-full border px-3 py-1.5 text-xs font-semibold {{ $availabilityClass }}">
                        {{ ucfirst($availability) }}
                    </span>
                    <a href="{{ route('driver.profile.edit') }}" class="vl-btn-outline py-2 text-xs">
                        <i data-lucide="user-round" class="vl-icon"></i>
                        Profile
                    </a>
                </div>
            </div>

            @if ($activeJob)
                <div class="mt-6 grid gap-6 lg:grid-cols-[1.05fr_0.95fr]">
                    <div class="space-y-4">
                        <div class="grid gap-3 sm:grid-cols-2">
                            <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                                <p class="text-xs uppercase tracking-[0.18em] text-white/45">Receiver</p>
                                <p class="mt-2 font-semibold text-white">{{ $activeJob->receiver_name }}</p>
                                <p class="mt-1 text-sm text-white/60">{{ $activeJob->receiver_phone }}</p>
                            </div>
                            <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                                <p class="text-xs uppercase tracking-[0.18em] text-white/45">ETA</p>
                                <p class="mt-2 font-semibold text-white">
                                    {{ $activeJob->estimated_delivery_at?->format('M d, g:i A') ?? 'Not scheduled' }}
                                </p>
                                <p class="mt-1 text-sm text-white/60">{{ ucfirst(str_replace('_', ' ', $activeJob->delivery_type ?? 'standard')) }}</p>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-xs uppercase tracking-[0.18em] text-white/45">Next Driver Action</p>
                                    <p class="mt-2 font-display text-xl font-semibold text-vl-peach">{{ $nextStatusLabel }}</p>
                                </div>
                                <x-vl-status-badge :status="$activeJob->status" />
                            </div>

                            <div class="mt-5 flex flex-col gap-3">
                                @foreach ($routeStops as $stop)
                                    <div class="flex gap-3">
                                        <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full border {{ $stop['done'] ? 'border-emerald-300/40 bg-emerald-400/15 text-emerald-100' : 'border-white/15 bg-white/5 text-white/60' }}">
                                            <i data-lucide="{{ $stop['done'] ? 'check' : 'map-pin' }}" class="h-4 w-4"></i>
                                        </span>
                                        <div>
                                            <p class="text-sm font-semibold text-white">{{ $stop['label'] }}</p>
                                            <p class="text-sm leading-6 text-white/60">{{ $stop['address'] }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col justify-between rounded-2xl border border-vl-peach/25 bg-vl-peach/10 p-5">
                        <div>
                            <p class="text-xs uppercase tracking-[0.18em] text-vl-peach">Live Operations</p>
                            <h3 class="mt-2 font-display text-xl font-semibold text-white">Customer tracking depends on your GPS</h3>
                            <dl class="mt-4 grid gap-3 text-sm">
                                <div class="flex items-center justify-between gap-3 border-b border-white/10 pb-3">
                                    <dt class="text-white/60">Last GPS</dt>
                                    <dd class="font-medium text-white">{{ $lastGps }}</dd>
                                </div>
                                <div class="flex items-center justify-between gap-3 border-b border-white/10 pb-3">
                                    <dt class="text-white/60">Vehicle</dt>
                                    <dd class="font-medium text-white">{{ $profile?->vehicle_number ?? 'Not set' }}</dd>
                                </div>
                                <div class="flex items-center justify-between gap-3">
                                    <dt class="text-white/60">COD / Fee</dt>
                                    <dd class="font-medium text-white">Rs. {{ number_format((float) $activeJob->price, 2) }}</dd>
                                </div>
                            </dl>
                        </div>

                        <div class="mt-6 grid gap-3 sm:grid-cols-2">
                            <a href="{{ route('agent.parcels.status', $activeJob) }}" class="vl-btn-primary justify-center">
                                <i data-lucide="refresh-cw" class="vl-icon"></i>
                                Update Job
                            </a>
                            <a href="tel:{{ $activeJob->receiver_phone }}" class="vl-btn-outline justify-center">
                                <i data-lucide="phone" class="vl-icon"></i>
                                Call Receiver
                            </a>
                        </div>
                    </div>
                </div>
            @else
                <div class="mt-8 rounded-2xl border border-dashed border-white/15 bg-white/5 p-8 text-center">
                    <i data-lucide="truck" class="mx-auto h-12 w-12 text-vl-peach"></i>
                    <h3 class="mt-4 font-display text-xl font-semibold text-white">No active delivery</h3>
                    <p class="mx-auto mt-2 max-w-xl text-sm leading-6 text-white/60">
                        Stay available and keep your profile updated. Admin can assign only free drivers, so active jobs will not overlap.
                    </p>
                    <a href="{{ route('agent.parcels.index') }}" class="vl-btn-primary mt-5">
                        <i data-lucide="clipboard-list" class="vl-icon"></i>
                        View Deliveries
                    </a>
                </div>
            @endif
        </section>

        <aside class="space-y-6">
            <div class="vl-panel">
                <p class="text-xs uppercase tracking-[0.22em] text-white/50">Driver Status</p>
                <div class="mt-4 space-y-4 text-sm">
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-white/60">Shift</span>
                        <span class="font-semibold text-white">{{ $shiftLabel }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-white/60">Phone</span>
                        <span class="font-semibold text-white">{{ $profile?->phone ?? $driver->phone ?? 'Not set' }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-white/60">Vehicle</span>
                        <span class="font-semibold text-white">{{ $profile?->vehicle_type ?? 'Not set' }}</span>
                    </div>
                </div>
            </div>

            <div class="vl-panel">
                <p class="text-xs uppercase tracking-[0.22em] text-white/50">Performance</p>
                <div class="mt-4 flex items-end justify-between">
                    <div>
                        <p class="font-display text-4xl font-bold text-vl-peach">{{ $performanceScore }}%</p>
                        <p class="mt-1 text-sm text-white/60">Driver score</p>
                    </div>
                    <i data-lucide="shield-check" class="h-10 w-10 text-vl-peach"></i>
                </div>
                <div class="mt-5 grid grid-cols-3 gap-3 text-center text-xs">
                    <div class="rounded-xl border border-white/10 bg-white/5 p-3">
                        <p class="font-semibold text-white">{{ $avgRating ? number_format($avgRating, 1) : '5.0' }}</p>
                        <p class="mt-1 text-white/50">Rating</p>
                    </div>
                    <div class="rounded-xl border border-white/10 bg-white/5 p-3">
                        <p class="font-semibold text-white">{{ $onTimeRate }}%</p>
                        <p class="mt-1 text-white/50">On time</p>
                    </div>
                    <div class="rounded-xl border border-white/10 bg-white/5 p-3">
                        <p class="font-semibold text-white">{{ $complaints }}</p>
                        <p class="mt-1 text-white/50">Issues</p>
                    </div>
                </div>
            </div>

            <div class="vl-panel">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs uppercase tracking-[0.22em] text-white/50">Route Readiness</p>
                        <h3 class="mt-1 font-display text-lg font-semibold text-vl-cream">Premium driver checklist</h3>
                    </div>
                    <i data-lucide="route" class="h-6 w-6 text-vl-peach"></i>
                </div>
                <div class="mt-5 space-y-3">
                    @foreach ($routeReadiness as $item)
                        <div class="flex items-start gap-3 border-b border-white/10 pb-3 last:border-b-0 last:pb-0">
                            <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full border {{ $item['ready'] ? 'border-emerald-300/40 bg-emerald-400/15 text-emerald-100' : 'border-amber-300/40 bg-amber-400/15 text-amber-100' }}">
                                <i data-lucide="{{ $item['ready'] ? 'check' : 'alert-triangle' }}" class="h-4 w-4"></i>
                            </span>
                            <div>
                                <p class="text-sm font-semibold text-white">{{ $item['label'] }}</p>
                                <p class="mt-1 text-xs text-white/50">{{ $item['hint'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-5 grid grid-cols-2 gap-3 text-center text-xs">
                    <div class="rounded-xl border border-white/10 bg-white/5 p-3">
                        <p class="font-semibold text-vl-peach">Rs. {{ number_format((float) $weeklyEarnings, 0) }}</p>
                        <p class="mt-1 text-white/50">Week earnings</p>
                    </div>
                    <div class="rounded-xl border border-white/10 bg-white/5 p-3">
                        <p class="font-semibold text-white">{{ $completedThisWeek }}</p>
                        <p class="mt-1 text-white/50">Week done</p>
                    </div>
                </div>
            </div>
        </aside>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
        <x-vl-stat-card label="Assigned Jobs" :value="$assigned" icon="clipboard-list" />
        <x-vl-stat-card label="Active Now" :value="$active" icon="radar" />
        <x-vl-stat-card label="Delivered Today" :value="$deliveredToday" icon="package-check" />
        <x-vl-stat-card label="Today Earnings" :value="'Rs. '.number_format((float) $earningsToday, 0)" icon="banknote" />
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-[1.35fr_0.65fr]">
        <div class="vl-table-panel">
            <div class="flex items-center justify-between border-b border-white/10 px-6 py-4">
                <div>
                    <h2 class="font-display text-lg font-semibold">Today's Job Queue</h2>
                    <p class="mt-1 text-sm text-white/55">Pickup, transit, delivery and proof workflow.</p>
                </div>
                <a href="{{ route('agent.parcels.index') }}" class="vl-muted-link">View all</a>
            </div>
            <div class="overflow-x-auto">
                <table class="vl-table">
                    <thead>
                        <tr>
                            <th>Tracking ID</th>
                            <th>Customer</th>
                            <th>Receiver</th>
                            <th>Route</th>
                            <th>Status</th>
                            <th>ETA</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($todayDeliveries as $parcel)
                            <tr>
                                <td class="font-mono text-vl-peach">{{ $parcel->tracking_id }}</td>
                                <td>{{ $parcel->user?->name ?? 'Customer' }}</td>
                                <td>
                                    <p class="font-medium">{{ $parcel->receiver_name }}</p>
                                    <p class="text-xs text-white/50">{{ $parcel->receiver_phone }}</p>
                                </td>
                                <td class="max-w-xs">
                                    <p class="truncate text-xs text-white/50">From: {{ $parcel->pickup_location ?: $parcel->pickup_address }}</p>
                                    <p class="truncate text-xs text-white/70">To: {{ $parcel->delivery_location ?: $parcel->delivery_address }}</p>
                                </td>
                                <td><x-vl-status-badge :status="$parcel->status" /></td>
                                <td>{{ $parcel->estimated_delivery_at?->format('M d, g:i A') ?? '-' }}</td>
                                <td>
                                    <a href="{{ route('agent.parcels.status', $parcel) }}" class="vl-action-link">
                                        <i data-lucide="refresh-cw" class="vl-icon"></i>
                                        Work
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="py-10 text-center text-white/60">No jobs assigned yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="vl-panel">
            <p class="text-xs uppercase tracking-[0.22em] text-white/50">Workload</p>
            <h3 class="mt-1 font-display text-lg font-semibold text-vl-cream">Delivery status split</h3>
            <div class="mt-5 h-64">
                <canvas id="agentStatusChart"></canvas>
            </div>
            <div class="mt-5 space-y-3 text-sm">
                <div class="flex items-center justify-between">
                    <span class="text-white/60">Pending pickup</span>
                    <span class="font-semibold text-white">{{ $pending }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-white/60">In progress</span>
                    <span class="font-semibold text-white">{{ $pickedUp }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-white/60">All delivered</span>
                    <span class="font-semibold text-white">{{ $delivered }}</span>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const ctx = document.getElementById('agentStatusChart');
                if (!ctx || !window.Chart) return;

                new window.Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: @json($chartLabels),
                        datasets: [{
                            data: @json($chartData),
                            backgroundColor: ['#F0B35A', '#62B3A8', '#E55634'],
                            borderColor: 'rgba(8,19,24,0.9)',
                            borderWidth: 4,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '68%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { color: 'rgba(255,255,255,0.72)', boxWidth: 10, usePointStyle: true },
                            },
                        },
                    },
                });
            });
        </script>
    @endpush
</x-village-link-layout>
