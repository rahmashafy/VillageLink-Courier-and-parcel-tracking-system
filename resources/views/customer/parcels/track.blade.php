<x-village-link-layout title="Track Parcel" header="Track Your Parcel">
    <div class="grid gap-6 xl:grid-cols-[0.85fr_1.15fr]">
        <div class="vl-panel vl-reveal">
            <div class="mb-6">
                <p class="text-xs uppercase tracking-[0.22em] text-white/50">Live lookup</p>
                <h2 class="mt-2 font-display text-3xl font-bold text-vl-peach">Find the full delivery timeline.</h2>
                <p class="mt-3 leading-7 text-white/70">Enter a tracking ID from your parcel list to view status, history, route and ETA.</p>
            </div>

            @if (session('error'))
                <div class="mb-4 rounded-xl border border-red-300/30 bg-red-500/15 px-4 py-3 text-sm text-red-100">
                    {{ session('error') }}
                </div>
            @endif

            <form method="POST" action="{{ route('customer.parcels.track.result') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="mb-2 block text-sm font-medium text-white/80">Tracking ID</label>
                    <div class="relative">
                        <i data-lucide="scan-search" class="absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-white/40"></i>
                        <input type="text" name="tracking_id" value="{{ old('tracking_id') }}" placeholder="TRK20260525..." required class="vl-input pl-12 font-mono uppercase">
                    </div>
                    @error('tracking_id')
                        <p class="mt-2 text-sm text-red-200">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit" class="vl-btn-primary w-full">
                    <i data-lucide="radar" class="vl-icon"></i>
                    Track Parcel
                </button>
            </form>

            @if ($recentParcels->isNotEmpty())
                <div class="mt-8">
                    <p class="mb-3 text-sm font-semibold text-white/70">Recent tracking IDs</p>
                    <div class="grid gap-2">
                        @foreach ($recentParcels as $parcel)
                            <form method="POST" action="{{ route('customer.parcels.track.result') }}">
                                @csrf
                                <input type="hidden" name="tracking_id" value="{{ $parcel->tracking_id }}">
                                <button type="submit" class="flex w-full items-center justify-between rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-left transition hover:bg-white/10">
                                    <span>
                                        <span class="block font-mono text-sm text-vl-peach">{{ $parcel->tracking_id }}</span>
                                        <span class="text-xs text-white/60">{{ $parcel->receiver_name }}</span>
                                    </span>
                                    <i data-lucide="arrow-right" class="vl-icon"></i>
                                </button>
                            </form>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <div class="vl-route-map min-h-[520px]">
            <video class="absolute inset-0 h-full w-full object-cover opacity-20" autoplay muted loop playsinline>
                <source src="{{ asset('videos/tracking-flow.mp4') }}" type="video/mp4">
            </video>
            <div class="absolute inset-0 bg-gradient-to-br from-[#081318]/90 via-transparent to-[#4a2330]/70"></div>
            <div class="vl-route-vehicle">
                <i data-lucide="truck" class="h-7 w-7"></i>
            </div>
            <div class="absolute left-6 top-6 max-w-xs rounded-2xl border border-white/15 bg-[#081318]/75 p-5 backdrop-blur">
                <p class="text-xs uppercase tracking-[0.22em] text-white/50">Tracking flow</p>
                <p class="mt-2 text-2xl font-bold text-vl-peach">Booked to delivered</p>
                <p class="mt-2 text-sm leading-6 text-white/70">Every status update becomes a timeline entry for the customer.</p>
            </div>
            <div class="absolute bottom-6 left-6 right-6 grid gap-3 sm:grid-cols-3">
                @foreach ([['Package accepted', 'pending_pickup'], ['On route', 'in_transit'], ['Delivered', 'delivered']] as $item)
                    <div class="rounded-xl border border-white/10 bg-[#081318]/70 p-3 backdrop-blur">
                        <p class="text-sm font-semibold">{{ $item[0] }}</p>
                        <p class="mt-1 text-xs text-white/60">{{ ucfirst(str_replace('_', ' ', $item[1])) }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-village-link-layout>
