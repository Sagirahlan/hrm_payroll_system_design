<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Step extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'grade_level_id', 'basic_salary'];

    public function gradeLevel()
    {
        return $this->belongsTo(GradeLevel::class);
    }
}