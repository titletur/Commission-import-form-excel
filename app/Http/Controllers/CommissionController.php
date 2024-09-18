<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\main_commission;
use App\Models\Commission;
use App\Models\tb_commission_sale;
use App\Models\tb_status;
use App\Models\tb_pc;
use App\Models\tb_month;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Mpdf\Mpdf;
use Barryvdh\DomPDF\Facade\Pdf;

class CommissionController extends Controller
{
    public function index(Request $request)
    {
        // Get the selected year, default to current year if not selected
        $year = $request->input('year', date('Y'));
        $years = range(date('Y'), date('Y') - 3);
        // Fetch data for each month
        $months = tb_month::all();
        $monthlyData = [];
            foreach ($months as $month) {
                $status = tb_status::where('as_of_month', $month->var_month)->where('as_of_year', $year)->first();

            $monthlyData[] = [
                'month' => $month->short_en,
                'var_month' => $month->var_month,
                'var_year' => $year,
                'sale_in' => main_commission::where('as_of_month', $month->var_month)->where('as_of_year', $year)->sum('sale_total'),
                'pay_com' => main_commission::where('as_of_month', $month->var_month)->where('as_of_year', $year)->sum('net_pay'),
                'target_link' => route('editTarget', [ 'year' => $year, 'month' => $month->short_en , 'var_month' => $month->var_month]), 
                'import_link' => route('import', ['year' => $year, 'month' => $month->short_en , 'var_month' => $month->var_month]),
                'show_link' => route('commissions.show', ['year' => $year, 'month' => $month->short_en , 'var_month' => $month->var_month]),

                'status' => $status ? $status->status_com : 0,
                'disabled' => $status && $status->status_com == 1
            ];
        }
        return view('index', compact('monthlyData', 'year', 'years', 'months'));
    }

