<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Complaint extends Model
{
    protected $fillable = [
        'user_id',
        'parcel_id',
        'category',
        'subject',
        'message',
        'image_path',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function parcel()
    {
        return $this->belongsTo(Parcel::class);
    }

    public function replies()
    {
        return $this->hasMany(ComplaintReply::class)->oldest();
    }
}
