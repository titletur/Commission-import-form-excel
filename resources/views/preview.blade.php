@extends('layouts.master')

@section('title', 'Home')
@section('home', 'active')

@section('content')

<div class="container mt-5">
    <h2 class="text-center">Preview Data for {{ $short_month }} {{ $var_year }}</h2>
    <form action="{{ route('import.store') }}" method="post" id="import-form">
        @csrf
        <div class="table-responsive">
            <table id="data-table" class="table table-bordered">
                <thead>
                    <tr>
                        <th>Report Code</th>
                        <th>Supplier Code</th>
                        <th>Business Format</th>
                        <th>Compare</th>
                        <th>Store ID</th>
                        <th>Store</th>
                        <th>As of Month</th>
                        <th>As of Year</th>
                        <th>Last Year Compare Month</th>
                        <th>Report Date</th>
                        <th>Division</th>
                        <th>Department</th>
                        <th>Subdepartment</th>
                        <th>Pro Class</th>
                        <th>Sub Pro Class</th>
                        <th>Barcode</th>
                        <th>Article</th>
                        <th>Article Name</th>
                        <th>Brand</th>
                        <th>Pro Model</th>
                        <th>Sale Amt TY</th>
                        <th>Sale Amt LY</th>
                        <th>Sale Amt Var</th>
                        <th>Sale Qty TY</th>
                        <th>Sale Qty LY</th>
                        <th>Sale Qty Var</th>
                        <th>Stock TY</th>
                        <th>Stock LY</th>
                        <th>Stock Var</th>
                        <th>Stock Qty TY</th>
                        <th>Stock Qty LY</th>
                        <th>Stock Qty Var</th>
                        <th>Day on Hand TY</th>
                        <th>Day on Hand LY</th>
                        <th>Day on Hand Diff</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data as $index => $row)
                        <tr>
                            <td>{{ $row['report_code'] }}</td>
                            <td>{{ $row['suppliercode'] }}</td>
                            <td>{{ $row['business_format'] }}</td>
                            <td>{{ $row['compare'] }}</td>
                            <td>{{ $row['store_id'] }}</td>
                            <td>{{ $row['store'] }}</td>
                            <td>{{ $row['as_of_month'] }}</td>
                            <td>{{ $row['as_of_year'] }}</td>
                            <td>{{ $row['last_year_compare_month'] }}</td>
                            <td>{{ $row['report_date'] }}</td>
                            <td>{{ $row['division'] }}</td>
                            <td>{{ $row['department'] }}</td>
                            <td>{{ $row['subdepartment'] }}</td>
                            <td>{{ $row['pro_Class'] }}</td>
                            <td>{{ $row['sub_pro_class'] }}</td>
                            <td>{{ $row['barcode'] }}</td>
                            <td>{{ $row['article'] }}</td>
                            <td>{{ $row['article_name'] }}</td>
                            <td>{{ $row['brand'] }}</td>
                            <td>{{ $row['pro_model'] }}</td>
                            <td>{{ $row['sale_amt_ty'] }}</td>
                            <td>{{ $row['sale_amt_ly'] }}</td>
                            <td>{{ $row['sale_amt_var'] }}</td>
                            <td>{{ $row['sale_qty_ty'] }}</td>
                            <td>{{ $row['sale_qty_ly'] }}</td>
                            <td>{{ $row['sale_qty_var'] }}</td>
                            <td>{{ $row['stock_ty'] }}</td>
                            <td>{{ $row['stock_ly'] }}</td>
                            <td>{{ $row['stock_var'] }}</td>
                            <td>{{ $row['stock_qty_ty'] }}</td>
                            <td>{{ $row['stock_qty_ly'] }}</td>
                            <td>{{ $row['stock_qty_var'] }}</td>
                            <td>{{ $row['day_on_hand_ty'] }}</td>
                            <td>{{ $row['day_on_hand_ly'] }}</td>
                            <td>{{ $row['day_on_hand_diff'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <input type="hidden" name="var_year" value="{{ $var_year }}">
        <input type="hidden" name="var_month" value="{{ $var_month }}">
        <input type="hidden" name="short_month" value="{{ $short_month }}">

        <input type="hidden" name="data" value="{{ json_encode($data) }}">
        <div class="text-center">
            <button type="submit" class="btn btn-success">Confirm and Calculate Commission</button>
        </div>
    </form>
</div>
<script>
    $(document).ready(function() {
        $('#data-table').DataTable({
            paging: true,
            searching: true,
            ordering: true,
            info: true
        });
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('import-form');
            const loadingOverlay = document.getElementById('loading-overlay');

            // Show the loading overlay when the form is submitted
            form.addEventListener('submit', function () {
                loadingOverlay.style.display = 'flex';
            });
        });
</script>

@endsection


