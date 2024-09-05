<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product  extends Model
{
    use HasFactory;
    protected $table = 'tb_product';
    protected $fillable = [
        'suppliercode', 'division', 'department', 'subdepartment', 
        'pro_Class', 'sub_pro_class', 'barcode', 'article', 
        'article_name', 'brand', 'pro_model','type_product','price','price_vat','com'
    ];
}
