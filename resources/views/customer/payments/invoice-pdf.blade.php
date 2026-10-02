<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $parcel->tracking_id }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; padding: 40px; color: #1e293b; }
        .header { border-bottom: 2px solid #1e40af; padding-bottom: 20px; margin-bottom: 30px; }
        .status { background: #fef3c7; color: #92400e; padding: 4px 12px; border-radius: 20px; font-size: 12px; }
        .paid { background: #dcfce7; color: #166534; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        td { padding: 8px 0; border-bottom: 1px solid #e2e8f0; }
        .total { font-size: 18px; font-weight: bold; color: #1e40af; }
    </style>
</head>
<body>
    <div class="header">
        <h1 style="color:#1e40af;margin:0">Village Link</h1>
        <p style="margin:4px 0">Smart Courier & Parcel Tracking</p>
        @if ($payment)<span class="status {{ $payment->status === 'paid' ? 'paid' : '' }}">{{ strtoupper($payment->status) }}</span>@endif
    </div>
    <p><strong>Tracking ID:</strong> {{ $parcel->tracking_id }}</p>
    <p><strong>Date:</strong> {{ $payment?->created_at?->format('d M Y') ?? now()->format('d M Y') }}</p>
    <p><strong>Receiver:</strong> {{ $parcel->receiver_name }}</p>
    @if ($payment?->reference)
        <p><strong>Payment Reference:</strong> {{ $payment->reference }}</p>
    @endif
    @if ($parcel->estimated_delivery_at)
        <p><strong>Estimated Delivery:</strong> {{ $parcel->estimated_delivery_at->format('M d, Y g:i A') }}</p>
    @endif
    <table>
        <tr><td>Base charge</td><td align="right">Rs. 300.00</td></tr>
        <tr><td>Weight ({{ $parcel->weight }} kg)</td><td align="right">Rs. {{ number_format($parcel->weight * 150, 2) }}</td></tr>
        <tr><td>Distance ({{ $parcel->distance_km ?? 0 }} km)</td><td align="right">Rs. {{ number_format(($parcel->distance_km ?? 0) * 5, 2) }}</td></tr>
        <tr><td class="total">Total</td><td align="right" class="total">Rs. {{ number_format($parcel->price, 2) }}</td></tr>
    </table>
    <p style="font-size:12px;color:#64748b">Thank you for using Village Link.</p>
</body>
</html>
