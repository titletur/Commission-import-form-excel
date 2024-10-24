<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Price  extends Model
{
    use HasFactory;
    protected $table = 'tb_price';
    protected $fillable = [
        'item_number', 'barcode', 'item_des', 'as_of_month', 'as_of_year', 
        'price_day1',
        'price_day2',
        'price_day3',
        'price_day4',
        'price_day5',
        'price_day6',
        'price_day7',
        'price_day8',
        'price_day9',
        'price_day10',
        'price_day11',
        'price_day12',
        'price_day13',
        'price_day14',
        'price_day15',
        'price_day16',
        'price_day17',
        'price_day18',
        'price_day19',
        'price_day20',
        'price_day21',
        'price_day22',
        'price_day23',
        'price_day24',
        'price_day25',
        'price_day26',
        'price_day27',
        'price_day28',
        'price_day29',
        'price_day30',
        'price_day31',
    ];
}
