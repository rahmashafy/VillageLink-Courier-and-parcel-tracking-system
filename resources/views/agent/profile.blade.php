<x-village-link-layout title="Driver Profile" header="Driver Profile">
    <div class="mx-auto max-w-2xl vl-panel">
        <form method="POST" action="{{ route('driver.profile.update') }}" class="grid gap-4 md:grid-cols-2">
            @csrf
            @method('PUT')
            <div><label class="mb-1 block text-sm text-white/80">Phone</label><input name="phone" value="{{ old('phone', $profile->phone) }}" class="vl-input"></div>
            <div><label class="mb-1 block text-sm text-white/80">Vehicle Type</label><input name="vehicle_type" value="{{ old('vehicle_type', $profile->vehicle_type) }}" class="vl-input"></div>
            <div><label class="mb-1 block text-sm text-white/80">Vehicle Number</label><input name="vehicle_number" value="{{ old('vehicle_number', $profile->vehicle_number) }}" class="vl-input"></div>
            <div><label class="mb-1 block text-sm text-white/80">License Number</label><input name="license_number" value="{{ old('license_number', $profile->license_number) }}" class="vl-input"></div>
            <div>
                <label class="mb-1 block text-sm text-white/80">Availability</label>
                <select name="availability_status" class="vl-input">
                    <option value="available" @selected(old('availability_status', $profile->availability_status) === 'available')>Available</option>
                    <option value="offline" @selected(old('availability_status', $profile->availability_status) === 'offline')>Offline</option>
                </select>
                <p class="mt-1 text-xs text-white/50">Assigned deliveries automatically set you as busy.</p>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div><label class="mb-1 block text-sm text-white/80">Shift Start</label><input type="time" name="shift_start" value="{{ old('shift_start', $profile->shift_start) }}" class="vl-input"></div>
                <div><label class="mb-1 block text-sm text-white/80">Shift End</label><input type="time" name="shift_end" value="{{ old('shift_end', $profile->shift_end) }}" class="vl-input"></div>
            </div>
            <div class="md:col-span-2"><label class="mb-1 block text-sm text-white/80">Notes</label><textarea name="notes" rows="3" class="vl-input">{{ old('notes', $profile->notes) }}</textarea></div>
            @if ($errors->any())<p class="text-sm text-red-300 md:col-span-2">{{ $errors->first() }}</p>@endif
            <button type="submit" class="vl-btn-primary md:col-span-2">Update Profile</button>
        </form>
    </div>
</x-village-link-layout>
