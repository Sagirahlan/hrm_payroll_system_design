<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsNotification extends Model
{
    protected $primaryKey = 'sms_id'; // if this is your PK
    protected $fillable = [
        'user_id', 
        'recipient_type', 
        'recipient_id', 
        'message', 
        'status', 
        'sent_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}

