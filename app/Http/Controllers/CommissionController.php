<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\main_commission;
use App\Models\Commission;
use App\Models\tb_commission_sale;
use App\Models\AccessControl;
use App\Models\tb_status;
use App\Models\tb_pc;
use App\Models\tb_month;
use App\Models\product;
use App\Models\Price;
use App\Models\tb_sale;
use App\Models\tb_sub_sale;
use App\Models\Sale_in;
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
                $access = AccessControl::where('month', $month->var_month)->where('year', $year)->first();

            $monthlyData[] = [
                'month' => $month->short_en,
                'var_month' => $month->var_month,
                'var_year' => $year,
                'sale_in' => Sale_in::where('month', $month->var_month)->where('year', $year)->sum('sale_in'),
                'sale_out' => main_commission::where('as_of_month', $month->var_month)->where('as_of_year', $year)->sum('sale_total'),
                'pay_com' => main_commission::where('as_of_month', $month->var_month)->where('as_of_year', $year)->sum('net_pay'),
                'price_link' => route('import_price', [ 'year' => $year, 'month' => $month->short_en , 'var_month' => $month->var_month]), 
                'target_link' => route('editTarget', [ 'year' => $year, 'month' => $month->short_en , 'var_month' => $month->var_month]),
                'import_link' => route('import', ['year' => $year, 'month' => $month->month_en , 'var_month' => $month->var_month]),
                'show_link' => route('commissions.show', ['year' => $year, 'month' => $month->short_en , 'var_month' => $month->var_month]),

                'status' => $status ? $status->status_com : 0,
                'show_link_enabled' => $access ? $access->show_link_enabled : 0,
                'price_link_enabled' => $access ? $access->price_link_enabled : 0,
                'target_link_enabled' => $access ? $access->target_link_enabled : 0,
                'disabled' => $status && $status->status_com == 1
            ];
        }
        return view('index', compact('monthlyData', 'year', 'years', 'months'));
    }

    public function updateAccess($months, $years, Request $request)
    {
        $field = $request->input('field'); 
        $month = $request->input('month');
        $year = $request->input('year');
        $isEnabled = $request->input('isEnabled') == 'true' ? true : false; // ตรวจสอบค่าที่ส่งมา

        $accessControl = AccessControl::updateOrCreate(
            [
                'month' => $month,
                'year' => $year,
            ],
            [
                $field => $isEnabled
            ]
        );

        return redirect()->back()->with('success', 'Status updated successfully!');
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
    public function sale_in(Request $request)
    {
        $validatedData = $request->validate([
            'month' => 'required|string',
            'year' => 'required|string',
            'sale_in' => 'required|numeric',
        ]);

        Sale_in::updateOrCreate(
            ['month' => $validatedData['month'], 'year' => $validatedData['year']],
            ['sale_in' => $validatedData['sale_in']]
        );

        return redirect()->back()->with('success', 'Sale In saved successfully!');
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
                        ->orderBy('store_id')
                        ->get();

        $commissions_sale = DB::table('tb_commission_sale')
                        ->join('tb_sale', 'tb_commission_sale.id_sale', '=', 'tb_sale.id_sale')
                        ->where('tb_commission_sale.as_of_year', $year)
                        ->where('tb_commission_sale.as_of_month', $month)
                        ->select('tb_commission_sale.*', 'tb_sale.name_sale', 'tb_sale.code_sale') // รวมข้อมูลจาก tb_commission_sale และ tb_sale
                        ->get();
       
        $currentMonthName = DB::table('tb_month')
                        ->where('var_month', $month)
                        ->first();   

        $previousMonth = $currentMonthName->id - 1;

        // กรณีเดือนเป็น 1 (มกราคม) ต้องย้อนกลับไปเป็นเดือน 12 (ธันวาคม) และลดปีลง 1
        if ($previousMonth < 1) {
            $previousMonth = 12;
            $year -= 1;
        }
        $previousMonth2 = $previousMonth - 1;
        // Query ชื่อเดือน (เดือนปัจจุบัน)
        
        $previousMonthName1 = DB::table('tb_month')
                            ->where('id', $previousMonth)
                            ->first();

        $previousMonthName2 = DB::table('tb_month')
                            ->where('id', $previousMonth2)
                            // ->value('short_en','var_month')  
                            ->first();                         

        if ($type === 'excel') {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Commissions PC');

        // กำหนดหัวข้อของตาราง
        $header = [
            'Store', 'Code' , 'PC', 'Type Store','Type PC',  'QTY TV','Sale TV',  'QTY AV', 'Sale AV',
             'QTY HA','Sale HA', 'Sale Total', 'Target', 'TV+AV %', 'ComTV+AV', 'ComTV+AV', 'Com HA', 
            'Pay Com', 'Extra TV+AV', 'Extra HA', 'Other' ,  'Net Com', 'Advance', 'Net Pay' , '% Com' ,
            'salary' , ' '.$currentMonthName->short_en.'', ' '.$previousMonthName1->short_en.'', ' '.$previousMonthName2->short_en.'', 'Remark'
        ];

        // ตั้งค่า header
        $sheet->fromArray($header, NULL, 'A1');

        // กรอกข้อมูล commissions
        $row = 2; // เริ่มที่แถวที่ 2 เนื่องจากแถวที่ 1 เป็นหัวข้อ
        foreach ($commissions as $commission) {
            $pcs = DB::table('tb_pc')
                        ->whereNull('status_pc')
                        ->where('id', $commission->id_pc)
                        ->first();

            $sheet->setCellValue('A' . $row, $commission->store_id);
            $sheet->setCellValue('B' . $row, $pcs->code_pc);
            $sheet->setCellValue('C' . $row, $commission->name_pc);
            $sheet->setCellValue('D' . $row, $commission->type_store);
            $sheet->setCellValue('E' . $row, $commission->type_pc);
            $sheet->setCellValue('F' . $row, $commission->unit_tv)->getStyle('F2:M' . $row)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER);
            $sheet->setCellValue('G' . $row, $commission->sale_tv);
            $sheet->setCellValue('H' . $row, $commission->unit_av);
            $sheet->setCellValue('I' . $row, $commission->sale_av);
            $sheet->setCellValue('K' . $row, $commission->sale_ha);
            $sheet->setCellValue('L' . $row, $commission->sale_total);
            $sheet->setCellValue('M' . $row, $commission->tarket);
            $sheet->setCellValue('N' . $row, '=IFERROR((G'.$row.' + I'.$row.') / M'.$row.',0)' )->getStyle('N2:N' . $row)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_PERCENTAGE_00);

            $sheet->setCellValue('O' . $row, $commission->normalcom_tv + $commission->normalcom_av )->getStyle('O2:X' . $row)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER);
            $sheet->setCellValue('P' . $row, $commission->com_tv + $commission->com_av);
            $sheet->setCellValue('Q' . $row, $commission->com_ha);
            $sheet->setCellValue('R' . $row, $commission->pay_com);
            $sheet->setCellValue('S' . $row, $commission->extra_tv);
            $sheet->setCellValue('T' . $row, $commission->extra_ha);
            $sheet->setCellValue('U' . $row, $commission->other);
            $sheet->setCellValue('V' . $row, $commission->net_com);
            $sheet->setCellValue('W' . $row, $commission->advance_pay);
            $sheet->setCellValue('X' . $row, $commission->net_pay);
            $sheet->setCellValue('Y' . $row, '=IFERROR(V'.$row.' / L'.$row.',0)')->getStyle('Y2:Y' . $row)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_PERCENTAGE_00);
            $sheet->setCellValue('Z' . $row, $commission->pc_salary)->getStyle('Z2:Z' . $row)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER);
            
            $commissions_previous1 = DB::table('tb_main_commission')
                        ->where('as_of_year', $year)
                        ->where('as_of_month', $previousMonthName1->var_month)
                        ->where('id_pc', $commission->id_pc)
                        ->first();
                        $net_com1 = optional($commissions_previous1)->net_com ?? 0;
                        $pc_salary1 = optional($commissions_previous1)->pc_salary ?? 0;
                        $sale_total1 = optional($commissions_previous1)->sale_total ?? 0;
                        
                        
