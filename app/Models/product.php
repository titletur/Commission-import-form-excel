<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product  extends Model
{
    use HasFactory;
    protected $table = 'tb_product';
    protected $fillable = [
        'supplier', 'sub_dept', 'sub_dept_name', 'pro_model', 
        'skucode', 'pro_name', 'type_product','price','price_vat','com','status_product'
    ];
}
