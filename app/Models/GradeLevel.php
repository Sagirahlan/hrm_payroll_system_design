<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GradeLevel extends Model
{
    protected $table = 'grade_levels';
    protected $primaryKey = 'id';
    protected $fillable = ['name', 'basic_salary', 'grade_level', 'step_level', 'description'];

    public function employees()
    {
        return $this->hasMany(Employee::class, 'grade_level_id', 'id');
    }
}