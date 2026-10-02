@props(['size' => 'md'])

@php
    $class = match($size) {
        'lg' => 'vl-logo-icon vl-logo-icon-lg',
        'banner' => 'vl-logo-banner',
        default => 'vl-logo-icon',
    };
    $uid = 'vl-logo-'.str_replace('.', '', uniqid('', true));
@endphp

@if ($size === 'banner')
    <div {{ $attributes->merge(['class' => $class]) }}>
        <svg class="vl-logo-wordmark" viewBox="0 0 248 64" role="img" aria-labelledby="{{ $uid }}-title">
            <title id="{{ $uid }}-title">{{ __('vl.site_name') }}</title>
            <defs>
                <linearGradient id="{{ $uid }}-mark" x1="8" y1="8" x2="56" y2="56" gradientUnits="userSpaceOnUse">
                    <stop offset="0" stop-color="#E55634"/>
                    <stop offset=".52" stop-color="#D59D80"/>
                    <stop offset="1" stop-color="#104C64"/>
                </linearGradient>
                <linearGradient id="{{ $uid }}-stroke" x1="15" y1="16" x2="50" y2="50" gradientUnits="userSpaceOnUse">
                    <stop offset="0" stop-color="#FFF5EC"/>
                    <stop offset="1" stop-color="#D59D80"/>
                </linearGradient>
            </defs>
            <rect x="4" y="6" width="52" height="52" rx="16" fill="#081318"/>
            <rect x="6.5" y="8.5" width="47" height="47" rx="14" fill="url(#{{ $uid }}-mark)" opacity=".95"/>
            <path d="M19 23.5 30 17l11 6.5v13L30 43l-11-6.5z" fill="none" stroke="#FFF5EC" stroke-width="3" stroke-linejoin="round"/>
            <path d="M19.5 24 30 30.2 40.5 24M30 30v12.3" fill="none" stroke="#FFF5EC" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" opacity=".95"/>
            <path d="M17 48c10-10 20 10 32-3" fill="none" stroke="url(#{{ $uid }}-stroke)" stroke-width="3" stroke-linecap="round"/>
            <circle cx="17" cy="48" r="3.2" fill="#081318" stroke="#FFF5EC" stroke-width="2"/>
            <circle cx="49" cy="45" r="3.2" fill="#081318" stroke="#FFF5EC" stroke-width="2"/>
            <text x="70" y="31" font-family="Manrope, Arial, sans-serif" font-size="23" font-weight="800" fill="#FFF5EC" letter-spacing=".2">Village Link</text>
            <text x="72" y="48" font-family="Manrope, Arial, sans-serif" font-size="10.5" font-weight="800" fill="#D59D80" letter-spacing="3.2">SMART COURIER</text>
        </svg>
    </div>
@else
    <div {{ $attributes->merge(['class' => $class]) }} aria-label="{{ __('vl.site_name') }}">
        <svg class="vl-logo-mark" viewBox="0 0 64 64" role="img" aria-labelledby="{{ $uid }}-title">
            <title id="{{ $uid }}-title">{{ __('vl.site_name') }}</title>
            <defs>
                <linearGradient id="{{ $uid }}-mark" x1="8" y1="8" x2="56" y2="56" gradientUnits="userSpaceOnUse">
                    <stop offset="0" stop-color="#E55634"/>
                    <stop offset=".52" stop-color="#D59D80"/>
                    <stop offset="1" stop-color="#104C64"/>
                </linearGradient>
                <linearGradient id="{{ $uid }}-stroke" x1="15" y1="16" x2="50" y2="50" gradientUnits="userSpaceOnUse">
                    <stop offset="0" stop-color="#FFF5EC"/>
                    <stop offset="1" stop-color="#D59D80"/>
                </linearGradient>
            </defs>
            <rect x="4" y="4" width="56" height="56" rx="18" fill="#081318"/>
            <rect x="8" y="8" width="48" height="48" rx="15" fill="url(#{{ $uid }}-mark)"/>
            <path d="M20 23.5 32 16.5l12 7v14L32 44.5l-12-7z" fill="none" stroke="#FFF5EC" stroke-width="3.4" stroke-linejoin="round"/>
            <path d="M20.5 24 32 30.8 43.5 24M32 31v13" fill="none" stroke="#FFF5EC" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round" opacity=".96"/>
            <path d="M16 50c11-11 22 10 34-4" fill="none" stroke="url(#{{ $uid }}-stroke)" stroke-width="3.3" stroke-linecap="round"/>
            <circle cx="16" cy="50" r="3.4" fill="#081318" stroke="#FFF5EC" stroke-width="2.2"/>
            <circle cx="50" cy="46" r="3.4" fill="#081318" stroke="#FFF5EC" stroke-width="2.2"/>
        </svg>
    </div>
@endif
