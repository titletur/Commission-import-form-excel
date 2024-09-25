<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale_in extends Model
{
    use HasFactory;
    protected $table = 'sales_in';
    protected $fillable = [
        'month', 'year', 'sale_in'
    ];
    public $timestamps = false;
}
