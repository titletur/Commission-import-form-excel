<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product  extends Model
{
    use HasFactory;
    protected $table = 'tb_product';
    protected $fillable = [
        'supplier_number', 'item_number', 'barcode', 'item_des', 
        'type_product', 'pack_type', 'price_vat', 'com', 'status_product'
    ];
}
