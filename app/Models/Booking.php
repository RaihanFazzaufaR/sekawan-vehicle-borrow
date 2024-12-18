<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $table = 'bookings';
    protected $primaryKey = 'booking_id';
    protected $fillable =
    [
        'admin_id',
        'vehicle_id',
        'driver_id',
        'booker_id',
        'booking_date',
        'start_date',
        'end_date',
        'status',
        'purpose',
    ];

    public function driver()
    {
        return $this->belongsTo(Driver::class, 'driver_id', 'driver_id');
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'booker_id', 'staff_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'admin_id', 'id');
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id', 'vehicle_id');
    }

    public function vehicleLogs()
    {
        return $this->hasOne(VehicleLogs::class, 'booking_id', 'booking_id');
    }

    public function booking_approvals()
    {
        return $this->hasOne(BookingApprovals::class, 'booking_id', 'booking_id');
    }
}
