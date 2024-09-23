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
            'suppliercode' => 'nullable|string',
            'division' => 'nullable|string',
            'department' => 'nullable|string',
            'subdepartment' => 'nullable|string',
            'pro_class' => 'nullable|string',
            'sub_pro_class' => 'nullable|string',
            'barcode' => 'nullable|string',
            'article' => 'nullable|string',
            'article_name' => 'nullable|string',
            'brand' => 'nullable|string',
            'pro_model' => 'required|string',
            'type_product' => 'nullable|string',
            'price' => 'nullable|string',
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
            'suppliercode' => 'nullable|string',
            'division' => 'nullable|string',
            'department' => 'nullable|string',
            'subdepartment' => 'nullable|string',
            'pro_class' => 'nullable|string',
            'sub_pro_class' => 'nullable|string',
            'barcode' => 'nullable|string',
            'article' => 'nullable|string',
            'article_name' => 'nullable|string',
            'brand' => 'nullable|string',
            'pro_model' => 'required|string',
            'type_product' => 'nullable|string',
            'price' => 'nullable|string',
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
            'ID', 'Supplier Code', 'Division', 'Department', 'Subdepartment','Pro Class','Sub Pro Class'
            ,'Barcode','Article','Article Name','Model','Type Product','Price','Price(vat)','Com'
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
        $sheet->getColumnDimension('J')->setWidth(20); 
        // $sheet->getColumnDimension('K')->setWidth(10); 
        $sheet->getColumnDimension('K')->setWidth(15); 
        $sheet->getColumnDimension('L')->setWidth(10); 
        $sheet->getColumnDimension('M')->setWidth(10); 
        $sheet->getColumnDimension('N')->setWidth(10); 
        $sheet->getColumnDimension('O')->setWidth(10); 

        // กรอกข้อมูล commissions
        $row = 2; // เริ่มที่แถวที่ 2 เนื่องจากแถวที่ 1 เป็นหัวข้อ
        foreach ($products as $product) {
            $sheet->setCellValue('A' . $row, $product->id);
            $sheet->setCellValue('B' . $row, $product->suppliercode);
            $sheet->setCellValue('C' . $row, $product->division);
            $sheet->setCellValue('D' . $row, $product->department);
            $sheet->setCellValue('E' . $row, $product->subdepartment);
            $sheet->setCellValue('F' . $row, $product->pro_class);
            $sheet->setCellValue('G' . $row, $product->sub_pro_class);
            $sheet->setCellValue('H' . $row, $product->barcode);
            $sheet->setCellValue('I' . $row, $product->article);
            $sheet->setCellValue('J' . $row, $product->article_name);
            // $sheet->setCellValue('K' . $row, $product->brand);
            $sheet->setCellValue('K' . $row, $product->pro_model);
            $sheet->setCellValue('L' . $row, $product->type_product);
            $sheet->setCellValue('M' . $row, $product->price);
            $sheet->setCellValue('N' . $row, $product->price_vat);
            $sheet->setCellValue('O' . $row, $product->com);
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