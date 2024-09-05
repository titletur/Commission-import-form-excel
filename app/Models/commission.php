<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Commission extends Model
{
    use HasFactory;

    protected $table = 'tb_commission';

    protected $fillable = [
        'suppliercode', 'store_id','type_store', 'as_of_month', 'as_of_year', 'pro_model','type_product',
        'sale_amt','sale_amt_vat','sale_qty','id_pc','type_pc','com'
    ];
}

