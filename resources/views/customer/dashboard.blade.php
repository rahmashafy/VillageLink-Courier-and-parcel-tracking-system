<x-village-link-layout title="Dashboard" header="{{ __('vl.welcome') }}, {{ auth()->user()->name }}">
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
        <x-vl-stat-card label="My Parcels" :value="$total" icon="package" />
        <x-vl-stat-card label="Delivered" :value="$delivered" icon="package-check" />
        <x-vl-stat-card label="In Transit" :value="$inTransit" icon="truck" />
        <x-vl-stat-card label="Loyalty Points" :value="auth()->user()->loyalty_points ?? 0" icon="star" />
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-[1.1fr_0.9fr]">
        <section class="vl-panel overflow-hidden">
            <div class="flex flex-col gap-4 border-b border-white/10 pb-5 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-xs uppercase tracking-[0.24em] text-white/50">Smart Delivery Hub</p>
                    <h2 class="mt-1 font-display text-2xl font-bold text-vl-cream">
                        {{ $nextParcel ? 'Next parcel: '.$nextParcel->tracking_id : 'Ready for your next booking' }}
                    </h2>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-white/60">
                        Premium view for ETA, payment readiness, driver assignment and live tracking status.
                    </p>
                </div>
                @if ($nextParcel)
                    <a href="{{ route('customer.parcels.show', $nextParcel) }}" class="vl-btn-primary justify-center">
                        <i data-lucide="radar" class="vl-icon"></i>
                        Track Live
                    </a>
                @else
                    <a href="{{ route('customer.parcels.create') }}" class="vl-btn-primary justify-center">
                        <i data-lucide="package-plus" class="vl-icon"></i>
                        Book Parcel
                    </a>
                @endif
            </div>

            @if ($nextParcel)
                <div class="mt-5 grid gap-5 md:grid-cols-[0.9fr_1.1fr]">
                    <div>
                        <p class="text-xs uppercase tracking-[0.18em] text-white/45">Route</p>
                        <p class="mt-2 text-lg font-semibold text-white">{{ $nextParcel->pickup_location ?: $nextParcel->pickup_address }}</p>
                        <div class="my-3 h-px bg-gradient-to-r from-vl-peach via-white/20 to-transparent"></div>
                        <p class="text-lg font-semibold text-white">{{ $nextParcel->delivery_location ?: $nextParcel->delivery_address }}</p>
                        <p class="mt-3 text-sm text-white/55">ETA: {{ $nextParcel->estimated_delivery_at?->format('M d, g:i A') ?? 'Planning' }}</p>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2">
                        @foreach ($nextParcelReadiness as $label => $ready)
                            <div class="flex items-center gap-3 rounded-xl border border-white/10 bg-white/5 p-3">
                                <span class="flex h-9 w-9 items-center justify-center rounded-full border {{ $ready ? 'border-emerald-300/40 bg-emerald-400/15 text-emerald-100' : 'border-amber-300/40 bg-amber-400/15 text-amber-100' }}">
                                    <i data-lucide="{{ $ready ? 'check' : 'clock-3' }}" class="h-4 w-4"></i>
                                </span>
                                <div>
                                    <p class="text-sm font-semibold text-white">{{ $label }}</p>
                                    <p class="text-xs text-white/45">{{ $ready ? 'Ready' : 'Pending' }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="mt-5 rounded-2xl border border-dashed border-white/15 bg-white/5 p-6 text-sm leading-6 text-white/65">
                    No active parcel right now. Book a parcel to unlock live ETA, route map, payment readiness and driver assignment tracking.
                </div>
            @endif
        </section>

        <aside class="vl-panel">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs uppercase tracking-[0.22em] text-white/50">Wallet Intelligence</p>
                    <h3 class="mt-1 font-display text-lg font-semibold text-vl-cream">Savings and support</h3>
                </div>
                <i data-lucide="badge-percent" class="h-6 w-6 text-vl-peach"></i>
            </div>
            <dl class="mt-5 space-y-4 text-sm">
                <div class="flex items-center justify-between border-b border-white/10 pb-3">
                    <dt class="text-white/60">Paid deliveries</dt>
                    <dd class="font-semibold text-white">{{ $paidDeliveries }}</dd>
                </div>
                <div class="flex items-center justify-between border-b border-white/10 pb-3">
                    <dt class="text-white/60">Total spent</dt>
                    <dd class="font-semibold text-vl-peach">Rs. {{ number_format((float) $totalSpent, 2) }}</dd>
                </div>
                <div class="flex items-center justify-between border-b border-white/10 pb-3">
                    <dt class="text-white/60">Loyalty saved</dt>
                    <dd class="font-semibold text-emerald-200">Rs. {{ number_format((float) $savedThroughLoyalty, 2) }}</dd>
                </div>
                <div class="flex items-center justify-between">
                    <dt class="text-white/60">Pending requests</dt>
                    <dd class="font-semibold text-amber-200">{{ $openRequests }}</dd>
                </div>
            </dl>
        </aside>
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-[1.35fr_0.65fr]">
        <div class="vl-table-panel">
            <div class="flex items-center justify-between border-b border-white/10 px-6 py-4">
                <div>
                    <p class="text-xs uppercase tracking-[0.22em] text-white/50">Latest activity</p>
                    <h2 class="mt-1 font-display text-lg font-semibold text-vl-cream">Recent Parcels</h2>
                </div>
                <a href="{{ route('customer.parcels.index') }}" class="vl-muted-link">View all</a>
            </div>
            <div class="overflow-x-auto">
                <table class="vl-table">
                    <thead>
                        <tr>
                            <th>Tracking ID</th>
                            <th>Receiver</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentParcels as $parcel)
                            <tr>
                                <td class="font-mono text-vl-peach">{{ $parcel->tracking_id }}</td>
                                <td>{{ $parcel->receiver_name }}</td>
                                <td><x-vl-status-badge :status="$parcel->status" /></td>
                                <td>
                                    <a href="{{ route('customer.parcels.show', $parcel) }}" class="vl-action-link">
                                        <i data-lucide="radar" class="vl-icon"></i>
                                        Track
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-10 text-center text-white/60">
                                    No parcels yet. <a href="{{ route('customer.parcels.create') }}" class="text-vl-peach hover:underline">Book your first parcel</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="grid gap-6">
            <div class="vl-panel">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs uppercase tracking-[0.22em] text-white/50">Parcel flow</p>
                        <h3 class="mt-1 font-display text-lg font-semibold text-vl-cream">Monthly bookings</h3>
                    </div>
                    <i data-lucide="chart-no-axes-combined" class="h-6 w-6 text-vl-peach"></i>
                </div>
                <div class="mt-5 h-56">
                    <canvas id="customerParcelChart"></canvas>
                </div>
            </div>

            <div class="vl-panel">
                <h3 class="font-display text-lg font-semibold text-vl-cream">Quick Actions</h3>
                <div class="mt-4 grid gap-3">
                    <a href="{{ route('customer.parcels.create') }}" class="vl-action-link justify-center py-3">
                        <i data-lucide="package-plus" class="vl-icon"></i>
                        Book New Parcel
                    </a>
                    <a href="{{ route('customer.parcels.track') }}" class="vl-action-link justify-center py-3">
                        <i data-lucide="scan-search" class="vl-icon"></i>
                        Track Parcel
                    </a>
                    <a href="{{ route('customer.payments.index') }}" class="vl-action-link justify-center py-3">
                        <i data-lucide="credit-card" class="vl-icon"></i>
                        Payment History
                    </a>
                    <a href="{{ route('customer.complaints.index') }}" class="vl-action-link justify-center py-3">
                        <i data-lucide="circle-alert" class="vl-icon"></i>
                        Complaints
                    </a>
                    <a href="{{ route('assistant.index') }}" class="vl-action-link justify-center py-3">
                        <i data-lucide="bot" class="vl-icon"></i>
                        AI Assistant
                    </a>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const ctx = document.getElementById('customerParcelChart');
                if (!ctx || !window.Chart) return;

                new window.Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: @json($chartLabels),
                        datasets: [{
                            label: 'Parcels',
                            data: @json($parcelCounts),
                            borderColor: '#D59D80',
                            backgroundColor: 'rgba(213, 157, 128, 0.18)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.38,
                            pointRadius: 4,
                            pointBackgroundColor: '#E55634',
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            x: { grid: { color: 'rgba(255,255,255,0.08)' }, ticks: { color: 'rgba(255,255,255,0.65)' } },
                            y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.08)' }, ticks: { color: 'rgba(255,255,255,0.65)', precision: 0 } },
                        },
                    },
                });
            });
        </script>
    @endpush
</x-village-link-layout>
