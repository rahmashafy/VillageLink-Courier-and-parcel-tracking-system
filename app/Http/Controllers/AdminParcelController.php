<?php

namespace App\Http\Controllers;

use App\Models\Parcel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminParcelController extends Controller
{
    private const DRIVER_ROLES = ['driver', 'agent'];

    public function index()
    {
        $parcels = Parcel::with('agent.driverProfile')->latest()->get();

        return view('admin.parcels.index', compact('parcels'));
    }

    public function assignForm(Parcel $parcel)
    {
        $busyDriverIds = Parcel::query()
            ->where('status', '!=', 'delivered')
            ->whereNotNull('agent_id')
            ->where('id', '!=', $parcel->id)
            ->pluck('agent_id')
            ->unique();

        $drivers = User::whereIn('role', self::DRIVER_ROLES)
            ->with('driverProfile')
            ->whereNotIn('id', $busyDriverIds)
            ->where(function ($query) {
                $query->whereDoesntHave('driverProfile')
                    ->orWhereHas('driverProfile', fn ($profile) => $profile->where('availability_status', 'available'));
            })
            ->orderBy('name')
            ->get();

        $busyDrivers = User::whereIn('role', self::DRIVER_ROLES)
            ->with('driverProfile')
            ->whereIn('id', $busyDriverIds)
            ->orderBy('name')
            ->get();

        $offlineDrivers = User::whereIn('role', self::DRIVER_ROLES)
            ->with('driverProfile')
            ->whereNotIn('id', $busyDriverIds)
            ->whereHas('driverProfile', fn ($profile) => $profile->where('availability_status', 'offline'))
            ->orderBy('name')
            ->get();

        return view('admin.parcels.assign', compact('parcel', 'drivers', 'busyDrivers', 'offlineDrivers'));
    }

    public function assignAgent(Request $request, Parcel $parcel)
    {
        $request->validate([
            'driver_id' => [
                'required',
            Rule::exists('users', 'id')->where(fn ($query) => $query->whereIn('role', self::DRIVER_ROLES)),
            ],
        ]);

        $busyParcel = Parcel::query()
            ->where('agent_id', $request->driver_id)
            ->where('status', '!=', 'delivered')
            ->where('id', '!=', $parcel->id)
            ->first();

        if ($busyParcel) {
            return back()
                ->withErrors(['driver_id' => 'This driver is already doing a delivery service. Please select a free driver.'])
                ->withInput();
        }

        $driver = User::with('driverProfile')->findOrFail($request->driver_id);
        if ($driver->driverProfile?->availability_status === 'offline') {
            return back()
                ->withErrors(['driver_id' => 'This driver is offline. Please select one of the free drivers.'])
                ->withInput();
        }

        $parcel->update([
            'agent_id' => $request->driver_id,
        ]);

        $driver->driverProfile()->updateOrCreate(
            ['user_id' => $driver->id],
            ['availability_status' => 'busy'],
        );

        return redirect()->route('admin.parcels.index')
            ->with('success', 'Driver assigned successfully.');
    }
}
