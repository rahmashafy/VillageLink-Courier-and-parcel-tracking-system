<x-village-link-layout title="Edit User" header="Change Role - {{ $user->name }}">
    <div class="mx-auto max-w-md vl-panel">
        <p class="text-sm text-white/70">{{ $user->email }}</p>
        @if ($errors->any())
            <div class="mt-4 rounded-xl border border-red-300/30 bg-red-500/15 px-4 py-3 text-sm text-red-100">{{ $errors->first() }}</div>
        @endif
        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="mt-6 space-y-4">
            @csrf
            @method('PUT')
            <select name="role" required class="vl-input">
                <option value="customer" @selected(old('role', $user->role) === 'customer')>Customer</option>
                <option value="driver" @selected(in_array(old('role', $user->role), ['driver', 'agent'], true))>Driver</option>
                <option value="admin" @selected(old('role', $user->role) === 'admin')>Admin</option>
            </select>
            <button type="submit" class="vl-btn-primary w-full">Update Role</button>
        </form>
    </div>
</x-village-link-layout>
