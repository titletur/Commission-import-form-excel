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
                        <th>Supplier Code</th>
                        <th>Store ID</th>
                        {{-- <th>Type Store</th> --}}
                        <th>Product Model</th>
                        <th>Type Product</th>
                        <th>Sale Amount</th>
                        <th>Sale Amount (VAT)</th>
                        <th>Total Sale Quantity</th>
                        @foreach($pcs as $pc)
                            <th>{{ $pc->name_pc }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($commissions as $commission)
                        <tr>
                            <td>{{ $commission->suppliercode }}</td>
                            <td>{{ $commission->store_id }}</td>
                            {{-- <td>{{ $commission->type_store }}</td> --}}
                            <td>{{ $commission->pro_model }}</td>
                            <td>{{ $commission->type_product }}</td>
                            <td>{{ number_format($commission->sale_amt, 0) }}</td>
                            <td>{{ number_format($commission->sale_amt_vat, 0) }}</td>
                            <td>{{ number_format($commission->total_sale_qty, 0) }}</td>
                            @foreach($pcs as $pc)
                                @php
                                    // Query sale_qty for this product model and PC
                                    // $pc_sale_qty = DB::table('tb_commission')
                                    //                  ->where('store_id', $commission->store_id)
                                    //                  ->where('pro_model', $commission->pro_model)
                                    //                  ->where('id_pc', $pc->id)
                                    //                  ->where('as_of_month', $var_month)
                                    //                  ->where('as_of_year', $year)
                                    //                  ->sum('sale_qty');
                                    $pc_sale_qty = DB::table('tb_commission')
                                        ->join('tb_pc', 'tb_commission.id_pc', '=', 'tb_pc.id') // Join tb_pc on id_pc
                                        ->where('tb_commission.store_id', $commission->store_id)
                                        ->where('tb_commission.pro_model', $commission->pro_model)
                                        ->where('tb_commission.id_pc', $pc->id)
                                        ->where('tb_commission.as_of_month', $var_month)
                                        ->where('tb_commission.as_of_year', $year)
                                        ->whereNull('tb_pc.status_pc') // Where status_pc is null
                                        ->sum('tb_commission.sale_qty');  // Sum sale_qty from tb_commission

                                @endphp
                                <td>
                                    <input type="number" name="pc_qty[{{ $commission->pro_model }}][{{ $pc->id }}]" value="{{ number_format($pc_sale_qty, 0) }}" class="sale-qty-input"/>
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
            
        @if(in_array('Edit_qty', $permissions))   
        <div align="center">
        <button type="submit" class="btn btn-primary" id="submit-btn">Update</button>
        </div>
        @endif
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const saleQtyInputs = document.querySelectorAll('.sale-qty-input');
            const submitBtn = document.getElementById('submit-btn');
        
            function validateQuantities() {
                let isValid = true;
        
                saleQtyInputs.forEach(input => {
                    const row = input.closest('tr');
                    const totalQty = parseFloat(row.querySelector('td:nth-child(7)').textContent.replace(',', '')) || 0;
                    let sumQty = 0;
        
                    row.querySelectorAll('.sale-qty-input').forEach(input => {
                        const value = parseFloat(input.value.replace(',', '')) || 0;
                        sumQty += value;
                    });

                    if (sumQty !== totalQty) {
                        row.style.backgroundColor = 'red';
                        isValid = false;
                    } else {
                        row.style.backgroundColor = '';
                    }
                });
        
                submitBtn.disabled = !isValid;
            }
        
            saleQtyInputs.forEach(input => {
                input.addEventListener('input', validateQuantities);
            });
        
            // Initial validation
            validateQuantities();
        });
        </script>
@endsection
