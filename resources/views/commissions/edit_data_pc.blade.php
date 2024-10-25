@extends('layouts.master')

@section('title', 'Edit Commission Data')
@php
$permissions = json_decode(Auth::user()->permissions, true); // แปลง JSON เป็น array
@endphp
@section('content')

    <h1 class="text-center">Edit Commission PC for {{ $month }} {{ $year }}</h1>
   <hr>

   <form action="{{ route('update_commission') }}" method="POST">
    @csrf
   <font size="+2">
    @foreach($pcs as $pc)
    
        <div class="row mb-12" align="center">
            <div class="col-md-2">
                <strong>Code:</strong> {{ $pc->code_pc }}
            </div>
            <div class="col-md-4">
                <strong>Name:</strong> {{ $pc->name_pc }}
            </div>
            <div class="col-md-2">
                <strong>Type:</strong> {{ $pc->type_pc }}
            </div>
            <div class="col-md-2">
                <strong>Target:</strong> {{ number_format($pc->tarket, 0) }}
            </div>
            <div class="col-md-2">
                <strong>Salary:</strong> {{ number_format($pc->salary, 0) }}
            </div>
        </div>
        <div class="row mb-12" align="center">
                    @php
                        $pc_advance = $main_commission->where('id_pc', $pc->id)
                            ->where('as_of_month', $var_month)
                            ->where('as_of_year', $year)
                            ->first()->advance_pay ?? 0;

                        $pc_other = $main_commission->where('id_pc', $pc->id)
                            ->where('as_of_month', $var_month)
                            ->where('as_of_year', $year)
                            ->first()->other ?? 0; 

                        $pc_remark = $main_commission->where('id_pc', $pc->id)
                            ->where('as_of_month', $var_month)
                            ->where('as_of_year', $year)
                            ->first()->remark ?? ''; 
                    @endphp
                    <div class="col-md-3">
                        <strong>Advance:</strong>:
                        <input type="text" name="advance[{{ $pc->id }}]" style="width: 200px;" value="{{ number_format($pc_advance, 0) }}">
                    </div>
                    <div class="col-md-3">
                        <strong>Other:</strong>:
                        <input type="text" name="other[{{ $pc->id }}]" style="width: 200px;" value="{{ number_format($pc_other, 0) }}">
                    </div>
                    <div class="col-md-6">
                        <strong>Remark:</strong>:
                        <input type="text" name="remark[{{ $pc->id }}]" style="width: 80%;" value="{{ $pc_remark }}">
                    </div>
        </div>
        <hr>
    @endforeach
   </font>
    
        <input type="hidden" name="month" value="{{ $month }}">
        <input type="hidden" name="var_month" value="{{ $var_month }}">
        <input type="hidden" name="year" value="{{ $year }}">
        <input type="hidden" name="store_id" value="{{ $store_id }}">

        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Supplier number</th>
                    <th>Store ID</th>
                    <th>Item number</th>
                    <th>Type Product</th>
                    <th>Sale total (VAT)</th>
                    <th>Price Com</th>
                    <th>Total Sale Quantity</th>
                </tr>
            </thead>
            <tbody>
                @foreach($commissions as $commission)
                    <tr>
                        <td>{{ $commission->supplier_number }}</td>
                        <td>{{ $commission->store_id }}</td>
                        <td>{{ $commission->item_number }}</td>
                        <td>{{ $commission->type_product }}</td>
                        <td>{{ number_format($commission->sale_total, 0) }}</td>
                        <td>{{ number_format($commission->com, 0) }}</td>
                        <td>{{ number_format($commission->total_sale_qty, 0) }}</td>
                    </tr>
            
                    <tr>
                        <td colspan="7">
                            <table class="table table-sm table-bordered">
                                <thead>
                                    <tr>
                                        <th>Total Day</th>
                                        @for ($i = 1; $i <= 31; $i++)
                                            <th>D{{ $i }}</th>
                                        @endfor
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Sale QTY</td>
                                        @for ($i = 1; $i <= 31; $i++)
                                            @php
                                                $dayField = 'total_day' . $i;
                                            @endphp
                                            <td>{{ number_format($commission->$dayField, 0) }}</td>
                                        @endfor
                                    </tr>
                                    
                                    @foreach($pcs as $pc)
                                        <tr>
                                            <td>{{ $pc->name_pc }}</td>
                                            @for ($i = 1; $i <= 31; $i++)
                                                <td>
                                                    <input type="number" 
                                                           name="pc_qty[{{ $commission->item_number }}][{{ $pc->id }}][day{{ $i }}]" 
                                                           value="{{ number_format($pcs_sale_qty[$commission->item_number][$pc->id][$i], 0) }}" 
                                                           class="sale-qty-input form-control"
                                                           data-total-day="{{ number_format($commission->$dayField, 0) }}"
                                                           data-day="{{ $i }}">
                                                </td>
                                            @endfor
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        
        
        
            
        @if(in_array('Edit_qty', $permissions))      
        <div align="center">
            <button id="submit-btn" class="btn btn-primary" >Submit</button>
            {{-- <button type="submit" class="btn btn-primary" id="submit-btn">Update</button> --}}
        </div>
        @endif
    </form>

    {{-- <script>
        document.addEventListener('DOMContentLoaded', function () {
            const saleQtyInputs = document.querySelectorAll('.sale-qty-input');
            const submitBtn = document.getElementById('submit-btn');
    
            function validateQuantities() {
                let isValid = true;
    
                // Loop through each commission row
                document.querySelectorAll('tbody > tr').forEach(row => {
                    const totalDays = [];
                    
                    // Get item_number from the commission row
                    const itemNumber = row.cells[2].textContent; // คอลัมน์ Item Number
    
                    // Collect total day values for this item_number
                    const totalDayFields = row.querySelectorAll('td:nth-child(n+5)'); // คอลัมน์จาก Sale Total (VAT) ขึ้นไป
                    totalDayFields.forEach((totalDayField, index) => {
                        totalDays[index] = parseFloat(totalDayField.textContent.replace(',', '')) || 0;
                    });
    
                    // Find the corresponding PC rows
                    const pcsRows = row.nextElementSibling.querySelectorAll('tbody tr');
                    pcsRows.forEach(pcRow => {
                        let sumQty = 0;
    
                        // Loop over each input for the current item_number and PC
                        pcRow.querySelectorAll('.sale-qty-input').forEach((input, index) => {
                            const value = parseFloat(input.value.replace(',', '')) || 0;
                            sumQty += value;
    
                            // Compare the input value to the totalDay for the current item_number
                            if (value !== totalDays[index]) {
                                input.style.backgroundColor = 'red'; // เปลี่ยนสีพื้นหลังเป็นสีแดง
                                isValid = false;
                            } else {
                                input.style.backgroundColor = ''; // คืนค่าพื้นหลังเป็นปกติ
                            }
                        });
    
                        // Optional: Check if the sum of quantities equals the total for the current item_number
                        const totalQtyField = row.cells[6]; // คอลัมน์ Total Sale Quantity
                        const totalQty = parseFloat(totalQtyField.textContent.replace(',', '')) || 0;
                        if (sumQty !== totalQty) {
                            pcRow.style.backgroundColor = 'red'; // เปลี่ยนสีพื้นหลังของแถว PC ถ้าจำนวนรวมไม่ตรง
                            isValid = false;
                        } else {
                            pcRow.style.backgroundColor = ''; // คืนค่าพื้นหลังเป็นปกติ
                        }
                    });
                });
    
                submitBtn.disabled = !isValid; // ปิดปุ่มส่งข้อมูลถ้าทุกค่าไม่ถูกต้อง
            }
    
            // Attach input event to each sale quantity input
            saleQtyInputs.forEach(input => {
                input.addEventListener('input', validateQuantities);
            });
    
            // Initial validation
            validateQuantities();
        });
    </script> --}}
    
    
@endsection
