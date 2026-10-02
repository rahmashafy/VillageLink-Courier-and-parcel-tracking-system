<x-village-link-layout title="Rate Delivery" header="Rate Delivery - {{ $parcel->tracking_id }}">
    <div class="mx-auto max-w-md vl-panel">
        <form method="POST" action="{{ route('customer.ratings.store', $parcel) }}" class="space-y-4">
            @csrf
            @foreach (['speed_rating' => 'Delivery Speed', 'behavior_rating' => 'Driver Behavior', 'safety_rating' => 'Parcel Safety', 'service_rating' => 'Service Quality'] as $field => $label)
                <div>
                    <label class="mb-2 block text-sm font-medium text-white/80">{{ $label }} (1-5)</label>
                    <select name="{{ $field }}" required class="vl-input">
                        @for ($i = 5; $i >= 1; $i--)
                            <option value="{{ $i }}">{{ $i }} Stars</option>
                        @endfor
                    </select>
                </div>
            @endforeach
            <div>
                <label class="mb-2 block text-sm font-medium text-white/80">Overall Rating</label>
                <select name="rating" required class="vl-input">
                    @for ($i = 5; $i >= 1; $i--)
                        <option value="{{ $i }}">{{ $i }} Stars</option>
                    @endfor
                </select>
            </div>
            <textarea name="feedback" rows="3" placeholder="Additional feedback..." class="vl-input"></textarea>
            <button type="submit" class="vl-btn-primary w-full">
                <i data-lucide="star" class="vl-icon"></i>
                Submit Rating
            </button>
        </form>
    </div>
</x-village-link-layout>
