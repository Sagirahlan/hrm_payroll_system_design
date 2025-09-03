<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalaryScale extends Model
{
    protected $fillable = [
        'acronym',
        'full_name',
        'sector_coverage',
        'grade_levels',
        'max_retirement_age',
        'max_years_of_service',
        'notes'
    ];

    public function gradeLevels()
    {
        return $this->hasMany(GradeLevel::class, 'salary_scale_id');
    }
}
