<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    protected $primaryKey = 'bank_id';

    protected $fillable = [
        'bank_name',
        'bank_code',
        'employee_id',
        'account_name',
        'account_no',
    ];
}