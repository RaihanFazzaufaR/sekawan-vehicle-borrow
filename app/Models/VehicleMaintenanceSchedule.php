<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleMaintenanceSchedule extends Model
{
    protected $table = 'vehicle_maintenance_schedules';
    protected $primaryKey = 'schedule_id';
    protected $fillable =
    [
        'vehicle_id',
        'maintenance_date',
        'description',
        'status',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id', 'vehicle_id');
    }
}
