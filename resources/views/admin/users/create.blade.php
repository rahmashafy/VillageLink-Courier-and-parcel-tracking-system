<x-village-link-layout title="Add User" header="Add New User">
    <div class="mx-auto max-w-xl vl-panel">
        @if ($errors->any())
            <div class="mb-4 rounded-xl border border-red-300/30 bg-red-500/15 px-4 py-3 text-sm text-red-100">{{ $errors->first() }}</div>
        @endif
        <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-4">
            @csrf
            <div><label class="mb-2 block text-sm font-medium text-white/80">Name</label><input name="name" value="{{ old('name') }}" required class="vl-input"></div>
            <div><label class="mb-2 block text-sm font-medium text-white/80">Email</label><input type="email" name="email" value="{{ old('email') }}" required class="vl-input"></div>
            <div><label class="mb-2 block text-sm font-medium text-white/80">Phone</label><input name="phone" value="{{ old('phone') }}" class="vl-input"></div>
            <div><label class="mb-2 block text-sm font-medium text-white/80">Address</label><textarea name="address" rows="2" class="vl-input">{{ old('address') }}</textarea></div>
            <div class="grid gap-4 md:grid-cols-2">
                <div><label class="mb-2 block text-sm font-medium text-white/80">Password</label><input type="password" name="password" required class="vl-input"></div>
                <div><label class="mb-2 block text-sm font-medium text-white/80">Confirm Password</label><input type="password" name="password_confirmation" required class="vl-input"></div>
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-white/80">Role</label>
                <select name="role" required class="vl-input">
                    <option value="customer">Customer</option>
                    <option value="driver">Driver</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <button type="submit" class="vl-btn-primary w-full">
                <i data-lucide="user-plus" class="vl-icon"></i>
                Create User
            </button>
        </form>
    </div>
</x-village-link-layout>
