<x-layouts.auth-villagelink title="Reset Password">
    <h2 class="mb-6 font-display text-2xl font-bold text-vl-peach">Reset Password</h2>

    <form method="POST" action="{{ route('password.store') }}" class="space-y-4">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div>
            <label for="email" class="mb-1 block text-sm text-white/90">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username" class="vl-input">
            @error('email')
                <p class="mt-2 text-sm text-red-300">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="mb-1 block text-sm text-white/90">New Password</label>
            <input id="password" type="password" name="password" required autocomplete="new-password" class="vl-input">
            @error('password')
                <p class="mt-2 text-sm text-red-300">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password_confirmation" class="mb-1 block text-sm text-white/90">Confirm Password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" class="vl-input">
            @error('password_confirmation')
                <p class="mt-2 text-sm text-red-300">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="vl-btn-primary w-full">
            <i data-lucide="key-round" class="vl-icon"></i>
            Reset Password
        </button>
    </form>
</x-layouts.auth-villagelink>
