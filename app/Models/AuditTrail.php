<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Role;

class AuditTrail extends Model
{
    protected $fillable = [
        'user_id',
        'role_id',
        'action',
        'description',
        'action_timestamp',
        'log_data', // ✅ new JSON column
    ];

    protected $casts = [
        'log_data' => 'array', // ✅ automatically cast to array when accessed
        'action_timestamp' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }
}
