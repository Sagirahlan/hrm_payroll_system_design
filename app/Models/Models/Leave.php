<?php

namespace App\Models\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Leave extends Model
{
    protected $fillable = [
        'employee_id',
        'leave_type',
        'start_date',
        'end_date',
        'days_requested',
        'reason',
        'status',
        'approval_remarks',
        'approved_by',
        'approved_at',
    ];

    protected $dates = [
        'start_date',
        'end_date',
        'approved_at',
        'created_at',
        'updated_at',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Employee::class, 'employee_id', 'employee_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'approved_by');
    }
}