<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleLogs extends Model
{
    protected $table = 'vehicle_logs';
    protected $primaryKey = 'log_id';
    protected $fillable =
    [
        'vehicle_id',
        'booking_id',
        'distance',
        'fuel_consumed',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id', 'vehicle_id');
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id', 'booking_id');
    }
}
