<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class tb_pc extends Model
{
    use HasFactory;
    protected $table = 'tb_pc';
    protected $fillable = [
        'store_id', 'type_store', 'code_pc', 'name_pc', 
        'type_pc', 'tarket', 'salary'
    ];
}
