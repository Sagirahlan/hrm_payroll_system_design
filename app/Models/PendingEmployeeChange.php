<?php

namespace App\Models;

use App\Helpers\ComparisonHelper;
use Illuminate\Database\Eloquent\Model;

class PendingEmployeeChange extends Model
{
    protected $fillable = [
        'employee_id',
        'requested_by',
        'change_type',
        'data',
        'previous_data',
        'reason',
        'status',
        'approved_by',
        'approved_at',
        'approval_notes'
    ];

    protected $casts = [
        'data' => 'array',
        'previous_data' => 'array',
        'approved_at' => 'datetime'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
    
    public function getChangeDescriptionAttribute()
    {
        switch ($this->change_type) {
            case 'create':
                return 'New employee creation request';
            case 'update':
                $changes = [];
                foreach ($this->data as $key => $value) {
                    $previousValue = $this->previous_data[$key] ?? null;
                    if (ComparisonHelper::isDifferent($previousValue, $value)) {
                        $changes[] = ucfirst(str_replace('_', ' ', $key));
                    }
                }
                return 'Update request for: ' . (count($changes) > 0 ? implode(', ', $changes) : 'No changes');
            case 'delete':
                return 'Employee deletion request';
            default:
                return 'Unknown change type';
        }
    }
    
    public function getEmployeeNameAttribute()
    {
        if ($this->employee) {
            return $this->employee->first_name . ' ' . $this->employee->surname;
        }
        
        if ($this->change_type === 'create' && isset($this->data['first_name']) && isset($this->data['surname'])) {
            return $this->data['first_name'] . ' ' . $this->data['surname'];
        }
        
        return 'Unknown Employee';
    }
}
