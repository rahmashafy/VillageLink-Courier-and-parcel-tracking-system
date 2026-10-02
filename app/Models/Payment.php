<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'parcel_id',
        'user_id',
        'amount',
        'currency',
        'method',
        'provider',
        'reference',
        'gateway_order_id',
        'gateway_payment_id',
        'gateway_status',
        'payment_details',
        'checkout_payload',
        'status',
        'paid_at',
        'failed_at',
        'failure_reason',
        'loyalty_points_redeemed',
        'loyalty_discount',
        'loyalty_points_awarded',
    ];

    protected $casts = [
        'payment_details' => 'array',
        'checkout_payload' => 'array',
        'paid_at' => 'datetime',
        'failed_at' => 'datetime',
        'loyalty_discount' => 'decimal:2',
    ];

    public function parcel()
    {
        return $this->belongsTo(Parcel::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function refundRequests()
    {
        return $this->hasMany(RefundRequest::class);
    }
}
