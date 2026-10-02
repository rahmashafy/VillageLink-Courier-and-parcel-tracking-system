<x-village-link-layout title="Admin Dashboard" header="Admin Overview">
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
        <x-vl-stat-card label="Total Parcels" :value="$totalParcels" icon="package" />
        <x-vl-stat-card label="Delivered Today" :value="$deliveredToday" icon="package-check" />
        <x-vl-stat-card label="Revenue Today" :value="'Rs. '.number_format($revenueToday)" icon="wallet-cards" />
        <x-vl-stat-card label="Pending Parcels" :value="$pendingParcels" icon="clock-3" />
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-[1.15fr_0.85fr]">
        <section class="vl-panel overflow-hidden">
            <div class="flex flex-col gap-4 border-b border-white/10 pb-5 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-xs uppercase tracking-[0.24em] text-white/50">Premium Control Tower</p>
                    <h2 class="mt-1 font-display text-2xl font-bold text-vl-cream">Live Operations Intelligence</h2>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-white/60">
                        Smart SLA, dispatch queue, driver capacity and revenue forecast for real courier operations.
                    </p>
                </div>
                <div class="rounded-2xl border border-vl-peach/30 bg-vl-peach/10 px-5 py-3 text-center">
                    <p class="text-xs uppercase tracking-[0.18em] text-white/50">SLA Health</p>
                    <p class="mt-1 font-display text-3xl font-bold text-vl-peach">{{ $slaScore }}%</p>
                </div>
            </div>

            <div class="mt-5 grid gap-4 md:grid-cols-4">
                <div class="border-r border-white/10 pr-4 last:border-r-0">
                    <p class="text-xs text-white/50">Active parcels</p>
                    <p class="mt-2 text-2xl font-bold text-white">{{ $activeParcels }}</p>
                    <p class="mt-1 text-xs text-white/45">Live delivery load</p>
                </div>
                <div class="border-r border-white/10 pr-4 last:border-r-0">
                    <p class="text-xs text-white/50">Need assignment</p>
                    <p class="mt-2 text-2xl font-bold text-amber-200">{{ $unassignedParcels }}</p>
                    <p class="mt-1 text-xs text-white/45">Waiting for driver</p>
                </div>
                <div class="border-r border-white/10 pr-4 last:border-r-0">
                    <p class="text-xs text-white/50">Drivers free / busy</p>
                    <p class="mt-2 text-2xl font-bold text-emerald-200">{{ $availableDrivers }} / {{ $busyDrivers }}</p>
                    <p class="mt-1 text-xs text-white/45">Capacity signal</p>
                </div>
                <div>
                    <p class="text-xs text-white/50">30-day forecast</p>
                    <p class="mt-2 text-2xl font-bold text-vl-peach">Rs. {{ number_format($revenueForecast) }}</p>
                    <p class="mt-1 text-xs text-white/45">{{ $collectionRate }}% collection rate</p>
                </div>
            </div>
        </section>

        <aside class="vl-panel">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs uppercase tracking-[0.22em] text-white/50">AI Dispatch Queue</p>
                    <h3 class="mt-1 font-display text-lg font-semibold text-vl-cream">Priority Assignments</h3>
                </div>
                <i data-lucide="sparkles" class="h-6 w-6 text-vl-peach"></i>
            </div>
            <div class="mt-5 space-y-4">
                @forelse ($dispatchQueue as $parcel)
                    <div class="border-b border-white/10 pb-4 last:border-b-0 last:pb-0">
                        <div class="flex items-center justify-between gap-3">
                            <p class="font-mono text-sm font-semibold text-vl-peach">{{ $parcel->tracking_id }}</p>
                            <span class="rounded-full border border-amber-300/30 bg-amber-400/10 px-2 py-1 text-[11px] font-semibold text-amber-100">
                                {{ ucfirst(str_replace('_', ' ', $parcel->delivery_type)) }}
                            </span>
                        </div>
                        <p class="mt-2 text-sm text-white">{{ $parcel->pickup_location ?: $parcel->pickup_address }} -> {{ $parcel->delivery_location ?: $parcel->delivery_address }}</p>
                        <p class="mt-1 text-xs text-white/50">Receiver: {{ $parcel->receiver_name }} | ETA {{ $parcel->estimated_delivery_at?->format('M d, g:i A') ?? 'planning' }}</p>
                    </div>
                @empty
                    <div class="rounded-2xl border border-emerald-300/20 bg-emerald-400/10 p-4 text-sm text-emerald-100">
                        All active parcels are already assigned. Dispatch board is clear.
                    </div>
                @endforelse
            </div>
        </aside>
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-2">
        <div class="vl-panel">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs uppercase tracking-[0.22em] text-white/50">Operations</p>
                    <h3 class="mt-1 font-display text-lg font-semibold text-vl-cream">Parcel Overview</h3>
                </div>
                <i data-lucide="chart-spline" class="h-6 w-6 text-vl-peach"></i>
            </div>
            <div class="mt-5 h-72">
                <canvas id="parcelChart"></canvas>
            </div>
        </div>
        <div class="vl-panel">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs uppercase tracking-[0.22em] text-white/50">Finance</p>
                    <h3 class="mt-1 font-display text-lg font-semibold text-vl-cream">Revenue Overview</h3>
                </div>
                <i data-lucide="chart-column" class="h-6 w-6 text-vl-peach"></i>
            </div>
            <div class="mt-5 h-72">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-2 gap-4 xl:grid-cols-4">
        <div class="vl-mini-card text-center"><p class="text-2xl font-bold text-emerald-200">{{ $deliveredParcels }}</p><p class="mt-1 text-xs text-white/60">Total Delivered</p></div>
        <div class="vl-mini-card text-center"><p class="text-2xl font-bold text-vl-peach">Rs. {{ number_format($revenue) }}</p><p class="mt-1 text-xs text-white/60">Total Revenue</p></div>
        <div class="vl-mini-card text-center"><p class="text-2xl font-bold text-amber-200">{{ $openComplaints }}</p><p class="mt-1 text-xs text-white/60">Open Complaints</p></div>
        <div class="vl-mini-card text-center"><p class="text-2xl font-bold text-white">{{ $totalParcels }}</p><p class="mt-1 text-xs text-white/60">All Parcels</p></div>
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-[1.25fr_0.75fr]">
        <div class="vl-table-panel">
            <div class="border-b border-white/10 px-6 py-4">
                <h3 class="font-display text-lg font-semibold">Recent Parcels</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="vl-table">
                    <thead>
                        <tr><th>ID</th><th>Customer</th><th>Receiver</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($recentParcels as $p)
                            <tr>
                                <td class="font-mono text-vl-peach">{{ $p->tracking_id }}</td>
                                <td>{{ $p->user->name ?? '-' }}</td>
                                <td>{{ $p->receiver_name }}</td>
                                <td><x-vl-status-badge :status="$p->status" /></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="py-8 text-center text-white/60">No parcels yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="vl-panel">
            <h3 class="font-display text-lg font-semibold">Top Drivers</h3>
            <ul class="mt-5 space-y-3">
                @forelse ($topAgents as $agent)
                    <li class="flex items-center justify-between gap-4 rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-sm">
                        <span class="truncate">{{ $agent->name }}</span>
                        <span class="shrink-0 font-semibold text-vl-peach">{{ $agent->deliveries_count }} delivered</span>
                    </li>
                @empty
                    <li class="text-sm text-white/60">No drivers yet.</li>
                @endforelse
            </ul>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                if (!window.Chart) return;
                const labels = @json($chartLabels);
                const baseOptions = {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { grid: { color: 'rgba(255,255,255,0.08)' }, ticks: { color: 'rgba(255,255,255,0.65)' } },
                        y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.08)' }, ticks: { color: 'rgba(255,255,255,0.65)' } },
                    },
                };

                new window.Chart(document.getElementById('parcelChart'), {
                    type: 'line',
                    data: {
                        labels,
                        datasets: [{
                            data: @json($parcelCounts),
                            borderColor: '#62B3A8',
                            backgroundColor: 'rgba(98, 179, 168, 0.16)',
                            fill: true,
                            tension: 0.35,
                            borderWidth: 3,
                        }],
                    },
                    options: baseOptions,
                });

                new window.Chart(document.getElementById('revenueChart'), {
                    type: 'bar',
                    data: {
                        labels,
                        datasets: [{
                            data: @json($revenueCounts),
                            backgroundColor: ['#E55634', '#D59D80', '#62B3A8', '#C0754D', '#8FB8DE', '#F0B35A'],
                            borderRadius: 10,
                        }],
                    },
                    options: baseOptions,
                });
            });
        </script>
    @endpush
</x-village-link-layout>
