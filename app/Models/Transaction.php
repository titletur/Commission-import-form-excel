<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;
    protected $fillable = [
        'supplier_number',
        'location_number',
        'location_name',
        'class_number',
        'sub_class',
        'item_number',
        'barcode',
        'as_of_month',
        'as_of_year',
        'item_des',
        'eoh_qty',
        'on_order',
        'pack_type',
        'unit',
        'avg_net_sale_qty',
        'net_sale_qty_ytd',
        'last_receved_date',
        'last_sold_date',
        'stock_cover_day',
        'net_sale_qty_mtd',
        'day1',
        'day2',
        'day3',
        'day4',
        'day5',
        'day6',
        'day7',
        'day8',
        'day9',
        'day10',
        'day11',
        'day12',
        'day13',
        'day14',
        'day15',
        'day16',
        'day17',
        'day18',
        'day19',
        'day20',
        'day21',
        'day22',
        'day23',
        'day24',
        'day25',
        'day26',
        'day27',
        'day28',
        'day29',
        'day30',
        'day31',
    ];
}
