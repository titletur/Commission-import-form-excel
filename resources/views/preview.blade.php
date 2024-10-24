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
                        <th>supplier_number</th>
                        <th>location_number</th>
                        <th>location_name</th>
                        <th>class_number</th>
                        <th>sub_class</th>
                        <th>item_number</th>
                        <th>barcode</th>
                        <th>as_of_month</th>
                        <th>as_of_year</th>
                        <th>item_des</th>
                        <th>eoh_qty</th>
                        <th>on_order</th>
                        <th>pack_type</th>
                        <th>unit</th>
                        <th>avg_net_sale_qty</th>
                        <th>net_sale_qty_ytd</th>
                        <th>last_receved_date</th>
                        <th>last_sold_date</th>
                        <th>stock_cover_day</th>
                        <th>net_sale_qty_mtd</th>
                        <th>day1</th>
                        <th>day2</th>
                        <th>day3</th>
                        <th>day4</th>
                        <th>day5</th>
                        <th>day6</th>
                        <th>day7</th>
                        <th>day8</th>
                        <th>day9</th>
                        <th>day10</th>
                        <th>day11</th>
                        <th>day12</th>
                        <th>day13</th>
                        <th>day14</th>
                        <th>day15</th>
                        <th>day16</th>
                        <th>day17</th>
                        <th>day18</th>
                        <th>day19</th>
                        <th>day20</th>
                        <th>day21</th>
                        <th>day22</th>
                        <th>day23</th>
                        <th>day24</th>
                        <th>day25</th>
                        <th>day26</th>
                        <th>day27</th>
                        <th>day28</th>
                        <th>day29</th>
                        <th>day30</th>
                        <th>day31</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data as $index => $row)
                        <tr>
                            <td>{{ $row['supplier_number'] }}</td>
                            <td>{{ $row['location_number'] }}</td>
                            <td>{{ $row['location_name'] }}</td>
                            <td>{{ $row['class_number'] }}</td>
                            <td>{{ $row['sub_class'] }}</td>
                            <td>{{ $row['item_number'] }}</td>
                            <td>{{ $row['barcode'] }}</td>
                            <td>{{ $row['as_of_month'] }}</td>
                            <td>{{ $row['as_of_year'] }}</td>
                            <td>{{ $row['item_des'] }}</td>
                            <td>{{ $row['eoh_qty'] }}</td>
                            <td>{{ $row['on_order'] }}</td>
                            <td>{{ $row['pack_type'] }}</td>
                            <td>{{ $row['unit'] }}</td>
                            <td>{{ $row['avg_net_sale_qty'] }}</td>
                            <td>{{ $row['net_sale_qty_ytd'] }}</td>
                            <td>{{ $row['last_receved_date'] }}</td>
                            <td>{{ $row['last_sold_date'] }}</td>
                            <td>{{ $row['stock_cover_day'] }}</td>
                            <td>{{ $row['net_sale_qty_mtd'] }}</td>
                            <td>{{ $row['day1'] }}</td>
                            <td>{{ $row['day2'] }}</td>
                            <td>{{ $row['day3'] }}</td>
                            <td>{{ $row['day4'] }}</td>
                            <td>{{ $row['day5'] }}</td>
                            <td>{{ $row['day6'] }}</td>
                            <td>{{ $row['day7'] }}</td>
                            <td>{{ $row['day8'] }}</td>
                            <td>{{ $row['day9'] }}</td>
                            <td>{{ $row['day10'] }}</td>
                            <td>{{ $row['day11'] }}</td>
                            <td>{{ $row['day12'] }}</td>
                            <td>{{ $row['day13'] }}</td>
                            <td>{{ $row['day14'] }}</td>
                            <td>{{ $row['day15'] }}</td>
                            <td>{{ $row['day16'] }}</td>
                            <td>{{ $row['day17'] }}</td>
                            <td>{{ $row['day18'] }}</td>
                            <td>{{ $row['day19'] }}</td>
                            <td>{{ $row['day20'] }}</td>
                            <td>{{ $row['day21'] }}</td>
                            <td>{{ $row['day22'] }}</td>
                            <td>{{ $row['day23'] }}</td>
                            <td>{{ $row['day24'] }}</td>
                            <td>{{ $row['day25'] }}</td>
                            <td>{{ $row['day26'] }}</td>
                            <td>{{ $row['day27'] }}</td>
                            <td>{{ $row['day28'] }}</td>
                            <td>{{ $row['day29'] }}</td>
                            <td>{{ $row['day30'] }}</td>
                            <td>{{ $row['day31'] }}</td>
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


