<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class tb_sub_sale extends Model
{
    use HasFactory;
    protected $table = 'tb_sub_sale';
    protected $fillable = [
        'id_sale', 'store_id', 
    ];
}
