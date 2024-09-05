<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class main_commission extends Model
{
    use HasFactory;

    protected $table = 'tb_main_commission';

    protected $fillable = [
        'store_id','type_store', 'as_of_month', 'as_of_year', 'sale_tv','sale_av','sale_ha',
        'sale_total', 'id_pc','name_pc','type_pc','pc_salary','tarket','achieve','normalcom_tv','normalcom_av','normalcom_ha',
        'com_tv','com_av','com_ha','unit_tv','unit_av','unit_ha'
        ,'pay_com','extra_tv','extra_ha','net_com','advance_pay','other','remark','net_pay','dis_pay'
    ];
}

