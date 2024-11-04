<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;
    protected $table = 'transaction';
    protected $fillable = [
        'supplier',
        'tdate',
        'as_of_month',
        'as_of_year',
        'sub_dept',
        'sub_dept_name',
        'store_id',
        'store',
        'skucode',
        'pro_model',
        'pro_name',
        'item_status',
        'atb_code',
        'distributemethod',
        'amount',
        'dcavail',
        'stock',
        'poondalivery',
        'toondalivery',
        'sale_qty',
        'sale_price',
        'sale_amount',
    ];
}

