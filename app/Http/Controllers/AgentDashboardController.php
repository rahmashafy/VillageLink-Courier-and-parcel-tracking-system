<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Models\Parcel;
use App\Models\Payment;
use App\Models\Rating;
use Illuminate\Support\Facades\Auth;

class AgentDashboardController extends Controller
{
    private const ACTIVE_STATUSES = [
        'pending_pickup',
        'picked_up',
        'in_transit',
        'arrived_center',
        'out_for_delivery',
    ];

    private const STATUS_FLOW = [
        'pending_pickup' => 'picked_up',
        'picked_up' => 'in_transit',
        'in_transit' => 'arrived_center',
        'arrived_center' => 'out_for_delivery',
        'out_for_delivery' => 'delivered',
    ];

    public function index()
    {
        $agentId = Auth::id();
        $driver = Auth::user()->load('driverProfile');

        $assigned = Parcel::where('agent_id', $agentId)->count();
        $active = Parcel::where('agent_id', $agentId)->whereIn('status', self::ACTIVE_STATUSES)->count();
        $pickedUp = Parcel::where('agent_id', $agentId)->whereIn('status', ['picked_up', 'in_transit', 'arrived_center', 'out_for_delivery'])->count();
        $delivered = Parcel::where('agent_id', $agentId)->where('status', 'delivered')->count();
        $pending = Parcel::where('agent_id', $agentId)->where('status', 'pending_pickup')->count();
        $deliveredToday = Parcel::where('agent_id', $agentId)
            ->where('status', 'delivered')
            ->whereDate('delivered_at', today())
            ->count();
        $completedThisWeek = Parcel::where('agent_id', $agentId)
            ->where('status', 'delivered')
            ->whereBetween('delivered_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();

        $activeJob = Parcel::with(['user', 'payment', 'latestLocation'])
            ->where('agent_id', $agentId)
            ->whereIn('status', self::ACTIVE_STATUSES)
            ->orderByRaw("
                case status
                    when 'pending_pickup' then 1
                    when 'picked_up' then 2
                    when 'in_transit' then 3
                    when 'arrived_center' then 4
                    when 'out_for_delivery' then 5
                    else 9
                end
            ")
            ->orderBy('estimated_delivery_at')
            ->latest('updated_at')
            ->first();

        $todayDeliveries = Parcel::with(['user', 'payment', 'latestLocation'])
            ->where('agent_id', $agentId)
            ->where(function ($query) {
                $query->whereIn('status', self::ACTIVE_STATUSES)
                    ->orWhere(function ($query) {
                        $query->where('status', 'delivered')
                            ->whereDate('delivered_at', today());
                    });
            })
            ->orderByRaw("
                case status
                    when 'pending_pickup' then 1
                    when 'picked_up' then 2
                    when 'in_transit' then 3
                    when 'arrived_center' then 4
                    when 'out_for_delivery' then 5
                    when 'delivered' then 8
                    else 9
                end
            ")
            ->latest('updated_at')
            ->take(8)
            ->get();

        $earningsToday = Payment::where('status', 'paid')
            ->whereHas('parcel', fn ($query) => $query->where('agent_id', $agentId))
            ->where(function ($query) {
                $query->whereDate('paid_at', today())
                    ->orWhere(function ($query) {
                        $query->whereNull('paid_at')->whereDate('created_at', today());
                    });
            })
            ->sum('amount');
        $weeklyEarnings = Payment::where('status', 'paid')
            ->whereHas('parcel', fn ($query) => $query->where('agent_id', $agentId))
            ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->sum('amount');

        $avgRating = (float) Rating::whereHas('parcel', fn ($query) => $query->where('agent_id', $agentId))->avg('rating');
        $complaints = Complaint::whereHas('parcel', fn ($query) => $query->where('agent_id', $agentId))->count();
        $onTime = Parcel::where('agent_id', $agentId)
            ->where('status', 'delivered')
            ->whereNotNull('estimated_delivery_at')
            ->whereColumn('delivered_at', '<=', 'estimated_delivery_at')
            ->count();
        $onTimeRate = $delivered ? round(($onTime / max($delivered, 1)) * 100) : 0;
        $performanceScore = round(
            (($avgRating ?: 5) / 5 * 45)
            + ($onTimeRate / 100 * 35)
            + (max(0, 20 - min($complaints, 5) * 4))
        );

        $nextStatus = $activeJob ? self::STATUS_FLOW[$activeJob->status] ?? null : null;
        $nextAction = $nextStatus ? 'Mark '.str_replace('_', ' ', $nextStatus) : 'Ready for assignment';
        $routeStops = $activeJob ? [
            ['label' => 'Pickup', 'address' => $activeJob->pickup_address, 'done' => in_array($activeJob->status, ['picked_up', 'in_transit', 'arrived_center', 'out_for_delivery', 'delivered'], true)],
            ['label' => 'Delivery', 'address' => $activeJob->delivery_address, 'done' => $activeJob->status === 'delivered'],
        ] : [];
        $routeReadiness = [
            ['label' => 'Vehicle profile', 'ready' => filled($driver->driverProfile?->vehicle_number), 'hint' => $driver->driverProfile?->vehicle_number ?? 'Add vehicle number'],
            ['label' => 'GPS signal', 'ready' => filled($driver->driverProfile?->last_location_at), 'hint' => $driver->driverProfile?->last_location_at?->diffForHumans() ?? 'Start GPS from a job'],
            ['label' => 'Shift window', 'ready' => filled($driver->driverProfile?->shift_start) && filled($driver->driverProfile?->shift_end), 'hint' => trim(($driver->driverProfile?->shift_start ?: '08:00').' - '.($driver->driverProfile?->shift_end ?: '18:00'))],
            ['label' => 'Active delivery', 'ready' => (bool) $activeJob, 'hint' => $activeJob?->tracking_id ?? 'Waiting for admin assignment'],
        ];

        $statusCounts = [
            'Pending Pickup' => $pending,
            'On Route' => $pickedUp,
            'Delivered' => $delivered,
        ];
        $chartLabels = array_keys($statusCounts);
        $chartData = array_values($statusCounts);

        return view('agent.dashboard', compact(
            'driver',
            'assigned',
            'active',
            'pickedUp',
            'delivered',
            'pending',
            'deliveredToday',
            'completedThisWeek',
            'todayDeliveries',
            'activeJob',
            'nextStatus',
            'nextAction',
            'routeStops',
            'earningsToday',
            'weeklyEarnings',
            'performanceScore',
            'avgRating',
            'onTimeRate',
            'complaints',
            'routeReadiness',
            'chartLabels',
            'chartData',
        ));
    }
}
