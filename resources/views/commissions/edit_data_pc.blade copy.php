@extends('layouts.master')

@section('title', 'Edit Commission Data')

@section('content')
    <h1 class="text-center">Edit Commission PC for {{ $month }} {{ $year }}</h1>
   <hr>

   <form action="{{ route('update_commission') }}" method="POST">
    @csrf
   <font size="+2">
    <div class="row mb-4" align="center">
        <div class="col-md-2">
            <strong>Code:</strong> {{ $pc_info->code_pc }}
        </div>
        <div class="col-md-2">
            <strong>Name:</strong> {{ $pc_info->name_pc }}
        </div>
        <div class="col-md-2">
            <strong>Type:</strong> {{ $pc_info->type_pc }}
        </div>
        <div class="col-md-2">
            <strong>Target:</strong> {{ number_format($pc_info->tarket, 0) }}
        </div>
        <div class="col-md-2">
            <strong>Salary:</strong> {{ number_format($pc_info->salary, 0) }}
        </div>
        <div class="col-md-2">
            <strong>Advance:</strong> <input type="Text" name="advance_pay" style="width: 150px;" value="{{ number_format($main_commission->advance_pay, 0) }}">
        </div>
    </div>
   </font>
    
        <input type="hidden" name="id_pc" value="{{ $pc_info->id }}">
        <input type="hidden" name="month" value="{{ $month }}">
        <input type="hidden" name="var_month" value="{{ $var_month }}">
        <input type="hidden" name="year" value="{{ $year }}">

        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Supplier Code</th>
                    <th>Store</th>
                    <th>Type Store</th>
                    <th>Product Model</th>
                    <th>Type Product</th>
                    <th>Sale Amount</th>
                    <th>Sale Amount VAT</th>
                    <th>Sale Quantity</th>
                    <th>Commission</th>
                </tr>
            </thead>
            <tbody>
                @foreach($commissions as $commission)
                <tr>
                    <input type="hidden" name="id[]" value="{{ $commission->id }}">
                    <td>{{ $commission->suppliercode }}</td>
                    <td>{{ $commission->store_id }}</td>
                    <td>{{ $commission->type_store }}</td>
                    <td>{{ $commission->pro_model }}</td>
                    <td>{{ $commission->type_product }}</td>
                    <td>{{ number_format($commission->sale_amt, 0) }}</td>
                    <td>{{ number_format($commission->sale_amt_vat, 0) }}</td>
                    <td>
                        <input type="text" name="sale_qty[]" value="{{ number_format($commission->sale_qty, 0) }}" class="form-control">
                    </td>
                    <td>
                        <input type="text" name="com[]" value="{{ number_format($commission->com, 0) }}" class="form-control">
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div align="center">
        <button type="submit" class="btn btn-primary">Update</button>
        </div>
    </form>
@endsection