@props(['label', 'value', 'icon' => 'package', 'color' => 'orange'])

<div class="vl-panel vl-reveal">
    <div class="flex items-start justify-between gap-4">
        <div class="min-w-0">
            <p class="text-sm font-medium text-white/60">{{ $label }}</p>
            <p class="mt-2 truncate text-3xl font-bold text-vl-peach">{{ $value }}</p>
        </div>
        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl border border-vl-peach/40 bg-vl-accent/20 text-white">
            <i data-lucide="{{ $icon }}" class="vl-icon-lg"></i>
        </span>
    </div>
</div>
