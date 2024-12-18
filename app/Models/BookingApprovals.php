<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingApprovals extends Model
{
    protected $fillable = ['booking_id', 'approver_id', 'approval_level', 'status'];

    protected $primaryKey = 'approval_id'; // Ensure the primary key is correctly set

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id', 'booking_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'approver_id', 'id');
    }

    public static function checkAndUpdateBookingStatus($bookingId)
    {
        $approvals = self::where('booking_id', $bookingId)->orderBy('approval_level')->get();

        $allApproved = true;
        $hasRejected = false;

        foreach ($approvals as $approval) {
            if ($approval->status === 'rejected') {
                $hasRejected = true;
                break;
            }
            if ($approval->status !== 'approved') {
                $allApproved = false;
            }
        }

        if ($hasRejected) {
            // Update the status of all subsequent approvals to 'rejected'
            $rejected = false;
            foreach ($approvals as $approval) {
                if ($approval->status === 'rejected') {
                    $rejected = true;
                }
                if ($rejected && $approval->status !== 'rejected') {
                    $approval->update(['status' => 'rejected']);
                }
            }
            // Update the booking status to 'rejected'
            Booking::where('booking_id', $bookingId)->update(['status' => 'rejected']);
        } elseif ($allApproved) {
            // Update the booking status to 'approved'
            Booking::where('booking_id', $bookingId)->update(['status' => 'approved']);
        } else {
            // Only update the booking status to 'pending' if it is not already 'rejected' or 'approved'
            Booking::where('booking_id', $bookingId)->whereNotIn('status', ['rejected', 'approved'])->update(['status' => 'pending']);
        }
    }

    protected static function boot()
    {
        parent::boot();

        static::updated(function ($approval) {
            self::checkAndUpdateBookingStatus($approval->booking_id);
        });
    }
}
