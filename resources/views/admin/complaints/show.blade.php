<x-village-link-layout title="Complaint" header="Complaint - {{ $complaint->subject }}">
    <div class="mx-auto grid max-w-5xl gap-6 xl:grid-cols-[0.8fr_1.2fr]">
        <div class="vl-panel">
            <p class="text-xs uppercase tracking-[0.22em] text-white/50">Customer</p>
            <p class="mt-2 text-xl font-semibold text-vl-peach">{{ $complaint->user?->name }}</p>
            <dl class="mt-5 space-y-3 text-sm">
                <div><dt class="text-white/50">Status</dt><dd>{{ ucfirst(str_replace('_', ' ', $complaint->status)) }}</dd></div>
                <div><dt class="text-white/50">Type</dt><dd>{{ ucfirst(str_replace('_', ' ', $complaint->category ?? 'general')) }}</dd></div>
                <div><dt class="text-white/50">Parcel</dt><dd class="font-mono text-vl-peach">{{ $complaint->parcel?->tracking_id ?? '-' }}</dd></div>
                <div><dt class="text-white/50">Message</dt><dd class="mt-1 leading-6">{{ $complaint->message }}</dd></div>
            </dl>
            @if ($complaint->image_path)
                <a href="{{ asset('storage/'.$complaint->image_path) }}" target="_blank" class="mt-4 inline-flex text-sm text-vl-peach hover:underline">View uploaded photo</a>
            @endif
        </div>

        <div class="vl-panel">
            <h2 class="font-display text-lg font-semibold text-vl-peach">Conversation</h2>
            <div class="mt-5 space-y-3">
                @forelse ($complaint->replies as $reply)
                    <div class="rounded-2xl border border-white/10 p-4 {{ $reply->is_admin ? 'bg-vl-accent/15' : 'bg-white/5' }}">
                        <div class="flex justify-between gap-4 text-xs text-white/50">
                            <span>{{ $reply->is_admin ? 'Admin' : 'Customer' }} - {{ $reply->user?->name }}</span>
                            <span>{{ $reply->created_at->format('M d, g:i A') }}</span>
                        </div>
                        <p class="mt-2 text-sm leading-6">{{ $reply->message }}</p>
                    </div>
                @empty
                    <p class="rounded-2xl border border-white/10 bg-white/5 p-4 text-sm text-white/60">No replies yet.</p>
                @endforelse
            </div>

            <form method="POST" action="{{ route('admin.complaints.reply', $complaint) }}" class="mt-5 space-y-3">
                @csrf
                <select name="status" class="vl-input">
                    @foreach (['open', 'in_progress', 'resolved', 'closed'] as $status)
                        <option value="{{ $status }}" @selected($complaint->status === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                    @endforeach
                </select>
                <textarea name="message" rows="4" required placeholder="Reply to customer..." class="vl-input"></textarea>
                @if ($errors->any())<p class="text-sm text-red-300">{{ $errors->first() }}</p>@endif
                <button type="submit" class="vl-btn-primary">
                    <i data-lucide="send" class="vl-icon"></i>
                    Send Reply
                </button>
            </form>
        </div>
    </div>
</x-village-link-layout>
