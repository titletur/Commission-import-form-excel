<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccessControl extends Model
{
    use HasFactory;
    
    protected $table = 'access_controls';

    protected $fillable = [
        'month', 'year','show_link_enabled', 'price_link_enabled','target_link_enabled'
    ];
}

