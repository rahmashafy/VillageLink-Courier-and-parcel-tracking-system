<x-village-link-layout title="Update Status" header="Update - {{ $parcel->tracking_id }}">
    <div class="mx-auto max-w-2xl vl-panel" x-data="driverLocationForm('{{ route('agent.parcels.location.update', $parcel) }}', @js($demoRoute))">
        <p class="text-sm text-white/70">Receiver: <strong class="text-white">{{ $parcel->receiver_name }}</strong></p>
        <div class="mt-4 text-sm">Current: <x-vl-status-badge :status="$parcel->status" /></div>
        <div class="mt-5 rounded-2xl border border-vl-peach/25 bg-vl-peach/10 p-4 text-sm leading-6 text-white/75">
            <p class="font-semibold text-vl-peach">Live GPS needs browser location permission.</p>
            <p class="mt-1">Turn on Windows Location, allow location for this site, then click Start Live GPS. Keep this page open while delivering.</p>
        </div>
        <form method="POST" action="{{ route('agent.parcels.status.update', $parcel) }}" enctype="multipart/form-data" class="mt-6 space-y-4">
            @csrf
            <select name="status" required class="vl-input">
                @foreach (['pending_pickup', 'picked_up', 'in_transit', 'arrived_center', 'out_for_delivery', 'delivered'] as $status)
                    <option value="{{ $status }}" @selected($parcel->status === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                @endforeach
            </select>
            <div class="grid gap-3 md:grid-cols-2">
                <input name="latitude" x-model="latitude" placeholder="Latitude" class="vl-input">
                <input name="longitude" x-model="longitude" placeholder="Longitude" class="vl-input">
            </div>
            <button type="button" @click="capture()" class="vl-btn-outline w-full">
                <i data-lucide="map-pin" class="vl-icon"></i>
                Use Current Location
            </button>
            <button type="button" @click="toggleLive()" class="vl-btn-outline w-full">
                <i data-lucide="radar" class="vl-icon"></i>
                <span x-text="live ? 'Live GPS Running - Stop' : 'Start Live GPS'">Start Live GPS</span>
            </button>
            <button type="button" @click="sendDemoLocation()" class="vl-btn-outline w-full">
                <i data-lucide="target" class="vl-icon"></i>
                Follow Route GPS
            </button>
            <p class="rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-xs text-white/65" x-text="liveStatus">
                Live GPS is off.
            </p>
            <textarea name="delivery_notes" rows="3" placeholder="Delivery notes" class="vl-input">{{ old('delivery_notes', $parcel->delivery_notes) }}</textarea>
            <div>
                <label class="mb-2 block text-sm text-white/80">Proof photo required when delivered</label>
                <input type="file" name="delivery_proof" accept="image/*" class="w-full rounded-xl border border-white/15 bg-white/5 px-4 py-3 text-sm text-white/80">
                @if ($parcel->delivery_proof_path)
                    <a href="{{ asset('storage/'.$parcel->delivery_proof_path) }}" target="_blank" class="mt-2 inline-block text-sm text-vl-peach hover:underline">View current proof</a>
                @endif
            </div>
            @if ($errors->any())<p class="text-sm text-red-300">{{ $errors->first() }}</p>@endif
            <button type="submit" class="vl-btn-primary w-full">
                <i data-lucide="refresh-cw" class="vl-icon"></i>
                Update Status
            </button>
        </form>
    </div>

    @push('scripts')
        <script>
            function driverLocationForm(locationUrl, demoRoute = []) {
                return {
                    latitude: '',
                    longitude: '',
                    live: false,
                    watchId: null,
                    demoTimer: null,
                    demoIndex: 0,
                    liveStatus: 'Live GPS is off.',
                    init() {
                        window.addEventListener('beforeunload', () => this.stopLive());
                    },
                    capture() {
                        if (!navigator.geolocation) {
                            this.liveStatus = 'Location is not available in this browser.';
                            return;
                        }
                        navigator.geolocation.getCurrentPosition((position) => {
                            this.latitude = position.coords.latitude.toFixed(7);
                            this.longitude = position.coords.longitude.toFixed(7);
                            this.liveStatus = 'Current location captured. Click Update Status to save it.';
                        }, (error) => this.handleGeoError(error), {
                            enableHighAccuracy: true,
                            maximumAge: 5000,
                            timeout: 20000,
                        });
                    },
                    toggleLive() {
                        if (this.live) {
                            this.stopLive();
                            return;
                        }

                        this.startLive();
                    },
                    startLive() {
                        if (!navigator.geolocation) {
                            this.startRouteGps('Browser location is not available. Route GPS is active for the live map.');
                            return;
                        }

                        this.live = true;
                        this.liveStatus = 'Starting live GPS. Allow location permission when the browser asks.';

                        const options = {
                            enableHighAccuracy: true,
                            maximumAge: 5000,
                            timeout: 20000,
                        };

                        navigator.geolocation.getCurrentPosition(
                            (position) => this.postPosition(position, 'Live driver GPS'),
                            (error) => this.handleGeoError(error),
                            options,
                        );

                        this.watchId = navigator.geolocation.watchPosition(
                            (position) => this.postPosition(position, 'Live driver GPS'),
                            (error) => this.handleGeoError(error),
                            options,
                        );
                    },
                    stopLive() {
                        this.live = false;

                        if (this.watchId !== null) {
                            navigator.geolocation.clearWatch(this.watchId);
                            this.watchId = null;
                        }

                        if (this.demoTimer !== null) {
                            clearInterval(this.demoTimer);
                            this.demoTimer = null;
                        }

                        this.liveStatus = 'Live GPS is off.';
                    },
                    async postPosition(position, note) {
                        if (!this.live) return;

                        this.latitude = position.coords.latitude.toFixed(7);
                        this.longitude = position.coords.longitude.toFixed(7);

                        try {
                            await window.axios.post(locationUrl, {
                                latitude: this.latitude,
                                longitude: this.longitude,
                                speed_kmh: position.coords.speed ? Math.round(position.coords.speed * 3.6) : null,
                                note,
                            });

                            this.liveStatus = `Live GPS active. Updated ${new Date().toLocaleTimeString()}`;
                        } catch (error) {
                            this.liveStatus = error.response?.data?.message || 'Could not send live GPS update.';
                        }
                    },
                    handleGeoError(error) {
                        if (this.watchId !== null) {
                            navigator.geolocation.clearWatch(this.watchId);
                            this.watchId = null;
                        }

                        const messages = {
                            1: 'Location permission is blocked. Click the lock icon near the address bar and set Location to Allow.',
                            2: 'Location is unavailable. Turn on Windows Location services and try again.',
                            3: 'Location request timed out. Move near a window or use a device with GPS.',
                        };

                        this.startRouteGps((messages[error?.code] || 'Could not read location.') + ' Route GPS is active for presentation.');
                    },
                    startRouteGps(message) {
                        if (!demoRoute.length) {
                            this.live = false;
                            this.liveStatus = message || 'Route GPS is unavailable for this parcel.';
                            return;
                        }

                        this.live = true;
                        this.liveStatus = message || 'Route GPS is running. Customer live map will update every 5 seconds.';
                        this.sendDemoLocation('Presentation route GPS');

                        if (this.demoTimer === null) {
                            this.demoTimer = setInterval(() => this.sendDemoLocation('Presentation route GPS'), 3000);
                        }
                    },
                    async sendDemoLocation(note = 'Route demo GPS') {
                        if (!demoRoute.length) {
                            this.liveStatus = 'Demo GPS route is unavailable for this parcel location.';
                            return;
                        }

                        const point = demoRoute[this.demoIndex % demoRoute.length];
                        this.demoIndex++;
                        this.latitude = Number(point.lat).toFixed(7);
                        this.longitude = Number(point.lng).toFixed(7);

                        try {
                            await window.axios.post(locationUrl, {
                                latitude: this.latitude,
                                longitude: this.longitude,
                                speed_kmh: 35,
                                note,
                            });

                            this.liveStatus = `${note} updated ${new Date().toLocaleTimeString()}. Customer live map is updating.`;
                        } catch (error) {
                            this.liveStatus = error.response?.data?.message || 'Could not send demo GPS update.';
                        }
                    },
                }
            }
        </script>
    @endpush
</x-village-link-layout>
