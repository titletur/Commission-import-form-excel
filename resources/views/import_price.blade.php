@extends('layouts.master')

@section('title', 'Home')
@section('home', 'active')

@section('content')

    <h1 align="center">Upload Product Price for {{ $month }} {{ $year }}</h1>
    <form action="{{ route('import.importprice') }}" id="import-form" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="var_year" value="{{ $year }}">
        <input type="hidden" name="var_month" value="{{ $var_month }}">
        <input type="hidden" name="short_month" value="{{ $month }}">
    
        <div class="form-group row align-items-center">
            <div class="col-md-4">&nbsp;</div>
            <div class="col-md-3">
                <label for="excel_file">Choose Excel File:</label>
                <input type="file" class="form-control" name="excel_file" required>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary mt-4">Import</button>
            </div>
            <div class="col-md-3">&nbsp;</div>
        </div>
    </form>
    
    <form action="{{ route('uploadprice') }}" method="POST">
        @csrf
        

        <input type="hidden" name="var_month" value="{{ $var_month }}">
        <input type="hidden" name="year" value="{{ $year }}">
        
        <div class="mb-3 text-right">
            <a href="{{ route('price.export', ['type' => 'excel']) }}" class="btn btn-success">Export to Excel</a>
            {{-- <a href="{{ route('stores.export', ['type' => 'pdf']) }}" target="_blank" class="btn btn-danger" >Export to PDF</a> --}}
        </div>
        <table id="data-table" class="table table-bordered">
            <thead>
                <tr>
                    <th>item number</th>
                    <th>barcode</th>
                    <th>description</th>
                    <th>type product</th>
                    <th>D1</th>
                    <th>D2</th>
                    <th>D3</th>
                    <th>D4</th>
                    <th>D5</th>
                    <th>D6</th>
                    <th>D7</th>
                    <th>D8</th>
                    <th>D9</th>
                    <th>D10</th>
                    <th>D11</th>
                    <th>D12</th>
                    <th>D13</th>
                    <th>D14</th>
                    <th>D15</th>
                    <th>D16</th>
                    <th>D17</th>
                    <th>D18</th>
                    <th>D19</th>
                    <th>D20</th>
                    <th>D21</th>
                    <th>D22</th>
                    <th>D23</th>
                    <th>D24</th>
                    <th>D25</th>
                    <th>D26</th>
                    <th>D27</th>
                    <th>D28</th>
                    <th>D29</th>
                    <th>D30</th>
                    <th>D31</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($product_prices as $price)

                    <tr>
                        <td align="left">{{ $price->item_number }}</td>
                        <td align="left">{{ $price->barcode }}</td>
                        <td align="left">{{ $price->item_des }}</td>
                        <td align="left">{{ $price->type_product }}</td>
                        @for ($i = 1; $i <= 31; $i++)
                            @php
                                $priceField = 'price_day' . $i;
                            @endphp
                            <td align="center">
                                {{-- <input type="text" style="width: 60px" name="price_day{{ $i }}[{{ $price->item_number }}]" value="{{ $price->$priceField }}"> --}}
                                {{ $price->$priceField }}
                            </td>
                        @endfor
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="text-center mt-3">
            {{-- <button type="submit" class="btn btn-success">Submit</button> --}}
        </div>
    </form>
    
    <script>
        $(document).ready(function() {
            $('#data-table').DataTable({
                paging: true,
                searching: true,
                ordering: true,
                info: true
            });
        });


        $(document).ready(function() {
            $(".chosen-select").chosen();
            $(".chosen-select").trigger("chosen:updated");
        });
        
    </script>

@endsection
