<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    protected $table = 'vehicles';
    protected $primaryKey = 'vehicle_id';
    protected $fillable =
    [
        'ownership_id',
        'license_plate',
        'vehicle_type',
        'brand',
        'model',
        'status',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'ownership_id', 'company_id');
    }

    public function vehiclelogs()
    {
        return $this->hasMany(VehicleLogs::class, 'vehicle_id', 'vehicle_id');
    }

    public function vehicleMaintenanceSchedule()
    {
        return $this->hasMany(VehicleMaintenanceSchedule::class, 'vehicle_id', 'vehicle_id');
    }

    public function booking()
    {
        return $this->hasMany(Booking::class);
    }
}
