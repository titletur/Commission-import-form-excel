<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class tb_sale extends Model
{
    use HasFactory;
    protected $table = 'tb_sale';
    protected $primaryKey = 'id_sale';
    protected $fillable = [
        'code_sale', 'name_sale', 'target', 'base_com', 'status_sale',
    ];
}
