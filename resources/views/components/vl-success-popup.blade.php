@if (session('success'))
<div x-data="{ show: true }" x-show="show" x-cloak
     class="fixed inset-0 z-[200] flex items-center justify-center p-4 bg-black/50 backdrop-blur-md"
     x-transition.opacity>
    <div class="vl-glass-card max-w-md w-full text-center border border-green-400/40 relative overflow-hidden">
        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-green-400 to-emerald-600"></div>
        <div class="w-20 h-20 mx-auto rounded-full flex items-center justify-center mb-5 mt-2"
             style="background: linear-gradient(145deg, rgba(34,197,94,0.3), rgba(16,185,129,0.15)); box-shadow: inset 2px 2px 8px rgba(255,255,255,0.2);">
            <svg class="w-12 h-12 text-green-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <p class="text-lg font-semibold text-white px-4">{{ session('success') }}</p>
        <button @click="show = false" type="button" class="mt-8 vl-btn-primary text-sm px-10">OK ✓</button>
    </div>
</div>
@endif
