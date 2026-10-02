<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Models\DriverProfile;
use App\Models\Parcel;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $totalParcels = Parcel::count();
        $deliveredParcels = Parcel::where('status', 'delivered')->count();
        $pendingParcels = Parcel::where('status', '!=', 'delivered')->count();
        $revenue = Payment::sum('amount');
        $openComplaints = Complaint::where('status', 'open')->count();
        $deliveredToday = Parcel::where('status', 'delivered')->whereDate('updated_at', today())->count();
        $revenueToday = Payment::whereDate('created_at', today())->sum('amount');
        $activeStatuses = ['pending_pickup', 'picked_up', 'in_transit', 'arrived_center', 'out_for_delivery'];
        $activeParcels = Parcel::whereIn('status', $activeStatuses)->count();
        $unassignedParcels = Parcel::whereIn('status', $activeStatuses)->whereNull('agent_id')->count();
        $overdueParcels = Parcel::whereIn('status', $activeStatuses)
            ->whereNotNull('estimated_delivery_at')
            ->where('estimated_delivery_at', '<', now())
            ->count();
        $slaScore = $activeParcels > 0 ? max(0, round((($activeParcels - $overdueParcels) / $activeParcels) * 100)) : 100;
        $availableDrivers = DriverProfile::where('availability_status', 'available')->count();
        $busyDrivers = DriverProfile::where('availability_status', 'busy')->count();
        $paidPayments = Payment::where('status', 'paid')->count();
        $allPayments = Payment::count();
        $collectionRate = $allPayments > 0 ? round(($paidPayments / $allPayments) * 100) : 100;
        $lastSevenDayRevenue = Payment::where('status', 'paid')
            ->where('created_at', '>=', now()->subDays(7)->startOfDay())
            ->sum('amount');
        $revenueForecast = round(($lastSevenDayRevenue / 7) * 30);

        $chartLabels = [];
        $parcelCounts = [];
        $revenueCounts = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $chartLabels[] = $month->format('M');
            $parcelCounts[] = Parcel::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)->count();
            $revenueCounts[] = (float) Payment::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)->sum('amount');
        }

        $recentParcels = Parcel::with('user', 'agent')->latest()->take(5)->get();
        $dispatchQueue = Parcel::with('user')
            ->whereIn('status', $activeStatuses)
            ->whereNull('agent_id')
            ->orderByRaw("case delivery_type when 'same_day' then 1 when 'express' then 2 else 3 end")
            ->orderBy('estimated_delivery_at')
            ->latest()
            ->take(4)
            ->get();
        $topAgents = User::whereIn('role', ['driver', 'agent'])
            ->withCount(['driverParcels as deliveries_count' => fn ($q) => $q->where('status', 'delivered')])
            ->orderByDesc('deliveries_count')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalParcels',
            'deliveredParcels',
            'pendingParcels',
            'revenue',
            'openComplaints',
            'deliveredToday',
            'revenueToday',
            'activeParcels',
            'unassignedParcels',
            'overdueParcels',
            'slaScore',
            'availableDrivers',
            'busyDrivers',
            'collectionRate',
            'revenueForecast',
            'chartLabels',
            'parcelCounts',
            'revenueCounts',
            'recentParcels',
            'dispatchQueue',
            'topAgents',
        ));
    }

    public function reports(Request $request)
    {
        $from = $request->date('from')?->startOfDay() ?? now()->subDays(30)->startOfDay();
        $to = $request->date('to')?->endOfDay() ?? now()->endOfDay();
        $status = $request->input('status');
        $driverId = $request->input('driver_id');

        $parcelQuery = Parcel::with('agent', 'user')
            ->whereBetween('created_at', [$from, $to])
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($driverId, fn ($query) => $query->where('agent_id', $driverId));

        $paymentQuery = Payment::whereBetween('created_at', [$from, $to]);
        $parcels = (clone $parcelQuery)->latest()->get();
        $payments = (clone $paymentQuery)->get();
        $drivers = User::whereIn('role', ['driver', 'agent'])->orderBy('name')->get();

        return view('admin.reports', [
            'totalParcels' => $parcels->count(),
            'deliveredParcels' => $parcels->where('status', 'delivered')->count(),
            'revenue' => $payments->where('status', 'paid')->sum('amount'),
            'openComplaints' => Complaint::where('status', 'open')->count(),
            'pendingPayments' => $payments->where('status', 'pending')->count(),
            'parcels' => $parcels,
            'drivers' => $drivers,
            'from' => $from,
            'to' => $to,
            'status' => $status,
            'driverId' => $driverId,
        ]);
    }

    public function exportReports(Request $request): StreamedResponse
    {
        $from = $request->date('from')?->startOfDay() ?? now()->subDays(30)->startOfDay();
        $to = $request->date('to')?->endOfDay() ?? now()->endOfDay();
        $status = $request->input('status');
        $driverId = $request->input('driver_id');

        $parcels = Parcel::with('user', 'agent', 'payment')
            ->whereBetween('created_at', [$from, $to])
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($driverId, fn ($query) => $query->where('agent_id', $driverId))
            ->latest()
            ->get();

        return response()->streamDownload(function () use ($parcels) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Tracking ID', 'Customer', 'Driver', 'Status', 'Payment Status', 'Amount', 'Created']);

            foreach ($parcels as $parcel) {
                fputcsv($handle, [
                    $parcel->tracking_id,
                    $parcel->user?->name,
                    $parcel->agent?->name,
                    $parcel->status,
                    $parcel->payment_status,
                    $parcel->price,
                    $parcel->created_at?->format('Y-m-d H:i'),
                ]);
            }

            fclose($handle);
        }, 'village-link-report-'.$from->format('Ymd').'-'.$to->format('Ymd').'.csv');
    }
}
