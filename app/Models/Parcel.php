<?php

namespace App\Models;
use App\Models\User;
use App\Models\ParcelStatus;


use Illuminate\Database\Eloquent\Model;

class Parcel extends Model
{
    protected $fillable = [
        'tracking_id',
        'user_id',
        'agent_id',
        'sender_name',
        'receiver_name',
        'receiver_phone',
        'pickup_address',
        'delivery_address',
        'parcel_type',
        'delivery_type',
        'weight',
        'length_cm',
        'width_cm',
        'height_cm',
        'volumetric_weight',
        'chargeable_weight',
        'distance_km',
        'original_price',
        'loyalty_discount_amount',
        'price',
        'status',
        'payment_status',
        'pickup_location',
        'delivery_location',
        'current_lat',
        'current_lng',
        'delivery_proof_path',
        'delivery_notes',
        'estimated_delivery_at',
        'delivered_at',
    ];

    protected function casts(): array
    {
        return [
            'estimated_delivery_at' => 'datetime',
            'delivered_at' => 'datetime',
            'weight' => 'decimal:2',
            'length_cm' => 'decimal:2',
            'width_cm' => 'decimal:2',
            'height_cm' => 'decimal:2',
            'volumetric_weight' => 'decimal:2',
            'chargeable_weight' => 'decimal:2',
            'price' => 'decimal:2',
            'original_price' => 'decimal:2',
            'loyalty_discount_amount' => 'decimal:2',
        ];
    }
    public function user()
{
    return $this->belongsTo(User::class);
}

public function agent()
{
    return $this->belongsTo(User::class, 'agent_id');
}

public function statuses()
{
    return $this->hasMany(ParcelStatus::class)->latest();
}

public function locations()
{
    return $this->hasMany(ParcelLocation::class)->latest();
}

public function latestLocation()
{
    return $this->hasOne(ParcelLocation::class)->latestOfMany();
}

public function payment()
{
    return $this->hasOne(Payment::class)->latestOfMany();
}

public function payments()
{
    return $this->hasMany(Payment::class);
}

public function complaint()
{
    return $this->hasOne(Complaint::class);
}

public function refundRequests()
{
    return $this->hasMany(RefundRequest::class);
}

}
