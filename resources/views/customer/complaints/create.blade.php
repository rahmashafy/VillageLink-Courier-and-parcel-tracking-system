<x-village-link-layout title="Complaint" header="{{ __('vl.complaints') }}">
    <div class="mx-auto max-w-xl vl-panel">
        @if ($errors->any())
            <div class="mb-4 rounded-xl border border-red-300/30 bg-red-500/15 px-4 py-3 text-sm text-red-100">{{ $errors->first() }}</div>
        @endif
        <form method="POST" action="{{ route('customer.complaints.store') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <label class="mb-2 block text-sm font-medium text-white/80">Complaint Type</label>
                <select name="category" required class="vl-input">
                    <option value="damage">Parcel Damage</option>
                    <option value="missing">Missing Parcel</option>
                    <option value="late_delivery">Late Delivery</option>
                    <option value="wrong_delivery">Wrong Delivery</option>
                    <option value="general">General</option>
                </select>
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-white/80">Related Parcel</label>
                <select name="parcel_id" class="vl-input">
                    <option value="">None</option>
                    @foreach ($parcels as $p)
                        <option value="{{ $p->id }}">{{ $p->tracking_id }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-white/80">Subject</label>
                <input type="text" name="subject" value="{{ old('subject') }}" required class="vl-input">
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-white/80">Description</label>
                <textarea name="message" rows="4" required class="vl-input">{{ old('message') }}</textarea>
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-white/80">Upload Photo (optional)</label>
                <input type="file" name="image" accept="image/*" class="w-full rounded-xl border border-white/15 bg-white/5 px-4 py-3 text-sm text-white/80">
            </div>
            <button type="submit" class="vl-btn-primary w-full">
                <i data-lucide="send" class="vl-icon"></i>
                Submit Complaint
            </button>
        </form>
    </div>
</x-village-link-layout>
