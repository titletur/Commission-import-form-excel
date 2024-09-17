<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\tb_pc;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Mpdf\Mpdf;

class pcController extends Controller
{
    public function index()
    {
        // $stores = tb_store::all();
        // return view('stores.index', compact('stores'));

        $pcs = tb_pc::whereNull('status_pc')
                        ->orderBy('id', 'DESC')
                        ->get();
        return view('pc.index', compact('pcs'));
        
    }

    public function store(Request $request)
    {
        try {
        
        $request->merge([
            'tarket' => $request->input('tarket') !== '' ? str_replace(',', '', $request->input('tarket')) : 0, 
            'salary' => $request->input('salary') !== '' ? str_replace(',', '', $request->input('salary')) : 0, 
        ]);
        $request->validate([
            'store_id' => 'nullable|string',
            'type_store' => 'nullable|string',
            'code_pc' => 'required|string',
            'name_pc' => 'nullable|string',
            'type_pc' => 'nullable|string',
            'tarket' => 'nullable|string',
            'salary' => 'nullable|string',
            
        ]);

        // tb_pc::create($request->all());
        tb_pc::updateOrCreate(
            [
                'store_id' => $request->input('store_id'),
                'code_pc' => $request->input('code_pc'),
                'name_pc' => $request->input('name_pc')
            ],
            [
                'type_store' => $request->input('type_store'),
                'type_pc' => $request->input('type_pc'),
                'tarket' => $request->input('tarket'),
                'salary' => $request->input('salary')
            ]
        );

        return redirect()->route('pc.index')->with('success', 'PC created successfully.');
        } catch (\Exception $e) {
            // จับข้อผิดพลาดแล้วส่งกลับพร้อมกับข้อความ error
            return redirect()->route('pc.index')->withErrors(['error' => 'Failed to Create PC: ' . $e->getMessage()]);
        }
    }


    public function update(Request $request, $id)
    {
        try {
        
        $request->merge([
            'tarket' => $request->input('tarket') !== '' ? str_replace(',', '', $request->input('tarket')) : 0, 
            'salary' => $request->input('salary') !== '' ? str_replace(',', '', $request->input('salary')) : 0, 
        ]);
        $request->validate([
            'store_id' => 'nullable|string',
            'type_store' => 'nullable|string',
            'code_pc' => 'required|string',
            'name_pc' => 'nullable|string',
            'type_pc' => 'nullable|string',
            'tarket' => 'nullable|string',
            'salary' => 'nullable|string',
        ]);
    
        $pc = tb_pc::findOrFail($id);
        $pc->update($request->all());
    
        return redirect()->route('pc.index')->with('success', 'PC updated successfully.');
        } catch (\Exception $e) {
            // จับข้อผิดพลาดแล้วส่งกลับพร้อมกับข้อความ error
            return redirect()->route('pc.index')->withErrors(['error' => 'Failed to update PC: ' . $e->getMessage()]);
        }
    }
    
    public function updateStatus(Request $request, $id)
    {
        try {
            $pc = tb_pc::findOrFail($id);
            $pc->status_pc = 'deleted'; 
            $pc->save();

            return redirect()->route('pc.index')->with('success', 'PC deleted successfully.');
        } catch (\Exception $e) {
            // จับข้อผิดพลาดแล้วส่งกลับพร้อมกับข้อความ error
            return redirect()->route('pc.index')->withErrors(['error' => 'Failed to Delete PC: ' . $e->getMessage()]);
        }
    }

    public function export(Request $request)
    {
        $type = $request->input('type');

        $pcs = tb_pc::whereNull('status_pc')
                        ->orderBy('id', 'DESC')
                        ->get();


        if ($type === 'excel') {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('PC List');

        // กำหนดหัวข้อของตาราง
        $header = [
            'ID', 'Store ID', 'Type Store', 'Code PC', 'Name','Type PC','Target','Salary'
        ];

        // ตั้งค่า header
        $sheet->fromArray($header, NULL, 'A1');
        $sheet->getColumnDimension('A')->setWidth(5); 
        $sheet->getColumnDimension('B')->setWidth(10); 
        $sheet->getColumnDimension('C')->setWidth(10); 
        $sheet->getColumnDimension('D')->setWidth(10); 
        $sheet->getColumnDimension('E')->setWidth(25); 
        $sheet->getColumnDimension('F')->setWidth(10); 
        $sheet->getColumnDimension('G')->setWidth(10); 
        $sheet->getColumnDimension('H')->setWidth(10); 

        // กรอกข้อมูล commissions
        $row = 2; // เริ่มที่แถวที่ 2 เนื่องจากแถวที่ 1 เป็นหัวข้อ
        foreach ($pcs as $pc) {
            $sheet->setCellValue('A' . $row, $pc->id);
            $sheet->setCellValue('B' . $row, $pc->store_id);
            $sheet->setCellValue('C' . $row, $pc->type_store);
            $sheet->setCellValue('D' . $row, $pc->code_pc);
            $sheet->setCellValue('E' . $row, $pc->name_pc);
            $sheet->setCellValue('F' . $row, $pc->type_pc);
            $sheet->setCellValue('G' . $row, $pc->tarket);
            $sheet->setCellValue('H' . $row, $pc->salary);
            $row++;
        }


        // จัดการ download ไฟล์
        $filename = "PC.xlsx";
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
    
}