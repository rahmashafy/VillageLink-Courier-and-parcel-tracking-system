<?php

namespace App\Http\Controllers;

use App\Models\Parcel;
use App\Models\Rating;
use Illuminate\Http\Request;

class RatingController extends Controller
{
    public function create(Parcel $parcel)
    {
        if ($parcel->user_id !== auth()->id()) {
            abort(403);
        }

        return view('customer.ratings.create', compact('parcel'));
    }

    public function store(Request $request, Parcel $parcel)
    {
        if ($parcel->user_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'speed_rating' => ['required', 'integer', 'min:1', 'max:5'],
            'behavior_rating' => ['required', 'integer', 'min:1', 'max:5'],
            'safety_rating' => ['required', 'integer', 'min:1', 'max:5'],
            'service_rating' => ['required', 'integer', 'min:1', 'max:5'],
            'feedback' => ['nullable', 'string'],
        ]);

        Rating::create([
            'user_id' => auth()->id(),
            'parcel_id' => $parcel->id,
            'rating' => $request->rating,
            'speed_rating' => $request->speed_rating,
            'behavior_rating' => $request->behavior_rating,
            'safety_rating' => $request->safety_rating,
            'service_rating' => $request->service_rating,
            'feedback' => $request->feedback,
        ]);

        return redirect()->route('customer.parcels.index')
            ->with('success', 'Thank you for your feedback.');
    }
}