    public function show($year, $month , $var_month)
    {
        // Query ข้อมูลจาก tb_main_commission
        $commissions = DB::table('tb_main_commission')
                        ->where('as_of_year', $year)
                        ->where('as_of_month', $var_month)
                        ->get();
        
        $commissions_sale = DB::table('tb_commission_sale')
                        ->join('tb_sale', 'tb_commission_sale.id_sale', '=', 'tb_sale.id_sale')
                        ->where('tb_commission_sale.as_of_year', $year)
                        ->where('tb_commission_sale.as_of_month', $var_month)
                        ->select('tb_commission_sale.*', 'tb_sale.name_sale', 'tb_sale.code_sale') // รวมข้อมูลจาก tb_commission_sale และ tb_sale
                        ->get();
        $totals = [
            'num_count' => $commissions->count('name_pc'),
            'pc_salary' => $commissions->sum('pc_salary'),
            'sale_tv' => $commissions->sum('sale_tv'),
            'unit_tv' => $commissions->sum('unit_tv'),
            'sale_av' => $commissions->sum('sale_av'),
            'unit_av' => $commissions->sum('unit_av'),
            'sale_ha' => $commissions->sum('sale_ha'),
            'unit_ha' => $commissions->sum('unit_ha'),
            'sale_total' => $commissions->sum('sale_total'),
            'a' => $commissions->sum('unit_tv'),
            'tarket' => $commissions->sum('tarket'),
            'achieve' => $commissions->sum('achieve'),
            'com_tv' => $commissions->sum('com_tv'),
            'com_av' => $commissions->sum('com_av'),
            'com_ha' => $commissions->sum('com_ha'),
            'pay_com' => $commissions->sum('pay_com'),
            'extra_tv' => $commissions->sum('extra_tv'),
            'extra_ha' => $commissions->sum('extra_ha'),
            'net_com' => $commissions->sum('net_com'),
            'advance_pay' => $commissions->sum('advance_pay'),
            'net_pay' => $commissions->sum('net_pay'),
        ];

        $totals_sale = [
            'num_count' => $commissions_sale->count('name_sale'),
            'target' => $commissions_sale->sum('target'),
            'sale_out' => $commissions_sale->sum('sale_out'),
            'sale_tv' => $commissions_sale->sum('sale_tv'),
            'sale_av' => $commissions_sale->sum('sale_av'),
            'sale_ha' => $commissions_sale->sum('sale_ha'),
            'unit_tv' => $commissions_sale->sum('unit_tv'),
            'unit_av' => $commissions_sale->sum('unit_av'),
            'unit_ha' => $commissions_sale->sum('unit_ha'),
            'achieve' => $commissions_sale->sum('achieve'),
            'base_com' => $commissions_sale->sum('base_com'),
            'com_sale' => $commissions_sale->sum('com_sale'),
            'extra_sale_out' => $commissions_sale->sum('extra_sale_out'),
            'extra_unit' => $commissions_sale->sum('extra_unit'),
            'extra_avg' => $commissions_sale->sum('extra_avg'),
            'other' => $commissions_sale->sum('other'),
            'total' => $commissions_sale->sum('total'),
        ];

        // ส่งข้อมูลไปยัง view เพื่อแสดงผล
        return view('show', compact('commissions', 'commissions_sale', 'totals' , 'totals_sale' , 'year', 'month','var_month'));
    }
    public function updateOrCreate(Request $request)
    {
        $validated = $request->validate([
            'as_of_month' => 'required|string',
            'as_of_year' => 'required|string',
        ]);

        // Update or Create status
        tb_status::updateOrCreate(
            [
                'as_of_month' => $validated['as_of_month'],
                'as_of_year' => $validated['as_of_year'],
            ],
            [
                'status_com' => 1 // Set status to Completed
            ]
        );

        return redirect()->back()->with('success', 'Status updated successfully!');
    }
    public function export(Request $request)
    {
        $month = $request->input('month');
        $show_month = $request->input('show_month');
        $year = $request->input('year');
        $type = $request->input('type');

        // ดึงข้อมูล commissions ตามเดือนและปี
        $commissions = DB::table('tb_main_commission')
                        ->where('as_of_year', $year)
                        ->where('as_of_month', $month)
                        ->get();

        $commissions_sale = DB::table('tb_commission_sale')
                        ->join('tb_sale', 'tb_commission_sale.id_sale', '=', 'tb_sale.id_sale')
                        ->where('tb_commission_sale.as_of_year', $year)
                        ->where('tb_commission_sale.as_of_month', $month)
                        ->select('tb_commission_sale.*', 'tb_sale.name_sale', 'tb_sale.code_sale') // รวมข้อมูลจาก tb_commission_sale และ tb_sale
                        ->get();
       

        if ($type === 'excel') {
                // // สร้าง Spreadsheet object
                // $spreadsheet = new Spreadsheet();
                // $sheet = $spreadsheet->getActiveSheet();
        
                // // เพิ่มข้อมูลลงในเซลล์
                // $sheet->setCellValue('A1', 'Store');
                // $sheet->setCellValue('B1', 'Type Store');
                // $sheet->setCellValue('C1', 'PC');
                // $sheet->setCellValue('D1', 'Type PC');
                // $sheet->setCellValue('E1', 'Salary');
                // $sheet->setCellValue('F1', 'Sale TV');
                // $sheet->setCellValue('G1', 'QTY TV');
                // $sheet->setCellValue('H1', 'Sale AV');
                // $sheet->setCellValue('I1', 'QTY AV');
                // $sheet->setCellValue('J1', 'Sale HA');
                // $sheet->setCellValue('K1', 'QTY HA');
                // $sheet->setCellValue('L1', 'Sale Total');
                // $sheet->setCellValue('M1', 'Target');
                // $sheet->setCellValue('N1', 'Achieve');
                // $sheet->setCellValue('O1', 'Com TV');
                // $sheet->setCellValue('P1', 'Com AV');
                // $sheet->setCellValue('Q1', 'Com HA');
                // $sheet->setCellValue('R1', 'Pay Com');
                // $sheet->setCellValue('S1', 'Extra');
                // $sheet->setCellValue('T1', 'Extra  HA');
                // $sheet->setCellValue('U1', 'Net Com');
                // $sheet->setCellValue('V1', 'Advance Pay');
                // $sheet->setCellValue('W1', 'Net Pay');
                // // ... เพิ่มข้อมูลหัวข้อที่เหลือ
                // // เพิ่มข้อมูล commission ลงใน sheet
                // $row = 2;
                // foreach ($commissions as $commission) {
                //     $sheet->setCellValue('A' . $row, $commission->store_id);
                //     $sheet->setCellValue('B' . $row, $commission->type_store);
                //     $sheet->setCellValue('C' . $row, $commission->name_pc);
                //     $sheet->setCellValue('D' . $row, $commission->type_pc);
                //     $sheet->setCellValue('E' . $row, number_format($commission->pc_salary,0) );
                //     $sheet->setCellValue('F' . $row, number_format($commission->sale_tv,0) );
                //     $sheet->setCellValue('G' . $row, number_format($commission->unit_tv,0));
                //     $sheet->setCellValue('H' . $row, number_format($commission->sale_av,0));
                //     $sheet->setCellValue('I' . $row, number_format($commission->unit_av,0));
                //     $sheet->setCellValue('J' . $row, number_format($commission->sale_ha,0));
                //     $sheet->setCellValue('K' . $row, number_format($commission->unit_ha,0));
                //     $sheet->setCellValue('L' . $row, number_format($commission->sale_total,0));
                //     $sheet->setCellValue('M' . $row, number_format($commission->tarket,0) );
                //     $sheet->setCellValue('N' . $row, $commission->achieve .'%');
                //     $sheet->setCellValue('O' . $row, number_format($commission->com_tv,0));
                //     $sheet->setCellValue('P' . $row, number_format($commission->com_av,0));
                //     $sheet->setCellValue('Q' . $row, number_format($commission->com_ha,0));
                //     $sheet->setCellValue('R' . $row, number_format($commission->pay_com,0));
                //     $sheet->setCellValue('S' . $row, number_format($commission->extra_tv,0));
                //     $sheet->setCellValue('T' . $row, number_format($commission->extra_ha,0));
                //     $sheet->setCellValue('U' . $row, number_format($commission->net_com,0));
                //     $sheet->setCellValue('V' . $row, number_format($commission->advance_pay,0));
                //     $sheet->setCellValue('W' . $row, number_format($commission->net_pay,0));

                //     // ... เพิ่มข้อมูลที่เหลือ
                //     $row++;

                // }
        
                // // สร้าง Writer object เพื่อเขียนไฟล์
                // $writer = new Xlsx($spreadsheet);
        
                // // กำหนดชื่อไฟล์
                // $fileName = 'commissions_' . $month . '_' . $year . '.xlsx';
        
                // // ส่งออกไฟล์เป็น Response
                // header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                // header('Content-Disposition: attachment; filename="' . $fileName . '"');
        
                // $writer->save('php://output');
                // exit;
            // สร้าง Spreadsheet และ Sheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Commissions PC');

        // กำหนดหัวข้อของตาราง
        $header = [
            'Store', 'Type Store', 'PC', 'Type PC', 'Salary', 'Sale TV', 'QTY TV', 'Sale AV', 'QTY AV', 
            'Sale HA', 'QTY HA', 'Sale Total', 'Target', 'Achieve', 'Com TV', 'Com AV', 'Com HA', 
            'Pay Com', 'Extra', 'Extra HA', 'Net Com', 'Advance', 'Net Pay'
        ];

        // ตั้งค่า header
        $sheet->fromArray($header, NULL, 'A1');

        // กรอกข้อมูล commissions
        $row = 2; // เริ่มที่แถวที่ 2 เนื่องจากแถวที่ 1 เป็นหัวข้อ
        foreach ($commissions as $commission) {
            $sheet->setCellValue('A' . $row, $commission->store_id);
            $sheet->setCellValue('B' . $row, $commission->type_store);
            $sheet->setCellValue('C' . $row, $commission->name_pc);
            $sheet->setCellValue('D' . $row, $commission->type_pc);
            $sheet->setCellValue('E' . $row, $commission->pc_salary);
            $sheet->setCellValue('F' . $row, $commission->sale_tv);
            $sheet->setCellValue('G' . $row, $commission->unit_tv);
            $sheet->setCellValue('H' . $row, $commission->sale_av);
            $sheet->setCellValue('I' . $row, $commission->unit_av);
            $sheet->setCellValue('J' . $row, $commission->sale_ha);
            $sheet->setCellValue('K' . $row, $commission->unit_ha);
            $sheet->setCellValue('L' . $row, $commission->sale_total);
            $sheet->setCellValue('M' . $row, $commission->tarket);
            $sheet->setCellValue('N' . $row, $commission->achieve );
            $sheet->setCellValue('O' . $row, $commission->com_tv);
            $sheet->setCellValue('P' . $row, $commission->com_av);
            $sheet->setCellValue('Q' . $row, $commission->com_ha);
            $sheet->setCellValue('R' . $row, $commission->pay_com);
            $sheet->setCellValue('S' . $row, $commission->extra_tv);
            $sheet->setCellValue('T' . $row, $commission->extra_ha);
            $sheet->setCellValue('U' . $row, $commission->net_com);
            $sheet->setCellValue('V' . $row, $commission->advance_pay);
            $sheet->setCellValue('W' . $row, $commission->net_pay);
            $row++;
        }

        // เพิ่มแถวผลรวม (Sum Total)
        $sheet->setCellValue('A' . $row, 'Total');
        $sheet->mergeCells("A$row:D$row"); // รวมเซลล์ A ถึง D
        $sheet->setCellValue('E' . $row, '=SUM(E2:E' . ($row - 1) . ')');
        $sheet->setCellValue('F' . $row, '=SUM(F2:F' . ($row - 1) . ')');
        $sheet->setCellValue('G' . $row, '=SUM(G2:G' . ($row - 1) . ')');
        $sheet->setCellValue('H' . $row, '=SUM(H2:H' . ($row - 1) . ')');
        $sheet->setCellValue('I' . $row, '=SUM(I2:I' . ($row - 1) . ')');
        $sheet->setCellValue('J' . $row, '=SUM(J2:J' . ($row - 1) . ')');
        $sheet->setCellValue('K' . $row, '=SUM(K2:K' . ($row - 1) . ')');
        $sheet->setCellValue('L' . $row, '=SUM(L2:L' . ($row - 1) . ')');
        $sheet->setCellValue('M' . $row, '=SUM(M2:M' . ($row - 1) . ')');
        $sheet->setCellValue('N' . $row, '=AVERAGE(N2:N' . ($row - 1) . ')');
        $sheet->setCellValue('O' . $row, '=SUM(O2:O' . ($row - 1) . ')');
        $sheet->setCellValue('P' . $row, '=SUM(P2:P' . ($row - 1) . ')');
        $sheet->setCellValue('Q' . $row, '=SUM(Q2:Q' . ($row - 1) . ')');
        $sheet->setCellValue('R' . $row, '=SUM(R2:R' . ($row - 1) . ')');
        $sheet->setCellValue('S' . $row, '=SUM(S2:S' . ($row - 1) . ')');
        $sheet->setCellValue('T' . $row, '=SUM(T2:T' . ($row - 1) . ')');
        $sheet->setCellValue('U' . $row, '=SUM(U2:U' . ($row - 1) . ')');
        $sheet->setCellValue('V' . $row, '=SUM(V2:V' . ($row - 1) . ')');
        $sheet->setCellValue('W' . $row, '=SUM(W2:W' . ($row - 1) . ')');

        // เพิ่ม sheet ใหม่สำหรับ Commissions Sale
        $sheetSale = $spreadsheet->createSheet();
        $sheetSale->setTitle('Commissions Sale');
        
        // ตั้งค่า header สำหรับ Commissions Sale
        $headerSale = [
            'ID', 'Code', 'Name', 'Target', 'Sale Out', 'Sale TV', 'Sale AV', 'Sale HA', 
            'Unit TV', 'Unit AV', 'Unit HA', 'Achieve', 'Base Com', 'Com', 'Extra Sale Out', 
            'Extra Unit', 'Extra AVG', 'Other', 'Total', 'Remark'
        ];
        $sheetSale->fromArray($headerSale, NULL, 'A1');
        
        // กรอกข้อมูล commissions_sale
        $rowSale = 2; // เริ่มที่แถวที่ 2 เนื่องจากแถวที่ 1 เป็นหัวข้อ
        foreach ($commissions_sale as $commissionSale) {
            $sheetSale->setCellValue('A' . $rowSale, $commissionSale->id);
            $sheetSale->setCellValue('B' . $rowSale, $commissionSale->code_sale);
            $sheetSale->setCellValue('C' . $rowSale, $commissionSale->name_sale);
            $sheetSale->setCellValue('D' . $rowSale, $commissionSale->target);
            $sheetSale->setCellValue('E' . $rowSale, $commissionSale->sale_out);
            $sheetSale->setCellValue('F' . $rowSale, $commissionSale->sale_tv);
            $sheetSale->setCellValue('G' . $rowSale, $commissionSale->sale_av);
            $sheetSale->setCellValue('H' . $rowSale, $commissionSale->sale_ha);
            $sheetSale->setCellValue('I' . $rowSale, $commissionSale->unit_tv);
            $sheetSale->setCellValue('J' . $rowSale, $commissionSale->unit_av);
            $sheetSale->setCellValue('K' . $rowSale, $commissionSale->unit_ha);
            $sheetSale->setCellValue('L' . $rowSale, $commissionSale->achieve);
            $sheetSale->setCellValue('M' . $rowSale, $commissionSale->base_com);
            $sheetSale->setCellValue('N' . $rowSale, $commissionSale->com_sale);
            $sheetSale->setCellValue('O' . $rowSale, $commissionSale->extra_sale_out);
            $sheetSale->setCellValue('P' . $rowSale, $commissionSale->extra_unit);
            $sheetSale->setCellValue('Q' . $rowSale, $commissionSale->extra_avg);
            $sheetSale->setCellValue('R' . $rowSale, $commissionSale->other);
            $sheetSale->setCellValue('S' . $rowSale, $commissionSale->total);
            $sheetSale->setCellValue('T' . $rowSale, $commissionSale->remark);
            $rowSale++;
        }

        // เพิ่มแถวผลรวม (Sum Total) สำหรับ Commissions Sale
        $sheetSale->setCellValue('A' . $rowSale, 'Sum Total');
        $sheetSale->mergeCells("A$rowSale:C$rowSale"); // รวมเซลล์ A ถึง C
        $sheetSale->setCellValue('D' . $rowSale, '=SUM(D2:D' . ($rowSale - 1) . ')');
        $sheetSale->setCellValue('E' . $rowSale, '=SUM(E2:E' . ($rowSale - 1) . ')');
        $sheetSale->setCellValue('F' . $rowSale, '=SUM(F2:F' . ($rowSale - 1) . ')');
        $sheetSale->setCellValue('G' . $rowSale, '=SUM(G2:G' . ($rowSale - 1) . ')');
        $sheetSale->setCellValue('H' . $rowSale, '=SUM(H2:H' . ($rowSale - 1) . ')');
        $sheetSale->setCellValue('I' . $rowSale, '=SUM(I2:I' . ($rowSale - 1) . ')');
        $sheetSale->setCellValue('J' . $rowSale, '=SUM(J2:J' . ($rowSale - 1) . ')');
        $sheetSale->setCellValue('K' . $rowSale, '=SUM(K2:K' . ($rowSale - 1) . ')');
        $sheetSale->setCellValue('L' . $rowSale, '=AVERAGE(L2:L' . ($rowSale - 1) . ')');
        $sheetSale->setCellValue('M' . $rowSale, '=SUM(M2:M' . ($rowSale - 1) . ')');
        $sheetSale->setCellValue('N' . $rowSale, '=SUM(N2:N' . ($rowSale - 1) . ')');
        $sheetSale->setCellValue('O' . $rowSale, '=SUM(O2:O' . ($rowSale - 1) . ')');
        $sheetSale->setCellValue('P' . $rowSale, '=SUM(P2:P' . ($rowSale - 1) . ')');
        $sheetSale->setCellValue('Q' . $rowSale, '=SUM(Q2:Q' . ($rowSale - 1) . ')');
        $sheetSale->setCellValue('R' . $rowSale, '=SUM(R2:R' . ($rowSale - 1) . ')');
        $sheetSale->setCellValue('S' . $rowSale, '=SUM(S2:S' . ($rowSale - 1) . ')');


        // ... เพิ่มการคำนวณ Sum Total สำหรับคอลัมน์อื่น ๆ ตามที่ต้องการ

        // จัดการ download ไฟล์
        $filename = "Commissions_{$month}_{$year}.xlsx";
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        /////////////////////////////////////////////////////////////////////////////////////////////////
        } elseif ($type === 'pdf') {
            // $pdf = PDF::loadView('commissions.commission_pdf', compact('commissions', 'month', 'year'))
            // ->setPaper('a4', 'landscape')
            // ->setOptions([
            //     'isHtml5ParserEnabled' => true,
            //     'isPhpEnabled' => true,
            //     'fontDir' => public_path('fonts/cssfont'),
            //     'fontCache' => public_path('fonts/cssfont'),
            //     'defaultFont' => 'Sarabun', // ใช้ฟอนต์ที่คุณมี
            // ]);
            
            // return $pdf->stream('commissions_' . $month . '_' . $year . '.pdf');
            $mpdf = new Mpdf();
            
            $mpdf->AddPage('L'); // แนวนอน
            $mpdf->SetFont('freeserif', '', 12); // ตั้งฟอนต์ที่รองรับภาษาไทย

            $html = view('commissions.commission_pdf', compact('commissions', 'month', 'year','show_month'))->render();
            $mpdf->WriteHTML($html);
            $mpdf->Output('commissions_' . $month . '_' . $year . '.pdf', 'I');
            
        }

        return redirect()->back();
    }

