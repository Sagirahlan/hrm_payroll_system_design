<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ward extends Model
{
    use HasFactory;

    protected $fillable = ['lga_id', 'ward_name'];

    public function lga()
    {
        return $this->belongsTo(Lga::class);
    }
}
