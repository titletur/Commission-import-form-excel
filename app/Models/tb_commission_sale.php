<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class tb_commission_sale extends Model
{
    use HasFactory;

    protected $table = 'tb_commission_sale';

    protected $fillable = [
        'is_sale','as_of_month', 'as_of_year','target', 'add_total' , 'achieve', 'base_com', 'com_sale','sale_tv',
        'unit_tv','sale_av','unit_av','sale_ha','unit_ha','sale_out','extra_sale_out','extra_unit',
        'extra_avg','other','total','remark'
    ];
}
