<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $table = 'companies';
    protected $primaryKey = 'company_id';
    protected $fillable =
    [
        'name',
        'status',
    ];

    public function staff()
    {
        return $this->hasMany(Staff::class, 'company_id', 'company_id');
    }

    public function user()
    {
        return $this->hasMany(User::class, 'company_id', 'company_id');
    }

    public function vehicle()
    {
        return $this->hasMany(Vehicle::class, 'ownership_id', 'company_id');
    }
}
