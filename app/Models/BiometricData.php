<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BiometricData extends Model
{
    protected $primaryKey = 'biometric_id';
    protected $fillable = ['employee_id', 'nin', 'fingerprint_data', 'verification_status', 'verification_date'];
}