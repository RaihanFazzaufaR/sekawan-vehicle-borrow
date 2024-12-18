<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingApprovals extends Model
{
    protected $table = 'booking_approvals';
    protected $primaryKey = 'approval_id';
    protected $fillable =
    [
        'booking_id',
        'approver_id',
        'approval_level',
        'status',
        'approved_at',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id', 'booking_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'approver_id', 'id');
    }
}
