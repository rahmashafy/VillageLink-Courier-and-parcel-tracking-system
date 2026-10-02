<x-village-link-layout title="Assign Driver" header="Assign Driver - {{ $parcel->tracking_id }}">
    <div class="mx-auto max-w-2xl vl-panel">
        <p class="text-sm text-white/70">Receiver: <strong class="text-white">{{ $parcel->receiver_name }}</strong></p>
        @if ($parcel->agent)
            <p class="mt-2 text-sm text-white/70">Current driver: <strong class="text-white">{{ $parcel->agent->name }}</strong></p>
        @endif
        @if ($errors->any())
            <div class="mt-4 rounded-xl border border-red-300/30 bg-red-500/15 px-4 py-3 text-sm text-red-100">{{ $errors->first() }}</div>
        @endif
        @if ($busyDrivers->isNotEmpty())
            <div class="mt-4 rounded-xl border border-amber-300/30 bg-amber-500/15 px-4 py-3 text-sm text-amber-100">
                Busy drivers are hidden from the free driver list. They are already doing a delivery service:
                <span class="font-semibold">{{ $busyDrivers->pluck('name')->join(', ') }}</span>.
            </div>
        @endif
        @if ($offlineDrivers->isNotEmpty())
            <div class="mt-4 rounded-xl border border-white/15 bg-white/10 px-4 py-3 text-sm text-white/70">
                Offline drivers are also hidden:
                <span class="font-semibold text-white">{{ $offlineDrivers->pluck('name')->join(', ') }}</span>.
            </div>
        @endif
        <form method="POST" action="{{ route('admin.parcels.assign.store', $parcel) }}" class="mt-6 space-y-4">
            @csrf
            <div>
                <label class="mb-2 block text-sm font-medium text-white/80">Select Free Driver</label>
                <select name="driver_id" required class="vl-input">
                    <option value="">Choose free driver...</option>
                    @foreach ($drivers as $driver)
                        <option value="{{ $driver->id }}" @selected(old('driver_id', $parcel->agent_id) == $driver->id)>
                            {{ $driver->name }} ({{ $driver->driverProfile?->vehicle_number ?? $driver->email }}) - Free
                        </option>
                    @endforeach
                </select>
                @if ($drivers->isEmpty())
                    <p class="mt-2 text-sm text-red-200">No free drivers available right now.</p>
                @else
                    <p class="mt-2 text-xs text-emerald-200">Only free drivers are shown here.</p>
                @endif
            </div>
            <button type="submit" class="vl-btn-primary w-full" @disabled($drivers->isEmpty())>
                <i data-lucide="user-plus" class="vl-icon"></i>
                Assign Driver
            </button>
        </form>
    </div>
</x-village-link-layout>
