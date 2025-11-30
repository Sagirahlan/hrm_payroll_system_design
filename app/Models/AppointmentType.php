<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppointmentType extends Model
{
    protected $primaryKey = 'id';
    protected $fillable = ['name', 'description'];

    public function employees()
    {
        return $this->hasMany(Employee::class, 'appointment_type_id', 'id');
    }
}
