@props(['status'])

@php
    $label = ucfirst(str_replace('_', ' ', $status));
    $class = match ($status) {
        'delivered' => 'border-emerald-300/40 bg-emerald-400/15 text-emerald-100',
        'approved', 'paid', 'refunded' => 'border-emerald-300/40 bg-emerald-400/15 text-emerald-100',
        'pending_pickup' => 'border-amber-300/40 bg-amber-400/15 text-amber-100',
        'pending' => 'border-amber-300/40 bg-amber-400/15 text-amber-100',
        'picked_up', 'in_transit', 'arrived_center', 'out_for_delivery' => 'border-sky-300/40 bg-sky-400/15 text-sky-100',
        'rejected', 'failed', 'cancelled' => 'border-red-300/40 bg-red-400/15 text-red-100',
        default => 'border-white/20 bg-white/10 text-white/75',
    };
@endphp

<span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold {{ $class }}">{{ $label }}</span>
