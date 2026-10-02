<x-village-link-layout title="Contact Messages" header="Contact Messages">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-white/70">
            Messages from the public contact form are saved here.
            @if ($unreadCount > 0)
                <span class="ml-1 rounded-full bg-vl-peach/20 px-2.5 py-1 text-xs font-semibold text-vl-peach">{{ $unreadCount }} unread</span>
            @endif
        </p>
    </div>

    <div class="vl-table-panel">
        <div class="overflow-x-auto">
            <table class="vl-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Message</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($messages as $message)
                        <tr class="align-top {{ $message->read_at ? '' : 'bg-vl-accent/10' }}">
                            <td class="whitespace-nowrap text-sm text-white/70">{{ $message->created_at->format('M d, Y g:i A') }}</td>
                            <td class="font-medium">{{ $message->name }}</td>
                            <td>
                                <a href="mailto:{{ $message->email }}" class="text-vl-peach hover:underline">{{ $message->email }}</a>
                            </td>
                            <td class="max-w-md whitespace-pre-wrap text-sm text-white/80">{{ $message->message }}</td>
                            <td>
                                @if ($message->read_at)
                                    <span class="inline-flex rounded-full border border-emerald-300/40 bg-emerald-400/15 px-2.5 py-1 text-xs font-semibold text-emerald-100">Read</span>
                                @else
                                    <span class="inline-flex rounded-full border border-vl-peach/40 bg-vl-peach/15 px-2.5 py-1 text-xs font-semibold text-vl-peach">New</span>
                                @endif
                            </td>
                            <td>
                                @unless ($message->read_at)
                                    <form method="POST" action="{{ route('admin.contact-messages.read', $message) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="vl-action-link">Mark read</button>
                                    </form>
                                @endunless
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-white/60">No contact messages yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-village-link-layout>
