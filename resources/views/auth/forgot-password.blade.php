<x-layouts.auth-villagelink title="Forgot Password">
    <h2 class="mb-3 font-display text-2xl font-bold text-vl-peach">Forgot Password</h2>
    <p class="mb-5 text-sm leading-6 text-white/70">
        Enter your account email. We will send a secure password reset link so you can create a new password.
    </p>

    <x-auth-session-status class="mb-4 text-sm text-green-300" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf
        <div>
            <label for="email" class="mb-1 block text-sm text-white/90">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus class="vl-input">
            @error('email')
                <p class="mt-2 text-sm text-red-300">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="vl-btn-primary w-full">
            <i data-lucide="mail" class="vl-icon"></i>
            Send Reset Link
        </button>

        <p class="text-center text-sm text-white/70">
            Remembered it?
            <a href="{{ route('login') }}" class="text-vl-peach hover:underline">{{ __('vl.login') }}</a>
        </p>
    </form>
</x-layouts.auth-villagelink>
