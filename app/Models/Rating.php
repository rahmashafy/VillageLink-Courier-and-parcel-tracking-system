<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    protected $fillable = [
        'user_id',
        'parcel_id',
        'rating',
        'speed_rating',
        'behavior_rating',
        'safety_rating',
        'service_rating',
        'feedback',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function parcel()
    {
        return $this->belongsTo(Parcel::class);
    }
}
