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
                        <th>supplier</th>
                        <th>tdate</th>
                        <th>as_of_month</th>
                        <th>as_of_year</th>
                        <th>sub_dept</th>
                        <th>sub_dept_name</th>
                        <th>store_id</th>
                        <th>store</th>
                        <th>skucode</th>
                        <th>pro_model</th>
                        <th>pro_name</th>
                        <th>item_status</th>
                        <th>atb_code</th>
                        <th>distributemethod</th>
                        <th>amount</th>
                        <th>dcavail</th>
                        <th>stock</th>
                        <th>poondalivery</th>
                        <th>toondalivery</th>
                        <th>sale_qty</th>
                        <th>sale_amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data as $index => $row)
                        <tr>
                            <td>{{ $row['supplier'] }}</td>
                            <td>{{ $row['tdate'] }}</td>
                            <td>{{ $row['as_of_month'] }}</td>
                            <td>{{ $row['as_of_year'] }}</td>
                            <td>{{ $row['sub_dept'] }}</td>
                            <td>{{ $row['sub_dept_name'] }}</td>
                            <td>{{ $row['store_id'] }}</td>
                            <td>{{ $row['store'] }}</td>
                            <td>{{ $row['skucode'] }}</td>
                            <td>{{ $row['pro_model'] }}</td>
                            <td>{{ $row['pro_name'] }}</td>
                            <td>{{ $row['item_status'] }}</td>
                            <td>{{ $row['atb_code'] }}</td>
                            <td>{{ $row['distributemethod'] }}</td>
                            <td>{{ $row['amount'] }}</td>
                            <td>{{ $row['dcavail'] }}</td>
                            <td>{{ $row['stock'] }}</td>
                            <td>{{ $row['poondalivery'] }}</td>
                            <td>{{ $row['toondalivery'] }}</td>
                            <td>{{ $row['sale_qty'] }}</td>
                            <td>{{ $row['sale_amount'] }}</td>
                            
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


