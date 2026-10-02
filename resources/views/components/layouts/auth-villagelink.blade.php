@props(['title' => 'Account'])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} | {{ __('vl.site_name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
@php
    $theme = session('theme', auth()->user()?->theme_preference ?? 'dark');
@endphp
<body class="vl-bg vl-bg-mesh {{ $theme === 'light' ? 'vl-light' : 'vl-dark' }} min-h-screen pb-14 text-white">
    <div class="min-h-screen lg:grid lg:grid-cols-2">
        <div class="relative hidden overflow-hidden lg:flex lg:items-center lg:justify-center">
            <video class="absolute inset-0 h-full w-full object-cover opacity-30" autoplay muted loop playsinline>
                <source src="{{ asset('videos/tracking-flow.mp4') }}" type="video/mp4">
            </video>
            <div class="absolute inset-0 bg-gradient-to-br from-[#081318]/90 via-[#104C64]/70 to-[#4a2330]/80"></div>
            <div class="relative z-10 max-w-md px-10">
                <x-vl-logo size="lg" class="mb-6" />
                <h1 class="font-display text-4xl font-bold text-vl-peach">{{ __('vl.site_name') }}</h1>
                <p class="mt-4 leading-7 text-white/75">Real-time tracking, secure payments and role-based courier operations.</p>
                <div class="mt-8 grid gap-3">
                    @foreach ([['radar', 'Live parcel timeline'], ['lock-keyhole', 'Protected account access'], ['truck', 'Driver delivery updates']] as $item)
                        <div class="flex items-center gap-3 rounded-xl border border-white/15 bg-white/10 px-4 py-3">
                            <i data-lucide="{{ $item[0] }}" class="vl-icon"></i>
                            <span class="text-sm">{{ $item[1] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="flex min-h-screen items-center justify-center p-6">
            <div class="w-full max-w-md">
                <div class="mb-6 flex justify-center lg:hidden">
                    <x-vl-logo size="lg" />
                </div>
                <div class="vl-panel">
                    {{ $slot }}
                </div>
                <div class="mt-5 flex justify-center gap-3 text-xs">
                    <a href="{{ route('locale.switch', 'en') }}" class="rounded-full px-2 py-1 text-white/60 hover:text-vl-peach">EN</a>
                    <a href="{{ route('locale.switch', 'si') }}" class="rounded-full px-2 py-1 text-white/60 hover:text-vl-peach">SI</a>
                    <a href="{{ route('locale.switch', 'ta') }}" class="rounded-full px-2 py-1 text-white/60 hover:text-vl-peach">TA</a>
                    <form method="POST" action="{{ route('theme.switch', $theme === 'light' ? 'dark' : 'light') }}">
                        @csrf
                        <button type="submit" class="rounded-full px-2 py-1 text-white/60 hover:text-vl-peach" aria-label="Switch theme">
                            {{ $theme === 'light' ? 'Dark' : 'Light' }}
                        </button>
                    </form>
                </div>
                <p class="mt-4 text-center text-sm text-white/50"><a href="{{ route('home') }}" class="hover:text-vl-peach">Back to home</a></p>
            </div>
        </div>
    </div>
    <x-vl-login-track />
    <x-vl-success-popup />
</body>
</html>
