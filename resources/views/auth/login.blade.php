<x-layouts.auth-villagelink :title="__('vl.login')">
    <h2 class="mb-6 font-display text-2xl font-bold text-vl-peach">{{ __('vl.login') }}</h2>
    <x-auth-session-status class="mb-4 text-sm text-green-300" :status="session('status')" />
    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf
        <div><label class="mb-1 block text-sm text-white/90">Email</label><input type="email" name="email" value="{{ old('email') }}" required class="vl-input"></div>
        <div>
            <div class="mb-1 flex items-center justify-between gap-3">
                <label class="block text-sm text-white/90">Password</label>
                <a href="{{ route('password.request') }}" class="text-xs font-semibold text-vl-peach hover:underline">Forgot password?</a>
            </div>
            <input type="password" name="password" required class="vl-input">
        </div>
        <label class="flex items-center gap-2 text-sm text-white/80"><input type="checkbox" name="remember" class="rounded"> Remember me</label>
        @if ($errors->any())<p class="text-sm text-red-300">{{ $errors->first() }}</p>@endif
        <button type="submit" class="vl-btn-primary w-full">
            <i data-lucide="log-in" class="vl-icon"></i>
            {{ __('vl.login') }}
        </button>
        <p class="text-center text-sm text-white/70">Need an account? <a href="{{ route('register') }}" class="text-vl-peach hover:underline">{{ __('vl.register') }}</a></p>
    </form>
</x-layouts.auth-villagelink>