// หากต้องการค่าอื่น ๆ ก็สามารถทำซ้ำได้สำหรับการ query อื่น ๆ
            $commissions_previous2 = DB::table('tb_main_commission')
                        ->where('as_of_year', $year)
                        ->where('as_of_month', $previousMonthName2->var_month)
                        ->where('id_pc', $commission->id_pc)
                        ->first();
                        $net_com2 = optional($commissions_previous2)->net_com ?? 0;
                        $pc_salary2 = optional($commissions_previous2)->pc_salary ?? 0;
                        $sale_total2 = optional($commissions_previous2)->sale_total ?? 0;
            
            $sheet->setCellValue('AA' . $row, '=IFERROR((V'.$row.' + Z'.$row.') / L'.$row.',0)')->getStyle('AA2:AC' . $row)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_PERCENTAGE_00);
            $sheet->setCellValue('AB' . $row, '=IFERROR((' . $net_com1 . ' + ' . $pc_salary1 . ') / ' . $sale_total1 . ',0)');
            $sheet->setCellValue('AC' . $row, '=IFERROR((' . $net_com2 . ' + ' . $pc_salary2 . ') / ' . $sale_total2 . ',0)');

            $sheet->setCellValue('AD' . $row, $commission->remark);
            $row++;
        }

        // เพิ่มแถวผลรวม (Sum Total)
        $sheet->setCellValue('A' . $row, 'Total');
        $sheet->mergeCells("A$row:E$row"); // รวมเซลล์ A ถึง D
        $sheet->setCellValue('F' . $row, '=SUM(F2:F' . ($row - 1) . ')')->getStyle('F2:M' . $row)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER);
        $sheet->setCellValue('G' . $row, '=SUM(G2:G' . ($row - 1) . ')');
        $sheet->setCellValue('H' . $row, '=SUM(H2:H' . ($row - 1) . ')');
        $sheet->setCellValue('I' . $row, '=SUM(I2:I' . ($row - 1) . ')');
        $sheet->setCellValue('J' . $row, '=SUM(J2:J' . ($row - 1) . ')');
        $sheet->setCellValue('K' . $row, '=SUM(K2:K' . ($row - 1) . ')');
        $sheet->setCellValue('L' . $row, '=SUM(L2:L' . ($row - 1) . ')');
        $sheet->setCellValue('M' . $row, '=SUM(M2:M' . ($row - 1) . ')');
        $sheet->setCellValue('N' . $row, '=AVERAGE(N2:N' . ($row - 1) . ')')->getStyle('N2:N' . $row)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_PERCENTAGE_00);
        $sheet->setCellValue('O' . $row, '=SUM(O2:O' . ($row - 1) . ')')->getStyle('O2:X' . $row)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER);
        $sheet->setCellValue('P' . $row, '=SUM(P2:P' . ($row - 1) . ')');
        $sheet->setCellValue('Q' . $row, '=SUM(Q2:Q' . ($row - 1) . ')');
        $sheet->setCellValue('R' . $row, '=SUM(R2:R' . ($row - 1) . ')');
        $sheet->setCellValue('S' . $row, '=SUM(S2:S' . ($row - 1) . ')');
        $sheet->setCellValue('T' . $row, '=SUM(T2:T' . ($row - 1) . ')');
        $sheet->setCellValue('U' . $row, '=SUM(U2:U' . ($row - 1) . ')');
        $sheet->setCellValue('V' . $row, '=SUM(V2:V' . ($row - 1) . ')');
        $sheet->setCellValue('W' . $row, '=SUM(W2:W' . ($row - 1) . ')');
        $sheet->setCellValue('X' . $row, '=SUM(W2:W' . ($row - 1) . ')');
        $sheet->setCellValue('Y' . $row, '=AVERAGE(Y2:Y' . ($row - 1) . ')')->getStyle('Y2:Y' . $row)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_PERCENTAGE_00);
        $sheet->setCellValue('Z' . $row, '=SUM(W2:W' . ($row - 1) . ')')->getStyle('Z2:Z' . $row)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER);

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
            $sheetSale->setCellValue('D' . $rowSale, $commissionSale->target)->getStyle('D'.$rowSale.':K' . $rowSale)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER);
            $sheetSale->setCellValue('E' . $rowSale, $commissionSale->sale_out);
            $sheetSale->setCellValue('F' . $rowSale, $commissionSale->sale_tv);
            $sheetSale->setCellValue('G' . $rowSale, $commissionSale->sale_av);
            $sheetSale->setCellValue('H' . $rowSale, $commissionSale->sale_ha);
            $sheetSale->setCellValue('I' . $rowSale, $commissionSale->unit_tv);
            $sheetSale->setCellValue('J' . $rowSale, $commissionSale->unit_av);
            $sheetSale->setCellValue('K' . $rowSale, $commissionSale->unit_ha);
            $sheetSale->setCellValue('L' . $rowSale, $commissionSale->achieve / 100)->getStyle('L2:L' . $rowSale)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_PERCENTAGE_00);
            $sheetSale->setCellValue('M' . $rowSale, $commissionSale->base_com)->getStyle('M'.$rowSale.':S' . $rowSale)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER);
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
        $sheetSale->setCellValue('D' . $rowSale, '=SUM(D2:D' . ($rowSale - 1) . ')')->getStyle('D'.$rowSale.':K' . $rowSale)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER);
        $sheetSale->setCellValue('E' . $rowSale, '=SUM(E2:E' . ($rowSale - 1) . ')');
        $sheetSale->setCellValue('F' . $rowSale, '=SUM(F2:F' . ($rowSale - 1) . ')');
        $sheetSale->setCellValue('G' . $rowSale, '=SUM(G2:G' . ($rowSale - 1) . ')');
        $sheetSale->setCellValue('H' . $rowSale, '=SUM(H2:H' . ($rowSale - 1) . ')');
        $sheetSale->setCellValue('I' . $rowSale, '=SUM(I2:I' . ($rowSale - 1) . ')');
        $sheetSale->setCellValue('J' . $rowSale, '=SUM(J2:J' . ($rowSale - 1) . ')');
        $sheetSale->setCellValue('K' . $rowSale, '=SUM(K2:K' . ($rowSale - 1) . ')');
        $sheetSale->setCellValue('L' . $rowSale, '=AVERAGE(L2:L' . ($rowSale - 1) . ')')->getStyle('L2:L' . $rowSale)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_PERCENTAGE_00);
        $sheetSale->setCellValue('M' . $rowSale, '=SUM(M2:M' . ($rowSale - 1) . ')')->getStyle('M'.$rowSale.':S' . $rowSale)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER);
        $sheetSale->setCellValue('N' . $rowSale, '=SUM(N2:N' . ($rowSale - 1) . ')');
        $sheetSale->setCellValue('O' . $rowSale, '=SUM(O2:O' . ($rowSale - 1) . ')');
        $sheetSale->setCellValue('P' . $rowSale, '=SUM(P2:P' . ($rowSale - 1) . ')');
        $sheetSale->setCellValue('Q' . $rowSale, '=SUM(Q2:Q' . ($rowSale - 1) . ')');
        $sheetSale->setCellValue('R' . $rowSale, '=SUM(R2:R' . ($rowSale - 1) . ')');
        $sheetSale->setCellValue('S' . $rowSale, '=SUM(S2:S' . ($rowSale - 1) . ')');

        $rowSale = $rowSale + 3 ;
        // ... เพิ่มการคำนวณ Sum Total สำหรับคอลัมน์อื่น ๆ ตามที่ต้องการ
        $sheetSale->setCellValue('C' . $rowSale, '');
        $sheetSale->setCellValue('D' . $rowSale, 'Net Sale In');
        $sheetSale->setCellValue('E' . $rowSale, 'Sale Out');
        $rowSale++;
        $query_total_sum = DB::table('tb_main_commission')
        ->select(
            DB::raw('SUM(sale_total) as total_sale'), 
        )
        ->where('as_of_year', $year)
        ->where('as_of_month', $month)
        ->first();
        $query_sale_in_sum = DB::table('sales_in')
        ->select(
            DB::raw('SUM(sale_in) as sale_in'), 
        )
        ->where('year', $year)
        ->where('month', $month)
        ->first();
        $sheetSale->setCellValue('C' . $rowSale, $currentMonthName->short_en);
        $sheetSale->setCellValue('D' . $rowSale, $query_sale_in_sum->sale_in);
        $sheetSale->setCellValue('E' . $rowSale, $query_total_sum->total_sale);

        $query_total_sum_year = DB::table('tb_main_commission')
                    ->select(DB::raw('SUM(sale_total) as total_sale'))
                    ->where(function($query) use ($year) {
                        $query->where(function($query) use ($year) {
                            // ช่วงเดือน 04 ถึง 12 ของปีปัจจุบัน
                            $query->where('as_of_year', $year)
                                ->whereBetween('as_of_month', [4, 12]);
                        })->orWhere(function($query) use ($year) {
                            // ช่วงเดือน 01 ถึง 03 ของปีถัดไป
                            $query->where('as_of_year', $year + 1)
                                ->whereBetween('as_of_month', [1, 3]);
                        });
                    })
                    ->first();
        $query_sale_in_sum_year = DB::table('sales_in')
                    ->select(DB::raw('SUM(sale_in) as sale_in_total'))
                    ->where(function($query) use ($year) {
                        $query->where(function($query) use ($year) {
                            // ช่วงเดือน 04 ถึง 12 ของปีปัจจุบัน
                            $query->where('year', $year)
                                  ->whereBetween('month', [4, 12]);
                        })->orWhere(function($query) use ($year) {
                            // ช่วงเดือน 01 ถึง 03 ของปีถัดไป
                            $query->where('year', $year + 1)
                                  ->whereBetween('month', [1, 3]);
                        });
                    })
                    ->first();
                    $rowSale++;
                $sheetSale->setCellValue('C' . $rowSale, 'Sum All'.$year.' ');
                $sheetSale->setCellValue('D' . $rowSale, $query_sale_in_sum_year->sale_in_total);
                $sheetSale->setCellValue('E' . $rowSale, $query_total_sum_year->total_sale);

        $rowSale = $rowSale + 3 ;
        // ... เพิ่มการคำนวณ Sum Total สำหรับคอลัมน์อื่น ๆ ตามที่ต้องการ
        $sheetSale->setCellValue('C' . $rowSale, 'ประเภทพนักงาน');
        $sheetSale->setCellValue('D' . $rowSale, 'Achieve');
        $sheetSale->setCellValue('E' . $rowSale, 'Com');
        $sheetSale->setCellValue('F' . $rowSale, '% Com');
        $sheetSale->setCellValue('G' . $rowSale, 'จำนวนคน');
        
        $query_type_pc = DB::table('tb_main_commission')
        ->select(
            'type_pc', 
            DB::raw('SUM(sale_total) as total_sale'), 
            DB::raw('SUM(net_com) as total_com'), 
            DB::raw('COUNT(id_pc) as count_pc')
        )
        ->where('as_of_year', $year)
        ->where('as_of_month', $month)
        ->groupBy('type_pc')
        ->get();

        $sumSaleTotal = 0;
        $sumCom = 0;
        $sumCountPc = 0;
        $rowSale++;
        
        foreach ($query_type_pc as $data) {
            $achieve = $data->total_sale;  
            $com = $data->total_com;      
            $countPc = $data->count_pc;    
            $percentCom = ($achieve != 0) ? ($com / $achieve) * 100 : 0; // % ค่าคอมมิชชั่น
        
            $sheetSale->setCellValue('C' . $rowSale, $data->type_pc); 
            $sheetSale->setCellValue('D' . $rowSale, $achieve)->getStyle('D'.$rowSale.':E' . $rowSale)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER);       
            $sheetSale->setCellValue('E' . $rowSale, $com);         
            $sheetSale->setCellValue('F' . $rowSale, $percentCom /100)->getStyle('F'.$rowSale.':F' . $rowSale)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_PERCENTAGE_00);    
            $sheetSale->setCellValue('G' . $rowSale, $countPc);       
            
            $sumSaleTotal += $achieve;
            $sumCom += $com;
            $sumCountPc += $countPc;
            $rowSale++; // เพิ่มแถวสำหรับการวนลูปถัดไป
        }
        $sumpercentCom = ($sumSaleTotal != 0) ? ($sumCom / $sumSaleTotal) * 100 : 0; // % ค่าคอมมิชชั่น
        
        $sheetSale->setCellValue('C' . $rowSale, 'Total');  
        $sheetSale->setCellValue('D' . $rowSale, $sumSaleTotal)->getStyle('D'.$rowSale.':E' . $rowSale)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER);  
        $sheetSale->setCellValue('E' . $rowSale, $sumCom);   
        $sheetSale->setCellValue('F' . $rowSale, $sumpercentCom/100)->getStyle('F'.$rowSale.':F' . $rowSale)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_PERCENTAGE_00);        
        $sheetSale->setCellValue('G' . $rowSale, $sumCountPc); 

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
            $mpdf->SetFont('THSarabunNew', '', 12); // ตั้งฟอนต์ที่รองรับภาษาไทย
            $mpdf->SetFooter('{PAGENO} / {nbpg}');

            $html = view('commissions.commission_pdf', compact('commissions', 'commissions_sale' , 'month', 'year','show_month','currentMonthName','previousMonth' ,'previousMonth2','previousMonthName1','previousMonthName2'))->render();
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
    public function exportprice(Request $request)
    {

        $var_month = $request->input('var_month');
        $short_month = $request->input('short_month');
        $year = $request->input('var_year');
        $type = $request->input('type');

        $products = DB::table('tb_product')
        ->select('item_number', 'barcode', 'item_des', 'type_product', 'price_vat')
        ->whereNull('status_product')
        ->get();

        $product_prices = $products->map(function($product) use ($var_month, $year) {
            // ดึงข้อมูลจาก tb_price ตาม item_number และเงื่อนไขเดือนและปี
            $price = DB::table('tb_price')
                ->select('price_day1', 'price_day2', 'price_day3', 'price_day4', 'price_day5', 
                         'price_day6', 'price_day7', 'price_day8', 'price_day9', 'price_day10', 
                         'price_day11', 'price_day12', 'price_day13', 'price_day14', 'price_day15', 
                         'price_day16', 'price_day17', 'price_day18', 'price_day19', 'price_day20', 
                         'price_day21', 'price_day22', 'price_day23', 'price_day24', 'price_day25', 
                         'price_day26', 'price_day27', 'price_day28', 'price_day29', 'price_day30', 'price_day31')
                ->where('item_number', $product->item_number)
                ->where('as_of_month', $var_month)
                ->where('as_of_year', $year)
                ->first();
            
            if (!$price) {
                $price = (object) [
                    'price_day1' => null, 'price_day2' => null, 'price_day3' => null, 'price_day4' => null, 'price_day5' => null,
                    'price_day6' => null, 'price_day7' => null, 'price_day8' => null, 'price_day9' => null, 'price_day10' => null,
                    'price_day11' => null, 'price_day12' => null, 'price_day13' => null, 'price_day14' => null, 'price_day15' => null,
                    'price_day16' => null, 'price_day17' => null, 'price_day18' => null, 'price_day19' => null, 'price_day20' => null,
                    'price_day21' => null, 'price_day22' => null, 'price_day23' => null, 'price_day24' => null, 'price_day25' => null,
                    'price_day26' => null, 'price_day27' => null, 'price_day28' => null, 'price_day29' => null, 'price_day30' => null,
                    'price_day31' => null,
                ];
            }
    
            // รวมข้อมูล product และ price (ถ้าไม่มีข้อมูล price ให้ใช้ null)
            return (object) array_merge((array) $product, (array) $price);
        });

        if ($type === 'excel') {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Product Price');

        // กำหนดหัวข้อของตาราง
        $header = [
            'item number', 'Barcode', 'Description', 'Type', 'Day1','Day2','Day3','Day4','Day5','Day6','Day7','Day8','Day9','Day10','Day11'
            ,'Day12','Day13','Day14','Day15','Day16','Day17','Day18','Day19','Day20','Day21','Day22','Day23','Day24','Day25','Day26','Day27','Day28','Day29','Day30','Day31'
        ];

        // ตั้งค่า header
        $sheet->fromArray($header, NULL, 'A1');
        $sheet->getColumnDimension('A')->setWidth(10); 
        $sheet->getColumnDimension('B')->setWidth(10); 
        $sheet->getColumnDimension('C')->setWidth(20); 
        $sheet->getColumnDimension('D')->setWidth(10); 
        $sheet->getColumnDimension('E')->setWidth(7); 
        $sheet->getColumnDimension('F')->setWidth(7); 
        $sheet->getColumnDimension('G')->setWidth(7); 
        $sheet->getColumnDimension('H')->setWidth(7); 
        $sheet->getColumnDimension('I')->setWidth(7); 
        $sheet->getColumnDimension('J')->setWidth(7); 
        $sheet->getColumnDimension('K')->setWidth(7); 
        $sheet->getColumnDimension('L')->setWidth(7); 
        $sheet->getColumnDimension('M')->setWidth(7); 
        $sheet->getColumnDimension('N')->setWidth(7); 
        $sheet->getColumnDimension('O')->setWidth(7); 
        $sheet->getColumnDimension('P')->setWidth(7); 
        $sheet->getColumnDimension('Q')->setWidth(7); 
        $sheet->getColumnDimension('R')->setWidth(7); 
        $sheet->getColumnDimension('S')->setWidth(7); 
        $sheet->getColumnDimension('T')->setWidth(7); 
        $sheet->getColumnDimension('U')->setWidth(7); 
        $sheet->getColumnDimension('V')->setWidth(7); 
        $sheet->getColumnDimension('W')->setWidth(7); 
        $sheet->getColumnDimension('X')->setWidth(7); 
        $sheet->getColumnDimension('Y')->setWidth(7); 
        $sheet->getColumnDimension('Z')->setWidth(7); 
        $sheet->getColumnDimension('AA')->setWidth(7); 
        $sheet->getColumnDimension('AB')->setWidth(7); 
        $sheet->getColumnDimension('AC')->setWidth(7); 
        $sheet->getColumnDimension('AE')->setWidth(7); 
        $sheet->getColumnDimension('AF')->setWidth(7); 
        $sheet->getColumnDimension('AG')->setWidth(7); 
        $sheet->getColumnDimension('AH')->setWidth(7); 
        $sheet->getColumnDimension('AI')->setWidth(7); 

        // กรอกข้อมูล commissions
        $row = 2; // เริ่มที่แถวที่ 2 เนื่องจากแถวที่ 1 เป็นหัวข้อ
        foreach ($product_prices as $product_price) {
            $sheet->setCellValue('A' . $row, $product_price->item_number);
            $sheet->setCellValue('B' . $row, $product_price->barcode);
            $sheet->setCellValue('C' . $row, $product_price->item_des);
            $sheet->setCellValue('D' . $row, $product_price->type_product);
            $sheet->setCellValue('E' . $row, $product_price->price_day1);
            $sheet->setCellValue('F' . $row, $product_price->price_day2);
            $sheet->setCellValue('G' . $row, $product_price->price_day3);
            $sheet->setCellValue('H' . $row, $product_price->price_day4);
            $sheet->setCellValue('I' . $row, $product_price->price_day5);
            $sheet->setCellValue('J' . $row, $product_price->price_day6);
            $sheet->setCellValue('K' . $row, $product_price->price_day7);
            $sheet->setCellValue('L' . $row, $product_price->price_day8);
            $sheet->setCellValue('M' . $row, $product_price->price_day9);
            $sheet->setCellValue('N' . $row, $product_price->price_day10);
            $sheet->setCellValue('O' . $row, $product_price->price_day11);
            $sheet->setCellValue('P' . $row, $product_price->price_day12);
            $sheet->setCellValue('Q' . $row, $product_price->price_day13);
            $sheet->setCellValue('R' . $row, $product_price->price_day14);
            $sheet->setCellValue('S' . $row, $product_price->price_day15);
            $sheet->setCellValue('T' . $row, $product_price->price_day16);
            $sheet->setCellValue('U' . $row, $product_price->price_day17);
            $sheet->setCellValue('V' . $row, $product_price->price_day18);
            $sheet->setCellValue('W' . $row, $product_price->price_day19);
            $sheet->setCellValue('X' . $row, $product_price->price_day20);
            $sheet->setCellValue('Y' . $row, $product_price->price_day21);
            $sheet->setCellValue('Z' . $row, $product_price->price_day22);
            $sheet->setCellValue('AA' . $row, $product_price->price_day23);
            $sheet->setCellValue('AB' . $row, $product_price->price_day24);
            $sheet->setCellValue('AC' . $row, $product_price->price_day25);
            $sheet->setCellValue('AD' . $row, $product_price->price_day26);
            $sheet->setCellValue('AE' . $row, $product_price->price_day27);
            $sheet->setCellValue('AF' . $row, $product_price->price_day28);
            $sheet->setCellValue('AG' . $row, $product_price->price_day29);
            $sheet->setCellValue('AH' . $row, $product_price->price_day30);
            $sheet->setCellValue('AI' . $row, $product_price->price_day31);
            $row++;
        }


        // จัดการ download ไฟล์
        $filename = "product_price.xlsx";
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');

        } elseif ($type === 'pdf') {

            // $mpdf = new Mpdf();
            // $mpdf->AddPage('L'); // แนวนอน
            // $mpdf->SetFont('sarabun-regular', '', 14); // ตั้งฟอนต์ที่รองรับภาษาไทย

            // $html = view('commissions.commission_pdf', compact('commissions', 'month', 'year'))->render();
            // $mpdf->WriteHTML($html);
            // $mpdf->Output('commissions_' . $month . '_' . $year . '.pdf', 'I');
            
        }

        return redirect()->back();
    }

    public function edit(Request $request)
    {
        $store_id = $request->input('store_id');
        $var_month = $request->input('var_month');
        $year = $request->input('year');
        $month = $request->input('month');

        $commissions = Commission::select(
            'supplier_number',
            'store_id',
            'item_number',
            'type_product',
            'com',
            DB::raw('SUM(sale_qty) as total_sale_qty'),
            DB::raw('SUM(day1) as total_day1'),
            DB::raw('SUM(day2) as total_day2'),
            DB::raw('SUM(day3) as total_day3'),
            DB::raw('SUM(day4) as total_day4'),
            DB::raw('SUM(day5) as total_day5'),
            DB::raw('SUM(day6) as total_day6'),
            DB::raw('SUM(day7) as total_day7'),
            DB::raw('SUM(day8) as total_day8'),
            DB::raw('SUM(day9) as total_day9'),
            DB::raw('SUM(day10) as total_day10'),
            DB::raw('SUM(day11) as total_day11'),
            DB::raw('SUM(day12) as total_day12'),
            DB::raw('SUM(day13) as total_day13'),
            DB::raw('SUM(day14) as total_day14'),
            DB::raw('SUM(day15) as total_day15'),
            DB::raw('SUM(day16) as total_day16'),
            DB::raw('SUM(day17) as total_day17'),
            DB::raw('SUM(day18) as total_day18'),
            DB::raw('SUM(day19) as total_day19'),
            DB::raw('SUM(day20) as total_day20'),
            DB::raw('SUM(day21) as total_day21'),
            DB::raw('SUM(day22) as total_day22'),
            DB::raw('SUM(day23) as total_day23'),
            DB::raw('SUM(day24) as total_day24'),
            DB::raw('SUM(day25) as total_day25'),
            DB::raw('SUM(day26) as total_day26'),
            DB::raw('SUM(day27) as total_day27'),
            DB::raw('SUM(day28) as total_day28'),
            DB::raw('SUM(day29) as total_day29'),
            DB::raw('SUM(day30) as total_day30'),
            DB::raw('SUM(day31) as total_day31'),
        )
        ->where('store_id', $store_id)
        ->where('as_of_month', $var_month)
        ->where('as_of_year', $year)
        ->groupBy('supplier_number', 'store_id', 'item_number', 'type_product',  'com')
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
                                
        $pcs_sale_qty = [];
        foreach ($commissions as $commission) {
            foreach ($pcs as $pc) {
                for ($i = 1; $i <= 31; $i++) {
                    $pcs_sale_qty[$commission->item_number][$pc->id][$i] = DB::table('tb_commission')
                        ->where('store_id', $commission->store_id)
                        ->where('item_number', $commission->item_number)
                        ->where('id_pc', $pc->id)
                        ->where('as_of_month', $var_month)
                        ->where('as_of_year', $year)
                        ->sum('day' . $i);
                }
            }
        }

        return view('commissions.edit_data_pc', compact('commissions', 'pcs', 'pcs_sale_qty', 'store_id', 'var_month', 'month', 'year', 'main_commission'));
        // return view('commissions.edit_data_pc', compact('commissions', 'pcs', 'store_id', 'var_month','month', 'year', 'main_commission'));
    }


    public function updateCommission_sale(Request $request)
    {

        $month = $request->input('month');
        $var_month = $request->input('var_month');
        $year = $request->input('year');
        $add_total = str_replace(',', '',$request->input('add_total'));
        $other = str_replace(',', '',$request->input('other'));
        $remark = $request->input('remark');
        try {
            foreach ($other as $id => $value) {

                $commissionSale = DB::table('tb_commission_sale')
                    ->where('id', $id)
                    ->first();

                if ($commissionSale) {
                    // คำนวณค่า total ใหม่
                    ////////////////////////////////////////
                
                $sale = tb_sale::where('id_sale', $commissionSale->id_sale)->first();

                $id_sale = $sale->id_sale;
                $subStores = tb_sub_sale::where('id_sale', $id_sale)->pluck('store_id');
                
                // Calculate total sales and units
                $total_sale_tv = main_commission::whereIn('store_id', $subStores)
                    ->where('as_of_month', $var_month)
                    ->where('as_of_year', $year)
                    ->sum('sale_tv');

                $total_sale_av = main_commission::whereIn('store_id', $subStores)
                    ->where('as_of_month', $var_month)
                    ->where('as_of_year', $year)
                    ->sum('sale_av');
                
                $total_sale_ha = main_commission::whereIn('store_id', $subStores)
                    ->where('as_of_month', $var_month)
                    ->where('as_of_year', $year)
                    ->sum('sale_ha');
                
                $totalSale = $add_total[$id] + $total_sale_tv + $total_sale_av + $total_sale_ha;

                $total_unit_tv = main_commission::whereIn('store_id', $subStores)
                    ->where('as_of_month', $var_month)
                    ->where('as_of_year', $year)
                    ->sum('unit_tv');
                
                $total_unit_av = main_commission::whereIn('store_id', $subStores)
                    ->where('as_of_month', $var_month)
                    ->where('as_of_year', $year)
                    ->sum('unit_av');

                $total_unit_ha = main_commission::whereIn('store_id', $subStores)
                    ->where('as_of_month', $var_month)
                    ->where('as_of_year', $year)
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
                    ->where('as_of_year', $year)
                    ->where('type_pc', 'PC')
                    ->sum('sale_total');

                $totalPCs = main_commission::whereIn('store_id', $subStores)
                    ->where('as_of_month', $var_month)
                    ->where('as_of_year', $year)
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

                $sum_total_sale = $comSale + $extraSaleOut + $extraUnit + $extraAvg + str_replace(',', '', $value); ;
                // Insert or update tb_commission_sale
                DB::table('tb_commission_sale')
                    ->where('id', $id)
                    ->update([
                        'target' => $sale->target,
                        'achieve' => $achieve,
                        'base_com' => $sale->base_com,
                        'com_sale' => $comSale,
                        'add_total' => str_replace(',', '', $add_total[$id]),
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
                        'other' => str_replace(',', '', $value), 
                        'remark' => $remark[$id] 
                    ]
                );
                    /////////////////////////////////////////
                // DB::table('tb_commission_sale')
                //     ->where('id', $id)
                //     ->update([
                //         'add_total' => str_replace(',', '', $add_total[$id]),
                //         'other' => str_replace(',', '', $value), 
                //         'total' => $newTotal,
                //         'remark' => $remark[$id] 
                //     ]);
                }
                #########################
             // Query all sales
            
                
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

        $month = $request->input('month');
        $var_month = $request->input('var_month');
        $year = $request->input('year');
        $store_id = $request->input('store_id');
        $advance_data = str_replace(',', '',$request->input('advance'));
        $other_data = str_replace(',', '',$request->input('other'));
        $remark_data = $request->input('remark');
        $pc_qty_data = $request->input('pc_qty');
        
        try {

                foreach ($pc_qty_data as $itemNumber => $pcs) {
                    foreach ($pcs as $id_pc => $days) {
                        // Prepare the update data array
                        $update_data = [];
                        foreach ($days as $day => $saleQty) {
                            $dayField = $day;
                            $update_data[$dayField] = $saleQty; // Store day fields in associative array
                        }

                        // Get product and price information
                        $product = Product::where('item_number', $itemNumber)->first();
                        $price = Price::where('item_number', $itemNumber)
                                    ->where('as_of_month', $var_month)
                                    ->where('as_of_year', $year)
                                    ->first();
        
                        $sale_total_price_item = 0;
                        $sale_qty = 0;

                        // Calculate sale_total_price_item and sale_qty
                        foreach ($update_data as $dayField => $day_qty) {
                            if ($day_qty != 0) {
                                $price_column = 'price_' . $dayField;
                                if (isset($price->$price_column) && $price->$price_column != 0) {
                                    $sale_total_price_item += $day_qty * $price->$price_column;
                                } else {
                                    $sale_total_price_item += $day_qty * $product->price_vat;
                                }
                                $sale_qty += $day_qty;
                            }
                        }

                        // Check if existing data exists
                        $existingData = Commission::where('store_id', $store_id)
                            ->where('item_number', $itemNumber)
                            ->where('id_pc', $id_pc)
                            ->where('as_of_month', $var_month)
                            ->where('as_of_year', $year)
                            ->first();

                            

                        if ($existingData) {
                            $update_fields = array_merge([
                                'sale_total' => $sale_total_price_item,
                                'sale_qty' => $sale_qty,
                            ], $update_data);

                            Commission::updateOrCreate(
                                [
                                    'store_id' => $store_id,
                                    'item_number' => $itemNumber,
                                    'id_pc' => $id_pc,
                                    'as_of_month' => $var_month,
                                    'as_of_year' => $year,
                                ],
                                $update_fields
                            );
                        } else {
                            
                            $pc_with_data = Commission::where('store_id', $store_id)
                            ->where('itemNumber', $itemNumber)
                            ->where('as_of_month', $var_month)
                            ->where('as_of_year', $year)
                            ->first(); // คัดลอกจาก PC แรกที่มีข้อมูล
                            
                            $create_fields = array_merge([
                                'supplier_number' => $pc_with_data->supplier_number,
                                'store_id' => $pc_with_data->store_id,
                                'type_store' => $pc_with_data->type_store,
                                'as_of_month' => $var_month,
                                'as_of_year' => $year,
                                'item_number' => $pc_with_data->itemNumber,
                                'type_product' => $pc_with_data->type_product,
                                'sale_total' => $sale_total_price_item,
                                'sale_qty' => $sale_qty,
                                'id_pc' => $id_pc,
                                'type_pc' => $pc_with_data->type_pc,
                                'com' => $pc_with_data->com,
                            ], $update_data);

                            if ($pc_with_data) {
                                Commission::create($create_fields);
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
                        ->where('as_of_year', $year)
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
                                            ->where('as_of_year', $year)
                                            ->where('type_pc', 'PC')
                                            ->where('store_id', $data->store_id)
                                            ->groupBy('id_pc')
                                            ->havingRaw('SUM(sale_total) >= 350000')
                                            ->get();
            
                                            if($extra_pc_store->count() == $pc_sales->count()){
                                                
                                                $extra_pc_sale_total = DB::table('tb_commission')
                                                ->select(DB::raw('SUM(sale_total) as total_sales'))
                                                ->where('as_of_month', $var_month)
                                                ->where('as_of_year', $year)
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
                        
                            
                            $advance_pay = $advance_data[$data->id_pc] ?? 0;
                            $remark_pay = $remark_data[$data->id_pc] ?? NULL;
                            $other_pay = $other_data[$data->id_pc] ?? 0;
                            
                        
                            //คำนวณ net_com และ net_pay
                            $data->pay_com = $data->com_tv + $data->com_av + $data->com_ha;
                            $data->net_com = $data->pay_com + $data->extra_tv + $data->extra_ha;

                            $data->net_pay = $data->net_com - $advance_pay;
                            $data->net_pay = $data->net_pay + $other_pay;
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

    public function import_price($year, $month,$var_month)
    {
        // Query all PC records from tb_pc
        $product_prices = DB::table('tb_product')
        ->leftJoin('tb_price', 'tb_product.item_number', '=', 'tb_price.item_number')
        ->select(
            'tb_product.item_number', 
            'tb_product.barcode', 
            'tb_product.item_des', 
            'tb_product.type_product', 
            'tb_product.price_vat',
            'tb_price.as_of_month', 
            'tb_price.as_of_year',
            DB::raw('tb_price.price_day1, tb_price.price_day2, tb_price.price_day3,tb_price.price_day4,tb_price.price_day5
            ,tb_price.price_day6,tb_price.price_day7,tb_price.price_day8,tb_price.price_day9,tb_price.price_day10,tb_price.price_day11
            ,tb_price.price_day12,tb_price.price_day13,tb_price.price_day14,tb_price.price_day15,tb_price.price_day16,tb_price.price_day17
            ,tb_price.price_day18,tb_price.price_day19,tb_price.price_day20,tb_price.price_day21,tb_price.price_day22,tb_price.price_day23
            ,tb_price.price_day24,tb_price.price_day25,tb_price.price_day26,tb_price.price_day27,tb_price.price_day28,tb_price.price_day29
            ,tb_price.price_day30, tb_price.price_day31') 
        )
        ->whereNull('tb_product.status_product')
        ->where(function($query) use ($var_month, $year) {
            $query->where('tb_price.as_of_month', '=', $var_month)
                  ->where('tb_price.as_of_year', '=', $year)
                  ->orWhereNull('tb_price.as_of_month') // ถ้าไม่มีข้อมูล tb_price ก็จะยังโชว์ข้อมูล tb_product
                  ->orWhereNull('tb_price.as_of_year');

        })
        ->get();

        return view('import_price', compact('product_prices', 'year', 'month','var_month'));
    }
    public function uploadprice(Request $request)
    {
        $var_month = $request->input('var_month');
        $year = $request->input('year');
        for ($i = 1; $i <= 31; $i++) {
            $price_day["price_day{$i}"] = $request->input("price_day{$i}");
        }
        // dd($request->all());
        try{
            foreach ($price_day['price_day1'] as $item_number => $day1_value) {
                $data = [
                    'item_number' => $item_number,
                    'as_of_month' => $var_month,
                    'as_of_year' => $year,
                ];
    
                // เพิ่มข้อมูล price_day1 - price_day31 ลงใน array
                for ($i = 1; $i <= 31; $i++) {
                    $data["price_day{$i}"] = $price_day["price_day{$i}"][$item_number] ?? 0;
                }
    
                // ตรวจสอบว่ามีข้อมูลใน tb_price สำหรับ item_number นี้หรือยัง
                $existingPrice = DB::table('tb_price')
                    ->where('item_number', $item_number)
                    ->where('as_of_month', $var_month)
                    ->where('as_of_year', $year)
                    ->first();
    
                if ($existingPrice) {
                    // ถ้ามีแล้ว ให้ทำการอัปเดต
                    DB::table('tb_price')
                        ->where('item_number', $item_number)
                        ->where('as_of_month', $var_month)
                        ->where('as_of_year', $year)
                        ->update($data);
                } else {
                    // ถ้ายังไม่มี ให้ทำการสร้างใหม่
                    DB::table('tb_price')->insert($data);
                }
            }

         return redirect()->route('commissions.index')->with('success', 'Product Price updated successfully!');
        }catch (\Exception $e) {
        return redirect()->route('commissions.index')->withErrors(['error' => $e->getMessage()]);
        }
    }
    protected function calculateCom($item_number)
    {
        // Example: Retrieve the commission from tb_product based on item_number
        $product = product::where('item_number', $item_number)
                        ->whereNull('status_product') 
                        ->first();
        return $product ? $product->com : 0;
    }
    public function updateTarget(Request $request)
    {
        // dd($request->all());
        $targets = $request->input('tarket');
        $type_pcs = $request->input('type_pc');
        $var_month = $request->input('var_month');
        $year = $request->input('year');
        
        try{

            $pro_commissions = DB::table('tb_commission')
            ->select('item_number') // เลือกเฉพาะ item_number
            ->where('as_of_month', $var_month)
            ->where('as_of_year', $year)
            ->distinct() // ใช้ distinct เพื่อลดการซ้ำของ item_number
            ->get();
            
        foreach ($pro_commissions as $row_pro) {
            // คำนวณค่า com สำหรับแต่ละ item_number
            
            $com = $this->calculateCom($row_pro->item_number);

            $affectedRows = DB::table('tb_commission')
            ->where('as_of_month', $var_month)
            ->where('as_of_year', $year)
            ->where('item_number', $row_pro->item_number)
            ->update([
                'com' => $com,
            ]);
            // dd($affectedRows);
        }
        /////////////////////////////
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

                        DB::raw('SUM(CASE WHEN type_product = "TV" THEN sale_total ELSE 0 END) as sale_tv'),
                        DB::raw('SUM(CASE WHEN type_product = "TV" THEN sale_qty ELSE 0 END) as unit_tv'),
                        
                        DB::raw('SUM(CASE WHEN type_product = "AV" THEN sale_total ELSE 0 END) as sale_av'),
                        DB::raw('SUM(CASE WHEN type_product = "AV" THEN sale_qty ELSE 0 END) as unit_av'),

                        DB::raw('SUM(CASE WHEN type_product = "HA" THEN sale_total ELSE 0 END) as sale_ha'),
                        DB::raw('SUM(CASE WHEN type_product = "HA" THEN sale_qty ELSE 0 END) as unit_ha')
                    )
                    ->where('as_of_month', $var_month)
                    ->where('as_of_year', $year)
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
                        $sale_tv_av = $data->sale_tv + $data->sale_av;
                        if ($sale_tv_av != 0) {
                        // $data->achieve = (($data->sale_tv + $data->sale_av) * 100) / $target;
                        $data->achieve = $target != 0 ? (($data->sale_tv + $data->sale_av) * 100) / $target: 0;
                        } else {
                            $data->achieve = 0; // หรือใช้ค่าอื่นๆตามที่ต้องการ
                        }
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
