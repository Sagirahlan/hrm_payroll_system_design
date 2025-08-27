<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NextOfKin extends Model
{
    protected $primaryKey = 'kin_id';
    protected $fillable = [
        'employee_id',
        'name',
        'relationship',
        'mobile_no',
        'address',
        'occupation',
        'place_of_work'
    ];
}
