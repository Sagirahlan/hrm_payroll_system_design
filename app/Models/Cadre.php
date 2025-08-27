<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cadre extends Model
{
    protected $primaryKey = 'cadre_id';
    protected $fillable = ['cadre_name', 'description'];
}
