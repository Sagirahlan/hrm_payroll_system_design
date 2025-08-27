<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    protected $table = 'reports';
    protected $primaryKey = 'report_id';
    
    protected $fillable = [
        'report_type',
        'generated_by',
        'generated_date',
        'report_data',
        'export_format',
        'file_path',
        'employee_id'
    ];

    protected $casts = [
        'report_data' => 'array',
        'generated_date' => 'datetime'
    ];

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by', 'user_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }

    public function promotionHistory()
{
    return $this->hasMany(PromotionHistory::class);
}

}