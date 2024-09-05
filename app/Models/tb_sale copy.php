<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class tb_commission_sale extends Model
{
    use HasFactory;
    protected $table = 'tb_commission_sale';
    protected $fillable = [
        'id', 'id_sale', 'as_of_month', 'as_of_year', 'target','achieve','base_com',
        'com_sale','extra_sale_out','extra_unit','extra_avg',
    ];
}

