<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;
    protected $fillable = [
        'report_code',
        'suppliercode',
        'suppliername',
        'business_format',
        'compare',
        'store_id',
        'store',
        'as_of_month',
        'as_of_year',
        'last_year_compare_month',
        'report_date',
        'division',
        'department',
        'subdepartment',
        'pro_Class',
        'sub_pro_class',
        'barcode',
        'article',
        'article_name',
        'brand',
        'pro_model',
        'type_product',
        'sale_amt_ty',
        'sale_amt_ty_vat',
        'sale_price',
        'sale_price_vat',
        'sale_amt_ly',
        'sale_amt_var',
        'sale_qty_ty',
        'sale_qty_ly',
        'sale_qty_var',
        'stock_ty',
        'stock_ly',
        'stock_var',
        'stock_qty_ty',
        'stock_qty_ly',
        'stock_qty_var',
        'day_on_hand_ty',
        'day_on_hand_ly',
        'day_on_hand_diff',
    ];
}
