<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    protected $table = 'drivers';
    protected $primaryKey = 'driver_id';
    protected $fillable =
    [
        'staff_id',
        'status',
    ];

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id', 'staff_id');
    }

    public function booking()
    {
        return $this->hasMany(Booking::class);
    }
}
