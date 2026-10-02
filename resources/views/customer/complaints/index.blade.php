<x-village-link-layout title="Complaints" header="{{ __('vl.complaints') }}">
    <div class="mb-5 flex justify-end">
        <a href="{{ route('customer.complaints.create') }}" class="vl-btn-primary">
            <i data-lucide="send" class="vl-icon"></i>
            New Complaint
        </a>
    </div>

    <div class="vl-table-panel">
        <div class="overflow-x-auto">
            <table class="vl-table">
                <thead>
                    <tr>
                        <th>Subject</th>
                        <th>Parcel</th>
                        <th>Status</th>
                        <th>Replies</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($complaints as $complaint)
                        <tr>
                            <td>{{ $complaint->subject }}</td>
                            <td class="font-mono text-vl-peach">{{ $complaint->parcel?->tracking_id ?? '-' }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $complaint->status)) }}</td>
                            <td>{{ $complaint->replies->count() }}</td>
                            <td><a href="{{ route('customer.complaints.show', $complaint) }}" class="vl-action-link">View</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-10 text-center text-white/60">No complaints yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-village-link-layout>
