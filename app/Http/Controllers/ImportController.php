<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\DB;
use App\Models\Transaction;
use App\Models\product;
use App\Models\Price;
use App\Models\tb_store;
use App\Models\Commission;
use App\Models\main_commission;
use App\Models\tb_pc;
use App\Models\tb_sale;
use App\Models\tb_sub_sale;
use App\Models\tb_commission_sale;
use RealRashid\SweetAlert\Facades\Alert;
class ImportController extends Controller
{
    //
    public function form($year, $month,$var_month)
    {
        return view('import', compact('year', 'month','var_month'));
    }
    public function import(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|mimes:xls,xlsx|max:10240',
        ]);
        $var_year = $request->input('var_year');
        $var_month = $request->input('var_month');
        $short_month = $request->input('short_month');
        
        if ($request->hasFile('excel_file')) {
            $file = $request->file('excel_file');
            $filename = time() . '-' . $file->getClientOriginalName();
            $filePath = $file->storeAs('uploads', $filename, 'public');
    
            // Load the spreadsheet from the uploaded file
            $spreadsheet = IOFactory::load($file->getPathname());
            // $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
    
            $data = [];
        foreach ($spreadsheet->getAllSheets() as $sheet) {
                $sheetData = $sheet->toArray(null, true, true, true);

                $as_of_month_year = $sheetData[16]['B']; // Assuming B16 is at index 16
                $as_of_date  = array_filter(preg_split('/\s+/', $as_of_month_year));
                $as_of_month = $as_of_date[0] ? $as_of_date[0] : null;
                $as_of_year = $as_of_date[1] ? $as_of_date[1] : null;
                
                //  dd($as_of_month, $short_month, $as_of_year);

        foreach ($sheetData as $key => $row) {
            if ($key > 19) {

            if ($row['R'] != '0' && $as_of_month == $var_month && $as_of_year == $var_year ) {
            $data[] = [
                'supplier_number' => $row['A'],
                'location_number' => $row['B'],
                'location_name' => $row['C'],
                'class_number' => $row['D'],
                'sub_class' => $row['E'],
                'item_number' => $row['F'],
                'barcode' => $row['G'],
                'as_of_month' => $var_month,
                'as_of_year' => $var_year,
                'item_des' => $row['H'],
                'eoh_qty' => $row['I'],
                'on_order' => $row['J'],
                'pack_type' => $row['K'],
                'unit' => $row['L'],
                'avg_net_sale_qty' => $row['M'],
                'net_sale_qty_ytd' => $row['N'],
                'last_receved_date' => $row['O'],
                'last_sold_date' => $row['P'],
                'stock_cover_day' => $row['Q'],
                'net_sale_qty_mtd' => $row['R'],
                'day1' => $sheet->getCell('S' . $key)->getCalculatedValue(),
                'day2' => $sheet->getCell('T' . $key)->getCalculatedValue(),
                'day3' => $sheet->getCell('U' . $key)->getCalculatedValue(),
                'day4' => $sheet->getCell('V' . $key)->getCalculatedValue(),
                'day5' => $sheet->getCell('W' . $key)->getCalculatedValue(),
                'day6' => $sheet->getCell('X' . $key)->getCalculatedValue(),
                'day7' => $sheet->getCell('Y' . $key)->getCalculatedValue(),
                'day8' => $sheet->getCell('Z' . $key)->getCalculatedValue(),
                'day9' => $sheet->getCell('AA' . $key)->getCalculatedValue(),
                'day10' => $sheet->getCell('AB' . $key)->getCalculatedValue(),
                'day11' => $sheet->getCell('AC' . $key)->getCalculatedValue(),
                'day12' => $sheet->getCell('AD' . $key)->getCalculatedValue(),
                'day13' => $sheet->getCell('AE' . $key)->getCalculatedValue(),
                'day14' => $sheet->getCell('AF' . $key)->getCalculatedValue(),
                'day15' => $sheet->getCell('AG' . $key)->getCalculatedValue(),
                'day16' => $sheet->getCell('AH' . $key)->getCalculatedValue(),
                'day17' => $sheet->getCell('AI' . $key)->getCalculatedValue(),
                'day18' => $sheet->getCell('AJ' . $key)->getCalculatedValue(),
                'day19' => $sheet->getCell('AK' . $key)->getCalculatedValue(),
                'day20' => $sheet->getCell('AL' . $key)->getCalculatedValue(),
                'day21' => $sheet->getCell('AM' . $key)->getCalculatedValue(),
                'day22' => $sheet->getCell('AN' . $key)->getCalculatedValue(),
                'day23' => $sheet->getCell('AO' . $key)->getCalculatedValue(),
                'day24' => $sheet->getCell('AP' . $key)->getCalculatedValue(),
                'day25' => $sheet->getCell('AQ' . $key)->getCalculatedValue(),
                'day26' => $sheet->getCell('AR' . $key)->getCalculatedValue(),
                'day27' => $sheet->getCell('AS' . $key)->getCalculatedValue(),
                'day28' => $sheet->getCell('AT' . $key)->getCalculatedValue(),
                'day29' => $sheet->getCell('AU' . $key)->getCalculatedValue(),
                'day30' => $sheet->getCell('AV' . $key)->getCalculatedValue(),
                'day31' => $sheet->getCell('AW' . $key)->getCalculatedValue(),
            ];
            }
            }
            }

        }
        return view('preview', compact('data', 'filePath','var_year', 'short_month','var_month'));
        }

        return redirect()->back()->withErrors(['error' => 'File upload failed']);
    }
    

    public function store(Request $request)
    {
        $var_year = $request->input('var_year');
        $var_month = $request->input('var_month');
        $short_month = $request->input('short_month');
        
        $data = json_decode($request->input('data'), true);
        Commission::where([
            'as_of_month' => $var_month,
            'as_of_year' => $var_year,
        ])->delete();
        Transaction::where([
            'as_of_month' => $var_month,
            'as_of_year' => $var_year,
        ])->delete();

        main_commission::where([
            'as_of_month' => $var_month,
            'as_of_year' => $var_year,
        ])->delete();
        if (is_array($data)) {
            foreach ($data as $key => $row) {
                
                if($row['supplier_number'] == '28106'){
                    $type_product = "AV";
                }else if($row['supplier_number'] == '29898'){
                    $type_product = "AV";
                }else if($row['supplier_number'] == '31445'){
                    $type_product = "AV";
                }else if($row['supplier_number'] == '92421'){
                    $type_product = "HA";
                }else if($row['supplier_number'] == '93510'){
                    $type_product = "HA";
                }else{
                    $type_product = "TV";
                }

                Transaction::create([
                'supplier_number'=> $row['supplier_number'],
                'location_number'=> $row['location_number'],
                'location_name'=> $row['location_name'],
                'class_number'=> $row['class_number'],
                'sub_class'=> $row['sub_class'],
                'item_number'=> $row['item_number'],
                'barcode'=> $row['barcode'],
                'as_of_month'=> $var_month,
                'as_of_year'=> $var_year,
                'item_des'=> $row['item_des'],
                'eoh_qty'=> $row['eoh_qty'],
                'on_order'=> $row['on_order'],
                'pack_type'=> $row['pack_type'],
                'unit'=> $row['unit'],
                'avg_net_sale_qty'=> $row['avg_net_sale_qty'],
                'net_sale_qty_ytd'=> $row['net_sale_qty_ytd'],
                'last_receved_date'=> $row['last_receved_date'],
                'last_sold_date'=> $row['last_sold_date'],
                'stock_cover_day'=> $row['stock_cover_day'],
                'net_sale_qty_mtd'=> $row['net_sale_qty_mtd'],
                'day1'=> $row['day1'],
                'day2'=> $row['day2'],
                'day3'=> $row['day3'],
                'day4'=> $row['day4'],
                'day5'=> $row['day5'],
                'day6'=> $row['day6'],
                'day7'=> $row['day7'],
                'day8'=> $row['day8'],
                'day9'=> $row['day9'],
                'day10'=> $row['day10'],
                'day11'=> $row['day11'],
                'day12'=> $row['day12'],
                'day13'=> $row['day13'],
                'day14'=> $row['day14'],
                'day15'=> $row['day15'],
                'day16'=> $row['day16'],
                'day17'=> $row['day17'],
                'day18'=> $row['day18'],
                'day19'=> $row['day19'],
                'day20'=> $row['day20'],
                'day21'=> $row['day21'],
                'day22'=> $row['day22'],
                'day23'=> $row['day23'],
                'day24'=> $row['day24'],
                'day25'=> $row['day25'],
                'day26'=> $row['day26'],
                'day27'=> $row['day27'],
                'day28'=> $row['day28'],
                'day29'=> $row['day29'],
                'day30'=> $row['day30'],
                'day31'=> $row['day31'],
                ]);
                //อัปเดทข้อมูล Store
                
                $storeData = [
                    'store' => $row['location_name'],
                    // 'type_store' =>'A'
                ];

                tb_store::updateOrCreate(
                    ['store_id' => $row['location_number']], // Unique key to check
                    $storeData
                );

                $productData = [
                    'supplier_number' => $row['supplier_number'],
                    // 'type_product' => $type_product,
                    'barcode' => $row['barcode'],
                    'item_des' => $row['item_des'],
                    'pack_type' => $row['pack_type'],
                ];

                product::updateOrCreate(
                    ['item_number' => $row['item_number']], // Unique key to check
                    $productData
                );


                // Get PC data
                $pcs = tb_pc::whereNull('status_pc')
                    ->where('store_id', $row['location_number'])
                    ->get();


                // Calculate and Insert into tb_commission
                if ($pcs->count() == 1) {
                    $pc = $pcs->first();

                    $product = product::where('item_number', $row['item_number'])->first();
                    $price = Price::where('item_number', $row['item_number'])->first();
                    $sale_total_price_item = 0;
                    for ($num_qty_price = 1; $num_qty_price <= 31; $num_qty_price++) {
                        if ($row['day'.$num_qty_price] != 0 && $row['day'.$num_qty_price] !== null) {

                            $price_column = 'price_day' . $num_qty_price;

                            if (isset($price->$price_column) && $price->$price_column != 0) {
                                $sale_total_price_item += $row['day'.$num_qty_price] * $price->$price_column ;
                            }else{
                                $sale_total_price_item += $row['day'.$num_qty_price] * $product->price_vat ;
                            }

                        }
                    }
                    Commission::updateOrInsert(
                        // เงื่อนไขในการเช็คว่ามีข้อมูลอยู่แล้วหรือไม่
                        [
                            'supplier_number' => $row['supplier_number'],
                            'store_id' => $row['location_number'],
                            'as_of_month' => $row['as_of_month'],
                            'as_of_year' => $row['as_of_year'],
                            'item_number' => $row['item_number'],
                            'id_pc' => $pc->id,
                        ],
                        // ถ้ามีข้อมูลอยู่แล้วให้ทำการอัปเดทข้อมูลนี้
                        [
                            'type_store' => $pc->type_store,
                            'type_product' => $product->type_product,
                            'sale_total' => $sale_total_price_item, 
                            'sale_qty' => $row['net_sale_qty_mtd'],
                            'com' => $product->com,
                            'type_pc' => $pc->type_pc,
                            'day1'=> $row['day1'],
                            'day2'=> $row['day2'],
                            'day3'=> $row['day3'],
                            'day4'=> $row['day4'],
                            'day5'=> $row['day5'],
                            'day6'=> $row['day6'],
                            'day7'=> $row['day7'],
                            'day8'=> $row['day8'],
                            'day9'=> $row['day9'],
                            'day10'=> $row['day10'],
                            'day11'=> $row['day11'],
                            'day12'=> $row['day12'],
                            'day13'=> $row['day13'],
                            'day14'=> $row['day14'],
                            'day15'=> $row['day15'],
                            'day16'=> $row['day16'],
                            'day17'=> $row['day17'],
                            'day18'=> $row['day18'],
                            'day19'=> $row['day19'],
                            'day20'=> $row['day20'],
                            'day21'=> $row['day21'],
                            'day22'=> $row['day22'],
                            'day23'=> $row['day23'],
                            'day24'=> $row['day24'],
                            'day25'=> $row['day25'],
                            'day26'=> $row['day26'],
                            'day27'=> $row['day27'],
                            'day28'=> $row['day28'],
                            'day29'=> $row['day29'],
                            'day30'=> $row['day30'],
                            'day31'=> $row['day31'],
                        ]
                    );
                } else if ($pcs->count() > 1) {
                    $sale_qty_per_pc = (int)(abs($row['net_sale_qty_mtd']) / $pcs->count());
                    $remaining_qty = abs($row['net_sale_qty_mtd']) - ($sale_qty_per_pc * $pcs->count());
                
                    foreach ($pcs as $pcIndex => $pc) {
                        $product = product::where('item_number', $row['item_number'])->first();
                        $price = Price::where('item_number', $row['item_number'])->first();
                
                        $day_totals = [];
                        $remaining_days_qty = [];
                        for ($day = 1; $day <= 31; $day++) {
                            $day_key = 'day' . $day;
                            $day_totals[$day] = (int)(abs($row[$day_key]) / $pcs->count());
                            $remaining_days_qty[$day] = (int)abs($row[$day_key]) - ($day_totals[$day] * $pcs->count());
                        }
                        
                        
                        $sale_total_price_item = 0;
                        for ($num_qty_price = 1; $num_qty_price <= 31; $num_qty_price++) {
                            $day_qty = $day_totals[$num_qty_price] + ($remaining_days_qty[$num_qty_price] > 0 && $pcIndex < $remaining_days_qty[$num_qty_price] ? 1 : 0);
                
                            if ($day_qty != 0) {
                                $price_column = 'price_day' . $num_qty_price; 
                                $sale_total_price_item += $day_qty * ($price->$price_column ?? $product->price_vat);
                            }
                        }


                        // Commission::updateOrInsert(
                        //     [
                        //         'supplier_number' => $row['supplier_number'],
                        //         'store_id' => $row['location_number'],
                        //         'as_of_month' => $row['as_of_month'],
                        //         'as_of_year' => $row['as_of_year'],
                        //         'item_number' => $row['item_number'],
                        //         'id_pc' => $pc->id,
                        //     ],
                        //     [
                        //         'type_store' => $pc->type_store,
                        //         'type_product' => $product->type_product,
                        //         'sale_total' => $sale_total_price_item,
                        //         'sale_qty' => abs(($row['net_sale_qty_mtd'] < 0 ? -1 : 1) * ($sale_qty_per_pc + ($remaining_qty > 0 && $pcIndex < $remaining_qty ? 1 : 0))),
                        //         'com' => $product->com,
                        //         'type_pc' => $pc->type_pc,
                        //     ] + array_reduce(range(1, 31), function ($carry, $day) use ($day_totals, $remaining_days_qty, $pcIndex) {
                        //         $day_key = 'day' . $day;
                        //         $carry[$day_key] = abs(($day_totals[$day]< 0 ? -1 : 1) * ($day_totals[$day] + ($remaining_days_qty[$day] > 0 && $pcIndex < $remaining_days_qty[$day] ? 1 : 0)));
                        //         return $carry;
                        //     }, [])
                        // );
                        Commission::updateOrInsert(
                            [
                                'supplier_number' => $row['supplier_number'],
                                'store_id' => $row['location_number'],
                                'as_of_month' => $row['as_of_month'],
                                'as_of_year' => $row['as_of_year'],
                                'item_number' => $row['item_number'],
                                'id_pc' => $pc->id,
                            ],
                            [
                                'type_store' => $pc->type_store,
                                'type_product' => $product->type_product,
                                'sale_total' => $sale_total_price_item,
                                'sale_qty' => abs(($row['net_sale_qty_mtd'] < 0 ? -1 : 1) * ($sale_qty_per_pc + ($remaining_qty > 0 && $pcIndex < $remaining_qty ? 1 : 0))),
                                'com' => $product->com,
                                'type_pc' => $pc->type_pc,
                                'day1'=> abs(($day_totals['1'] < 0 ? -1 : 1) * ($day_totals['1'] + ($remaining_days_qty['1'] > 0 && $pcIndex < $remaining_days_qty['1'] ? 1 : 0))),
                                'day2'=> abs(($day_totals['2'] < 0 ? -1 : 1) * ($day_totals['2'] + ($remaining_days_qty['2'] > 0 && $pcIndex < $remaining_days_qty['2'] ? 1 : 0))),
                                'day3'=> abs(($day_totals['3'] < 0 ? -1 : 1) * ($day_totals['3'] + ($remaining_days_qty['3'] > 0 && $pcIndex < $remaining_days_qty['3'] ? 1 : 0))),
                                'day4'=> abs(($day_totals['4'] < 0 ? -1 : 1) * ($day_totals['4'] + ($remaining_days_qty['4'] > 0 && $pcIndex < $remaining_days_qty['4'] ? 1 : 0))),
                                'day5'=> abs(($day_totals['5'] < 0 ? -1 : 1) * ($day_totals['5'] + ($remaining_days_qty['5'] > 0 && $pcIndex < $remaining_days_qty['5'] ? 1 : 0))),
                                'day6'=> abs(($day_totals['6'] < 0 ? -1 : 1) * ($day_totals['6'] + ($remaining_days_qty['6'] > 0 && $pcIndex < $remaining_days_qty['6'] ? 1 : 0))),
                                'day7'=> abs(($day_totals['7'] < 0 ? -1 : 1) * ($day_totals['7'] + ($remaining_days_qty['7'] > 0 && $pcIndex < $remaining_days_qty['7'] ? 1 : 0))),
                                'day8'=> abs(($day_totals['8'] < 0 ? -1 : 1) * ($day_totals['8'] + ($remaining_days_qty['8'] > 0 && $pcIndex < $remaining_days_qty['8'] ? 1 : 0))),
                                'day9'=> abs(($day_totals['9'] < 0 ? -1 : 1) * ($day_totals['9'] + ($remaining_days_qty['9'] > 0 && $pcIndex < $remaining_days_qty['9'] ? 1 : 0))),
                                'day10'=> abs(($day_totals['10'] < 0 ? -1 : 1) * ($day_totals['10'] + ($remaining_days_qty['10'] > 0 && $pcIndex < $remaining_days_qty['10'] ? 1 : 0))),
                                'day11'=> abs(($day_totals['11'] < 0 ? -1 : 1) * ($day_totals['11'] + ($remaining_days_qty['11'] > 0 && $pcIndex < $remaining_days_qty['11'] ? 1 : 0))),
                                'day12'=> abs(($day_totals['12'] < 0 ? -1 : 1) * ($day_totals['12'] + ($remaining_days_qty['12'] > 0 && $pcIndex < $remaining_days_qty['12'] ? 1 : 0))),
                                'day13'=> abs(($day_totals['13'] < 0 ? -1 : 1) * ($day_totals['13'] + ($remaining_days_qty['13'] > 0 && $pcIndex < $remaining_days_qty['13'] ? 1 : 0))),
                                'day14'=> abs(($day_totals['14'] < 0 ? -1 : 1) * ($day_totals['14'] + ($remaining_days_qty['14'] > 0 && $pcIndex < $remaining_days_qty['14'] ? 1 : 0))),
                                'day15'=> abs(($day_totals['15'] < 0 ? -1 : 1) * ($day_totals['15'] + ($remaining_days_qty['15'] > 0 && $pcIndex < $remaining_days_qty['15'] ? 1 : 0))),
                                'day16'=> abs(($day_totals['16'] < 0 ? -1 : 1) * ($day_totals['16'] + ($remaining_days_qty['16'] > 0 && $pcIndex < $remaining_days_qty['16'] ? 1 : 0))),
                                'day17'=> abs(($day_totals['17'] < 0 ? -1 : 1) * ($day_totals['17'] + ($remaining_days_qty['17'] > 0 && $pcIndex < $remaining_days_qty['17'] ? 1 : 0))),
                                'day18'=> abs(($day_totals['18'] < 0 ? -1 : 1) * ($day_totals['18'] + ($remaining_days_qty['18'] > 0 && $pcIndex < $remaining_days_qty['18'] ? 1 : 0))),
                                'day19'=> abs(($day_totals['19'] < 0 ? -1 : 1) * ($day_totals['19'] + ($remaining_days_qty['19'] > 0 && $pcIndex < $remaining_days_qty['19'] ? 1 : 0))),
                                'day20'=> abs(($day_totals['20'] < 0 ? -1 : 1) * ($day_totals['20'] + ($remaining_days_qty['20'] > 0 && $pcIndex < $remaining_days_qty['20'] ? 1 : 0))),
                                'day21'=> abs(($day_totals['21'] < 0 ? -1 : 1) * ($day_totals['21'] + ($remaining_days_qty['21'] > 0 && $pcIndex < $remaining_days_qty['21'] ? 1 : 0))),
                                'day22'=> abs(($day_totals['22'] < 0 ? -1 : 1) * ($day_totals['22'] + ($remaining_days_qty['22'] > 0 && $pcIndex < $remaining_days_qty['22'] ? 1 : 0))),
                                'day23'=> abs(($day_totals['23'] < 0 ? -1 : 1) * ($day_totals['23'] + ($remaining_days_qty['23'] > 0 && $pcIndex < $remaining_days_qty['23'] ? 1 : 0))),
                                'day24'=> abs(($day_totals['24'] < 0 ? -1 : 1) * ($day_totals['24'] + ($remaining_days_qty['24'] > 0 && $pcIndex < $remaining_days_qty['24'] ? 1 : 0))),
                                'day25'=> abs(($day_totals['25'] < 0 ? -1 : 1) * ($day_totals['25'] + ($remaining_days_qty['25'] > 0 && $pcIndex < $remaining_days_qty['25'] ? 1 : 0))),
                                'day26'=> abs(($day_totals['26'] < 0 ? -1 : 1) * ($day_totals['26'] + ($remaining_days_qty['26'] > 0 && $pcIndex < $remaining_days_qty['26'] ? 1 : 0))),
                                'day27'=> abs(($day_totals['27'] < 0 ? -1 : 1) * ($day_totals['27'] + ($remaining_days_qty['27'] > 0 && $pcIndex < $remaining_days_qty['27'] ? 1 : 0))),
                                'day28'=> abs(($day_totals['28'] < 0 ? -1 : 1) * ($day_totals['28'] + ($remaining_days_qty['28'] > 0 && $pcIndex < $remaining_days_qty['28'] ? 1 : 0))),
                                'day29'=> abs(($day_totals['29'] < 0 ? -1 : 1) * ($day_totals['29'] + ($remaining_days_qty['29'] > 0 && $pcIndex < $remaining_days_qty['29'] ? 1 : 0))),
                                'day30'=> abs(($day_totals['30'] < 0 ? -1 : 1) * ($day_totals['30'] + ($remaining_days_qty['30'] > 0 && $pcIndex < $remaining_days_qty['30'] ? 1 : 0))),
                                'day31'=> abs(($day_totals['31'] < 0 ? -1 : 1) * ($day_totals['31'] + ($remaining_days_qty['31'] > 0 && $pcIndex < $remaining_days_qty['31'] ? 1 : 0))),
                            ]
                        );
                        
                    }
                } else {
                    // กรณีไม่มี PC พบ ให้ข้ามขั้นตอนนี้ไป
                }

                
               
            }   

            
            $salesData = DB::table('tb_commission')
                ->select(
                    'store_id',
                    'id_pc',

                    DB::raw('SUM(CASE WHEN type_product = "TV" THEN sale_total ELSE 0 END) as sale_tv'),
                    DB::raw('SUM(CASE WHEN type_product = "TV" THEN sale_qty ELSE 0 END) as unit_tv'),
                    
                    DB::raw('SUM(CASE WHEN type_product = "AV" THEN sale_total ELSE 0 END) as sale_av'),
                    DB::raw('SUM(CASE WHEN type_product = "AV" THEN sale_qty ELSE 0 END) as unit_av'),

                    DB::raw('SUM(CASE WHEN type_product = "HA" THEN sale_total ELSE 0 END) as sale_ha'),
                    DB::raw('SUM(CASE WHEN type_product = "HA" THEN sale_qty ELSE 0 END) as unit_ha')
                )
                ->where('as_of_month', $var_month)
                ->where('as_of_year', $var_year)
                ->groupBy('store_id', 'id_pc')
                ->get();
            
            $commissionData = DB::table('tb_commission')
                ->select(
                    'store_id',
                    'id_pc',
                    DB::raw('SUM(CASE WHEN type_product = "TV" THEN sale_qty * com ELSE 0 END) as normalcom_tv'),
                    DB::raw('SUM(CASE WHEN type_product = "AV" THEN sale_qty * com ELSE 0 END) as normalcom_av'),
                    DB::raw('SUM(CASE WHEN type_product = "HA" THEN sale_qty * com ELSE 0 END) as normalcom_ha')
                )
                ->where('as_of_month', $var_month)
                ->where('as_of_year', $var_year)
                ->groupBy('store_id', 'id_pc')
                ->get();
            
            $combinedData = $salesData->map(function ($sale) use ($commissionData) {
                $commission = $commissionData->first(function ($item) use ($sale) {
                    return $item->store_id == $sale->store_id && $item->id_pc == $sale->id_pc;
                });
            
                return (object) [
                    'store_id' => $sale->store_id,
                    'id_pc' => $sale->id_pc,
                    'sale_tv' => $sale->sale_tv,
                    'unit_tv' => $sale->unit_tv,
                    'sale_av' => $sale->sale_av,
                    'unit_av' => $sale->unit_av,
                    'sale_ha' => $sale->sale_ha,
                    'unit_ha' => $sale->unit_ha,
                    'normalcom_tv' => $commission ? $commission->normalcom_tv : 0,
                    'normalcom_av' => $commission ? $commission->normalcom_av : 0,
                    'normalcom_ha' => $commission ? $commission->normalcom_ha : 0,
                    'extra_tv' => 0,
                    'extra_ha' => 0,
                ];
            });
            
            foreach ($combinedData as $data) {

                $pcs = tb_pc::whereNull('status_pc')
                    ->where('id', $data->id_pc)
                    ->get();
                $pc = $pcs->first();

                $data->achieve = $pc->tarket != 0 ? (($data->sale_tv + $data->sale_av) * 100) / $pc->tarket: 0;
            
                switch ($pc->type_pc) {
                    case 'PC':
                        if ($data->achieve >= 101) {
                            $achieve_percent = min($data->achieve, 120);
                            $data->com_tv = $data->normalcom_tv * ($achieve_percent / 100);
                            $data->com_av = $data->normalcom_av * ($achieve_percent / 100);
                            $data->com_ha = $data->normalcom_ha;
                        } elseif ($data->achieve >= 70) {
                            $data->com_tv = $data->normalcom_tv;
                            $data->com_av = $data->normalcom_av;
                            $data->com_ha = $data->normalcom_ha;
                        } elseif ($data->achieve >= 30) {
                            $data->com_tv = $data->normalcom_tv * ($data->achieve / 100);
                            $data->com_av = $data->normalcom_av * ($data->achieve / 100);
                            $data->com_ha = $data->normalcom_ha;
                        } else {
                            $data->com_tv = 0;
                            $data->com_av = 0;
                            // $data->com_ha = 0;
                            $data->com_ha = $data->normalcom_ha;
                        }
                        
                        switch ($pc->type_store) {
                            case 'A':
                                if ($data->achieve >= 150) {
                                    $data->extra_tv = 9000;
                                } elseif ($data->achieve >= 130) {
                                    $data->extra_tv = 8000;
                                } elseif ($data->achieve >= 120) {
                                    $data->extra_tv = 7000;
                                } elseif ($data->achieve >= 100) {
                                    $data->extra_tv = 6000;
                                } else {
                                    $data->extra_tv = 0;
                                }
                                break;
                            case 'B':
                                if ($data->achieve >= 150) {
                                    $data->extra_tv = 6000;
                                } elseif ($data->achieve >= 130) {
                                    $data->extra_tv = 5000;
                                } elseif ($data->achieve >= 120) {
                                    $data->extra_tv = 4000;
                                } elseif ($data->achieve >= 100) {
                                    $data->extra_tv = 3000;
                                } else {
                                    $data->extra_tv = 0;
                                }
                                break;
                            case 'C':
                                if ($data->achieve >= 120) {
                                    $data->extra_tv = 3000;
                                } elseif ($data->achieve >= 100) {
                                    $data->extra_tv = 2000;
                                } else {
                                    $data->extra_tv = 0;
                                }
                                break;
                            default:
                                $data->extra_tv = 0;
                                break;
                        }
                        
                        if ($data->sale_ha > 300000) {
                            $data->extra_ha = 4000;
                        } elseif ($data->sale_ha > 200000) {
                            $data->extra_ha = 3000;
                        } elseif ($data->sale_ha > 100000) {
                            $data->extra_ha = 2000;
                        } elseif ($data->sale_ha >= 70000) {
                            $data->extra_ha = 1000;
                        } else {
                            $data->extra_ha = 0;
                        }

                        //Extra PC
                        $sale_out = $data->sale_tv + $data->sale_av + $data->sale_ha;
                        if ($sale_out >= 500000) {
                            $data->extra_tv += 2000;
                        } elseif ($sale_out >= 600000) {
                            $data->extra_tv += 3000;
                        } else {
                            $data->extra_tv += 0;
                        }

                        //Extra store 350000
                        if ($sale_out >= 350000) {

                            $extra_pc_store = tb_pc::whereNull('status_pc')
                                ->where('store_id', $data->store_id)
                                ->where('type_pc', 'PC')
                                ->get();
                                
                            if ($extra_pc_store->count() == 1) {
                                if ($sale_out >= 1000000) {
                                    $data->extra_tv += 5000;
                                }else if ($sale_out >= 800000) {
                                    $data->extra_tv += 3000;
                                }
                            }else if ($extra_pc_store->count() > 1) {

                                $pc_sales = DB::table('tb_commission')
                                ->select('id_pc', DB::raw('SUM(sale_total) as sale_total'))
                                ->where('as_of_month', $var_month)
                                ->where('as_of_year', $var_year)
                                ->where('type_pc', 'PC')
                                ->where('store_id', $data->store_id)
                                ->groupBy('id_pc')
                                ->havingRaw('SUM(sale_total) >= 350000')
                                ->get();

                                if($extra_pc_store->count() == $pc_sales->count()){
                                    
                                    $extra_pc_sale_total = DB::table('tb_commission')
                                    ->select(DB::raw('SUM(sale_total) as total_sales'))
                                    ->where('as_of_month', $var_month)
                                    ->where('as_of_year', $var_year)
                                    ->where('type_pc', 'PC')
                                    ->where('store_id', $data->store_id)
                                    ->first();

                                    if ($extra_pc_sale_total->total_sales >= 1000000) {
                                        $data->extra_tv += 5000 / $extra_pc_store->count();
                                    }else if ($extra_pc_sale_total->total_sales >= 800000) {
                                        $data->extra_tv += 3000 / $extra_pc_store->count();
                                    }
                                }
                            }else{

                            }
                        }
                        break;
                    case 'PC_HA':
                        $data->com_tv = $data->normalcom_tv;
                        $data->com_av = $data->normalcom_av;
                        $data->com_ha = $data->normalcom_ha;

                        if ($data->sale_ha > 300000) {
                            $data->extra_ha = 4000;
                        } elseif ($data->sale_ha > 250000) {
                            $data->extra_ha = 3500;
                        } elseif ($data->sale_ha > 200000) {
                            $data->extra_ha = 2500;
                        } elseif ($data->sale_ha > 150000) {
                            $data->extra_ha = 1000;
                        }else {
                            $data->extra_ha = 0;
                        }

                        break;
                    case 'Freelance':
                        $data->com_tv = $data->sale_tv * 0.05;
                        $data->com_av = $data->sale_av * 0.05;
                        $data->com_ha = $data->sale_ha * 0.05;
                        $sale_out = $data->sale_tv + $data->sale_av + $data->sale_ha;
                        if ($sale_out > 299999) {
                            $data->extra_tv = 6000;
                        } elseif ($sale_out > 199999) {
                            $data->extra_tv = 5000;
                        } elseif ($sale_out > 149999) {
                            $data->extra_tv = 4000;
                        } elseif ($sale_out > 99999) {
                            $data->extra_tv = 2000;
                        } else {
                            $data->extra_tv = 0;
                        }
                        break;
                    case 'Freelance_plus':
                        // $data->com_tv = $data->sale_tv * 0.05;
                        // $data->com_av = $data->sale_av * 0.05;
                        // $data->com_ha = $data->sale_ha * 0.05;
                        $data->com_tv = $data->normalcom_tv;
                        $data->com_av = $data->normalcom_av;
                        $data->com_ha = $data->normalcom_ha;

                        $sale_out = $data->sale_tv + $data->sale_av + $data->sale_ha;
                        if ($sale_out > 250000) {
                            $data->extra_tv = 11000;
                        } elseif ($sale_out > 200000) {
                            $data->extra_tv = 9000;
                        } elseif ($sale_out > 150000) {
                            $data->extra_tv = 7000;
                        } elseif ($sale_out > 100000) {
                            $data->extra_tv = 4000;
                        } elseif ($sale_out >= 70000) {
                            $data->extra_tv = 2000;
                        } else {
                            $data->extra_tv = 0;
                        }
                        break;
                    case 'pc_promotion':
                        $data->com_tv = $data->normalcom_tv;
                        $data->com_av = $data->normalcom_av;
                        $data->com_ha = $data->normalcom_ha;
                        break;
                    default:
                        $data->com_tv = 0;
                        $data->com_av = 0;
                        $data->com_ha = 0;
                        $data->extra_tv = 0;
                        $data->extra_ha = 0;
                        break;
                }
            
                
            
                
            
                //คำนวณ net_com และ net_pay
                $data->pay_com = $data->com_tv + $data->com_av + $data->com_ha;
                $data->net_com = $data->pay_com + $data->extra_tv + $data->extra_ha;
                $data->net_pay = $data->net_com ;
                $sale_total = $data->sale_tv+$data->sale_av+$data->sale_ha;
            
                //คำนวณ dis_pay
                // $data->dis_pay = $data->pay_com / $data->net_com;
                if ($data->net_com != 0) {
                    $data->dis_pay = $data->net_com / $sale_total;
                } else {
                    // กำหนดค่า $data->dis_pay เป็นค่าอื่นที่คุณต้องการในกรณีที่ net_com เป็น 0
                    $data->dis_pay = 0; // หรืออาจจะเป็นค่าที่เหมาะสมกับ logic ของคุณ
                }
                
                DB::table('tb_main_commission')->updateOrInsert(
                    [
                        'store_id' => $data->store_id,
                        'as_of_month' => $var_month,
                        'as_of_year' => $var_year,
                        'id_pc' => $pc->id,
                    ],
                    [
                    'type_store' => $pc->type_store,
                    'name_pc' => $pc->name_pc,
                    'sale_tv' => $data->sale_tv,
                    'unit_tv' => $data->unit_tv,
                    'sale_av' => $data->sale_av,
                    'unit_av' => $data->unit_av,
                    'sale_ha' => $data->sale_ha,
                    'unit_ha' => $data->unit_ha,
                    'sale_total' => $sale_total,
                    'type_pc' => $pc->type_pc,
                    'pc_salary' => $pc->salary,
                    'tarket' => $pc->tarket,
                    'achieve' => $data->achieve,
                    'normalcom_tv' => $data->normalcom_tv,
                    'normalcom_av' => $data->normalcom_av,
                    'normalcom_ha' => $data->normalcom_ha,
                    'com_tv' => $data->com_tv,
                    'com_av' => $data->com_av,
                    'com_ha' => $data->com_ha,
                    'extra_tv' => $data->extra_tv,
                    'extra_ha' => $data->extra_ha,
                    'pay_com' => $data->pay_com,
                    'net_com' => $data->net_com,
                    'net_pay' => $data->net_pay,
                    'dis_pay' => $data->dis_pay
                ]);
            }


            #########################
             // Query all sales
            $sales = tb_sale::all();

            foreach ($sales as $sale) {
                $id_sale = $sale->id_sale;
                $subStores = tb_sub_sale::where('id_sale', $id_sale)->pluck('store_id');
                
                // Calculate total sales and units
                $total_sale_tv = main_commission::whereIn('store_id', $subStores)
                    ->where('as_of_month', $var_month)
                    ->where('as_of_year', $var_year)
                    ->sum('sale_tv');

                $total_sale_av = main_commission::whereIn('store_id', $subStores)
                    ->where('as_of_month', $var_month)
                    ->where('as_of_year', $var_year)
                    ->sum('sale_av');
                
                $total_sale_ha = main_commission::whereIn('store_id', $subStores)
                    ->where('as_of_month', $var_month)
                    ->where('as_of_year', $var_year)
                    ->sum('sale_ha');
                
                $totalSale = $total_sale_tv + $total_sale_av + $total_sale_ha;

                $total_unit_tv = main_commission::whereIn('store_id', $subStores)
                    ->where('as_of_month', $var_month)
                    ->where('as_of_year', $var_year)
                    ->sum('unit_tv');
                
                $total_unit_av = main_commission::whereIn('store_id', $subStores)
                    ->where('as_of_month', $var_month)
                    ->where('as_of_year', $var_year)
                    ->sum('unit_av');

                $total_unit_ha = main_commission::whereIn('store_id', $subStores)
                    ->where('as_of_month', $var_month)
                    ->where('as_of_year', $var_year)
                    ->sum('unit_ha');
                    
                    if ($sale->target > 0) {
                        $achieve = ($totalSale * 100) / $sale->target;
                    } else {
                        $achieve = 0; // หรือค่าที่เหมาะสมอื่น ๆ ถ้า target เป็น 0
                    }
                // Calculate achieve and commission
                // $achieve = ($totalSale *100) / $sale->target ;
                $comSale = ($sale->base_com * $achieve) / 100;

                // Calculate extra_sale_out based on achieve
                $extraSaleOut = 0;
                if ($achieve > 140) $extraSaleOut = 12000;
                elseif ($achieve > 120) $extraSaleOut = 11000;
                elseif ($achieve > 100) $extraSaleOut = 8000;
                elseif ($achieve > 80) $extraSaleOut = 7000;

                // Calculate extra_unit based on total units sold
                $extraUnit = 0;
                if ($total_unit_tv > 4999) $extraUnit = 6000;
                elseif ($total_unit_tv > 3999) $extraUnit = 5000;

                // Calculate avgSalePerPC for extra_avg
                $totalSale_pc = main_commission::whereIn('store_id', $subStores)
                    ->where('as_of_month', $var_month)
                    ->where('as_of_year', $var_year)
                    ->where('type_pc', 'PC')
                    ->sum('sale_total');

                $totalPCs = main_commission::whereIn('store_id', $subStores)
                    ->where('as_of_month', $var_month)
                    ->where('as_of_year', $var_year)
                    ->where('type_pc', 'pc')
                    ->count();

                    if ($totalSale_pc > 0 && $totalPCs >0) {
                        $avgSalePerPC = $totalSale_pc / $totalPCs;
                    }else{
                        $avgSalePerPC =0;
                    }

                $extraAvg = 0;
                if ($avgSalePerPC > 400000) $extraAvg = 8000;
                elseif ($avgSalePerPC > 350000) $extraAvg = 6000;
                elseif ($avgSalePerPC > 300000) $extraAvg = 5000;

                $sum_total_sale = $comSale + $extraSaleOut + $extraUnit + $extraAvg ;
                // Insert or update tb_commission_sale
                tb_commission_sale::updateOrInsert(
                    [
                        'id_sale' => $id_sale,
                        'as_of_month' => $var_month,
                        'as_of_year' => $var_year,
                    ],
                    [
                        'target' => $sale->target,
                        'achieve' => $achieve,
                        'base_com' => $sale->base_com,
                        'com_sale' => $comSale,
                        
                        'sale_tv' => $total_sale_tv,
                        'unit_tv' => $total_unit_tv,
                        'sale_av' => $total_sale_av,
                        'unit_av' => $total_unit_av,
                        'sale_ha' => $total_sale_ha,
                        'unit_ha' => $total_unit_ha,
                        'sale_out' => $totalSale,

                        'extra_sale_out' => $extraSaleOut,
                        'extra_unit' => $extraUnit,
                        'extra_avg' => $extraAvg,
                        'total' => $sum_total_sale,
                    ]
                );
            }

                
            ////////////////////////////////////////////////////////

        return redirect()->route('commissions.index')->with('success', 'Data imported successfully');
        // return redirect()->route('index', ['year' => $var_year, 'month' => $short_month, 'var_month' => $var_month]) ->with('success', 'Data imported successfully');
        }

    return redirect()->back()->withErrors(['error' => 'Invalid data format']);
    

    }

    public function importprice(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|mimes:xls,xlsx|max:10240',
        ]);

        $var_year = $request->input('var_year');
        $var_month = $request->input('var_month');
        $short_month = $request->input('short_month');

        if ($request->hasFile('excel_file')) {
            $file = $request->file('excel_file');
            $filename = time() . '-' . $file->getClientOriginalName();
            $filePath = $file->storeAs('uploads', $filename, 'public');

            // Load the spreadsheet from the uploaded file
            $spreadsheet = IOFactory::load($file->getPathname());
            
            foreach ($spreadsheet->getAllSheets() as $sheet) {
                $sheetData = $sheet->toArray(null, true, true, true);

                foreach ($sheetData as $key => $row) {
                    if ($key > 1 && $row['D'] != 'Type') { // Skip headers
                        $data = [
                            'barcode' => $row['B'],
                            'item_des' => $row['C'],
                            'type' => $row['D'],
                            'price_day1' => $row['E'],
                            'price_day2' => $row['F'],
                            'price_day3' => $row['G'],
                            'price_day4' => $row['H'],
                            'price_day5' => $row['I'],
                            'price_day6' => $row['J'],
                            'price_day7' => $row['K'],
                            'price_day8' => $row['L'],
                            'price_day9' => $row['M'],
                            'price_day10' => $row['N'],
                            'price_day11' => $row['O'],
                            'price_day12' => $row['P'],
                            'price_day13' => $row['Q'],
                            'price_day14' => $row['R'],
                            'price_day15' => $row['S'],
                            'price_day16' => $row['T'],
                            'price_day17' => $row['U'],
                            'price_day18' => $row['V'],
                            'price_day19' => $row['W'],
                            'price_day20' => $row['X'],
                            'price_day21' => $row['Y'],
                            'price_day22' => $row['Z'],
                            'price_day23' => $row['AA'],
                            'price_day24' => $row['AB'],
                            'price_day25' => $row['AC'],
                            'price_day26' => $row['AD'],
                            'price_day27' => $row['AE'],
                            'price_day28' => $row['AF'],
                            'price_day29' => $row['AG'],
                            'price_day30' => $row['AH'],
                            'price_day31' => $row['AI'],
                        ];

                        Price::updateOrCreate(
                            [
                                'item_number' => $row['A'],
                                'as_of_month' => $var_month,
                                'as_of_year' => $var_year
                            ], // Unique key to check
                            $data // Data to update/create
                        );
                    }
                }
            }

            return redirect()->route('commissions.index')->with('success', 'Data imported successfully');
        }

        return redirect()->back()->withErrors(['error' => 'Invalid data format']);
    }

}
