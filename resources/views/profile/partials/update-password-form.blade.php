<section class="vl-glass-card text-white">
    <header>
        <h2 class="font-display text-xl font-semibold text-vl-peach">Change Password</h2>
        <p class="mt-1 text-sm leading-6 text-white/60">Keep your account secure by using a strong new password.</p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-4">
        @csrf
        @method('put')

        <div>
            <label for="update_password_current_password" class="mb-1 block text-sm text-white/90">Current Password</label>
            <input id="update_password_current_password" name="current_password" type="password" required autocomplete="current-password" class="vl-input">
            @foreach ($errors->updatePassword->get('current_password') as $message)
                <p class="mt-2 text-sm text-red-300">{{ $message }}</p>
            @endforeach
        </div>

        <div>
            <label for="update_password_password" class="mb-1 block text-sm text-white/90">New Password</label>
            <input id="update_password_password" name="password" type="password" required autocomplete="new-password" class="vl-input">
            @foreach ($errors->updatePassword->get('password') as $message)
                <p class="mt-2 text-sm text-red-300">{{ $message }}</p>
            @endforeach
        </div>

        <div>
            <label for="update_password_password_confirmation" class="mb-1 block text-sm text-white/90">Confirm Password</label>
            <input id="update_password_password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" class="vl-input">
            @foreach ($errors->updatePassword->get('password_confirmation') as $message)
                <p class="mt-2 text-sm text-red-300">{{ $message }}</p>
            @endforeach
        </div>

        <div class="flex flex-wrap items-center gap-4">
            <button type="submit" class="vl-btn-primary">
                <i data-lucide="shield-check" class="vl-icon"></i>
                Update Password
            </button>
            @if (session('status') === 'password-updated')
                <p class="text-sm text-emerald-200">Password updated successfully.</p>
            @endif
        </div>
    </form>
</section>
