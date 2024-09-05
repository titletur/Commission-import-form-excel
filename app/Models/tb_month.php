<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class tb_month extends Model
{
    use HasFactory;
    
    protected $table = 'tb_month';

    protected $fillable = [
        'month_th', 'short_th','month_en', 'short_en','var_month'
    ];
}

