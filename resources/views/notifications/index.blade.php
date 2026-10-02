<x-village-link-layout title="Notifications" header="Notifications">
    <form method="POST" action="{{ route('notifications.read-all') }}" class="mb-4 text-right">
        @csrf
        <button type="submit" class="vl-action-link">Mark all as read</button>
    </form>

    <div class="vl-panel divide-y divide-white/10 p-0">
        @forelse ($notifications as $notification)
            <div class="flex justify-between gap-4 p-5 {{ $notification->read_at ? 'opacity-70' : 'bg-white/5' }}">
                <div>
                    <p class="text-sm font-medium">{{ $notification->data['message'] ?? 'Notification' }}</p>
                    <p class="mt-1 text-xs text-white/60">{{ $notification->created_at->diffForHumans() }}</p>
                </div>
                @if (! $notification->read_at)
                    <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                        @csrf
                        <button type="submit" class="text-xs font-semibold text-vl-peach">Mark read</button>
                    </form>
                @endif
            </div>
        @empty
            <p class="p-8 text-center text-white/60">No notifications yet.</p>
        @endforelse
    </div>
    <div class="mt-4">{{ $notifications->links() }}</div>
</x-village-link-layout>
