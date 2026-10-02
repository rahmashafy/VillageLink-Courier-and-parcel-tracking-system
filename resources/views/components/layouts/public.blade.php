@props(['title' => 'Village Link'])
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
<body class="vl-bg vl-bg-mesh {{ $theme === 'light' ? 'vl-light' : 'vl-dark' }} text-white antialiased flex min-h-screen flex-col">
    <nav class="sticky top-0 z-50 mx-4 mt-4 w-[calc(100%-2rem)] max-w-7xl rounded-2xl border border-white/15 bg-[#081318]/80 backdrop-blur-xl md:mx-auto md:w-full">
        <div class="flex items-center justify-between px-4 py-4 md:px-6">
            <a href="{{ route('home') }}" class="flex items-center gap-3">
                <x-vl-logo />
                <span class="hidden font-display text-lg font-semibold sm:inline">{{ __('vl.site_name') }}</span>
            </a>
            <div class="hidden items-center gap-8 text-sm font-medium md:flex">
                <a href="{{ route('home') }}" class="transition hover:text-vl-peach">{{ __('vl.home') }}</a>
                <a href="{{ route('services') }}" class="transition hover:text-vl-peach">{{ __('vl.services') }}</a>
                <a href="{{ route('about') }}" class="transition hover:text-vl-peach">{{ __('vl.about') }}</a>
                <a href="{{ route('contact') }}" class="transition hover:text-vl-peach">{{ __('vl.contact') }}</a>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('locale.switch', 'en') }}" class="rounded-full px-2 py-1 text-xs {{ app()->getLocale()==='en'?'bg-white/20':'' }}">EN</a>
                <a href="{{ route('locale.switch', 'si') }}" class="rounded-full px-2 py-1 text-xs {{ app()->getLocale()==='si'?'bg-white/20':'' }}">SI</a>
                <a href="{{ route('locale.switch', 'ta') }}" class="rounded-full px-2 py-1 text-xs {{ app()->getLocale()==='ta'?'bg-white/20':'' }}">TA</a>
                <form method="POST" action="{{ route('theme.switch', $theme === 'light' ? 'dark' : 'light') }}">
                    @csrf
                    <button type="submit" class="rounded-full border border-white/15 bg-white/10 p-2" aria-label="Switch theme">
                        <i data-lucide="{{ $theme === 'light' ? 'moon' : 'sun' }}" class="vl-icon"></i>
                    </button>
                </form>
                @auth
                    <a href="{{ route('dashboard') }}" class="vl-btn-primary px-5 py-2 text-sm">{{ __('vl.dashboard') }}</a>
                @else
                    <a href="{{ route('login') }}" class="vl-btn-ghost-glass hidden sm:inline-flex">{{ __('vl.login') }}</a>
                    <a href="{{ route('register') }}" class="vl-btn-primary px-5 py-2 text-sm">{{ __('vl.register') }}</a>
                @endauth
            </div>
        </div>
    </nav>

    <main class="relative z-[1] flex-1">{{ $slot }}</main>

    <x-vl-footer />
    <x-vl-success-popup />
    @stack('scripts')
</body>
</html>
