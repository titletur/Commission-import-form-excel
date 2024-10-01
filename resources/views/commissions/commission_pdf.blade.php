<!DOCTYPE html>
<html>
<head>

    {{-- <link rel="stylesheet" href="{{ asset('css/cssfont.css') }}"> --}}
    <style>
        body {
            font-family: 'freeserif', 'THSarabunNew';
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 4px;
            text-align: left;
            word-wrap: break-word;
            overflow: hidden;
            white-space: nowrap;
        }
        th {
            background-color: #f2f2f2;
        }
        td {
            font-size: 12px;
            word-wrap: break-word;
            overflow: hidden;
            white-space: nowrap;
        }
        .page-break {
        page-break-before: always;
        }
        .text-center {
            text-align: center;
        }
        .no-border-table, .no-border-table td {
        border: none;
        }
    </style>
</head>
<body>

    <h2 class="text-center">Commissions PC for {{ $show_month }} {{ $year }}</h2>
    <table>
            <thead>
                <tr align="center">
                    <th width="4%" rowspan="2" align="center">Store</th>
                    <th width="4%" rowspan="2" align="center">Code</th> 
                    <th width="8%" rowspan="2" align="center">PC</th>
                    <th width="3%" rowspan="2" align="center">Store</th> 
                    <th width="4%" rowspan="2" align="center">Type PC</th>
                    <th width="4%" rowspan="2" align="center">Sale TV</th>
                    <th width="4%" rowspan="2" align="center">Sale AV</th>
                    <th width="4%" rowspan="2" align="center">Sale HA</th>
                    <th width="6%" rowspan="2" align="center">Sale Total</th>
                    <th width="4%" rowspan="2" align="center">Target</th>
                    <th width="5%" rowspan="2" align="center">Achieve<br>TV+AV %</th> 
                    <th width="4%" rowspan="2" align="center">Normal<br>ComTV+AV</th> 
                    <th width="3%" rowspan="2" align="center">ComTV+AV</th> 
                    <th width="3%" rowspan="2" align="center">Com HA</th>
                    <th width="3%" rowspan="2" align="center">Pay<br>Com</th>
                    <th width="3%" rowspan="2" align="center">Extra</th> 
                    <th width="3%" rowspan="2" align="center">Other</th> 
                    <th width="3%" rowspan="2" align="center">Net<br>Com</th>
                    <th width="3%" rowspan="2" align="center">Advance</th> 
                    <th width="3%" rowspan="2" align="center">Net Pay</th> 
                    <th width="3%" rowspan="2" align="center">Com %</th> 
                    <th width="4%" rowspan="2" align="center">Salary</th> 
                    <th width="9%" colspan="3" align="center">com+salary/achieve</th> 
                    <th width="5%" rowspan="2" align="center">Remark</th> 
                </tr>
                <tr align="center">
                    <th width="3%">{{ $currentMonthName->short_en }}</th> 
                    <th width="3%">{{ $previousMonthName1->short_en }}</th> 
                    <th width="3%">{{ $previousMonthName2->short_en }}</th> 
                </tr>
            </thead>
        <tbody>
            @php
                $sum_sale_tv = 0;
                $sum_sale_av = 0;
                $sum_sale_ha = 0;
                $sum_sale_total = 0;
                $sum_target = 0;
                $sum_normal_com = 0;
                $sum_com_tv_av = 0;
                $sum_com_ha = 0;
                $sum_pay_com = 0;
                $sum_extra = 0;
                $sum_other = 0;
                $sum_net_com = 0;
                $sum_advance = 0;
                $sum_net_pay = 0;
                $sum_salary = 0;
                $count = count($commissions);
                $sum_percent_achieve = 0;
                $sum_percent_com = 0;
                $sum_percent_com0 = 0;
                $sum_percent_com1 = 0;
                $sum_percent_com2 = 0;
                
            @endphp

            @foreach($commissions as $commission)
            @php
                $pcs = DB::table('tb_pc')
                        ->whereNull('status_pc')
                        ->where('id', $commission->id_pc)
                        ->first();

                // เก็บผลรวมของคอลัมน์ต่างๆ
                $sum_sale_tv += $commission->sale_tv;
                $sum_sale_av += $commission->sale_av;
                $sum_sale_ha += $commission->sale_ha;
                $sum_sale_total += $commission->sale_total;
                $sum_target += $commission->tarket;
                $sum_normal_com += $commission->normalcom_tv + $commission->normalcom_av;
                $sum_com_tv_av += $commission->com_tv + $commission->com_av;
                $sum_com_ha += $commission->com_ha;
                $sum_pay_com += $commission->pay_com;
                $sum_extra += $commission->extra_tv + $commission->extra_ha;
                $sum_other += $commission->other;
                $sum_net_com += $commission->net_com;
                $sum_advance += $commission->advance_pay;
                $sum_net_pay += $commission->net_pay;
                $sum_salary += $commission->pc_salary;
                $sum_percent_achieve += $commission->tarket != 0 ? ($commission->sale_total / $commission->tarket) * 100 : 0;
                $sum_percent_com += $commission->net_com != 0 ? ($commission->net_com / $commission->sale_total) * 100: 0;
                

                
               
            @endphp
            <tr>
                <td align="center">{{ $commission->store_id }}</td> 
                <td>{{ $pcs->code_pc }}</td> 
                <td>{{ $commission->name_pc }}</td> 
                <td align="center">{{ $commission->type_store }}</td> 
                <td align="center">{{ $commission->type_pc }}</td> 
                <td align="right">{{ number_format($commission->sale_tv,0) }}</td> 
                <td align="right">{{ number_format($commission->sale_av,0) }}</td> 
                <td align="right">{{ number_format($commission->sale_ha,0) }}</td> 
                <td align="right">{{ number_format($commission->sale_total,0) }}</td> 
                <td align="right">{{ number_format($commission->tarket,0) }}</td> 
                <td align="right">{{ $commission->tarket != 0 ? number_format((($commission->sale_tv+ $commission->sale_av) / $commission->tarket) * 100, 2) : '0' }} %</td> 
                <td align="right">{{ number_format($commission->normalcom_tv + $commission->normalcom_av, 0) }}</td> 
                <td align="right">{{ number_format($commission->com_tv + $commission->com_av, 0) }}</td> 
                <td align="right">{{ number_format($commission->com_ha,0) }}</td> 
                <td align="right">{{ number_format($commission->pay_com,0) }}</td> 
                <td align="right">{{ number_format($commission->extra_tv + $commission->extra_ha ,0) }}</td> 
                <td align="right">{{ number_format($commission->other, 0) }}</td> 
                <td align="right">{{ number_format($commission->net_com,0) }}</td> 
                <td align="right">{{ number_format($commission->advance_pay,0) }}</td> 
                <td align="right">{{ number_format($commission->net_pay,0) }}</td> 
                <td align="right">{{ $commission->net_com != 0 ? number_format(($commission->net_com / $commission->sale_total) * 100 ,2) : '0'}} %</td> 
                <td align="right">{{ number_format($commission->pc_salary,0) }}</td> 
                @php
                    $commissions_previous1 = DB::table('tb_main_commission')
                        ->where('as_of_year', $year)
                        ->where('as_of_month', $previousMonthName1->var_month)
                        ->where('id_pc', $commission->id_pc)
                        ->first();
                        $net_com1 = optional($commissions_previous1)->net_com ?? 0;
                        $pc_salary1 = optional($commissions_previous1)->pc_salary ?? 0;
                        $sale_total1 = optional($commissions_previous1)->sale_total ?? 0;
                        
                        
                    $commissions_previous2 = DB::table('tb_main_commission')
                        ->where('as_of_year', $year)
                        ->where('as_of_month', $previousMonthName2->var_month)
                        ->where('id_pc', $commission->id_pc)
                        ->first();
                        $net_com2 = optional($commissions_previous2)->net_com ?? 0;
                        $pc_salary2 = optional($commissions_previous2)->pc_salary ?? 0;
                        $sale_total2 = optional($commissions_previous2)->sale_total ?? 0;

                    $sum_percent_com0 += $commission->sale_total != 0 ? (($commission->net_com + $commission->pc_salary) / $commission->sale_total) * 100: 0;
                    $sum_percent_com1 += $sale_total1 != 0 ? (($net_com1 + $pc_salary1) / $sale_total1) * 100: 0;
                    $sum_percent_com2 += $sale_total2 != 0 ? (($net_com2 + $pc_salary2)/ $sale_total2) * 100: 0;
                @endphp
                <td align="right">{{ $commission->sale_total != 0 ? number_format((($commission->net_com + $commission->pc_salary) / $commission->sale_total) * 100, 2) : '0' }} %</td> 
                <td align="right">{{ $sale_total1 != 0 ? number_format((($net_com1 + $pc_salary1) / $sale_total1) * 100, 2) : '0' }} %</td>
                <td align="right">{{ $sale_total2 != 0 ? number_format((($net_com2 + $pc_salary2) / $sale_total2) * 100, 2) : '0' }} %</td>
                <td >{{ $commission->remark }}</td> 
            </tr>
            @endforeach
            <tr>
                <td colspan="5" align="center"><strong>Sum Total</strong></td>
                <td align="right">{{ number_format($sum_sale_tv, 0) }}</td>
                <td align="right">{{ number_format($sum_sale_av, 0) }}</td>
                <td align="right">{{ number_format($sum_sale_ha, 0) }}</td>
                <td align="right">{{ number_format($sum_sale_total, 0) }}</td>
                <td align="right">{{ number_format($sum_target, 0) }}</td>
                <td align="center">{{ number_format($sum_percent_achieve / $count, 2) }} %</td>
                <td align="right">{{ number_format($sum_normal_com, 0) }}</td>
                <td align="right">{{ number_format($sum_com_tv_av, 0) }}</td>
                <td align="right">{{ number_format($sum_com_ha, 0) }}</td>
                <td align="right">{{ number_format($sum_pay_com, 0) }}</td>
                <td align="right">{{ number_format($sum_extra, 0) }}</td>
                <td align="right">{{ number_format($sum_other, 0) }}</td>
                <td align="right">{{ number_format($sum_net_com, 0) }}</td>
                <td align="right">{{ number_format($sum_advance, 0) }}</td>
                <td align="right">{{ number_format($sum_net_pay, 0) }}</td>
                <td align="right">{{ number_format($sum_percent_com / $count, 2) }} %</td>
                <td align="right">{{ number_format($sum_salary, 0) }}</td>
                <td align="right">{{ number_format($sum_percent_com0 / $count, 2) }}%</td>
                <td align="right">{{ number_format($sum_percent_com1 / $count, 2) }}%</td>
                <td align="right">{{ number_format($sum_percent_com2 / $count, 2) }}%</td>
                <td align="right"></td>
            </tr>
        </tbody>
    </table>

    <div class="page-break"></div>
    <h2 class="text-center">Commissions Sale for {{ $show_month }} {{ $year }}</h2>
    <table style="width: 100%; table-layout: fixed; border: none;" class="no-border-table">
        <tr>
            <td style="width: 30%; padding-right: 10px; vertical-align: top;">
                <table style="width: 80%; border: 1px solid black;">
                    <thead>
                        <tr align="center">
                            <th width="40%" align="center">#</th>
                            <th width="20%" align="center">Net Sale IN</th> 
                            <th width="20%" align="center">Sale Out</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $query_total_sum = DB::table('tb_main_commission')
                            ->select(
                                DB::raw('SUM(sale_total) as total_sale'), 
                            )
                            ->where('as_of_year', $year)
                            ->where('as_of_month', $month)
                            ->first();
        
                            $query_sale_in_sum = DB::table('sales_in')
                            ->select(
                                DB::raw('SUM(sale_in) as sale_in'), 
                            )
                            ->where('year', $year)
                            ->where('month', $month)
                            ->first();
                        @endphp 
                        <tr style="border: 1px solid black;">
                            <td style="border: 1px solid black;">{{ $currentMonthName->short_en }}</td>
                            <td style="border: 1px solid black;" align="right">{{ number_format($query_sale_in_sum->sale_in,0) }}</td>
                            <td style="border: 1px solid black;" align="right">{{ number_format($query_total_sum->total_sale,0) }}</td>
                        </tr>
                        @php
                        $query_total_sum_year = DB::table('tb_main_commission')
                            ->select(DB::raw('SUM(sale_total) as total_sale'))
                            ->where(function($query) use ($year) {
                                $query->where(function($query) use ($year) {
                                    // ช่วงเดือน 04 ถึง 12 ของปีปัจจุบัน
                                    $query->where('as_of_year', $year)
                                        ->whereBetween('as_of_month', [4, 12]);
                                })->orWhere(function($query) use ($year) {
                                    // ช่วงเดือน 01 ถึง 03 ของปีถัดไป
                                    $query->where('as_of_year', $year + 1)
                                        ->whereBetween('as_of_month', [1, 3]);
                                });
                            })
                            ->first();
        
                            $query_sale_in_sum_year = DB::table('sales_in')
                            ->select(DB::raw('SUM(sale_in) as sale_in_total'))
                            ->where(function($query) use ($year) {
                                $query->where(function($query) use ($year) {
                                    // ช่วงเดือน 04 ถึง 12 ของปีปัจจุบัน
                                    $query->where('year', $year)
                                        ->whereBetween('month', [4, 12]);
                                })->orWhere(function($query) use ($year) {
                                    // ช่วงเดือน 01 ถึง 03 ของปีถัดไป
                                    $query->where('year', $year + 1)
                                        ->whereBetween('month', [1, 3]);
                                });
                            })
                            ->first();
                            
                    @endphp
                    <tr style="border: 1px solid black;">
                        <td style="border: 1px solid black;">Sum All {{ $year }}</td>
                        <td style="border: 1px solid black;" align="right">{{ number_format($query_sale_in_sum_year->sale_in_total, 0) }}</td>
                        <td style="border: 1px solid black;" align="right">{{ number_format($query_total_sum_year->total_sale, 0) }}</td>
                    </tr>
                    </tbody>
                </table>
            </td>
            <td style="width: 70%; padding-left: 10px; vertical-align: top;" align="center">
                <table style="width: 80%;">
                    <thead>
                        <tr>
                            <th width="35%" align="center">ประเภทพนักงาน</th>
                            <th width="15%" align="center">Achieve</th> 
                            <th width="10%" align="center">Com</th>
                            <th width="10%" align="center">% Com</th>
                            <th width="10%" align="center">จำนวนคน</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $query_type_pc = DB::table('tb_main_commission')
                            ->select(
                                'type_pc', 
                                DB::raw('SUM(sale_total) as total_sale'), 
                                DB::raw('SUM(net_com) as total_com'), 
                                DB::raw('COUNT(id_pc) as count_pc')
                            )
                            ->where('as_of_year', $year)
                            ->where('as_of_month', $month)
                            ->groupBy('type_pc')
                            ->get();
        
                            $sumSaleTotal = 0;
                            $sumCom = 0;
                            $sumCountPc = 0;
                        @endphp 
                            @foreach ($query_type_pc as $data) 
                        @php
                                $achieve = $data->total_sale;  
                                $com = $data->total_com;      
                                $countPc = $data->count_pc;    
                                $percentCom = ($achieve != 0) ? ($com / $achieve) * 100 : 0; // % ค่าคอมมิชชั่น
                        @endphp 
                    <tr style="border: 1px solid black;">
                        <td style="border: 1px solid black;">{{ $data->type_pc }}</td>
                        <td style="border: 1px solid black;" align="right">{{ number_format($achieve , 0) }}</td>
                        <td style="border: 1px solid black;" align="right">{{ number_format($com , 0) }}</td>
                        <td style="border: 1px solid black;" align="right">{{ number_format($percentCom , 2) }}%</td>
                        <td style="border: 1px solid black;" align="right">{{ number_format($countPc , 0) }}</td>
                    </tr>
                    @php
                        $sumSaleTotal += $achieve;
                        $sumCom += $com;
                        $sumCountPc += $countPc;
                    @endphp
                    @endforeach
                    @php
                    $sumpercentCom = ($sumSaleTotal != 0) ? ($sumCom / $sumSaleTotal) * 100 : 0; // % ค่าคอมมิชชั่น
                    @endphp
                        <tr style="border: 1px solid black;">
                            <td style="border: 1px solid black;" align="center">Sum Total</td>
                            <td style="border: 1px solid black;" align="right">{{ number_format($sumSaleTotal , 0) }}</td>
                            <td style="border: 1px solid black;" align="right">{{ number_format($sumCom , 0) }}</td>
                            <td style="border: 1px solid black;" align="right">{{ number_format($sumpercentCom , 2) }}%</td>
                            <td style="border: 1px solid black;" align="right">{{ number_format($sumCountPc , 0) }}</td>
                        </tr>
                    </tbody>
                </table>
            </td>
        </tr>
    </table>
        
        <br><br>

            <table>
                <thead>
                    <tr align="center">
                        <th width="3%" align="center">No</th>
                        <th width="5%" align="center">Code</th> 
                        <th width="15%" align="center">Name</th>
                        <th width="7%" align="center">Target</th> 
                        <th width="7%" align="center">Sale Out</th>
                        <th width="7%" align="center">Achieve</th>
                        <th width="7%" align="center">Base Com</th>
                        <th width="6%" align="center">Com</th>
                        <th width="7%" align="center">Extra <br>Sale Out</th>
                        <th width="7%" align="center">Extra <br>Unit</th>
                        <th width="6%" align="center">Extra <br>AVG</th>
                        <th width="5%" align="center">Other</th> 
                        <th width="7%" align="center">Total</th>
                        <th width="10%" align="center">Remark</th> 
                    </tr>
                </thead>
            <tbody>
                @php
                    $num_row =1;
                    $sum_target = 0;
                    $sum_sale_out = 0;
                    $sum_achieve = 0;
                    $sum_base_com = 0;
                    $sum_com_sale = 0;
                    $sum_extra_sale_out = 0;
                    $sum_extra_unit = 0;
                    $sum_extra_avg = 0;
                    $sum_other = 0;
                    $sum_total = 0;
                    $count_sale = 0;
                @endphp
            @foreach ($commissions_sale as $commissionSale) 
                @php
                    // คำนวณยอดรวมแต่ละฟิลด์
                    $sum_target += $commissionSale->target;
                    $sum_sale_out += $commissionSale->sale_out;
                    $sum_achieve += $commissionSale->achieve;
                    $sum_base_com += $commissionSale->base_com;
                    $sum_com_sale += $commissionSale->com_sale;
                    $sum_extra_sale_out += $commissionSale->extra_sale_out;
                    $sum_extra_unit += $commissionSale->extra_unit;
                    $sum_extra_avg += $commissionSale->extra_avg;
                    $sum_other += $commissionSale->other;
                    $sum_total += $commissionSale->total;
                    $count_sale++;
                @endphp
            <tr>
                <td align="Right">{{ $num_row++; }}</td> 
                <td align="left">{{ $commissionSale->code_sale }}</td> 
                <td align="left">{{ $commissionSale->name_sale }}</td> 
                <td align="Right">{{ number_format($commissionSale->target ,0) }}</td> 
                <td align="Right">{{ number_format($commissionSale->sale_out ,0) }}</td> 
                <td align="Right">{{ number_format($commissionSale->achieve,2) }}%</td> 
                <td align="Right">{{ number_format($commissionSale->base_com,0) }}</td> 
                <td align="Right">{{ number_format($commissionSale->com_sale,0) }}</td> 
                <td align="Right">{{ number_format($commissionSale->extra_sale_out,0) }}</td> 
                <td align="Right">{{ number_format($commissionSale->extra_unit,0) }}</td> 
                <td align="Right">{{ number_format($commissionSale->extra_avg,0) }}</td> 
                <td align="Right">{{ number_format($commissionSale->other,0) }}</td> 
                <td align="Right">{{ number_format($commissionSale->total,0) }}</td> 
                <td align="left">{{ $commissionSale->remark }}</td> 
            </tr>
            @endforeach
            </tbody>
            <tfoot>
            <tr align="center">
                <td colspan="3" align="center"><strong>Total</strong></td>
                <td align="Right">{{ number_format($sum_target, 0) }}</td>
                <td align="Right">{{ number_format($sum_sale_out, 0) }}</td>
                <td align="Right">{{ number_format($sum_achieve / $count_sale, 2) }}%</td> <!-- ช่องว่างสำหรับ Achieve -->
                <td align="Right">{{ number_format($sum_base_com, 0) }}</td>
                <td align="Right">{{ number_format($sum_com_sale, 0) }}</td>
                <td align="Right">{{ number_format($sum_extra_sale_out, 0) }}</td>
                <td align="Right">{{ number_format($sum_extra_unit, 0) }}</td>
                <td align="Right">{{ number_format($sum_extra_avg, 0) }}</td>
                <td align="Right">{{ number_format($sum_other, 0) }}</td>
                <td align="Right">{{ number_format($sum_total, 0) }}</td>
                <td></td> <!-- ช่องว่างสำหรับ Remark -->
            </tr>
            </tfoot>
            </table>
            <br><hr>
            <br><br><br><br><br>
            <table class="no-border-table">
                <tr align="center">
                    <td align="center">............................<br>Sale co-Ordinator</td>
                    <td align="center">............................<br>Key Account</td>
                    <td align="center">............................<br>Senior Director Sales</td>
                    <td align="center">............................<br>AR Accountant</td>
                    <td align="center">............................<br>Managing Director</td>
                </tr>
            </table>

</body>
</html>