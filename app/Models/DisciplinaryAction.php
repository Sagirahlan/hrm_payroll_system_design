<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class DisciplinaryAction extends Model
{
    protected $primaryKey = 'action_id';
    protected $fillable = ['employee_id', 'action_type', 'description', 'action_date', 'resolution_date', 'status', 'days_counted'];

    protected $casts = [
        'action_date' => 'date',
        'resolution_date' => 'date',
    ];

    public function employee()
{
    return $this->belongsTo(\App\Models\Employee::class, 'employee_id', 'employee_id');
}

    /**
     * Calculate days between action date and resolution date (or current date if not resolved)
     */
    public function getDaysCountedAttribute(): int
    {
        $startDate = Carbon::parse($this->action_date);
        $endDate = $this->resolution_date ? Carbon::parse($this->resolution_date) : Carbon::now();

        return $startDate->diffInDays($endDate);
    }
}

