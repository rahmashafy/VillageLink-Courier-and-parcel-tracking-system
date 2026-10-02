<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? __('vl.site_name') }} | {{ __('vl.tagline') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
@php
    $user = auth()->user();
    $theme = session('theme', $user?->theme_preference ?? 'dark');
    $role = $user->role ?? 'customer';
    $roleLabel = $role === 'agent' ? 'driver' : $role;
    $nav = match ($role) {
        'admin' => [
            ['label' => __('vl.dashboard'), 'route' => 'admin.dashboard', 'icon' => 'layout-dashboard'],
            ['label' => __('vl.ai_assistant'), 'route' => 'assistant.index', 'icon' => 'bot'],
            ['label' => 'Parcels', 'route' => 'admin.parcels.index', 'icon' => 'package'],
            ['label' => 'Payments', 'route' => 'admin.payments.index', 'icon' => 'wallet-cards'],
            ['label' => __('vl.refunds'), 'route' => 'admin.refunds.index', 'icon' => 'refresh-cw'],
            ['label' => 'Drivers', 'route' => 'admin.drivers.index', 'icon' => 'truck'],
            ['label' => __('vl.users'), 'route' => 'admin.users.index', 'icon' => 'users'],
            ['label' => __('vl.complaints'), 'route' => 'admin.complaints.index', 'icon' => 'shield-alert'],
            ['label' => 'Contact Messages', 'route' => 'admin.contact-messages.index', 'icon' => 'mail'],
            ['label' => __('vl.reports'), 'route' => 'admin.reports', 'icon' => 'chart-no-axes-combined'],
        ],
        'driver', 'agent' => [
            ['label' => __('vl.dashboard'), 'route' => 'agent.dashboard', 'icon' => 'layout-dashboard'],
            ['label' => 'Deliveries', 'route' => 'agent.parcels.index', 'icon' => 'truck'],
            ['label' => __('vl.ai_assistant'), 'route' => 'assistant.index', 'icon' => 'bot'],
            ['label' => 'Driver Profile', 'route' => 'driver.profile.edit', 'icon' => 'user-round'],
        ],
        default => [
            ['label' => __('vl.dashboard'), 'route' => 'customer.dashboard', 'icon' => 'layout-dashboard'],
            ['label' => __('vl.book_parcel'), 'route' => 'customer.parcels.create', 'icon' => 'package-plus'],
            ['label' => __('vl.my_parcels'), 'route' => 'customer.parcels.index', 'icon' => 'boxes'],
            ['label' => __('vl.track_parcel'), 'route' => 'customer.parcels.track', 'icon' => 'radar'],
            ['label' => __('vl.payments'), 'route' => 'customer.payments.index', 'icon' => 'credit-card'],
            ['label' => __('vl.refunds'), 'route' => 'customer.refunds.index', 'icon' => 'refresh-cw'],
            ['label' => __('vl.complaints'), 'route' => 'customer.complaints.index', 'icon' => 'circle-alert'],
            ['label' => __('vl.ai_assistant'), 'route' => 'assistant.index', 'icon' => 'bot'],
        ],
    };
    $photo = $user?->profile_photo ? asset('storage/'.$user->profile_photo) : null;
@endphp
<body class="vl-bg vl-bg-mesh vl-dashboard {{ $theme === 'light' ? 'vl-light' : 'vl-dark' }} text-white antialiased min-h-screen">

<div class="relative z-[1] min-h-screen md:flex">
    <aside class="vl-sidebar fixed inset-y-0 left-0 z-40 hidden w-72 flex-col md:flex">
        <div class="px-6 py-6 border-b border-white/10">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                <x-vl-logo />
                <div>
                    <p class="font-display text-lg font-bold">{{ __('vl.site_name') }}</p>
                    <p class="text-xs text-white/60 capitalize">{{ $roleLabel }} workspace</p>
                </div>
            </a>
        </div>

        <nav class="flex-1 p-4 space-y-1">
            @foreach ($nav as $item)
                @php
                    $active = request()->routeIs($item['route']) || request()->routeIs($item['route'].'*');
                @endphp
                <a href="{{ route($item['route']) }}"
                   class="group flex items-center gap-3 rounded-xl px-4 py-3 text-sm transition {{ $active ? 'bg-white/15 text-vl-peach font-semibold' : 'text-white/75 hover:bg-white/10 hover:text-white' }}">
                    <i data-lucide="{{ $item['icon'] }}" class="vl-icon-lg"></i>
                    <span>{{ $item['label'] }}</span>
                </a>
            @endforeach
            <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm text-white/75 transition hover:bg-white/10 hover:text-white">
                <i data-lucide="user-round" class="vl-icon-lg"></i>
                <span>{{ __('vl.profile') }}</span>
            </a>
        </nav>

        <div class="m-4 rounded-2xl border border-white/10 bg-white/[0.07] p-4">
            <div class="flex items-center gap-3">
                @if ($photo)
                    <img src="{{ $photo }}" alt="" class="h-11 w-11 rounded-xl object-cover border border-vl-peach/70">
                @else
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl border border-vl-peach/50 bg-vl-peach/20 text-sm font-bold">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </span>
                @endif
                <div class="min-w-0">
                    <p class="truncate text-sm font-semibold">{{ $user->name }}</p>
                    <p class="text-xs text-white/60">{{ $user->email }}</p>
                </div>
            </div>
        </div>
    </aside>

    <div class="min-w-0 flex-1 md:pl-72">
        <header class="sticky top-0 z-30 border-b border-white/10 bg-[#081318]/80 px-4 py-4 backdrop-blur-xl md:px-8">
            <div class="flex items-center gap-4">
                <a href="{{ route('dashboard') }}" class="md:hidden">
                    <x-vl-logo />
                </a>
                <div class="min-w-0">
                    <p class="text-xs uppercase tracking-[0.22em] text-white/50">{{ ucfirst($roleLabel) }}</p>
                    <h1 class="truncate font-display text-xl font-semibold text-vl-cream">{{ $header ?? ($title ?? __('vl.dashboard')) }}</h1>
                </div>
                <div class="ml-auto flex items-center gap-2">
                    <div class="hidden items-center rounded-xl border border-white/10 bg-white/[0.07] p-1 text-xs sm:flex">
                        <a href="{{ route('locale.switch', 'en') }}" class="rounded-lg px-2 py-1 {{ app()->getLocale() === 'en' ? 'bg-white/15 text-vl-peach' : 'text-white/60' }}">EN</a>
                        <a href="{{ route('locale.switch', 'si') }}" class="rounded-lg px-2 py-1 {{ app()->getLocale() === 'si' ? 'bg-white/15 text-vl-peach' : 'text-white/60' }}">SI</a>
                        <a href="{{ route('locale.switch', 'ta') }}" class="rounded-lg px-2 py-1 {{ app()->getLocale() === 'ta' ? 'bg-white/15 text-vl-peach' : 'text-white/60' }}">TA</a>
                    </div>
                    <form method="POST" action="{{ route('theme.switch', $theme === 'light' ? 'dark' : 'light') }}">
                        @csrf
                        <button type="submit" class="rounded-xl border border-white/10 bg-white/[0.07] p-3 transition hover:bg-white/15" aria-label="Switch theme">
                            <i data-lucide="{{ $theme === 'light' ? 'moon' : 'sun' }}" class="vl-icon"></i>
                        </button>
                    </form>
                    <a href="{{ route('profile.edit') }}" class="rounded-xl border border-white/10 bg-white/[0.07] p-3 transition hover:bg-white/15" aria-label="{{ __('vl.profile') }}">
                        <i data-lucide="user-round" class="vl-icon"></i>
                    </a>
                    <a href="{{ route('notifications.index') }}" class="relative rounded-xl border border-white/10 bg-white/[0.07] p-3 transition hover:bg-white/15" aria-label="Notifications">
                        <i data-lucide="bell" class="vl-icon"></i>
                        @if ($user->unreadNotifications->count())
                            <span class="absolute -right-1 -top-1 flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-vl-accent px-1 text-[10px] font-bold">{{ $user->unreadNotifications->count() }}</span>
                        @endif
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="rounded-xl border border-white/10 bg-white/[0.07] p-3 transition hover:bg-white/15" aria-label="{{ __('vl.logout') }}">
                            <i data-lucide="log-out" class="vl-icon"></i>
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <main class="px-4 py-6 pb-28 md:px-8 md:py-8 md:pb-10">
            {{ $slot }}
        </main>
    </div>
</div>

<nav class="fixed bottom-3 left-3 right-3 z-50 grid grid-cols-5 gap-1 rounded-2xl border border-white/15 bg-[#081318]/90 p-2 backdrop-blur-xl md:hidden">
    @foreach (array_slice($nav, 0, 5) as $item)
        @php
            $active = request()->routeIs($item['route']) || request()->routeIs($item['route'].'*');
        @endphp
        <a href="{{ route($item['route']) }}" class="flex min-w-0 flex-col items-center gap-1 rounded-xl px-1 py-2 text-[10px] {{ $active ? 'bg-white/15 text-vl-peach' : 'text-white/60' }}">
            <i data-lucide="{{ $item['icon'] }}" class="vl-icon"></i>
            <span class="max-w-full truncate">{{ $item['label'] }}</span>
        </a>
    @endforeach
</nav>

<x-vl-success-popup />
@stack('scripts')
</body>
</html>
