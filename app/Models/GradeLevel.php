<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GradeLevel extends Model
{
    protected $table = 'grade_levels';
    protected $primaryKey = 'id';
    protected $fillable = ['name', 'grade_level', 'description', 'salary_scale_id'];

    public function employees()
    {
        return $this->hasMany(Employee::class, 'grade_level_id', 'id');
    }

    public function salaryScale()
    {
        return $this->belongsTo(SalaryScale::class, 'salary_scale_id');
    }

    public function deductionTypes()
    {
        return $this->morphedByMany(DeductionType::class, 'adjustable', 'grade_level_adjustments')->withPivot('percentage');
    }

    public function additionTypes()
    {
        return $this->morphedByMany(AdditionType::class, 'adjustable', 'grade_level_adjustments')->withPivot('percentage');
    }

    public function steps()
    {
        return $this->hasMany(Step::class);
    }

    // Get the basic salary (assuming first step is the basic salary)
    public function getBasicSalaryAttribute()
    {
        $firstStep = $this->steps()->orderBy('name')->first();
        return $firstStep ? $firstStep->basic_salary : 0;
    }
}