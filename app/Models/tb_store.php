<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class tb_store extends Model
{
    use HasFactory;

    protected $table = 'tb_store';

    protected $fillable = [
        'suppliercode', 'store_id', 'store','type_store'
    ];
}
