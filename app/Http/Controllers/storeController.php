<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\tb_store;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Mpdf\Mpdf;
class StoreController extends Controller
{
    public function index()
    {
        // $stores = tb_store::all();
        // return view('stores.index', compact('stores'));

        $stores = tb_store::whereNull('status_store')
                        ->orderBy('store_id')
                        ->get();
        return view('stores.index', compact('stores'));
        
    }

    public function store(Request $request)
    {
        try {
        $request->validate([
            'suppliercode' => 'required|string',
            'store_id' => 'nullable|string',
            'store' => 'nullable|string',
            'type_store' => 'nullable|string',
        ]);

        tb_store::create($request->all());

        return redirect()->route('stores.index')->with('success', 'Store created successfully.');
        } catch (\Exception $e) {
            // จับข้อผิดพลาดแล้วส่งกลับพร้อมกับข้อความ error
            return redirect()->route('pc.index')->withErrors(['error' => 'Failed to Create Store: ' . $e->getMessage()]);
        }
    }

    public function update(Request $request, $id)
    {
        try {
        $request->validate([
            'suppliercode' => 'required|string',
            'store_id' => 'nullable|string',
            'store' => 'nullable|string',
            'type_store' => 'nullable|string',
        ]);
    
        $store = tb_store::findOrFail($id);
        $store->update($request->all());
    
        return redirect()->route('stores.index')->with('success', 'Store updated successfully.');
        } catch (\Exception $e) {
            // จับข้อผิดพลาดแล้วส่งกลับพร้อมกับข้อความ error
            return redirect()->route('pc.index')->withErrors(['error' => 'Failed to update Store: ' . $e->getMessage()]);
        }
    }
    
    public function updateStatus(Request $request, $id)
    {
        try {
        $store = tb_store::findOrFail($id);
        $store->status_store = 'deleted'; // Or any other status indicating deletion
        $store->save();
    
        return redirect()->route('stores.index')->with('success', 'Store deleted successfully.');
        } catch (\Exception $e) {
            // จับข้อผิดพลาดแล้วส่งกลับพร้อมกับข้อความ error
            return redirect()->route('pc.index')->withErrors(['error' => 'Failed to Delete Store: ' . $e->getMessage()]);
        }
    }

    public function export(Request $request)
    {
        $type = $request->input('type');

        $stores = tb_store::whereNull('status_store')
        ->orderBy('store_id')
        ->get();


        if ($type === 'excel') {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Stores List');

        // กำหนดหัวข้อของตาราง
        $header = [
            'ID', 'Supplier Code', 'Store ID', 'Store', 'Type Store'
        ];

        // ตั้งค่า header
        $sheet->fromArray($header, NULL, 'A1');
        $sheet->getColumnDimension('A')->setWidth(7); // กำหนดความกว้างของคอลัมน์ A เป็น 10
        $sheet->getColumnDimension('B')->setWidth(15); // กำหนดความกว้างของคอลัมน์ B เป็น 20
        $sheet->getColumnDimension('C')->setWidth(15); // กำหนดความกว้างของคอลัมน์ C เป็น 15
        $sheet->getColumnDimension('D')->setWidth(40); // กำหนดความกว้างของคอลัมน์ D เป็น 30
        $sheet->getColumnDimension('E')->setWidth(15); 

        // กรอกข้อมูล commissions
        $row = 2; // เริ่มที่แถวที่ 2 เนื่องจากแถวที่ 1 เป็นหัวข้อ
        foreach ($stores as $store) {
            $sheet->setCellValue('A' . $row, $store->store_id);
            $sheet->setCellValue('B' . $row, $store->suppliercode);
            $sheet->setCellValue('C' . $row, $store->store_id);
            $sheet->setCellValue('D' . $row, $store->store);
            $sheet->setCellValue('E' . $row, $store->type_store);
            $row++;
        }


        // จัดการ download ไฟล์
        $filename = "Stores.xlsx";
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