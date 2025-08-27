<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalaryScale extends Model
{
    protected $table = 'salary_scales';
    protected $primaryKey = 'scale_id';
    protected $fillable = ['scale_name', 'basic_salary', 'grade_level', 'step_level', 'description'];

    public function employees()
    {
        return $this->hasMany(Employee::class, 'salary_scale_id', 'scale_id');
    }
}