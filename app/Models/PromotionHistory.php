<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PromotionHistory extends Model
{
    use HasFactory;

    protected $table = 'promotion_history';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'employee_id',
        'promotion_type',
        'previous_grade_level',
        'new_grade_level',
        'previous_step',
        'new_step',
        'promotion_date',
        'effective_date',
        'approving_authority',
        'reason',
        'status',
        'created_by',
    ];

    protected $casts = [
        'promotion_date' => 'date',
        'effective_date' => 'date',
    ];

    // Define relationships
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }
}
