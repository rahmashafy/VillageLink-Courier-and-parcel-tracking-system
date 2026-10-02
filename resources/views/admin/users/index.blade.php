<x-village-link-layout title="Users" header="User Management">
    <div class="mb-5 flex justify-end">
        <a href="{{ route('admin.users.create') }}" class="vl-btn-primary">
            <i data-lucide="user-plus" class="vl-icon"></i>
            Add New User
        </a>
    </div>

    <div class="vl-table-panel">
        <div class="overflow-x-auto">
            <table class="vl-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @php($roleLabel = $user->role === 'agent' ? 'driver' : $user->role)
                                <span class="inline-flex rounded-full border border-white/15 bg-white/10 px-2.5 py-1 text-xs font-semibold capitalize">{{ $roleLabel }}</span>
                            </td>
                            <td>
                                <div class="flex flex-wrap gap-2">
                                    <a href="{{ route('admin.users.edit', $user) }}" class="vl-action-link">Edit</a>
                                    @if ($user->id !== auth()->id())
                                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="inline" onsubmit="return confirm('Delete this user?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="vl-action-link border-red-300/40 bg-red-500/15 text-red-100">Delete</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-10 text-center text-white/60">No users found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-village-link-layout>
