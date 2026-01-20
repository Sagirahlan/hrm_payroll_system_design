<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PendingPensionerChange extends Model
{
    protected $fillable = [
        'pensioner_id',
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

    public function pensioner()
    {
        return $this->belongsTo(Pensioner::class, 'pensioner_id', 'id');
    }

    public function requestedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'requested_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'approved_by');
    }

    public function getChangeDescriptionAttribute()
    {
        switch ($this->change_type) {
            case 'create':
                return 'New pensioner creation request';
            case 'update':
                $changes = [];
                foreach ($this->data as $key => $value) {
                    $previousValue = $this->previous_data[$key] ?? null;
                    if ($previousValue != $value) {
                        $changeLabel = ucfirst(str_replace('_', ' ', $key));
                        $changes[] = $changeLabel;
                    }
                }
                return 'Update request for: ' . (count($changes) > 0 ? implode(', ', $changes) : 'No changes');
            case 'delete':
                return 'Pensioner deletion request';
            default:
                return 'Unknown change type';
        }
    }

    public function getPensionerNameAttribute()
    {
        if ($this->pensioner) {
            return $this->pensioner->full_name;
        }

        if ($this->change_type === 'create' && isset($this->data['full_name'])) {
            return $this->data['full_name'];
        }

        return 'Unknown Pensioner';
    }
}
