<h1>Submit Complaint</h1>

<a href="{{ route('customer.dashboard') }}">Back to Dashboard</a>

<br><br>

<form method="POST" action="{{ route('customer.complaints.store') }}">
    @csrf

    <select name="parcel_id">
        <option value="">Select Parcel (Optional)</option>

        @foreach ($parcels as $parcel)
            <option value="{{ $parcel->id }}">
                {{ $parcel->tracking_id }} - {{ $parcel->receiver_name }}
            </option>
        @endforeach
    </select>

    <br><br>

    <input type="text" name="subject" placeholder="Subject" required>

    <br><br>

    <textarea name="message" placeholder="Your complaint" required></textarea>

    <br><br>

    <button type="submit">Submit Complaint</button>
</form>