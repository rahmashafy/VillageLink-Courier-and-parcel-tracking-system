<x-village-link-layout title="{{ __('vl.profile') }}" header="{{ __('vl.profile') }}">
    <div class="mx-auto grid max-w-5xl gap-6 xl:grid-cols-[1fr_0.9fr]">
        <div class="vl-glass-card text-white">
            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                @method('patch')
                <div class="mb-2">
                    <h2 class="font-display text-xl font-semibold text-vl-peach">Profile Details</h2>
                    <p class="mt-1 text-sm text-white/60">Update your personal information and contact details.</p>
                </div>
                <div class="flex items-center gap-4">
                    @if ($user->profile_photo)
                        <img src="{{ asset('storage/'.$user->profile_photo) }}" class="w-20 h-20 rounded-full object-cover border-2 border-vl-peach">
                    @else
                        <div class="w-20 h-20 rounded-full bg-vl-teal flex items-center justify-center text-2xl font-bold">{{ substr($user->name, 0, 1) }}</div>
                    @endif
                    <div>
                        <label class="block text-sm font-medium mb-1">Profile Picture</label>
                        <input type="file" name="profile_photo" accept="image/*" class="text-sm text-white/80">
                    </div>
                </div>
                <div><label class="block text-sm mb-1">Name</label><input name="name" value="{{ old('name', $user->name) }}" required class="vl-input"></div>
                <div><label class="block text-sm mb-1">Email</label><input type="email" name="email" value="{{ old('email', $user->email) }}" required class="vl-input"></div>
                <div><label class="block text-sm mb-1">Phone</label><input name="phone" value="{{ old('phone', $user->phone) }}" class="vl-input"></div>
                <div><label class="block text-sm mb-1">Address</label><textarea name="address" rows="2" class="vl-input">{{ old('address', $user->address) }}</textarea></div>
                @if ($errors->any())<p class="text-red-300 text-sm">{{ $errors->first() }}</p>@endif
                <button type="submit" class="vl-btn-primary">Save Profile</button>
            </form>
        </div>

        @include('profile.partials.update-password-form')
    </div>
</x-village-link-layout>
