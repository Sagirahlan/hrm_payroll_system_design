<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use Notifiable, HasRoles, HasApiTokens;

    protected $primaryKey = 'user_id';
    

    protected $fillable = [
        'employee_id',
        'username',
        'email',
        'password_hash',
    ];

    protected $hidden = [
        'password_hash',
        'remember_token',
    ];

    /**
     * Override getAuthPassword to use password_hash column for authentication
     */
    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    public function auditTrails()
    {
        return $this->hasMany(AuditTrail::class, 'user_id', 'user_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }
}