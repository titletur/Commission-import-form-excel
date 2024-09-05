<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class tb_status extends Model
{
    use HasFactory;
    
    protected $table = 'tb_status';

    protected $fillable = [
        'as_of_month', 'as_of_year','status_com'
    ];
}

