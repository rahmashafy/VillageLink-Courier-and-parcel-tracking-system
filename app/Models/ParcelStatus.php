<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParcelStatus extends Model
{
    protected $fillable = [
        'parcel_id',
        'user_id',
        'status',
        'note',
    ];
}
