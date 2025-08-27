<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DisciplinaryAction extends Model
{
    protected $primaryKey = 'action_id';
    protected $fillable = ['employee_id', 'action_type', 'description', 'action_date', 'resolution_date', 'status'];
    
    public function employee()
{
    return $this->belongsTo(\App\Models\Employee::class, 'employee_id', 'employee_id');
}

}

