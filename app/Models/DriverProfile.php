<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DriverProfile extends Model
{
    protected $fillable = [
        'user_id',
        'phone',
        'vehicle_type',
        'vehicle_number',
        'license_number',
        'availability_status',
        'shift_start',
        'shift_end',
        'current_lat',
        'current_lng',
        'last_location_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'last_location_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
