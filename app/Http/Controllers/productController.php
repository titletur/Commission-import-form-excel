<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\product;
use App\Models\tb_disable_product;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Mpdf\Mpdf;

class productController extends Controller
{
    public function index()
    {
        // $stores = tb_store::all();
        // return view('stores.index', compact('stores'));
        $editMode = DB::table('tb_disable_product')->value('edit_mode');
        $products = product::whereNull('status_product')
                        ->orderBy('type_product', 'DESC')
                        ->get();
        return view('product.index', compact('products', 'editMode'));
        
    }
    public function toggleEditMode()
    {
        $editMode = DB::table('tb_disable_product')->value('edit_mode');
        DB::table('tb_disable_product')->update(['edit_mode' => !$editMode]);

        return redirect()->route('product.index')->with('success', $editMode ? 'Edit mode disabled.' : 'Edit mode enabled.');
    }

    public function store(Request $request)
    {
        try {
        $request->validate([
            'supplier_number' => 'nullable|string',
            'item_number' => 'nullable|string',
            'barcode' => 'nullable|string',
            'item_des' => 'nullable|string',
            'pack_type' => 'nullable|string',
            'type_product' => 'nullable|string',
            'price_vat' => 'nullable|string',
            'com' => 'nullable|string',
            
        ]);


        product::create($request->all());

        return redirect()->route('product.index')->with('success', 'product created successfully.');
        } catch (\Exception $e) {
            // จับข้อผิดพลาดแล้วส่งกลับพร้อมกับข้อความ error
            return redirect()->route('product.index')->withErrors(['error' => 'Failed to create product: ' . $e->getMessage()]);
        }
    }


    public function update(Request $request, $id)
    {
        try {
        $request->validate([
            'supplier_number' => 'nullable|string',
            'item_number' => 'nullable|string',
            'barcode' => 'nullable|string',
            'item_des' => 'nullable|string',
            'pack_type' => 'nullable|string',
            'type_product' => 'nullable|string',
            'price_vat' => 'nullable|string',
            'com' => 'nullable|string',
        ]);
    
        $product = product::findOrFail($id);
        $product->update($request->all());
    
        return redirect()->route('product.index')->with('success', 'product updated successfully.');
        } catch (\Exception $e) {
            // จับข้อผิดพลาดแล้วส่งกลับพร้อมกับข้อความ error
            return redirect()->route('product.index')->withErrors(['error' => 'Failed to update product: ' . $e->getMessage()]);
        }
    }
    
    public function updateStatus(Request $request, $id)
    {
        try {
            $product = product::findOrFail($id);
            $product->status_product = 'deleted'; 
            $product->save();

            return redirect()->route('product.index')->with('success', 'product deleted successfully.');
        } catch (\Exception $e) {
            // จับข้อผิดพลาดแล้วส่งกลับพร้อมกับข้อความ error
            return redirect()->route('product.index')->withErrors(['error' => 'Failed to deleted product: ' . $e->getMessage()]);
        }
    }
    
    public function export(Request $request)
    {
        $type = $request->input('type');

        $products = product::whereNull('status_product')
                        ->orderBy('type_product', 'DESC')
                        ->get();

        if ($type === 'excel') {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Product List');
        // กำหนดหัวข้อของตาราง
        $header = [
            'ID', 'Supplier number', 'item number', 'barcode', 'item description','type Product','pack type'
            ,'price(vat)','com'
        ];

        // ตั้งค่า header
        $sheet->fromArray($header, NULL, 'A1');
        $sheet->getColumnDimension('A')->setWidth(5); 
        $sheet->getColumnDimension('B')->setWidth(10); 
        $sheet->getColumnDimension('C')->setWidth(10); 
        $sheet->getColumnDimension('D')->setWidth(15); 
        $sheet->getColumnDimension('E')->setWidth(15); 
        $sheet->getColumnDimension('F')->setWidth(15); 
        $sheet->getColumnDimension('G')->setWidth(15); 
        $sheet->getColumnDimension('H')->setWidth(20); 
        $sheet->getColumnDimension('I')->setWidth(10); 
        

        // กรอกข้อมูล commissions
        $row = 2; // เริ่มที่แถวที่ 2 เนื่องจากแถวที่ 1 เป็นหัวข้อ
        foreach ($products as $product) {
            $sheet->setCellValue('A' . $row, $product->id);
            $sheet->setCellValue('B' . $row, $product->supplier_number);
            $sheet->setCellValue('C' . $row, $product->item_number);
            $sheet->setCellValue('D' . $row, $product->barcode);
            $sheet->setCellValue('E' . $row, $product->item_des);
            $sheet->setCellValue('F' . $row, $product->pack_type);
            $sheet->setCellValue('G' . $row, $product->type_product);
            $sheet->setCellValue('H' . $row, $product->price_vat);
            $sheet->setCellValue('I' . $row, $product->com);

            $row++;
        }


        // จัดการ download ไฟล์
        $filename = "Product.xlsx";
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