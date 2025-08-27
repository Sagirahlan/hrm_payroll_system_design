<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class UserRole extends Model
{

    protected $table = 'user_roles';
    protected $primaryKey = 'role_id';

    protected $fillable = ['role_name', 'description', 'permissions'];

    protected $casts = [
        'permissions' => 'array', // 👈 This is important
    ];

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permissions');
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function hasPermission($permission)
    {
        return $this->permissions->contains('name', $permission);
    }
    public function getRouteKeyName()
{
    return 'role_id';
}

}




