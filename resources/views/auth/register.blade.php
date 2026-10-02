<x-layouts.auth-villagelink :title="__('vl.register')">
    <h2 class="mb-6 font-display text-2xl font-bold text-vl-peach">{{ __('vl.register') }}</h2>
    <form method="POST" action="{{ route('register') }}" class="space-y-3">
        @csrf
        <select name="role" required class="vl-input text-sm">
            <option value="customer">Customer</option>
            <option value="driver">Driver</option>
        </select>
        <input name="name" placeholder="Full Name" value="{{ old('name') }}" required class="vl-input text-sm">
        <input type="email" name="email" placeholder="Email" value="{{ old('email') }}" required class="vl-input text-sm">
        <input name="phone" placeholder="Phone" value="{{ old('phone') }}" required class="vl-input text-sm">
        <textarea name="address" rows="2" placeholder="Address" required class="vl-input text-sm">{{ old('address') }}</textarea>
        <input type="password" name="password" placeholder="Password" required class="vl-input text-sm">
        <input type="password" name="password_confirmation" placeholder="Confirm Password" required class="vl-input text-sm">
        @if ($errors->any())<p class="text-sm text-red-300">{{ $errors->first() }}</p>@endif
        <button type="submit" class="vl-btn-primary w-full">
            <i data-lucide="user-plus" class="vl-icon"></i>
            {{ __('vl.register') }}
        </button>
        <p class="text-center text-sm text-white/70"><a href="{{ route('login') }}" class="text-vl-peach hover:underline">{{ __('vl.login') }}</a></p>
    </form>
</x-layouts.auth-villagelink>
