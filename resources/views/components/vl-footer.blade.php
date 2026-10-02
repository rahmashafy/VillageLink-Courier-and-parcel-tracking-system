<footer class="vl-footer-rich">
    <div class="vl-footer-scene"></div>
    <div class="relative z-10 mx-auto max-w-7xl px-6 pb-6 pt-16">
        <div class="mb-12 grid gap-10 md:grid-cols-4">
            <div>
                <x-vl-logo size="lg" class="mb-4" />
                <p class="max-w-xs text-sm leading-relaxed text-white/75">{{ __('vl.tagline') }}. Trusted courier for rural and urban delivery.</p>
                <div class="mt-5 flex gap-3">
                    <a href="#" class="flex h-10 w-10 items-center justify-center rounded-full border border-white/30 text-sm hover:bg-white/10">f</a>
                    <a href="#" class="flex h-10 w-10 items-center justify-center rounded-full border border-white/30 text-sm hover:bg-white/10">in</a>
                    <a href="#" class="flex h-10 w-10 items-center justify-center rounded-full border border-white/30 text-sm hover:bg-white/10">@</a>
                </div>
            </div>
            <div>
                <h4 class="mb-4 border-b border-white/20 pb-2 font-semibold text-white">{{ __('vl.quick_links') }}</h4>
                <ul class="space-y-2.5 text-sm text-white/75">
                    <li><a href="{{ route('home') }}" class="hover:text-vl-peach">{{ __('vl.home') }}</a></li>
                    <li><a href="{{ route('services') }}" class="hover:text-vl-peach">{{ __('vl.services') }}</a></li>
                    <li><a href="{{ route('about') }}" class="hover:text-vl-peach">{{ __('vl.about') }}</a></li>
                    <li><a href="{{ route('contact') }}" class="hover:text-vl-peach">{{ __('vl.contact') }}</a></li>
                </ul>
            </div>
            <div>
                <h4 class="mb-4 border-b border-white/20 pb-2 font-semibold text-white">{{ __('vl.services') }}</h4>
                <ul class="space-y-2.5 text-sm text-white/75">
                    <li>{{ __('vl.book_parcel') }}</li>
                    <li>{{ __('vl.track_parcel') }}</li>
                    <li>Express Delivery</li>
                    <li>Cash on Delivery</li>
                </ul>
            </div>
            <div>
                <h4 class="mb-4 border-b border-white/20 pb-2 font-semibold text-white">{{ __('vl.contact') }}</h4>
                <ul class="space-y-3 text-sm text-white/75">
                    <li class="flex gap-2"><i data-lucide="mail" class="vl-icon text-vl-peach"></i> info@villagelink.lk</li>
                    <li class="flex gap-2"><i data-lucide="phone" class="vl-icon text-vl-peach"></i> +94 11 234 5678</li>
                    <li class="flex gap-2"><i data-lucide="map-pin" class="vl-icon text-vl-peach"></i> Colombo, Sri Lanka</li>
                </ul>
            </div>
        </div>
        <div class="flex flex-col items-center justify-between gap-4 border-t border-white/15 pt-6 text-xs text-white/50 md:flex-row">
            <x-vl-logo size="banner" />
            <div class="flex flex-wrap justify-center gap-4">
                <a href="#" class="hover:text-white/80">Privacy Policy</a>
                <a href="#" class="hover:text-white/80">Terms</a>
                <a href="{{ route('contact') }}" class="hover:text-white/80">{{ __('vl.contact') }}</a>
            </div>
            <p>&copy; {{ date('Y') }} {{ __('vl.site_name') }}. {{ __('vl.all_rights') }}</p>
        </div>
    </div>
</footer>
