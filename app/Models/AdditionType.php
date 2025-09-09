<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdditionType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'is_statutory',
        'calculation_type',
        'rate_or_amount',
    ];

    public function gradeLevels()
    {
        return $this->morphToMany(GradeLevel::class, 'adjustable', 'grade_level_adjustments')->withPivot('percentage');
    }
}
