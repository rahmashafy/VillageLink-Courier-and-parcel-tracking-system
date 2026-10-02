<x-village-link-layout title="Complaints" header="Complaints Management">
    <div class="vl-table-panel">
        <div class="overflow-x-auto">
            <table class="vl-table">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Subject</th>
                        <th>Message</th>
                        <th>Status</th>
                        <th>Replies</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($complaints as $complaint)
                        <tr class="align-top">
                            <td>{{ $complaint->user->name ?? '-' }}</td>
                            <td class="font-medium">{{ $complaint->subject }}</td>
                            <td class="max-w-xs">
                                <span class="text-xs text-white/60">{{ ucfirst(str_replace('_', ' ', $complaint->category ?? 'general')) }}</span><br>
                                {{ Str::limit($complaint->message, 60) }}
                                @if ($complaint->image_path)
                                    <br><a href="{{ asset('storage/'.$complaint->image_path) }}" target="_blank" class="text-xs text-vl-peach hover:underline">View photo</a>
                                @endif
                            </td>
                            <td>
                                <span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold
                                    {{ $complaint->status === 'open' ? 'border-red-300/40 bg-red-500/15 text-red-100' : ($complaint->status === 'resolved' ? 'border-emerald-300/40 bg-emerald-400/15 text-emerald-100' : 'border-amber-300/40 bg-amber-400/15 text-amber-100') }}">
                                    {{ ucfirst(str_replace('_', ' ', $complaint->status)) }}
                                </span>
                            </td>
                            <td>{{ $complaint->replies->count() }}</td>
                            <td>
                                <a href="{{ route('admin.complaints.show', $complaint) }}" class="vl-action-link">Open</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-10 text-center text-white/60">No complaints found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-village-link-layout>
