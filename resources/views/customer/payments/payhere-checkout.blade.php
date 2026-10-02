<x-village-link-layout title="Redirecting to Secure Payment" header="Secure Checkout">
    <div class="mx-auto max-w-xl vl-panel text-center">
        <i data-lucide="lock-keyhole" class="mx-auto h-12 w-12 text-vl-peach"></i>
        <h2 class="mt-4 font-display text-2xl font-semibold text-vl-peach">Redirecting to secure payment</h2>
        <p class="mt-3 text-sm leading-6 text-white/70">Please wait while we send your payment request to the secure provider.</p>

        <form id="payhere-form" method="POST" action="{{ $checkoutUrl }}" class="mt-6">
            @foreach ($payload as $key => $value)
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endforeach
            <button type="submit" class="vl-btn-primary mx-auto">
                <i data-lucide="arrow-right" class="vl-icon"></i>
                Continue to Secure Payment
            </button>
        </form>
    </div>

    @push('scripts')
        <script>
            setTimeout(() => document.getElementById('payhere-form')?.submit(), 900);
        </script>
    @endpush
</x-village-link-layout>
