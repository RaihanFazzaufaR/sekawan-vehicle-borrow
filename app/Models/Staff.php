<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    protected $table = 'staff';
    protected $primaryKey = 'staff_id';
    protected $fillable =
    [
        'company_id',
        'full_name',
        'phone_number',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'company_id');
    }

    public function driver()
    {
        return $this->hasOne(Driver::class, 'staff_id', 'staff_id');
    }
}
