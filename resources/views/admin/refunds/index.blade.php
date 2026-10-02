<x-village-link-layout title="Refunds" header="Refund / Cancel Review">
    <div class="vl-table-panel">
        <div class="overflow-x-auto">
            <table class="vl-table">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Tracking</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Reason</th>
                        <th>Review</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($requests as $request)
                        <tr>
                            <td>
                                <p class="font-medium">{{ $request->user?->name }}</p>
                                <p class="text-xs text-white/50">{{ $request->user?->email }}</p>
                            </td>
                            <td class="font-mono text-vl-peach">{{ $request->parcel?->tracking_id }}</td>
                            <td>{{ ucfirst($request->type) }}</td>
                            <td>
                                Rs. {{ number_format($request->requested_amount, 2) }}
                                @if ($request->approved_amount)
                                    <p class="text-xs text-emerald-200">Approved: Rs. {{ number_format($request->approved_amount, 2) }}</p>
                                @endif
                            </td>
                            <td><x-vl-status-badge :status="$request->status" /></td>
                            <td class="max-w-xs text-sm text-white/70">{{ $request->reason }}</td>
                            <td class="min-w-[260px]">
                                @if ($request->status === 'pending')
                                    <form method="POST" action="{{ route('admin.refunds.update', $request) }}" class="space-y-2">
                                        @csrf
                                        @method('PATCH')
                                        <div class="grid grid-cols-2 gap-2">
                                            <select name="status" required class="vl-input py-2 text-xs">
                                                <option value="approved">Approve</option>
                                                <option value="rejected">Reject</option>
                                            </select>
                                            <input name="approved_amount" type="number" step="0.01" min="0" value="{{ $request->requested_amount }}" class="vl-input py-2 text-xs" placeholder="Amount">
                                        </div>
                                        <textarea name="admin_notes" rows="2" class="vl-input py-2 text-xs" placeholder="Admin note"></textarea>
                                        <button type="submit" class="vl-action-link w-full justify-center">Save Review</button>
                                    </form>
                                @else
                                    <p class="text-sm text-white/65">{{ $request->admin_notes ?? 'Reviewed '.$request->reviewed_at?->format('M d, Y g:i A') }}</p>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-10 text-center text-white/60">No refund or cancel requests.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-village-link-layout>
