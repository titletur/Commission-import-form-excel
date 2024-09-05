<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\tb_sale;
use App\Models\tb_store;
use App\Models\tb_sub_sale;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Mpdf\Mpdf;

class saleController extends Controller
{
    public function index()
    {
        // $stores = tb_store::all();
        // return view('stores.index', compact('stores'));

        $sales = tb_sale::whereNull('status_sale')
                        ->orderBy('id_sale', 'DESC')
                        ->get();
        
        $stores = tb_store::whereNull('status_store')
                        ->get();



        return view('sale.index', compact('sales','stores'));
        
    }
    public function getStoresForSale($id_sale)
    {
        $stores = tb_store::whereNull('status_store')->get();
        $selectedStores = tb_sub_sale::where('id_sale', $id_sale)->pluck('store_id')->toArray();

        $storeData = $stores->map(function ($store) use ($selectedStores) {
            return [
                'store_id' => $store->store_id,
                'store' => $store->store,
                'selected' => in_array($store->store_id, $selectedStores)
            ];
        });


        return response()->json(['stores' => $storeData]);
    }


    public function store(Request $request)
    {
        try {
        $request->validate([
            'code_sale' => 'required|string',
            'name_sale' => 'required|string',
            'target' => 'nullable|string',
            'base_com' => 'nullable|string',
            'store_ids' => 'required|array',

        ]);

        $sale = tb_sale::create([
            'code_sale' => $request->input('code_sale'),
            'name_sale' => $request->input('name_sale'),
            'target' => $request->input('target'),
            'base_com' => $request->input('base_com'),
        ]);

        foreach ($request->input('store_ids') as $store_id) {
            tb_sub_sale::create([
                'id_sale' => $sale->id_sale,
                'store_id' => $store_id,
            ]);
        }

        return redirect()->route('sale.index')->with('success', 'Sale created successfully.');
        } catch (\Exception $e) {
            // จับข้อผิดพลาดแล้วส่งกลับพร้อมกับข้อความ error
            return redirect()->route('sale.index')->withErrors(['error' => 'Failed to update status: ' . $e->getMessage()]);
        }
    }


    public function update(Request $request, $id_sale)
    {
        try {
        $request->validate([
            'code_sale' => 'required|string',
            'name_sale' => 'required|string',
            'target' => 'nullable|string',
            'base_com' => 'nullable|string',
            'store_ids' => 'required|array',
        ]);
        $sale = tb_sale::where('id_sale', $id_sale)->firstOrFail();
        $sale->update([
            'code_sale' => $request->input('code_sale'),
            'name_sale' => $request->input('name_sale'),
            'target' => $request->input('target'),
            'base_com' => $request->input('base_com'),
        ]);
        tb_sub_sale::where('id_sale', $id_sale)->delete();
        foreach ($request->input('store_ids') as $store_id) {
            tb_sub_sale::create([
                'id_sale' => $sale->id_sale,
                'store_id' => $store_id,
            ]);
        }
    
        return redirect()->route('sale.index')->with('success', 'Sale updated successfully.');
        } catch (\Exception $e) {
            // จับข้อผิดพลาดแล้วส่งกลับพร้อมกับข้อความ error
            return redirect()->route('sale.index')->withErrors(['error' => 'Failed to update status: ' . $e->getMessage()]);
        }
    }
    
    public function updateStatus(Request $request, $id_sale)
    {
        try {
            $sale = tb_sale::where('id_sale', $id_sale)->firstOrFail();
            $sale->status_sale = 'deleted'; 
            $sale->save();

            return redirect()->route('sale.index')->with('success', 'Sale deleted successfully.');
        } catch (\Exception $e) {
            // จับข้อผิดพลาดแล้วส่งกลับพร้อมกับข้อความ error
            return redirect()->route('sale.index')->withErrors(['error' => 'Failed to update status: ' . $e->getMessage()]);
        }
    }

    public function export(Request $request)
    {
        $type = $request->input('type');

        $sales = tb_sale::whereNull('status_sale')
        ->orderBy('id_sale', 'DESC')
        ->get();


        if ($type === 'excel') {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Sale List');

        // กำหนดหัวข้อของตาราง
        $header = [
            'ID', 'Code', 'Name', 'Target', 'Base com','Stores'
        ];

        // ตั้งค่า header
        $sheet->fromArray($header, NULL, 'A1');
        $sheet->getColumnDimension('A')->setWidth(7); 
        $sheet->getColumnDimension('B')->setWidth(10); 
        $sheet->getColumnDimension('C')->setWidth(25); 
        $sheet->getColumnDimension('D')->setWidth(10); 
        $sheet->getColumnDimension('E')->setWidth(10); 
        $sheet->getColumnDimension('F')->setWidth(38); 

        // กรอกข้อมูล commissions
        $row = 2; // เริ่มที่แถวที่ 2 เนื่องจากแถวที่ 1 เป็นหัวข้อ
        foreach ($sales as $sale) {
            $sheet->setCellValue('A' . $row, $sale->id_sale);
            $sheet->setCellValue('B' . $row, $sale->code_sale);
            $sheet->setCellValue('C' . $row, $sale->name_sale);
            $sheet->setCellValue('D' . $row, $sale->target);
            $sheet->setCellValue('E' . $row, $sale->base_com);

            $selectedStores = tb_sub_sale::where('id_sale', $sale->id_sale)->get();
            $stores = [];
            foreach ($selectedStores as $selectedStore) {
                $stores[] = $selectedStore->store_id; // เก็บ store_id ใน array
            }

            $sheet->setCellValue('F' . $row, implode(', ', $stores));

            $row++;
        }


        // จัดการ download ไฟล์
        $filename = "Sale.xlsx";
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