    // public function edit(Request $request)
    // {
    //     $store_id = $request->input('store_id');
    //     $id_pc = $request->input('id_pc');
    //     $month = $request->input('month');
    //     $var_month = $request->input('var_month');
    //     $year = $request->input('year');

    //     // ดึงข้อมูลจาก tb_commission
    //     $commissions = Commission::where('store_id', $store_id)
    //                             ->where('id_pc', $id_pc)
    //                             ->where('as_of_month', $var_month)
    //                             ->where('as_of_year', $year)
    //                             ->get();

    //     $main_commission = main_commission::where('store_id', $store_id)
    //                             ->where('id_pc', $id_pc)
    //                             ->where('as_of_month', $var_month)
    //                             ->where('as_of_year', $year)
    //                             ->first();

    //     // ดึงข้อมูล PC จาก tb_pc
    //     $pc_info = DB::table('tb_pc')->whereNull('status_pc')
    //             ->where('id', $id_pc)
    //             ->first();

    //     // ส่งข้อมูลไปยัง view
    //     return view('commissions.edit_data_pc', compact('commissions','main_commission', 'store_id', 'id_pc', 'month', 'var_month', 'year', 'pc_info'));
    // }
    // Controller (Updated)
    public function edit(Request $request)
    {
        $store_id = $request->input('store_id');
        $var_month = $request->input('var_month');
        $year = $request->input('year');
        $month = $request->input('month');


        // Group by Product Model and sum Sale Quantity and Amounts
        $commissions = Commission::select(
                                'suppliercode',
                                'store_id',

                                'pro_model',
                                'type_product',
                                'sale_amt',
                                'sale_amt_vat',
                                DB::raw('SUM(sale_qty) as total_sale_qty')
                            )
                            ->where('store_id', $store_id)
                            ->where('as_of_month', $var_month)
                            ->where('as_of_year', $year)
                            ->groupBy('pro_model', 'suppliercode', 'store_id', 'type_product','sale_amt','sale_amt_vat')
                            ->get();

        // Query PC information in the store
        $pcs = DB::table('tb_pc')->where('store_id', $store_id)
                                ->whereNull('status_pc')
                                ->get();

        $main_commission = DB::table('tb_main_commission')
                                ->where('store_id', $store_id)
                                ->where('as_of_month', $var_month)
                                ->where('as_of_year', $year)
                                ->get();                        

        // ส่งข้อมูลไปยัง view
        return view('commissions.edit_data_pc', compact('commissions', 'pcs', 'store_id', 'var_month','month', 'year', 'main_commission'));
    }


