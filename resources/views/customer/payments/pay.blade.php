<x-village-link-layout title="Payment" header="{{ __('vl.pay_now') }} - {{ $parcel->tracking_id }}">
    <div class="mx-auto grid max-w-4xl gap-6 md:grid-cols-5">
        <div class="vl-panel md:col-span-2">
            <h3 class="font-display text-lg font-semibold text-vl-peach">Payment Summary</h3>
            <dl class="mt-5 space-y-3 text-sm">
                <div class="flex justify-between gap-4"><dt class="text-white/70">Tracking</dt><dd class="font-mono">{{ $parcel->tracking_id }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-white/70">Delivery</dt><dd>{{ ucfirst(str_replace('_', ' ', $parcel->delivery_type ?? 'standard')) }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-white/70">Weight</dt><dd>{{ $parcel->weight }} kg</dd></div>
                @if (($parcel->chargeable_weight ?? 0) > 0)
                    <div class="flex justify-between gap-4"><dt class="text-white/70">Chargeable</dt><dd>{{ $parcel->chargeable_weight }} kg</dd></div>
                @endif
                <div class="flex justify-between gap-4 border-t border-white/10 pt-3 text-lg font-bold"><dt>Total</dt><dd class="text-vl-peach">Rs. {{ number_format($parcel->price, 2) }}</dd></div>
            </dl>
            <div class="mt-6 rounded-2xl border border-white/10 bg-white/5 p-4 text-sm leading-6 text-white/70">
                Card details are processed on the payment gateway page. Village Link never stores card numbers or CVV.
            </div>
        </div>

        <div class="vl-panel md:col-span-3" x-data="{ method: '{{ $gatewayReady ? 'payhere' : 'bank_transfer' }}', useLoyalty: false, total: {{ (float) $parcel->price }}, discount: {{ (float) $loyalty['discount'] }}, payable() { return this.useLoyalty ? Math.max(this.total - this.discount, 0) : this.total } }">
            <p class="mb-4 text-sm text-white/80">Select a payment method</p>
            <form method="POST" action="{{ route('customer.payments.store', $parcel) }}" class="space-y-4">
                @csrf
                <div class="grid gap-2 sm:grid-cols-3">
                    @foreach ([
                        'payhere' => ['Online Gateway', 'credit-card', $gatewayReady],
                        'bank_transfer' => ['Bank Transfer', 'landmark', true],
                        'cash' => ['Cash on Delivery', 'banknote', true],
                    ] as $val => $item)
                        <label class="flex cursor-pointer items-center gap-2 rounded-xl border p-3 transition"
                               :class="method === '{{ $val }}' ? 'border-vl-peach bg-white/15' : 'border-white/20 hover:bg-white/5'"
                               @class(['opacity-50' => ! $item[2]])>
                            <input type="radio" name="method" value="{{ $val }}" x-model="method" required class="text-vl-accent" @disabled(! $item[2])>
                            <i data-lucide="{{ $item[1] }}" class="vl-icon"></i>
                            <span class="text-sm">{{ $item[0] }}{{ ! $item[2] ? ' (Unavailable)' : '' }}</span>
                        </label>
                    @endforeach
                </div>

                <div class="rounded-2xl border border-vl-peach/30 bg-vl-accent/15 p-4">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm font-semibold text-vl-peach">Loyalty Points</p>
                            <p class="mt-1 text-xs text-white/65">
                                Available: {{ $loyalty['available_points'] }} points.
                                @if ($loyalty['points'] > 0)
                                    Redeem {{ $loyalty['points'] }} points for Rs. {{ number_format($loyalty['discount'], 2) }} discount.
                                @else
                                    Earn points after admin confirms payment.
                                @endif
                            </p>
                        </div>
                        <label class="inline-flex items-center gap-2 text-sm text-white/80">
                            <input type="checkbox" name="use_loyalty" value="1" x-model="useLoyalty" @disabled($loyalty['points'] <= 0) class="rounded">
                            Use points
                        </label>
                    </div>
                    <p class="mt-3 text-sm text-white/70">
                        Payable now:
                        <span class="font-bold text-vl-peach" x-text="'Rs. ' + payable().toFixed(2)"></span>
                    </p>
                </div>

                <div x-show="method === 'payhere'" x-cloak class="rounded-2xl border border-white/10 bg-white/5 p-4 text-sm leading-6 text-white/70">
                    You will be redirected to the secure online payment page.
                </div>
                @unless ($gatewayReady)
                    <div class="rounded-2xl border border-amber-300/30 bg-amber-500/15 p-4 text-sm leading-6 text-amber-100">
                        Online payment is not available right now. Use Bank Transfer or Cash for now.
                    </div>
                @endunless

                <div x-show="method === 'bank_transfer'" x-cloak class="space-y-3 border-t border-white/10 pt-4">
                    <input name="bank_name" placeholder="Bank name" class="vl-input text-sm" :required="method === 'bank_transfer'">
                    <input name="transfer_reference" placeholder="Transfer reference / slip number" class="vl-input text-sm" :required="method === 'bank_transfer'">
                    <p class="text-xs text-white/60">Admin will verify this transfer and mark the parcel payment as paid.</p>
                </div>

                <div x-show="method === 'cash'" x-cloak class="border-t border-white/10 pt-4">
                    <label class="flex items-start gap-2 text-sm text-white/80">
                        <input type="checkbox" name="cash_confirm" value="1" :required="method === 'cash'" class="mt-1 rounded">
                        I confirm cash payment at pickup or delivery time.
                    </label>
                </div>

                @if ($errors->any())
                    <p class="rounded-xl border border-red-300/30 bg-red-500/15 px-4 py-3 text-sm text-red-100">{{ $errors->first() }}</p>
                @endif

                <button type="submit" class="vl-btn-primary w-full">
                    <i data-lucide="lock-keyhole" class="vl-icon"></i>
                    <span x-text="'Continue - Rs. ' + payable().toFixed(2)">Continue - Rs. {{ number_format($parcel->price, 2) }}</span>
                </button>
            </form>
        </div>
    </div>
</x-village-link-layout>
