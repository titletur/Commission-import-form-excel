@extends('layouts.master')

@section('title', 'Home')
@section('home', 'active')

@section('content')

        @if (session('success'))
            <script>
                Swal.fire({
                    title: 'Success!',
                    text: "{{ session('success') }}",
                    icon: 'success',
                    confirmButtonText: 'OK'
                });
            </script>
        @endif
        @if ($errors->any())
            <script>
                Swal.fire({
                    title: 'Error!',
                    text: "{{ $errors->first('error') }}",
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            </script>
        @endif

    <h1 align="center">Commissions PC for {{ $month }} {{ $year }}</h1>
     <!-- ปุ่มสำหรับ Export -->
     <div class="mb-3 text-right">
        <a href="{{ route('commissions.export', ['month' => $var_month, 'show_month' =>$month, 'year' => $year, 'type' => 'excel']) }}" class="btn btn-success">
            <img src="{{ asset('exportexcel.png') }}" width="40" height="40" alt="Export"> 
        </a>
        <a href="{{ route('commissions.export', ['month' => $var_month, 'show_month' =>$month, 'year' => $year, 'type' => 'pdf']) }}" target="_blank" class="btn btn-danger" >
            <img src="{{ asset('exportpdf.png') }}" width="40" height="40" alt="Export">
        </a>
    </div>
        <table id="data-table" class="table table-bordered table-striped">
        {{-- <table class="table table-bordered table-striped"> --}}
            <thead>
                <tr align="center">
                    <th width="4%">Store</th>
                    <th width="2%">Type<br>Store</th>
                    <th width="8%">PC</th>
                    <th width="6%">Type PC</th>
                    <th width="4%">Salary</th>
                    <th width="6%">Sale<br>TV</th>
                    <th width="4%">QTY<br>TV</th>
                    <th width="6%">Sale<br>AV</th>
                    <th width="4%">QTY<br>AV</th>
                    <th width="6%">Sale<br>HA</th>
                    <th width="4%">QTY<br>HA</th>
                    <th width="8%">Sale Total</th>
                    <th width="4%">Target</th>
                    <th width="4%">Achieve</th>
                    <th width="3%">Com TV</th>
                    <th width="3%">Com AV</th>
                    <th width="3%">Com HA</th>
                    <th width="4%">Pay Com</th>
                    <th width="3%">Extra</th>
                    <th width="3%">Extra HA</th>
                    <th width="4%">Net Com</th>
                    <th width="3%">Advance</th>
                    <th width="4%">Net Pay</th>
                    <!-- เพิ่ม columns ตามที่ต้องการ -->
                </tr>
            </thead>
            <tbody>
                @foreach($commissions as $commission)
                    <tr>
                        {{-- <td align="center">{{ $commission->store_id }}</td> --}}
                        <td align="center">
                            <a href="{{ route('commissions.edit', ['store_id' => $commission->store_id, 'id_pc' => $commission->id_pc, 'month' => $month, 'var_month' => $var_month, 'year' => $year]) }}">
                                {{ $commission->store_id }}
                            </a>
                        </td>
                        <td align="center">{{ $commission->type_store }}</td>
                        <td>{{ $commission->name_pc }}</td>
                        <td align="center">{{ $commission->type_pc }}</td>
                        <td align="right">{{ number_format($commission->pc_salary,0) }}</td>
                        <td align="right">{{ number_format($commission->sale_tv,0) }}</td>
                        <td align="right">{{ number_format($commission->unit_tv,0) }}</td>
                        <td align="right">{{ number_format($commission->sale_av,0) }}</td>
                        <td align="right">{{ number_format($commission->unit_av,0) }}</td>
                        <td align="right">{{ number_format($commission->sale_ha,0) }}</td>
                        <td align="right">{{ number_format($commission->unit_ha,0) }}</td>
                        <td align="right">{{ number_format($commission->sale_total,0) }}</td>
                        <td align="right">{{ number_format($commission->tarket,0) }}</td>
                        <td align="center">{{ $commission->achieve }} %</td>
                        <td align="right">{{ number_format($commission->com_tv,0) }}</td>
                        <td align="right">{{ number_format($commission->com_av,0) }}</td>
                        <td align="right">{{ number_format($commission->com_ha,0) }}</td>
                        <td align="right">{{ number_format($commission->pay_com,0) }}</td>
                        <td align="right">{{ number_format($commission->extra_tv,0) }}</td>
                        <td align="right">{{ number_format($commission->extra_ha,0) }}</td>
                        <td align="right">{{ number_format($commission->net_com,0) }}</td>
                        <td align="right">{{ number_format($commission->advance_pay,0) }}</td>
                        <td align="right">{{ number_format($commission->net_pay,0) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="4">Total</th>
                    <th align="right">{{ number_format($totals['pc_salary'],0) }}</th>
                    <th align="right">{{ number_format($totals['sale_tv'],0) }}</th>
                    <th align="right">{{ number_format($totals['unit_tv'],0) }}</th>
                    <th align="right">{{ number_format($totals['sale_av'],0) }}</th>
                    <th align="right">{{ number_format($totals['unit_av'],0) }}</th>
                    <th align="right">{{ number_format($totals['sale_ha'],0) }}</th>
                    <th align="right">{{ number_format($totals['unit_ha'],0) }}</th>
                    <th align="right">{{ number_format($totals['sale_total'],0) }}</th>
                    <th align="right">{{ number_format($totals['tarket'],0) }}</th>
                    <th align="center">{{ number_format($totals['achieve'] / $totals['num_count'] ,0) }} %</th>
                    <th align="right">{{ number_format($totals['com_tv'],0) }}</th>
                    <th align="right">{{ number_format($totals['com_av'],0) }}</th>
                    <th align="right">{{ number_format($totals['com_ha'],0) }}</th>
                    <th align="right">{{ number_format($totals['pay_com'],0) }}</th>
                    <th align="right">{{ number_format($totals['extra_tv'],0) }}</th>
                    <th align="right">{{ number_format($totals['extra_ha'],0) }}</th>
                    <th align="right">{{ number_format($totals['net_com'],0) }}</th>
                    <th align="right">{{ number_format($totals['advance_pay'],0) }}</th>
                    <th align="right">{{ number_format($totals['net_pay'],0) }}</th>
                </tr>
            </tfoot>
        </table>

        <br><br>
        <h1 align="center">Commissions Sale for {{ $month }} {{ $year }}</h1>
        <hr>
        <form action="{{ route('update_commission_sale') }}" method="POST">
            @csrf
            <input type="hidden" name="month" value="{{ $month }}">
            <input type="hidden" name="var_month" value="{{ $var_month }}">
            <input type="hidden" name="year" value="{{ $year }}">

        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Target</th>
                    <th>Add Sale Out</th>
                    <th>Sale Out</th>
                    <th>Sale TV</th>
                    <th>Sale AV</th>
                    <th>Sale HA</th>
                    <th>Unit TV</th>
                    <th>Unit AV</th>
                    <th>Unit HA</th>
                    <th>Achieve</th>
                    <th>Base Com</th>
                    <th>Com</th>
                    <th>Extra Sale out</th>
                    <th>Extra Unit</th>
                    <th>Extra AVG</th>
                    <th>Other</th>
                    <th>Total</th>
                    <th>Remark</th>
                </tr>
            </thead>
            <tbody>
                @foreach($commissions_sale as $commissionSale)
                    <tr>
                        <td>{{ $commissionSale->id }}</td>
                        <td>{{ $commissionSale->code_sale }}</td>
                        <td>{{ $commissionSale->name_sale }}</td>
                        <td>{{ number_format($commissionSale->target,0) }}</td>
                        <td><input type="text" name="add_total[{{ $commissionSale->id }}]" value="{{ number_format($commissionSale->add_total,0) }}"  style="width: 100px;"></td>
                        <td>{{ number_format($commissionSale->sale_out,0) }}</td>
                        <td>{{ number_format($commissionSale->sale_tv,0) }}</td>
                        <td>{{ number_format($commissionSale->sale_av,0) }}</td>
                        <td>{{ number_format($commissionSale->sale_ha,0) }}</td>
                        <td>{{ number_format($commissionSale->unit_tv,0) }}</td>
                        <td>{{ number_format($commissionSale->unit_av,0) }}</td>
                        <td>{{ number_format($commissionSale->unit_ha,0) }}</td>
                        <td>{{ number_format($commissionSale->achieve,0) }}%</td>
                        <td>{{ number_format($commissionSale->base_com,0) }}</td>
                        <td>{{ number_format($commissionSale->com_sale,0) }}</td>
                        <td>{{ number_format($commissionSale->extra_sale_out,0) }}</td>
                        <td>{{ number_format($commissionSale->extra_unit,0) }}</td>
                        <td>{{ number_format($commissionSale->extra_avg,0) }}</td>
                        <td><input type="text" name="other[{{ $commissionSale->id }}]" value="{{ number_format($commissionSale->other,0) }}"  style="width: 100px;"></td>
                        <td>{{ number_format($commissionSale->total,0) }}</td>
                        <td><input type="text" name="remark[{{ $commissionSale->id }}]" value="{{ $commissionSale->remark }}"></td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="3" align="center">Sum Total</th>
                    <th>{{ number_format($totals_sale['target'],0) }}</th>
                    <th></th>
                    <th>{{ number_format($totals_sale['sale_out'],0) }}</th>
                    <th>{{ number_format($totals_sale['sale_tv'],0) }}</th>
                    <th>{{ number_format($totals_sale['sale_av'],0) }}</th>
                    <th>{{ number_format($totals_sale['sale_ha'],0) }}</th>
                    <th>{{ number_format($totals_sale['unit_tv'],0) }}</th>
                    <th>{{ number_format($totals_sale['unit_av'],0) }}</th>
                    <th>{{ number_format($totals_sale['unit_ha'],0) }}</th>
                    <th>{{ number_format($totals_sale['achieve'] / $totals_sale['num_count'],0) }}%</th>
                    <th>{{ number_format($totals_sale['base_com'],0) }}</th>
                    <th>{{ number_format($totals_sale['com_sale'],0) }}</th>
                    <th>{{ number_format($totals_sale['extra_sale_out'],0) }}</th>
                    <th>{{ number_format($totals_sale['extra_unit'],0) }}</th>
                    <th>{{ number_format($totals_sale['extra_avg'],0) }}</th>
                    <th>{{ number_format($totals_sale['other'],0) }}</th>
                    <th>{{ number_format($totals_sale['total'],0) }}</th>
                    <th></th>
                </tr>
            </tfoot>
        </table>
            <div align="center">
            <button type="submit" class="btn btn-primary">Update</button>
            </div>
        </form>
        <br><br><br>
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
@endsection