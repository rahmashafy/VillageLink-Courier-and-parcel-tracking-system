<x-layouts.public :title="__('vl.home')">
    <section class="relative min-h-[calc(100vh-6rem)] overflow-hidden">
        <video class="absolute inset-0 h-full w-full object-cover opacity-40" autoplay muted loop playsinline poster="{{ asset('images/courier-logo.svg') }}">
            <source src="{{ asset('videos/tracking-flow.mp4') }}" type="video/mp4">
        </video>
        <div class="absolute inset-0 bg-gradient-to-br from-[#081318]/95 via-[#102f36]/80 to-[#4a2330]/80"></div>
        <div class="relative mx-auto flex min-h-[calc(100vh-6rem)] max-w-7xl flex-col justify-center px-4 py-16">
            <div class="max-w-3xl vl-reveal">
                <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-4 py-2 text-sm text-white/75 backdrop-blur">
                    <i data-lucide="radar" class="vl-icon"></i>
                    <span>{{ __('vl.tagline') }}</span>
                </div>
                <h1 class="font-display text-5xl font-bold leading-[1.02] md:text-7xl">{{ __('vl.site_name') }}</h1>
                <p class="mt-5 max-w-2xl text-lg leading-8 text-white/80 md:text-xl">{{ __('vl.hero_sub') }}</p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ route('register') }}" class="vl-btn-primary">
                        <i data-lucide="package-plus" class="vl-icon"></i>
                        {{ __('vl.get_started') }}
                    </a>
                    <a href="{{ route('login') }}" class="vl-btn-outline">
                        <i data-lucide="scan-search" class="vl-icon"></i>
                        {{ __('vl.track_now') }}
                    </a>
                </div>
            </div>

            <div class="mt-12 grid max-w-5xl gap-4 sm:grid-cols-3">
                @foreach ([['20K+', 'Customers'], ['50K+', 'Delivered'], ['99%', 'On time']] as $stat)
                    <div class="vl-mini-card vl-reveal" style="animation-delay: {{ $loop->index * 90 }}ms">
                        <p class="text-3xl font-bold text-vl-peach">{{ $stat[0] }}</p>
                        <p class="mt-1 text-sm text-white/60">{{ $stat[1] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-16">
        <div class="grid gap-8 lg:grid-cols-[1.05fr_0.95fr] lg:items-center">
            <div class="relative min-h-[520px]">
                <div class="absolute left-0 top-0 h-[390px] w-[82%] overflow-hidden rounded-2xl border border-white/15 shadow-2xl">
                    <img src="https://images.pexels.com/photos/6169177/pexels-photo-6169177.jpeg?auto=compress&cs=tinysrgb&w=900"
                         alt="Courier parcels ready for dispatch"
                         class="h-full w-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-[#081318]/80 via-transparent to-transparent"></div>
                </div>
                <div class="absolute bottom-0 right-0 h-[300px] w-[56%] overflow-hidden rounded-2xl border-[12px] border-[#0D1D25] shadow-2xl">
                    <img src="https://images.pexels.com/photos/6699420/pexels-photo-6699420.jpeg?auto=compress&cs=tinysrgb&w=700"
                         alt="Courier driver handling a delivery"
                         class="h-full w-full object-cover">
                </div>
                <div class="absolute left-8 bottom-16 max-w-xs rounded-2xl border border-white/15 bg-[#081318]/85 p-5 backdrop-blur">
                    <p class="text-xs uppercase tracking-[0.24em] text-white/50">Delivery flow</p>
                    <h2 class="mt-2 font-display text-2xl font-bold text-vl-peach">Pickup, dispatch and doorstep handover</h2>
                    <p class="mt-2 text-sm leading-6 text-white/70">A practical courier workflow with photos, proof, status updates and customer visibility.</p>
                </div>
            </div>

            <div class="grid gap-4">
                @foreach ([
                    ['package-check', 'Parcel Booking', 'Sender, receiver, weight and delivery speed details in one clear form.'],
                    ['truck', 'Driver Dispatch', 'Admin assigns only free drivers, then driver updates each delivery stage.'],
                    ['radar', 'Customer Tracking', 'Customers see the timeline, live map, driver and proof when delivery is complete.'],
                    ['shield-check', 'Admin Control', 'Payments, complaints, reports and users stay organized for daily operation.'],
                ] as $feature)
                    <div class="vl-panel flex items-start gap-4">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-vl-peach/40 bg-vl-accent/20">
                            <i data-lucide="{{ $feature[0] }}" class="vl-icon-lg"></i>
                        </span>
                        <div>
                            <h3 class="font-display text-lg font-semibold text-vl-peach">{{ $feature[1] }}</h3>
                            <p class="mt-1 text-sm leading-6 text-white/70">{{ $feature[2] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 pb-16">
        <div class="vl-panel grid gap-8 md:grid-cols-[0.9fr_1.1fr] md:items-center">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.24em] text-white/50">Courier operations</p>
                <h2 class="mt-3 font-display text-3xl font-bold text-vl-peach">Fast booking, secure payment, clear tracking.</h2>
                <p class="mt-4 leading-7 text-white/70">The system is built around the practical workflow: customer books, pays, driver updates status, customer tracks the complete timeline.</p>
                <a href="{{ route('register') }}" class="vl-btn-primary mt-6 w-fit">
                    <i data-lucide="arrow-right" class="vl-icon"></i>
                    Start shipping
                </a>
            </div>
            <div class="grid gap-3 sm:grid-cols-2">
                <div class="vl-mini-card">
                    <p class="text-sm text-white/60">Security</p>
                    <p class="mt-2 text-xl font-bold">Role based access</p>
                </div>
                <div class="vl-mini-card">
                    <p class="text-sm text-white/60">Tracking</p>
                    <p class="mt-2 text-xl font-bold">Timeline history</p>
                </div>
                <div class="vl-mini-card">
                    <p class="text-sm text-white/60">Payments</p>
                    <p class="mt-2 text-xl font-bold">Masked details</p>
                </div>
                <div class="vl-mini-card">
                    <p class="text-sm text-white/60">Reports</p>
                    <p class="mt-2 text-xl font-bold">Live charts</p>
                </div>
            </div>
        </div>
    </section>
</x-layouts.public>
