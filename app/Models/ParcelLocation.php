<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParcelLocation extends Model
{
    protected $fillable = [
        'parcel_id',
        'driver_id',
        'latitude',
        'longitude',
        'speed_kmh',
        'note',
    ];

    public function parcel()
    {
        return $this->belongsTo(Parcel::class);
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }
}