    public function updateCommission_sale(Request $request)
    {

        $month = $request->input('month');
        $var_month = $request->input('var_month');
        $year = $request->input('year');
        $other = str_replace(',', '',$request->input('other'));
        $remark = $request->input('remark');
        try {
            foreach ($other as $id => $value) {

                $commissionSale = DB::table('tb_commission_sale')
                    ->where('id', $id)
                    ->first();

                if ($commissionSale) {
                    // คำนวณค่า total ใหม่
                    $newTotal = $commissionSale->total + str_replace(',', '', $value);

                DB::table('tb_commission_sale')
                    ->where('id', $id)
                    ->update([
                        'other' => str_replace(',', '', $value), 
                        'total' => $newTotal,
                        'remark' => $remark[$id] 
                    ]);
                }
            }

        return redirect()->route('commissions.show', ['year' => $year, 'month' => $month , 'var_month' => $var_month])
        ->with('success', 'Data Update successfully');
        } catch (\Exception $e) {
        return redirect()->route('commissions.show', ['year' => $year, 'month' => $month , 'var_month' => $var_month])
        ->withErrors(['error' , 'Can not Update']);
        }

    }
    
    public function updateCommission(Request $request)
    {
        // $ids = $request->input('id');
        // $sale_qty = $request->input('sale_qty');
        // $com = $request->input('com');
        $month = $request->input('month');
        $var_month = $request->input('var_month');
        $year = $request->input('year');
        $store_id = $request->input('store_id');
        $advance_data = str_replace(',', '',$request->input('advance'));
        $other_data = str_replace(',', '',$request->input('other'));
        $remark_data = $request->input('remark');
        $pc_qty_data = $request->input('pc_qty');

        try {

                foreach ($pc_qty_data as $pro_model => $pcs) {
                    foreach ($pcs as $id_pc => $qty) {
                        $existingData = Commission::where('store_id', $store_id)
                        ->where('pro_model', $pro_model)
                        ->where('id_pc', $id_pc)
                        ->where('as_of_month', $var_month)
                        ->where('as_of_year', $year)
                        ->first();
                        if ($existingData) {
                            Commission::updateOrCreate(
                                [
                                    'store_id' => $store_id,
                                    'pro_model' => $pro_model,
                                    'id_pc' => $id_pc,
                                    'as_of_month' => $var_month,
                                    'as_of_year' => $year,
                                ],
                                [
                                    'sale_qty' => $qty,
                                ]
                            );
                        } else {
                
                            $pc_with_data = Commission::where('store_id', $store_id)
                            ->where('pro_model', $pro_model)
                            ->where('as_of_month', $var_month)
                            ->where('as_of_year', $year)
                            ->first(); // คัดลอกจาก PC แรกที่มีข้อมูล

                            if ($pc_with_data) {
                                Commission::create([
                                    'suppliercode' => $pc_with_data->suppliercode,
                                    'store_id' => $pc_with_data->store_id,
                                    'type_store' => $pc_with_data->type_store,
                                    'as_of_month' => $var_month,
                                    'as_of_year' => $year,
                                    'pro_model' => $pc_with_data->pro_model,
                                    'type_product' => $pc_with_data->type_product,
                                    'sale_amt' => $pc_with_data->sale_amt,
                                    'sale_amt_vat' => $pc_with_data->sale_amt_vat,
                                    'sale_qty' => $qty, // อัปเดตจำนวนตามที่กรอกใหม่
                                    'id_pc' => $id_pc,
                                    'type_pc' => $pc_with_data->type_pc,
                                    'com' => $pc_with_data->com,
                                ]);
                            }
                        }
    
            $salesData = DB::table('tb_commission')
                ->select(
                    'store_id',
                    'id_pc',
                    DB::raw('SUM(CASE WHEN type_product = "TV" THEN sale_amt_vat * sale_qty ELSE 0 END) as sale_tv'),
                    DB::raw('SUM(CASE WHEN type_product = "TV" THEN sale_qty ELSE 0 END) as unit_tv'),
                    DB::raw('SUM(CASE WHEN type_product = "AV" THEN sale_amt_vat * sale_qty ELSE 0 END) as sale_av'),
                    DB::raw('SUM(CASE WHEN type_product = "AV" THEN sale_qty ELSE 0 END) as unit_av'),
                    DB::raw('SUM(CASE WHEN type_product = "HA" THEN sale_amt_vat * sale_qty ELSE 0 END) as sale_ha'),
                    DB::raw('SUM(CASE WHEN type_product = "HA" THEN sale_qty ELSE 0 END) as unit_ha')
                )
                ->where('as_of_month', $var_month)
                ->where('as_of_year', $year)
                ->where('id_pc', $id_pc)
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
                ->where('as_of_year', $year)
                ->where('id_pc', $id_pc)
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
                
                    
                    $advance_pay = $advance_data[$id_pc] ?? 0;
                    $remark_pay = $remark_data[$id_pc] ?? NULL;
                    $other_pay = $other_data[$id_pc] ?? 0;
                
                    //คำนวณ net_com และ net_pay
                    $data->pay_com = $data->com_tv + $data->com_av + $data->com_ha;
                    $data->net_com = $data->pay_com + $data->extra_tv + $data->extra_ha;

                    $data->net_pay = $data->net_com - $advance_pay;
                    $data->net_pay = $data->net_pay + $other_pay;
                    $sale_total = $data->sale_tv+$data->sale_av+$data->sale_ha;
                
                    //คำนวณ dis_pay
                    // $data->dis_pay = $data->pay_com / $data->net_com;
                    if ($data->net_com != 0) {
                        $data->dis_pay = $data->pay_com / $data->net_com;
                    } else {
                        // กำหนดค่า $data->dis_pay เป็นค่าอื่นที่คุณต้องการในกรณีที่ net_com เป็น 0
                        $data->dis_pay = 0; // หรืออาจจะเป็นค่าที่เหมาะสมกับ logic ของคุณ
                    }
                    
                    DB::table('tb_main_commission')->updateOrInsert(
                        [
                            'store_id' => $data->store_id,
                            'as_of_month' => $var_month,
                            'as_of_year' => $year,
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
                        'advance_pay' => $advance_pay,
                        'other' => $other_pay ,
                        'remark' => $remark_pay ,
                        'net_pay' => $data->net_pay,
                        'dis_pay' => $data->dis_pay
                    ]);
                }
            }
            
            }
        return redirect()->route('commissions.show', ['year' => $year, 'month' => $month , 'var_month' => $var_month])
        ->with('success', 'Data Update successfully');
        } catch (\Exception $e) {
        return redirect()->route('commissions.show', ['year' => $year, 'month' => $month , 'var_month' => $var_month])
        ->withErrors(['error' , 'Can not Update']);
        }
    }
    public function import($year, $month)
    {
        // Logic to import data for the given year and month
    }

    public function editTarget($year, $month,$var_month)
    {
        // Query all PC records from tb_pc
        $pcs = tb_pc::select('id','store_id', 'type_store', 'code_pc', 'name_pc', 'type_pc', 'tarket', 'salary')
                    ->whereNull('status_pc')
                    ->get();

        // Query corresponding commission data from tb_main_commission
        // $commissions = DB::table('tb_main_commission')
        //                     ->where('as_of_year', $year)
        //                     ->where('as_of_month', $var_month)
        //                     ->get();

        // Show form to edit the target
        return view('edit_target', compact('pcs', 'year', 'month','var_month'));
    }


    public function updateTarget(Request $request)
    {
        // dd($request->all());
        $targets = $request->input('tarket');
        $type_pcs = $request->input('type_pc');
        $var_month = $request->input('var_month');
        $year = $request->input('year');

        try{
        foreach ($targets as $id_pc  => $target) {
            $target = str_replace(',', '', $target);
            $type_pc = $type_pcs[$id_pc];
            // อัปเดทข้อมูลในตาราง tb_pc
            DB::table('tb_pc')
                ->where('id', $id_pc)
                ->update([
                    'tarket' => $target,
                    'type_pc' => $type_pc,
            ]);
            DB::table('tb_commission')
                ->where('as_of_month', $request->input('var_month'))
                ->where('as_of_year', $request->input('year'))
                ->where('id_pc', $id_pc)
                ->update(['type_pc' => $type_pc,]);


            $salesData = DB::table('tb_commission')
                    ->select(
                        'store_id',
                        'id_pc',
                        DB::raw('SUM(CASE WHEN type_product = "TV" THEN sale_amt_vat * sale_qty ELSE 0 END) as sale_tv'),
                        DB::raw('SUM(CASE WHEN type_product = "TV" THEN sale_qty ELSE 0 END) as unit_tv'),
                        DB::raw('SUM(CASE WHEN type_product = "AV" THEN sale_amt_vat * sale_qty ELSE 0 END) as sale_av'),
                        DB::raw('SUM(CASE WHEN type_product = "AV" THEN sale_qty ELSE 0 END) as unit_av'),
                        DB::raw('SUM(CASE WHEN type_product = "HA" THEN sale_amt_vat * sale_qty ELSE 0 END) as sale_ha'),
                        DB::raw('SUM(CASE WHEN type_product = "HA" THEN sale_qty ELSE 0 END) as unit_ha')
                    )
                    ->where('as_of_month', $request->input('var_month'))
                    ->where('as_of_year', $request->input('year'))
                    ->where('id_pc', $id_pc)
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
                    ->where('as_of_month', $request->input('var_month'))
                    ->where('as_of_year', $request->input('year'))
                    ->where('id_pc', $id_pc)
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
                            ->where('id', $id_pc)
                            ->get();
                        $pc = $pcs->first();
        
                        $data->achieve = (($data->sale_tv + $data->sale_av) * 100) / $target;
                    
                        switch ($type_pc) {
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
                                    $data->com_ha = 0;
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
                            $data->dis_pay = $data->pay_com / $data->net_com;
                        } else {
                            // กำหนดค่า $data->dis_pay เป็นค่าอื่นที่คุณต้องการในกรณีที่ net_com เป็น 0
                            $data->dis_pay = 0; // หรืออาจจะเป็นค่าที่เหมาะสมกับ logic ของคุณ
                        }
                        
                        DB::table('tb_main_commission')->updateOrInsert(
                            [
                                'store_id' => $data->store_id,
                                'as_of_month' =>  $request->input('var_month'),
                                'as_of_year' => $request->input('year'),
                                'id_pc' => $id_pc,
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
                            'type_pc' => $type_pc,
                            'pc_salary' => $pc->salary,
                            'tarket' => $target,
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
                
                    
           
        }

        // return redirect()->route('commissions.index')->with('success', 'Targets updated successfully!');
        return redirect()->route('commissions.index')->with('success', 'Targets updated successfully!');
        }catch (\Exception $e) {
        return redirect()->route('commissions.index')->withErrors(['error' => $e->getMessage()]);
        }
        
    }



}
