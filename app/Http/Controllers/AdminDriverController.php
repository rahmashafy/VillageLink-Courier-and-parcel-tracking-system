<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Models\Parcel;
use App\Models\Rating;
use App\Models\User;
use Illuminate\Http\Request;

class AdminDriverController extends Controller
{
    public function index()
    {
        $drivers = User::whereIn('role', ['driver', 'agent'])
            ->with('driverProfile')
            ->withCount([
                'driverParcels as active_deliveries_count' => fn ($query) => $query->where('status', '!=', 'delivered'),
                'driverParcels as completed_deliveries_count' => fn ($query) => $query->where('status', 'delivered'),
            ])
            ->orderBy('name')
            ->get()
            ->map(function (User $driver) {
                $delivered = max((int) $driver->completed_deliveries_count, 1);
                $onTime = Parcel::where('agent_id', $driver->id)
                    ->where('status', 'delivered')
                    ->whereNotNull('delivered_at')
                    ->whereNotNull('estimated_delivery_at')
                    ->whereColumn('delivered_at', '<=', 'estimated_delivery_at')
                    ->count();

                $complaints = Complaint::whereHas('parcel', fn ($query) => $query->where('agent_id', $driver->id))->count();
                $avgRating = (float) Rating::whereHas('parcel', fn ($query) => $query->where('agent_id', $driver->id))->avg('rating');
                $onTimeRate = $driver->completed_deliveries_count ? round(($onTime / $delivered) * 100) : 0;

                $ratingScore = $avgRating ? ($avgRating / 5) * 50 : 35;
                $onTimeScore = ($onTimeRate / 100) * 30;
                $complaintScore = max(20 - ($complaints * 5), 0);

                $driver->performance_score = round($ratingScore + $onTimeScore + $complaintScore);
                $driver->performance_on_time = $onTimeRate;
                $driver->performance_rating = $avgRating ? round($avgRating, 1) : null;
                $driver->performance_complaints = $complaints;

                return $driver;
            });

        return view('admin.drivers.index', compact('drivers'));
    }

    public function edit(User $driver)
    {
        abort_unless(in_array($driver->role, ['driver', 'agent'], true), 404);

        $profile = $driver->driverProfile()->firstOrCreate([
            'user_id' => $driver->id,
        ], [
            'phone' => $driver->phone,
            'availability_status' => 'available',
        ]);

        return view('admin.drivers.edit', compact('driver', 'profile'));
    }

    public function update(Request $request, User $driver)
    {
        abort_unless(in_array($driver->role, ['driver', 'agent'], true), 404);

        $validated = $request->validate([
            'phone' => ['nullable', 'string', 'max:20'],
            'vehicle_type' => ['nullable', 'string', 'max:100'],
            'vehicle_number' => ['nullable', 'string', 'max:50'],
            'license_number' => ['nullable', 'string', 'max:100'],
            'availability_status' => ['required', 'in:available,busy,offline'],
            'shift_start' => ['nullable', 'date_format:H:i'],
            'shift_end' => ['nullable', 'date_format:H:i'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $driver->driverProfile()->updateOrCreate(['user_id' => $driver->id], $validated);

        return redirect()->route('admin.drivers.index')->with('success', 'Driver profile updated.');
    }
}
