<?php

namespace App\Http\Controllers;

use App\Models\Parcel;
use App\Models\Payment;
use App\Models\RefundRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class CustomerDashboardController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        $total = Parcel::where('user_id', $userId)->count();
        $delivered = Parcel::where('user_id', $userId)->where('status', 'delivered')->count();
        $inTransit = Parcel::where('user_id', $userId)->whereIn('status', ['picked_up', 'in_transit', 'arrived_center', 'out_for_delivery'])->count();
        $pending = Parcel::where('user_id', $userId)->where('status', 'pending_pickup')->count();
        $recentParcels = Parcel::where('user_id', $userId)->latest()->take(5)->get();
        $activeStatuses = ['pending_pickup', 'picked_up', 'in_transit', 'arrived_center', 'out_for_delivery'];
        $nextParcel = Parcel::where('user_id', $userId)
            ->whereIn('status', $activeStatuses)
            ->orderBy('estimated_delivery_at')
            ->latest()
            ->first();
        $totalSpent = Payment::where('user_id', $userId)->where('status', 'paid')->sum('amount');
        $savedThroughLoyalty = Payment::where('user_id', $userId)->sum('loyalty_discount');
        $paidDeliveries = Payment::where('user_id', $userId)->where('status', 'paid')->count();
        $openRequests = RefundRequest::where('user_id', $userId)->where('status', 'pending')->count();
        $nextParcelReadiness = $nextParcel ? [
            'Payment' => $nextParcel->payment_status === 'paid',
            'Driver assigned' => filled($nextParcel->agent_id),
            'ETA planned' => filled($nextParcel->estimated_delivery_at),
            'Live tracking' => filled($nextParcel->current_lat) && filled($nextParcel->current_lng),
        ] : [];

        $chartLabels = [];
        $parcelCounts = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $chartLabels[] = $month->format('M');
            $parcelCounts[] = Parcel::where('user_id', $userId)
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();
        }

        $statusCounts = [
            'Delivered' => $delivered,
            'In transit' => $inTransit,
            'Pending' => $pending,
        ];

        return view('customer.dashboard', compact(
            'total',
            'delivered',
            'inTransit',
            'pending',
            'recentParcels',
            'nextParcel',
            'totalSpent',
            'savedThroughLoyalty',
            'paidDeliveries',
            'openRequests',
            'nextParcelReadiness',
            'chartLabels',
            'parcelCounts',
            'statusCounts',
        ));
    }
}
