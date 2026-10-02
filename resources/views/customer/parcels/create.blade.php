<x-village-link-layout title="Book Parcel" header="{{ __('vl.book_parcel') }}">
    <div class="mx-auto max-w-4xl" x-data="parcelBookingForm('{{ route('customer.parcels.route-preview') }}')">
        <div class="mb-6 grid grid-cols-4 gap-2">
            @foreach ([1 => 'Sender', 2 => 'Receiver', 3 => 'Parcel', 4 => 'Review'] as $n => $label)
                <button type="button" @click="step = {{ $n }}" class="rounded-xl border px-2 py-3 text-center transition"
                        :class="step >= {{ $n }} ? 'border-vl-peach/40 bg-vl-accent/20 text-white' : 'border-white/10 bg-white/5 text-white/60'">
                    <span class="mx-auto flex h-8 w-8 items-center justify-center rounded-full text-xs font-bold" :class="step >= {{ $n }} ? 'bg-vl-accent' : 'bg-white/10'">{{ $n }}</span>
                    <span class="mt-2 block truncate text-xs">{{ $label }}</span>
                </button>
            @endforeach
        </div>

        <form method="POST" action="{{ route('customer.parcels.store') }}" class="vl-panel space-y-6">
            @csrf

            @if ($errors->any())
                <div class="rounded-xl border border-red-300/30 bg-red-500/15 px-4 py-3 text-sm text-red-100">{{ $errors->first() }}</div>
            @endif

            <div x-show="step === 1">
                <h3 class="font-display text-xl font-semibold text-vl-peach">Sender Details</h3>
                <div class="mt-5 grid gap-4">
                    <input type="text" name="sender_name" value="{{ old('sender_name', auth()->user()->name) }}" required placeholder="Sender name" class="vl-input">
                    <textarea name="pickup_address" rows="3" required placeholder="Pickup address" class="vl-input" x-model="pickupAddress">{{ old('pickup_address', auth()->user()->address) }}</textarea>
                    <input type="text" name="pickup_location" value="{{ old('pickup_location') }}" required placeholder="Pickup city or area (e.g. Negombo, Matara)" class="vl-input" x-model="pickupLocation" @input.debounce.700ms="refreshRoute()">
                </div>
            </div>

            <div x-show="step === 2" x-cloak>
                <h3 class="font-display text-xl font-semibold text-vl-peach">Receiver Details</h3>
                <div class="mt-5 grid gap-4">
                    <input type="text" name="receiver_name" value="{{ old('receiver_name') }}" required placeholder="Receiver name" class="vl-input">
                    <input type="text" name="receiver_phone" value="{{ old('receiver_phone') }}" required placeholder="Phone" class="vl-input">
                    <textarea name="delivery_address" rows="3" required placeholder="Delivery address" class="vl-input" x-model="deliveryAddress">{{ old('delivery_address') }}</textarea>
                    <input type="text" name="delivery_location" value="{{ old('delivery_location') }}" required placeholder="Delivery city or area (e.g. Anuradhapura, Batticaloa)" class="vl-input" x-model="deliveryLocation" @input.debounce.700ms="refreshRoute()">
                </div>

                <div class="mt-6 space-y-3">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-sm font-medium text-white/80">Live Route Preview</p>
                            <p class="text-xs text-white/55" x-text="routeStatus">Enter pickup and delivery cities to preview the driving route.</p>
                        </div>
                        <div class="text-right text-sm" x-show="distance > 0">
                            <p class="font-semibold text-vl-peach" x-text="distance.toFixed(1) + ' km'"></p>
                            <p class="text-xs text-white/55" x-show="durationMinutes" x-text="durationMinutes + ' min drive'"></p>
                        </div>
                    </div>
                    <div id="bookingRouteMap" class="h-64 overflow-hidden rounded-2xl border border-white/10 bg-[#081318]"></div>
                </div>
            </div>

            <div x-show="step === 3" x-cloak>
                <h3 class="font-display text-xl font-semibold text-vl-peach">Parcel Information</h3>
                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    <select name="parcel_type" required class="vl-input">
                        <option value="document">Document</option>
                        <option value="package">Package</option>
                        <option value="fragile">Fragile</option>
                    </select>
                    <input type="number" step="0.1" name="weight" x-model="weight" required min="0.1" class="vl-input" placeholder="Weight (kg)">
                </div>
                <div class="mt-4">
                    <label class="mb-2 block text-sm font-medium text-white/80">Parcel Size Calculator (cm)</label>
                    <div class="grid gap-3 md:grid-cols-3">
                        <input type="number" step="0.1" name="length_cm" x-model="length" min="1" class="vl-input" placeholder="Length">
                        <input type="number" step="0.1" name="width_cm" x-model="width" min="1" class="vl-input" placeholder="Width">
                        <input type="number" step="0.1" name="height_cm" x-model="height" min="1" class="vl-input" placeholder="Height">
                    </div>
                    <div class="mt-3 grid gap-3 text-sm md:grid-cols-2">
                        <div class="rounded-xl border border-white/10 bg-white/5 p-3">
                            <p class="text-white/55">Volumetric weight</p>
                            <p class="mt-1 font-semibold text-vl-peach" x-text="volumetricWeight().toFixed(2) + ' kg'"></p>
                        </div>
                        <div class="rounded-xl border border-white/10 bg-white/5 p-3">
                            <p class="text-white/55">Chargeable weight</p>
                            <p class="mt-1 font-semibold text-vl-peach" x-text="chargeableWeight().toFixed(2) + ' kg'"></p>
                        </div>
                    </div>
                </div>
                <div class="mt-4">
                    <label class="mb-2 block text-sm font-medium text-white/80">Delivery Speed</label>
                    <select name="delivery_type" x-model="deliveryType" required class="vl-input">
                        <option value="standard">Standard (2-3 days)</option>
                        <option value="express">Express (24h)</option>
                        <option value="same_day">Same Day</option>
                    </select>
                </div>
                <div class="mt-5 rounded-2xl border border-vl-peach/30 bg-vl-accent/15 p-4">
                    <p class="text-sm text-white/60">Estimated Price</p>
                    <p class="mt-1 text-3xl font-bold text-vl-peach" x-text="'Rs. ' + price().toFixed(2)"></p>
                    <p class="mt-2 text-sm text-white/70">Price uses the live route distance between your pickup and delivery locations.</p>
                </div>
            </div>

            <div x-show="step === 4" x-cloak>
                <h3 class="font-display text-xl font-semibold text-vl-peach">Review & Pay</h3>
                <div class="mt-5 rounded-2xl border border-white/10 bg-white/5 p-5">
                    <p class="text-sm leading-6 text-white/70">After submit, the system generates a secure tracking ID and opens the payment page.</p>
                    <p class="mt-3 text-sm text-white/60" x-show="distance > 0" x-text="'Route distance: ' + distance.toFixed(1) + ' km'"></p>
                    <p class="mt-3 text-3xl font-bold text-vl-peach" x-text="'Total: Rs. ' + price().toFixed(2)"></p>
                </div>
            </div>

            <div class="flex justify-between border-t border-white/10 pt-5">
                <button type="button" x-show="step > 1" @click="step--" class="vl-btn-outline">Back</button>
                <span x-show="step === 1"></span>
                <button type="button" x-show="step < 4" @click="step++" class="vl-btn-primary ml-auto">Next</button>
                <button type="submit" x-show="step === 4" class="vl-btn-primary ml-auto">
                    <i data-lucide="credit-card" class="vl-icon"></i>
                    Proceed to Payment
                </button>
            </div>
        </form>
    </div>

    @push('styles')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    @endpush

    @push('scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <script>
            function parcelBookingForm(routePreviewUrl) {
                return {
                    step: 1,
                    weight: 1,
                    length: '',
                    width: '',
                    height: '',
                    distance: 80,
                    durationMinutes: null,
                    deliveryType: 'standard',
                    pickupLocation: @json(old('pickup_location', '')),
                    deliveryLocation: @json(old('delivery_location', '')),
                    pickupAddress: @json(old('pickup_address', auth()->user()->address)),
                    deliveryAddress: @json(old('delivery_address', '')),
                    routeStatus: 'Enter pickup and delivery cities to preview the driving route.',
                    map: null,
                    routeLine: null,
                    pickupMarker: null,
                    deliveryMarker: null,
                    init() {
                        this.$watch('step', (value) => {
                            if (value === 2) {
                                this.$nextTick(() => {
                                    this.ensureMap();
                                    this.refreshRoute();
                                });
                            }
                        });
                    },
                    volumetricWeight() {
                        const l = parseFloat(this.length) || 0;
                        const w = parseFloat(this.width) || 0;
                        const h = parseFloat(this.height) || 0;
                        if (!l || !w || !h) return 0;
                        return (l * w * h) / 5000;
                    },
                    chargeableWeight() {
                        return Math.max(parseFloat(this.weight) || 0, this.volumetricWeight());
                    },
                    price() {
                        const base = 300;
                        const weightCharge = this.chargeableWeight() * 150;
                        const distanceCharge = (parseFloat(this.distance) || 80) * 5;
                        let multiplier = 1;
                        if (this.deliveryType === 'express') multiplier = 1.5;
                        if (this.deliveryType === 'same_day') multiplier = 2;
                        return (base + weightCharge + distanceCharge) * multiplier;
                    },
                    ensureMap() {
                        if (!window.L || this.map) return;

                        this.map = window.L.map('bookingRouteMap', {
                            zoomControl: true,
                            scrollWheelZoom: false,
                        }).setView([7.8731, 80.7718], 7);

                        window.L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            maxZoom: 19,
                            attribution: '&copy; OpenStreetMap contributors',
                        }).addTo(this.map);

                        setTimeout(() => this.map.invalidateSize(), 250);
                    },
                    async refreshRoute() {
                        if (!this.pickupLocation || !this.deliveryLocation) {
                            return;
                        }

                        this.routeStatus = 'Loading live route...';

                        try {
                            const { data } = await window.axios.post(routePreviewUrl, {
                                pickup_location: this.pickupLocation,
                                delivery_location: this.deliveryLocation,
                                pickup_address: this.pickupAddress,
                                delivery_address: this.deliveryAddress,
                            });

                            if (data.distance_km) {
                                this.distance = Number(data.distance_km);
                            }

                            this.durationMinutes = data.duration_minutes || null;
                            this.routeStatus = `Live route ready: ${this.distance.toFixed(1)} km`;
                            this.drawRoute(data);
                        } catch (error) {
                            this.routeStatus = error.response?.data?.message || 'Could not load route for these locations.';
                        }
                    },
                    drawRoute(data) {
                        if (!window.L) return;

                        this.ensureMap();

                        const routePoints = (data.route_points || [])
                            .filter((point) => point && point.lat && point.lng)
                            .map((point) => [Number(point.lat), Number(point.lng)]);

                        if (routePoints.length < 2) {
                            return;
                        }

                        if (this.routeLine) {
                            this.routeLine.setLatLngs(routePoints);
                        } else {
                            this.routeLine = window.L.polyline(routePoints, {
                                color: '#E55634',
                                weight: 5,
                                opacity: 0.9,
                            }).addTo(this.map);
                        }

                        this.setMarker('pickupMarker', data.pickup, 'P');
                        this.setMarker('deliveryMarker', data.delivery, 'D');
                        this.map.fitBounds(this.routeLine.getBounds(), { padding: [28, 28] });
                    },
                    setMarker(key, point, label) {
                        if (!point || !point.lat || !point.lng || !this.map) return;

                        const latLng = [Number(point.lat), Number(point.lng)];
                        const icon = window.L.divIcon({
                            className: 'vl-map-point-icon',
                            html: `<span>${label}</span>`,
                            iconSize: [34, 34],
                            iconAnchor: [17, 17],
                        });

                        if (this[key]) {
                            this[key].setLatLng(latLng);
                            return;
                        }

                        this[key] = window.L.marker(latLng, { icon }).addTo(this.map);
                    },
                }
            }
        </script>
    @endpush
</x-village-link-layout>
