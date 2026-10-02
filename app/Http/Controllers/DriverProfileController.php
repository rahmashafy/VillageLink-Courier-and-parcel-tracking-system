<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DriverProfileController extends Controller
{
    public function edit(Request $request)
    {
        $profile = $request->user()->driverProfile()->firstOrCreate([
            'user_id' => $request->user()->id,
        ], [
            'phone' => $request->user()->phone,
            'availability_status' => 'available',
        ]);

        return view('agent.profile', compact('profile'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'phone' => ['nullable', 'string', 'max:20'],
            'vehicle_type' => ['nullable', 'string', 'max:100'],
            'vehicle_number' => ['nullable', 'string', 'max:50'],
            'license_number' => ['nullable', 'string', 'max:100'],
            'availability_status' => ['required', 'in:available,offline'],
            'shift_start' => ['nullable', 'date_format:H:i'],
            'shift_end' => ['nullable', 'date_format:H:i'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $busy = $request->user()->activeDriverParcels()->exists();
        if ($busy && $validated['availability_status'] === 'available') {
            $validated['availability_status'] = 'busy';
        }

        $request->user()->driverProfile()->updateOrCreate(
            ['user_id' => $request->user()->id],
            $validated,
        );

        return back()->with('success', 'Driver profile updated successfully.');
    }
}
