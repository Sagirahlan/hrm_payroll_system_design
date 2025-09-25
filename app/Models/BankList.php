<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankList extends Model
{
    protected $table = 'bank_list';
    
    protected $fillable = [
        'bank_name',
        'bank_code',
        'is_active'
    ];
}
