<x-village-link-layout title="Tracking Result" header="Parcel: {{ $parcel->tracking_id }}">
    @php
        $currentIndex = array_search($parcel->status, $statusSteps, true);
        $currentIndex = $currentIndex === false ? 0 : $currentIndex;
        $progress = count($statusSteps) > 1 ? round(($currentIndex / (count($statusSteps) - 1)) * 100) : 0;
    @endphp

    <div class="grid gap-6 xl:grid-cols-[1.25fr_0.75fr]">
        <div class="space-y-6">
            <div class="vl-panel vl-reveal">
                <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                    <div>
                        <p class="text-xs uppercase tracking-[0.22em] text-white/50">Current Status</p>
                        <div class="mt-2"><x-vl-status-badge :status="$parcel->status" /></div>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-right">
                        <p class="text-xs text-white/50">Tracking ID</p>
                        <p class="font-mono text-2xl font-bold text-vl-peach">{{ $parcel->tracking_id }}</p>
                    </div>
                </div>

                <div class="mt-8">
                    <div class="vl-progress-track">
                        <div class="vl-progress-fill" style="width: {{ $progress }}%"></div>
                    </div>
                    <div class="mt-5 grid grid-cols-2 gap-3 md:grid-cols-6">
                        @foreach ($statusSteps as $step)
                            @php
                                $done = $currentIndex >= $loop->index;
                            @endphp
                            <div class="rounded-xl border px-3 py-3 text-center {{ $done ? 'border-vl-peach/40 bg-vl-accent/15' : 'border-white/10 bg-white/5' }}">
                                <span class="mx-auto flex h-9 w-9 items-center justify-center rounded-full {{ $done ? 'bg-vl-accent text-white' : 'bg-white/10 text-white/50' }}">
                                    @if ($done)
                                        <i data-lucide="check" class="h-4 w-4"></i>
                                    @else
                                        {{ $loop->iteration }}
                                    @endif
                                </span>
                                <p class="mt-2 text-xs text-white/70">{{ ucfirst(str_replace('_', ' ', $step)) }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="vl-panel">
                <div class="flex items-center justify-between">
                    <h3 class="font-display text-lg font-semibold">Status History</h3>
                    <i data-lucide="history" class="h-5 w-5 text-vl-peach"></i>
                </div>
                <div class="mt-6 grid gap-6 lg:grid-cols-[0.85fr_1.15fr]">
                    <ol class="space-y-4">
                        @forelse ($statusHistory as $history)
                            <li class="relative border-l border-vl-peach/40 pl-5">
                                <span class="absolute -left-[7px] top-1 h-3 w-3 rounded-full bg-vl-peach"></span>
                                <x-vl-status-badge :status="$history->status" />
                                <p class="mt-2 text-sm text-white/70">{{ $history->note }}</p>
                                <p class="mt-1 text-xs text-white/50">
                                    {{ $history->created_at->timezone(config('app.timezone'))->format('M d, Y g:i A') }}
                                </p>
                            </li>
                        @empty
                            <li class="rounded-xl border border-white/10 bg-white/5 p-4 text-sm text-white/60">No history yet.</li>
                        @endforelse
                    </ol>

                    <div class="vl-tracking-video" aria-hidden="true">
                        <video src="{{ asset('videos/delivery-route-demo.mp4') }}" autoplay muted loop playsinline preload="auto"></video>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-6" x-data="parcelLiveLocation('{{ route('customer.parcels.location', $parcel) }}', @js(config('services.maps.api_key')))">
            <div class="vl-live-map-panel">
                <div id="liveRouteMap" class="vl-live-map"></div>
                <div class="vl-live-route-fallback" x-show="useFallbackMap" x-cloak>
                    <svg viewBox="0 0 1000 520" role="img" aria-label="Live delivery route">
                        <defs>
                            <pattern id="route-grid" width="32" height="32" patternUnits="userSpaceOnUse">
                                <path d="M 32 0 L 0 0 0 32" fill="none" stroke="rgba(255,255,255,0.06)" stroke-width="1"/>
                            </pattern>
                        </defs>
                        <rect width="1000" height="520" fill="url(#route-grid)"></rect>
                        <path :d="fallbackPath" class="vl-fallback-route-shadow"></path>
                        <path :d="fallbackPath" class="vl-fallback-route-line"></path>
                        <template x-if="fallbackPickup">
                            <g :transform="`translate(${fallbackPickup.x} ${fallbackPickup.y})`">
                                <circle r="20" class="vl-fallback-point vl-fallback-pickup"></circle>
                                <text text-anchor="middle" dy="5">P</text>
                            </g>
                        </template>
                        <template x-if="fallbackDelivery">
                            <g :transform="`translate(${fallbackDelivery.x} ${fallbackDelivery.y})`">
                                <circle r="20" class="vl-fallback-point vl-fallback-delivery"></circle>
                                <text text-anchor="middle" dy="5">D</text>
                            </g>
                        </template>
                        <template x-if="fallbackVehicle">
                            <g :transform="`translate(${fallbackVehicle.x} ${fallbackVehicle.y})`" class="vl-fallback-vehicle">
                                <path d="M -38 5 L -28 -15 H 16 C 27 -15 36 -6 38 5 L 38 17 H -38 Z"></path>
                                <rect x="-24" y="-11" width="17" height="13" rx="3"></rect>
                                <rect x="-3" y="-11" width="18" height="13" rx="3"></rect>
                                <circle cx="-22" cy="18" r="7"></circle>
                                <circle cx="24" cy="18" r="7"></circle>
                            </g>
                        </template>
                    </svg>
                </div>
                <div class="absolute left-4 top-4 z-[500] max-w-[75%] rounded-2xl border border-white/15 bg-[#081318]/80 p-4 backdrop-blur">
                    <p class="text-xs text-white/50">Route</p>
                    <p class="mt-1 font-semibold text-vl-peach">{{ $parcel->pickup_location ?? 'Origin' }} to {{ $parcel->delivery_location ?? 'Destination' }}</p>
                </div>
                <div class="absolute right-4 top-4 z-[500] flex items-center gap-2" x-show="isLive" x-cloak>
                    <span class="vl-live-badge">
                        <span class="vl-live-badge-dot"></span>
                        LIVE
                    </span>
                </div>
                <button type="button"
                        @click="toggleFollow()"
                        class="vl-map-follow-btn"
                        :class="followVehicle ? 'vl-map-follow-btn-active' : ''"
                        :title="followVehicle ? 'Following driver' : 'Follow driver'">
                    <i data-lucide="target" class="h-4 w-4"></i>
                </button>
                <div class="absolute bottom-4 right-4 z-[500] max-w-[75%] rounded-2xl border border-white/15 bg-[#081318]/80 p-4 text-sm backdrop-blur">
                    <p class="font-semibold" x-text="driverName">{{ $parcel->agent->name ?? 'Driver pending' }}</p>
                    <p class="mt-1 text-white/60" x-text="etaText">{{ $parcel->estimated_delivery_at ? 'ETA '.$parcel->estimated_delivery_at->format('M d, g:i A') : 'ETA pending' }}</p>
                    <p class="mt-1 text-xs text-white/50" x-text="distanceText"></p>
                    <p class="mt-1 text-xs text-white/50" x-text="remainingText" x-show="remainingText"></p>
                    <p class="mt-1 text-xs text-white/50" x-text="summary"></p>
                </div>
                <div x-show="mapStatus" class="absolute bottom-4 left-4 z-[500] flex max-w-[75%] items-center gap-2 rounded-2xl border border-white/15 bg-[#081318]/80 px-4 py-3 text-xs text-white/70 backdrop-blur">
                    <i data-lucide="radar" class="h-4 w-4 text-vl-peach"></i>
                    <span x-text="mapStatus"></span>
                </div>
            </div>

            <div class="vl-panel text-sm">
                <h3 class="font-display text-lg font-semibold text-vl-cream">Parcel Details</h3>
                <dl class="mt-5 space-y-3">
                    <div class="flex justify-between gap-4"><dt class="text-white/60">Receiver</dt><dd class="text-right font-medium">{{ $parcel->receiver_name }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-white/60">Phone</dt><dd class="text-right">{{ $parcel->receiver_phone }}</dd></div>
                    <div class="border-t border-white/10 pt-3"><dt class="text-white/60">From</dt><dd class="mt-1">{{ $parcel->pickup_address }}</dd></div>
                    <div class="border-t border-white/10 pt-3"><dt class="text-white/60">To</dt><dd class="mt-1">{{ $parcel->delivery_address }}</dd></div>
                    <div class="flex justify-between gap-4 border-t border-white/10 pt-3"><dt class="text-white/60">Price</dt><dd class="font-semibold text-vl-peach">Rs. {{ number_format($parcel->price, 2) }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-white/60">Payment</dt><dd>{{ ucfirst($parcel->payment_status ?? 'unpaid') }}</dd></div>
                    <div class="border-t border-white/10 pt-3">
                        <dt class="text-white/60">Latest GPS</dt>
                        <dd class="mt-1 text-xs" x-text="gpsText">{{ $parcel->current_lat ? $parcel->current_lat.', '.$parcel->current_lng : 'Waiting for driver update' }}</dd>
                    </div>
                    @if ($parcel->delivery_proof_path)
                        <div class="border-t border-white/10 pt-3">
                            <dt class="text-white/60">Delivery Proof</dt>
                            <dd class="mt-1"><a href="{{ asset('storage/'.$parcel->delivery_proof_path) }}" target="_blank" class="text-vl-peach hover:underline">View proof photo</a></dd>
                        </div>
                    @endif
                </dl>
            </div>
        </div>
    </div>

    @push('styles')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    @endpush

    @push('scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <script>
            function parcelLiveLocation(url, mapsApiKey = null) {
                return {
                    gpsText: 'Loading latest location...',
                    summary: '',
                    distanceText: '',
                    remainingText: '',
                    mapStatus: 'Opening live route map...',
                    driverName: @json($parcel->agent->name ?? 'Driver pending'),
                    etaText: @json($parcel->estimated_delivery_at ? 'ETA '.$parcel->estimated_delivery_at->format('M d, g:i A') : 'ETA pending'),
                    isLive: false,
                    followVehicle: true,
                    mapsApiKey,
                    pollTimer: null,
                    map: null,
                    pickupMarker: null,
                    deliveryMarker: null,
                    vehicleMarker: null,
                    baseRouteLine: null,
                    traveledRouteLine: null,
                    remainingRouteLine: null,
                    hasFitted: false,
                    useFallbackMap: false,
                    fallbackPath: '',
                    fallbackPickup: null,
                    fallbackDelivery: null,
                    fallbackVehicle: null,
                    animationFrame: null,
                    displayedVehicle: null,
                    currentBearing: 0,
                    init() {
                        this.load();
                        this.pollTimer = setInterval(() => this.load(), 2000);
                    },
                    destroy() {
                        if (this.pollTimer) clearInterval(this.pollTimer);
                        if (this.animationFrame) cancelAnimationFrame(this.animationFrame);
                    },
                    toggleFollow() {
                        this.followVehicle = !this.followVehicle;
                        this.mapStatus = this.followVehicle
                            ? 'Following driver on map.'
                            : 'Map follow mode off.';
                    },
                    async load() {
                        try {
                            const { data } = await window.axios.get(url);
                            this.driverName = data.driver || 'Driver pending';
                            this.isLive = Boolean(data.is_live && data.vehicle);
                            this.etaText = data.live_eta_minutes
                                ? `Arriving in ~${data.live_eta_minutes} min`
                                : (data.eta ? `ETA ${data.eta}` : 'ETA pending');
                            this.gpsText = data.vehicle
                                ? `${Number(data.vehicle.lat).toFixed(6)}, ${Number(data.vehicle.lng).toFixed(6)}`
                                : 'Waiting for driver live GPS';
                            this.summary = data.updated_at ? `GPS updated ${new Date(data.updated_at).toLocaleString()}` : '';
                            this.distanceText = data.distance_km ? `Total route ${Number(data.distance_km).toFixed(1)} km` : '';
                            this.remainingText = data.remaining_km
                                ? `${Number(data.remaining_km).toFixed(1)} km remaining${data.speed_kmh ? ` · ${Math.round(data.speed_kmh)} km/h` : ''}`
                                : '';
                            this.updateMap(data);
                        } catch (error) {
                            this.gpsText = 'Location unavailable';
                            this.mapStatus = 'Live location temporarily unavailable.';
                            this.isLive = false;
                        }
                    },
                    addTileLayer() {
                        if (this.mapsApiKey) {
                            return window.L.tileLayer('https://mt1.google.com/vt/lyrs=m&x={x}&y={y}&z={z}&key=' + this.mapsApiKey, {
                                maxZoom: 20,
                                attribution: '&copy; Google Maps',
                            });
                        }

                        return window.L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            maxZoom: 19,
                            attribution: '&copy; OpenStreetMap contributors',
                        });
                    },
                    updateMap(data) {
                        if (!window.L) {
                            this.useFallbackMap = true;
                            this.buildFallbackMap(data);
                            this.mapStatus = data.vehicle
                                ? 'Live route view is active. Vehicle position is updating.'
                                : 'Route view is active. Waiting for driver live GPS.';
                            return;
                        }

                        this.useFallbackMap = false;
                        const fallback = data.vehicle || data.pickup || data.delivery || { lat: 7.8731, lng: 80.7718 };

                        if (!this.map) {
                            this.map = window.L.map('liveRouteMap', {
                                zoomControl: true,
                                scrollWheelZoom: true,
                            }).setView([fallback.lat, fallback.lng], data.vehicle ? 14 : 8);

                            this.addTileLayer().addTo(this.map);

                            this.map.on('dragstart', () => {
                                this.followVehicle = false;
                            });

                            setTimeout(() => this.map.invalidateSize(), 250);
                        }

                        const pickupIcon = this.pointIcon('P', 'pickup');
                        const deliveryIcon = this.pointIcon('D', 'delivery');
                        const vehicleLabel = [data.vehicle_type, data.vehicle_number].filter(Boolean).join(' - ') || 'Delivery vehicle';
                        const mapVehicle = data.route_vehicle || data.vehicle;
                        const vehicleBearing = this.routeBearing(mapVehicle, data.route_points || []);

                        this.setMarker('pickupMarker', data.pickup, pickupIcon, 'Pickup');
                        this.setMarker('deliveryMarker', data.delivery, deliveryIcon, 'Drop-off');
                        this.animateVehicle(
                            mapVehicle || data.pickup,
                            vehicleBearing,
                            vehicleLabel,
                            data.vehicle_type,
                            Boolean(data.vehicle),
                            data.is_live,
                        );

                        const routePoints = (data.route_points || [])
                            .filter((point) => point && point.lat && point.lng)
                            .map((point) => [Number(point.lat), Number(point.lng)]);

                        if (routePoints.length >= 2) {
                            const splitIndex = this.nearestRouteIndex(routePoints, mapVehicle);
                            const traveledPoints = routePoints.slice(0, splitIndex + 1);
                            const remainingPoints = routePoints.slice(splitIndex);

                            this.baseRouteLine = this.setPolyline(this.baseRouteLine, routePoints, {
                                color: '#ffffff',
                                weight: 10,
                                opacity: 0.96,
                            });
                            this.traveledRouteLine = this.setPolyline(this.traveledRouteLine, traveledPoints, {
                                color: '#16a34a',
                                weight: 7,
                                opacity: 0.98,
                            });
                            this.remainingRouteLine = this.setPolyline(this.remainingRouteLine, remainingPoints, {
                                color: '#f97316',
                                weight: 7,
                                opacity: 0.98,
                            });

                            if (!this.hasFitted) {
                                this.map.fitBounds(this.baseRouteLine.getBounds(), { padding: [34, 34] });
                                this.hasFitted = true;
                            }
                        }

                        this.mapStatus = data.vehicle
                            ? (data.is_live ? 'Live driver GPS is active.' : 'Driver location received.')
                            : 'Route preview ready. Waiting for driver live GPS.';
                    },
                    animateVehicle(point, bearing, label, vehicleType, isLiveVehicle, isGpsLive) {
                        if (!point || !point.lat || !point.lng || !this.map) return;

                        const target = {
                            lat: Number(point.lat),
                            lng: Number(point.lng),
                        };
                        const showPulse = Boolean(isGpsLive && isLiveVehicle);
                        const buildIcon = (currentBearing) => {
                            const spec = this.vehicleIconSpec(vehicleType, label, currentBearing, showPulse);

                            return window.L.divIcon({
                                className: 'vl-map-vehicle-icon',
                                html: spec.html,
                                iconSize: spec.size,
                                iconAnchor: spec.anchor,
                            });
                        };

                        if (!this.vehicleMarker) {
                            this.displayedVehicle = { ...target };
                            this.currentBearing = bearing;
                            this.vehicleMarker = window.L.marker([target.lat, target.lng], {
                                icon: buildIcon(bearing),
                                title: label,
                                zIndexOffset: 1000,
                            }).addTo(this.map);
                            return;
                        }

                        if (this.animationFrame) {
                            cancelAnimationFrame(this.animationFrame);
                        }

                        const start = this.displayedVehicle || {
                            lat: this.vehicleMarker.getLatLng().lat,
                            lng: this.vehicleMarker.getLatLng().lng,
                        };
                        const startBearing = this.currentBearing;
                        const startTime = performance.now();
                        const duration = 1800;

                        const step = (now) => {
                            const progress = Math.min(1, (now - startTime) / duration);
                            const eased = progress < 0.5
                                ? 2 * progress * progress
                                : 1 - ((-2 * progress + 2) ** 2) / 2;
                            const lat = start.lat + (target.lat - start.lat) * eased;
                            const lng = start.lng + (target.lng - start.lng) * eased;
                            const currentBearing = startBearing + this.bearingDelta(startBearing, bearing) * eased;

                            this.displayedVehicle = { lat, lng };
                            this.currentBearing = currentBearing;
                            this.vehicleMarker.setLatLng([lat, lng]);
                            this.vehicleMarker.setIcon(buildIcon(Math.round(currentBearing)));

                            if (this.followVehicle) {
                                this.map.panTo([lat, lng], { animate: true, duration: 0.35 });
                            }

                            if (progress < 1) {
                                this.animationFrame = requestAnimationFrame(step);
                            } else {
                                this.animationFrame = null;
                            }
                        };

                        this.animationFrame = requestAnimationFrame(step);
                    },
                    bearingDelta(from, to) {
                        let delta = ((to - from + 540) % 360) - 180;
                        return delta;
                    },
                    buildFallbackMap(data) {
                        const mapVehicle = data.route_vehicle || data.vehicle;
                        const routePoints = (data.route_points || [])
                            .filter((point) => point && point.lat && point.lng)
                            .map((point) => ({ lat: Number(point.lat), lng: Number(point.lng) }));
                        const points = [
                            data.pickup,
                            ...routePoints,
                            mapVehicle,
                            data.delivery,
                        ].filter((point) => point && point.lat && point.lng)
                            .map((point) => ({ lat: Number(point.lat), lng: Number(point.lng) }));

                        if (points.length === 0) {
                            this.fallbackPath = '';
                            return;
                        }

                        const bounds = this.pointBounds(points);
                        const projectedRoute = (routePoints.length >= 2 ? routePoints : points)
                            .map((point) => this.projectPoint(point, bounds));

                        this.fallbackPath = projectedRoute
                            .map((point, index) => `${index === 0 ? 'M' : 'L'} ${point.x} ${point.y}`)
                            .join(' ');
                        this.fallbackPickup = data.pickup ? this.projectPoint(data.pickup, bounds) : null;
                        this.fallbackDelivery = data.delivery ? this.projectPoint(data.delivery, bounds) : null;
                        this.fallbackVehicle = mapVehicle
                            ? this.projectPoint(mapVehicle, bounds)
                            : this.fallbackPickup;
                    },
                    pointBounds(points) {
                        const lats = points.map((point) => Number(point.lat));
                        const lngs = points.map((point) => Number(point.lng));

                        return {
                            minLat: Math.min(...lats),
                            maxLat: Math.max(...lats),
                            minLng: Math.min(...lngs),
                            maxLng: Math.max(...lngs),
                        };
                    },
                    projectPoint(point, bounds) {
                        const width = 1000;
                        const height = 520;
                        const padding = 78;
                        const latRange = Math.max(bounds.maxLat - bounds.minLat, 0.0001);
                        const lngRange = Math.max(bounds.maxLng - bounds.minLng, 0.0001);
                        const x = padding + ((Number(point.lng) - bounds.minLng) / lngRange) * (width - padding * 2);
                        const y = height - padding - ((Number(point.lat) - bounds.minLat) / latRange) * (height - padding * 2);

                        return {
                            x: Number(x.toFixed(2)),
                            y: Number(y.toFixed(2)),
                        };
                    },
                    setMarker(key, point, icon, title) {
                        if (!point || !point.lat || !point.lng || !this.map) return;

                        const latLng = [Number(point.lat), Number(point.lng)];

                        if (this[key]) {
                            this[key].setLatLng(latLng);
                            this[key].setIcon(icon);
                            return;
                        }

                        this[key] = window.L.marker(latLng, { icon, title }).addTo(this.map);
                    },
                    nearestRouteIndex(routePoints, vehicle) {
                        if (!vehicle || !vehicle.lat || !vehicle.lng || routePoints.length < 2) {
                            return 0;
                        }

                        let bestIndex = 0;
                        let bestDistance = Number.POSITIVE_INFINITY;

                        routePoints.forEach((point, index) => {
                            const distance = Math.abs(point[0] - Number(vehicle.lat)) + Math.abs(point[1] - Number(vehicle.lng));

                            if (distance < bestDistance) {
                                bestDistance = distance;
                                bestIndex = index;
                            }
                        });

                        return bestIndex;
                    },
                    routeBearing(vehicle, routePoints) {
                        const points = (routePoints || [])
                            .filter((point) => point && point.lat && point.lng)
                            .map((point) => [Number(point.lat), Number(point.lng)]);
                        const index = this.nearestRouteIndex(points, vehicle);
                        const from = points[Math.max(0, index - 1)] || points[index];
                        const to = points[Math.min(points.length - 1, index + 1)] || points[index];

                        if (!from || !to) {
                            return 0;
                        }

                        const lat1 = from[0] * Math.PI / 180;
                        const lat2 = to[0] * Math.PI / 180;
                        const deltaLng = (to[1] - from[1]) * Math.PI / 180;
                        const y = Math.sin(deltaLng) * Math.cos(lat2);
                        const x = Math.cos(lat1) * Math.sin(lat2) - Math.sin(lat1) * Math.cos(lat2) * Math.cos(deltaLng);

                        return Math.round((Math.atan2(y, x) * 180 / Math.PI + 360) % 360);
                    },
                    setPolyline(line, points, options) {
                        if (points.length < 2) {
                            if (line) {
                                line.remove();
                            }

                            return null;
                        }

                        if (line) {
                            line.setLatLngs(points);
                            line.setStyle(options);
                            line.bringToFront();
                            return line;
                        }

                        return window.L.polyline(points, options).addTo(this.map).bringToFront();
                    },
                    pointIcon(label, type) {
                        return window.L.divIcon({
                            className: `vl-map-point-icon vl-map-point-${type}`,
                            html: `<span>${label}</span>`,
                            iconSize: [34, 34],
                            iconAnchor: [17, 17],
                        });
                    },
                    vehicleKind(type) {
                        const value = String(type || 'van').toLowerCase();

                        if (value.includes('bike') || value.includes('motor') || value.includes('scooter')) return 'bike';
                        if (value.includes('truck') || value.includes('lorry')) return 'truck';
                        if (value.includes('car')) return 'car';

                        return 'van';
                    },
                    vehicleIconSpec(type, label, bearing, isLive = false) {
                        const kind = this.vehicleKind(type);
                        const liveClass = isLive ? ' vl-map-vehicle-live' : '';
                        const specs = {
                            car: {
                                size: [26, 39],
                                anchor: [13, 19.5],
                                svg: `
                                    <svg class="vl-map-vehicle-svg" viewBox="0 0 64 96" role="img" aria-hidden="true">
                                        <ellipse class="vl-car-shadow" cx="32" cy="84" rx="21" ry="7"></ellipse>
                                        <path class="vl-car-body" d="M32 5C43.5 5 49.4 15.7 51.2 32.6L55 70.8C56.1 82.6 48.8 91 38.9 91H25.1C15.2 91 7.9 82.6 9 70.8L12.8 32.6C14.6 15.7 20.5 5 32 5Z"></path>
                                        <path class="vl-car-glass-front" d="M22.1 16.7C25.3 11.1 38.7 11.1 41.9 16.7L46 32.8C37.1 29.5 26.9 29.5 18 32.8L22.1 16.7Z"></path>
                                        <path class="vl-car-glass-main" d="M18.3 38.8C27.5 35.2 36.5 35.2 45.7 38.8L47.8 61.3C38.1 58.3 25.9 58.3 16.2 61.3L18.3 38.8Z"></path>
                                        <path class="vl-car-hood-line" d="M21.5 68.5C28.2 71.4 35.8 71.4 42.5 68.5"></path>
                                        <path class="vl-car-light vl-car-light-left" d="M17.1 75.7C20.3 78.3 23.7 79.4 27.4 79.2"></path>
                                        <path class="vl-car-light vl-car-light-right" d="M46.9 75.7C43.7 78.3 40.3 79.4 36.6 79.2"></path>
                                        <path class="vl-car-mirror vl-car-mirror-left" d="M12.8 36.2L5.7 41.1"></path>
                                        <path class="vl-car-mirror vl-car-mirror-right" d="M51.2 36.2L58.3 41.1"></path>
                                    </svg>
                                `,
                            },
                            van: {
                                size: [29, 43],
                                anchor: [14.5, 21.5],
                                svg: `
                                    <svg class="vl-map-vehicle-svg" viewBox="0 0 68 100" role="img" aria-hidden="true">
                                        <ellipse class="vl-car-shadow" cx="34" cy="88" rx="23" ry="8"></ellipse>
                                        <path class="vl-car-body vl-van-body" d="M34 5C46.2 5 53.4 16.6 54.8 34.1L58 72.8C59 84.7 50.8 93 39.6 93H28.4C17.2 93 9 84.7 10 72.8L13.2 34.1C14.6 16.6 21.8 5 34 5Z"></path>
                                        <path class="vl-car-glass-front" d="M22.4 17C26.4 11.2 41.6 11.2 45.6 17L49.4 34.4C40.1 31 27.9 31 18.6 34.4L22.4 17Z"></path>
                                        <path class="vl-car-glass-main" d="M18.6 42.4C28 39.1 40 39.1 49.4 42.4L51.2 63.8C40.4 60.8 27.6 60.8 16.8 63.8L18.6 42.4Z"></path>
                                        <path class="vl-car-hood-line" d="M22 73C29.8 76 38.2 76 46 73"></path>
                                        <path class="vl-car-light vl-car-light-left" d="M18.2 80.4C21.6 82.9 25.3 84 29.3 83.8"></path>
                                        <path class="vl-car-light vl-car-light-right" d="M49.8 80.4C46.4 82.9 42.7 84 38.7 83.8"></path>
                                        <path class="vl-car-mirror vl-car-mirror-left" d="M13.4 38.1L6.1 43.2"></path>
                                        <path class="vl-car-mirror vl-car-mirror-right" d="M54.6 38.1L61.9 43.2"></path>
                                    </svg>
                                `,
                            },
                            truck: {
                                size: [31, 46],
                                anchor: [15.5, 23],
                                svg: `
                                    <svg class="vl-map-vehicle-svg" viewBox="0 0 72 108" role="img" aria-hidden="true">
                                        <ellipse class="vl-car-shadow" cx="36" cy="94" rx="25" ry="8"></ellipse>
                                        <path class="vl-car-body vl-truck-cargo" d="M15 30H57L61 82C61.7 91.1 55.6 98 46.7 98H25.3C16.4 98 10.3 91.1 11 82L15 30Z"></path>
                                        <path class="vl-car-body vl-truck-cabin" d="M22.8 8H49.2C53.4 8 56.8 15.4 57.5 27.2L14.5 27.2C15.2 15.4 18.6 8 22.8 8Z"></path>
                                        <path class="vl-car-glass-front" d="M23.5 14.6C27.7 12.3 44.3 12.3 48.5 14.6L51 25.8H21L23.5 14.6Z"></path>
                                        <path class="vl-truck-rib" d="M18.5 42H53.5M18.5 55H53.5M18.5 68H53.5"></path>
                                        <path class="vl-car-light vl-car-light-left" d="M19.8 87.4C23.4 90 27.3 91.1 31.5 90.8"></path>
                                        <path class="vl-car-light vl-car-light-right" d="M52.2 87.4C48.6 90 44.7 91.1 40.5 90.8"></path>
                                    </svg>
                                `,
                            },
                            bike: {
                                size: [22, 36],
                                anchor: [11, 18],
                                svg: `
                                    <svg class="vl-map-vehicle-svg" viewBox="0 0 56 92" role="img" aria-hidden="true">
                                        <ellipse class="vl-car-shadow" cx="28" cy="80" rx="13" ry="6"></ellipse>
                                        <path class="vl-bike-wheel" d="M28 11V23"></path>
                                        <path class="vl-bike-body" d="M28 20C36 23.5 40 35.8 37.6 48.2L34.7 63.7C33.6 69.6 30.6 73.4 28 73.4C25.4 73.4 22.4 69.6 21.3 63.7L18.4 48.2C16 35.8 20 23.5 28 20Z"></path>
                                        <path class="vl-bike-seat" d="M21.5 34.2C25.2 31.8 30.8 31.8 34.5 34.2"></path>
                                        <path class="vl-bike-handle" d="M15.4 24.5C19.6 20.8 36.4 20.8 40.6 24.5"></path>
                                        <path class="vl-bike-tail" d="M23.5 67.5C26.1 69.2 29.9 69.2 32.5 67.5"></path>
                                    </svg>
                                `,
                            },
                        };
                        const spec = specs[kind] || specs.van;

                        return {
                            size: spec.size,
                            anchor: spec.anchor,
                            html: `<span class="vl-map-vehicle-marker vl-map-vehicle-${kind}${liveClass}" style="--vl-bearing: ${bearing}deg" aria-label="${label}">${isLive ? '<span class="vl-vehicle-pulse"></span>' : ''}${spec.svg}</span>`,
                        };
                    },
                }
            }
        </script>
    @endpush
</x-village-link-layout>
