<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\DB;
use App\Models\Transaction;
use App\Models\product;
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
            'excel_file' => 'required|mimes:xls,xlsx|max:2048',
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
        foreach ($sheetData as $key => $row) {
            if ($key > 0) {
            if ($row['V'] != '0' && $row['V'] != "Sale Qty TY"){
                
            // แปลงข้อมูลจากฟอร์แมต 'M-y' เช่น 'Jun-24' ให้เป็นเดือนและปี
            // $as_of_month_year = \DateTime::createFromFormat('M-y', $row['F']);
            // $as_of_month = $as_of_month_year ? $as_of_month_year->format('m') : null;
            // $as_of_year = $as_of_month_year ? $as_of_month_year->format('Y') : null;

            $as_of_month_year = $row['F']; 
            $clean_date_f = substr($as_of_month_year, 2);
            // แปลงรูปแบบวันที่
            $date_object_f = \DateTime::createFromFormat('M-y', $clean_date_f);
            $as_of_month = $date_object_f ? $date_object_f->format('m') : null;
            $as_of_year = $date_object_f ? $date_object_f->format('Y') : null;

            $last_year_compare_month = $row['G']; 
            $clean_date_g = substr($last_year_compare_month, 2);
            // แปลงรูปแบบวันที่
            $date_object_g = \DateTime::createFromFormat('M-y', $clean_date_g);
            $formatted_last_year_compare_month = $date_object_g ? $date_object_g->format('Y-m') . '-01' : null;

            $report_date = $row['H']; 
            $clean_date_h = substr($report_date, 2);
            $date_object_h = \DateTime::createFromFormat('d-M-y', $clean_date_h);
            $formatted_report_date = $date_object_h ? $date_object_h->format('Y-m-d') : null;

            if ($as_of_month == $var_month && $as_of_year == $var_year) {
            $data[] = [
                'report_code' => $row['A'],
                'suppliercode' => substr($row['B'],0,7),
                'suppliername' => $row['B'],
                'business_format' => $row['C'],
                'compare' => $row['D'],
                'store_id' => substr($row['E'],0,5),
                'store' => $row['E'],
                'as_of_month' => $as_of_month,
                'as_of_year' => $as_of_year,
                'last_year_compare_month' => $formatted_last_year_compare_month,
                'report_date' => $formatted_report_date,
                'division' => $row['I'],
                'department' => $row['J'],
                'subdepartment' => $row['K'],
                'pro_Class' => $row['L'],
                'sub_pro_class' => $row['M'],
                'barcode' => $row['N'],
                'article' => $row['O'],
                'article_name' => $row['P'],
                'brand' => $row['Q'],
                'pro_model' => $row['R'],
                'sale_amt_ty' => $row['S'],
                'sale_amt_ly' => $row['T'],
                'sale_amt_var' => $row['U'],
                'sale_qty_ty' => $row['V'],
                'sale_qty_ly' => $row['W'],
                'sale_qty_var' => $row['X'],
                'stock_ty' => $row['Y'],
                'stock_ly' => $row['Z'],
                'stock_var' => $row['AA'],
                'stock_qty_ty' => $row['AB'],
                'stock_qty_ly' => $row['AC'],
                'stock_qty_var' => $row['AD'],
                'day_on_hand_ty' => $row['AE'],
                'day_on_hand_ly' => $row['AF'],
                'day_on_hand_diff' => $row['AG'],
            ];
            }
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
                
                $price_amt_vat = ($row['sale_amt_ty']*1.07);
                // $price = $row['sale_amt_ty']/$row['sale_qty_ty'];
                // $price_vat = ($row['sale_amt_ty']*1.07)/$row['sale_qty_ty'];
                $price = abs($row['sale_amt_ty']) / abs($row['sale_qty_ty']);
                $price_vat = abs(($row['sale_amt_ty'] * 1.07)) / abs($row['sale_qty_ty']);


                if($row['suppliercode'] == '4400215'){
                    $type_product = "AV";
                }else if($row['suppliercode'] == '7001389'){
                    $type_product = "HA";
                }else{
                    $type_product = "TV";
                }
                // if($row['pro_model'] ='AN-FR5250S' && $row['store_id'] =='11129'){
                // dd($data);
                // }
                Transaction::create([
                'report_code' => $row['report_code'],
                'suppliercode' => $row['suppliercode'],
                'suppliername' => $row['suppliername'],
                'business_format' => $row['business_format'],
                'compare' => $row['compare'],
                'store_id' => $row['store_id'],
                'store' => $row['store'],
                'as_of_month' => $row['as_of_month'],
                'as_of_year' => $row['as_of_year'],
                'last_year_compare_month' => $row['last_year_compare_month'],
                'report_date' => $row['report_date'],
                'division' => $row['division'],
                'department' => $row['department'],
                'subdepartment' => $row['subdepartment'],
                'pro_Class' => $row['pro_Class'],
                'sub_pro_class' => $row['sub_pro_class'],
                'barcode' => $row['barcode'],
                'article' => $row['article'],
                'article_name' => $row['article_name'],
                'brand' => $row['brand'],
                'pro_model' => $row['pro_model'],
                'type_product'=> $type_product,
                'sale_amt_ty' => $row['sale_amt_ty'],
                'sale_amt_ty_vat' => $price_amt_vat,
                'sale_price' => $price,
                'sale_price_vat' => $price_vat,
                'sale_amt_ly' => is_numeric($row['sale_amt_ly']) ? $row['sale_amt_ly'] : 0, // ตรวจสอบค่า หากไม่ใช่ตัวเลขให้ใช้ค่าเริ่มต้นเป็น 0
                'sale_amt_var' => $row['sale_amt_var'],
                'sale_qty_ty' => $row['sale_qty_ty'],
                'sale_qty_ly' => $row['sale_qty_ly'],
                'sale_qty_var' => $row['sale_qty_var'],
                'stock_ty' => $row['stock_ty'],
                'stock_ly' => $row['stock_ly'],
                'stock_var' => $row['stock_var'],
                'stock_qty_ty' => $row['stock_qty_ty'],
                'stock_qty_ly' => $row['stock_qty_ly'],
                'stock_qty_var' => $row['stock_qty_var'],
                'day_on_hand_ty' => $row['day_on_hand_ty'],
                'day_on_hand_ly' => $row['day_on_hand_ly'],
                'day_on_hand_diff' => $row['day_on_hand_diff'],
                ]);
                //อัปเดทข้อมูล Store
                
                $storeData = [
                    'suppliercode' => $row['suppliercode'],
                    'store' => $row['store'],
                    // 'type_store' =>'A'
                ];

                tb_store::updateOrCreate(
                    ['store_id' => $row['store_id']], // Unique key to check
                    $storeData
                );

                
                
                $productData = [
                    'suppliercode' => $row['suppliercode'],
                    'division' => $row['division'],
                    'department' => $row['department'],
                    'subdepartment' => $row['subdepartment'],
                    'pro_Class' => $row['pro_Class'],
                    'sub_pro_class' => $row['sub_pro_class'],
                    'barcode' => $row['barcode'],
                    'article' => $row['article'],
                    'article_name' => $row['article_name'],
                    'brand' => $row['brand'],
                    'pro_model' => $row['pro_model'],
                    'type_product' => $type_product,
                    'price' => $price,
                    'price_vat' => $price_vat,
                    // 'com' => 0
                ];

                product::updateOrCreate(
                    ['pro_model' => $row['pro_model']], // Unique key to check
                    $productData
                );


                // Get PC data
                $pcs = tb_pc::whereNull('status_pc')
                    ->where('store_id', $row['store_id'])
                    ->get();


                // Calculate and Insert into tb_commission
                if ($pcs->count() == 1) {
                    $pc = $pcs->first();
                    $com = $this->calculateCom($row['pro_model']); // Assume this is a method for calculating commission

                    // Commission::create([
                    //     'suppliercode' => $row['suppliercode'],
                    //     'store_id' => $row['store_id'],
                    //     'type_store' => $pc->type_store,
                    //     'as_of_month' => $row['as_of_month'],
                    //     'as_of_year' => $row['as_of_year'],
                    //     'pro_model' => $row['pro_model'],
                    //     'type_product' => $type_product,
                    //     'sale_amt' => $row['sale_amt_ty']/$row['sale_qty_ty'],
                    //     'sale_amt_vat' => ($row['sale_amt_ty'] * 1.07)/$row['sale_qty_ty'], // Example VAT calculation
                    //     'sale_total' => $row['sale_amt_ty'],
                    //     'sale_total_vat' => $row['sale_amt_ty'] * 1.07,
                    //     'sale_qty' => $row['sale_qty_ty'],
                    //     'com' => $com,
                    //     'id_pc' => $pc->id,
                    //     'type_pc' => $pc->type_pc
                    // ]);
                    Commission::updateOrInsert(
                        // เงื่อนไขในการเช็คว่ามีข้อมูลอยู่แล้วหรือไม่
                        [
                            'suppliercode' => $row['suppliercode'],
                            'store_id' => $row['store_id'],
                            'as_of_month' => $row['as_of_month'],
                            'as_of_year' => $row['as_of_year'],
                            'pro_model' => $row['pro_model'],
                            'id_pc' => $pc->id,
                        ],
                        // ถ้ามีข้อมูลอยู่แล้วให้ทำการอัปเดทข้อมูลนี้
                        [
                            'type_store' => $pc->type_store,
                            'type_product' => $type_product,
                            'sale_amt' => ($row['sale_amt_ty'] < 0 ? -1 : 1) * (abs($row['sale_amt_ty']) / abs($row['sale_qty_ty'])),
                            'sale_amt_vat' => ($row['sale_amt_ty'] < 0 ? -1 : 1) * ((abs($row['sale_amt_ty']) * 1.07) / abs($row['sale_qty_ty'])), // การคำนวณ VAT
                            'sale_qty' => $row['sale_qty_ty'],
                            'com' => $com,
                            'type_pc' => $pc->type_pc,
                        ]
                    );
                } else if ($pcs->count() > 1) {
                    $sale_qty_per_pc = (int)(abs($row['sale_qty_ty']) / $pcs->count());
                    $remaining_qty = abs($row['sale_qty_ty']) - (abs($sale_qty_per_pc) * $pcs->count());

                    foreach ($pcs as $pc) {
                        $com = $this->calculateCom($row['pro_model']); // Calculate commission

                        // Commission::create([
                        //     'suppliercode' => $row['suppliercode'],
                        //     'store_id' => $row['store_id'],
                        //     'type_store' => $pc->type_store,
                        //     'as_of_month' => $row['as_of_month'],
                        //     'as_of_year' => $row['as_of_year'],
                        //     'pro_model' => $row['pro_model'],
                        //     'type_product' => $type_product,
                        //     'sale_amt' => $row['sale_amt_ty'],
                        //     'sale_amt_vat' => $row['sale_amt_ty'] * 1.07,
                        //     'sale_total' => $sale_qty_per_pc * $row['sale_amt_ty'],
                        //     'sale_total_vat' => $sale_qty_per_pc * $row['sale_amt_ty'] * 1.07,
                        //     'sale_qty' => $sale_qty_per_pc + ($remaining_qty > 0 ? 1 : 0),
                        //     'com' => $com,
                        //     'id_pc' => $pc->id,
                        //     'type_pc' => $pc->type_pc
                        // ]);

                        Commission::updateOrInsert(
                            [
                                'suppliercode' => $row['suppliercode'],
                                'store_id' => $row['store_id'],
                                'as_of_month' => $row['as_of_month'],
                                'as_of_year' => $row['as_of_year'],
                                'pro_model' => $row['pro_model'],
                                'id_pc' => $pc->id,
                            ],
                            [
                                'type_store' => $pc->type_store,
                                'type_product' => $type_product,
                                'sale_amt' => ($row['sale_amt_ty'] < 0 ? -1 : 1) * (abs($row['sale_amt_ty']) / abs($row['sale_qty_ty'])),
                                'sale_amt_vat' => ($row['sale_amt_ty'] < 0 ? -1 : 1) * ((abs($row['sale_amt_ty']) * 1.07) / abs($row['sale_qty_ty'])), 
                                // 'sale_qty' => $sale_qty_per_pc + ($remaining_qty > 0 ? 1 : 0),
                                'sale_qty' => ($row['sale_qty_ty'] < 0 ? -1 : 1) * ($sale_qty_per_pc + ($remaining_qty > 0 ? 1 : 0)),

                                'com' => $com,
                                'type_pc' => $pc->type_pc,
                            ]
                        );
                        $remaining_qty--;
                    }
                } else {
                    // กรณีไม่มี PC พบ ให้ข้ามขั้นตอนนี้ไป
                }

                
               
            }   


                ///////////Funtion cal commissiom/////////////
            
            // $as_of_month = $var_month; 
            // $as_of_year = $var_year; 

            // Query ข้อมูล sale_tv, unit_tv, sale_av, unit_av, sale_ha, unit_ha
            $salesData = DB::table('tb_commission')
                ->select(
                    'store_id',
                    'id_pc',
                    // DB::raw('SUM(CASE WHEN type_product = "TV" THEN sale_amt_vat * sale_qty ELSE 0 END) as sale_tv'),
                    DB::raw('SUM(CASE WHEN type_product = "TV" AND sale_qty > 0  THEN sale_amt_vat * sale_qty
                                WHEN type_product = "TV" AND sale_qty < 0 THEN -1 * ABS(sale_amt_vat) * ABS(sale_qty)
                                ELSE 0 END) as sale_tv'),
                    DB::raw('SUM(CASE WHEN type_product = "TV" THEN sale_qty ELSE 0 END) as unit_tv'),
                    // DB::raw('SUM(CASE WHEN type_product = "AV" THEN sale_amt_vat * sale_qty ELSE 0 END) as sale_av'),
                    DB::raw('SUM(CASE WHEN type_product = "AV" AND sale_qty > 0  THEN sale_amt_vat * sale_qty
                                WHEN type_product = "AV" AND sale_qty < 0 THEN -1 * ABS(sale_amt_vat) * ABS(sale_qty)
                                ELSE 0 END) as sale_av'),
                    DB::raw('SUM(CASE WHEN type_product = "AV" THEN sale_qty ELSE 0 END) as unit_av'),
                    // DB::raw('SUM(CASE WHEN type_product = "HA" THEN sale_amt_vat * sale_qty ELSE 0 END) as sale_ha'),
                    DB::raw('SUM(CASE WHEN type_product = "HA" AND sale_qty > 0  THEN sale_amt_vat * sale_qty
                                WHEN type_product = "HA" AND sale_qty < 0 THEN -1 * ABS(sale_amt_vat) * ABS(sale_qty)
                                ELSE 0 END) as sale_ha'),
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

                $data->achieve = (($data->sale_tv + $data->sale_av) * 100) / $pc->tarket;
            
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
                                    $data->extra_tv = 2000;
                                } elseif ($data->achieve >= 100) {
                                    $data->extra_tv = 1000;
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
                        break;
                    case 'PC_HA':
                        $data->com_tv = $data->normalcom_tv;
                        $data->com_av = $data->normalcom_av;
                        $data->com_ha = $data->normalcom_ha;

                        if ($data->sale_ha > 350000) {
                            $data->extra_ha = 5500;
                        } elseif ($data->sale_ha > 320000) {
                            $data->extra_ha = 5000;
                        } elseif ($data->sale_ha > 300000) {
                            $data->extra_ha = 4500;
                        } elseif ($data->sale_ha > 280000) {
                            $data->extra_ha = 4000;
                        }elseif ($data->sale_ha > 250000) {
                            $data->extra_ha = 3500;
                        }elseif ($data->sale_ha > 230000) {
                            $data->extra_ha = 3000;
                        }elseif ($data->sale_ha > 200000) {
                            $data->extra_ha = 2500;
                        }elseif ($data->sale_ha > 180000) {
                            $data->extra_ha = 2000;
                        }elseif ($data->sale_ha >= 150000) {
                            $data->extra_ha = 1000;
                        } else {
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
                        $data->com_tv = $data->sale_tv * 0.05;
                        $data->com_av = $data->sale_av * 0.05;
                        $data->com_ha = $data->sale_ha * 0.05;
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

    protected function calculateCom($pro_model)
    {
        // Example: Retrieve the commission from tb_product based on pro_model
        $product = product::where('pro_model', $pro_model)->first();
        return $product ? $product->com : 0;
    }

}
