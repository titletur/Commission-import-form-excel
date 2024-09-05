<!DOCTYPE html>
<html>
<head>
    {{-- <link rel="stylesheet" href="{{ asset('css/cssfont.css') }}"> --}}
    <link rel="icon" href="{{ asset('bigc.jpg') }}" type="image/x-icon">
    <style>
        body {
            font-family: 'freeserif', sans-serif;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>

    <h2 class="text-center">Commissions for {{ $show_month }} {{ $year }}</h2>
    <table>
        <thead>
            <tr align="center">
                <th width="4%">Store</th>
                <th width="4%">Type<br>Store</th>
                <th width="12%">PC</th>
                <th width="8%">Type PC</th>
                <th width="6%">Salary</th>
                <th width="8%">Sale<br>TV</th>
                <th width="5%">QTY<br>TV</th>
                <th width="8%">Sale<br>AV</th>
                <th width="5%">QTY<br>AV</th>
                <th width="8%">Sale<br>HA</th>
                <th width="5%">QTY<br>HA</th>
                <th width="10%">Sale Total</th>
                <th width="4%">Target</th>
                <th width="4%">Achieve</th>
                <th width="4%">Com TV</th>
                <th width="4%">Com AV</th>
                <th width="4%">Com HA</th>
                <th width="4%">Pay Com</th>
                <th width="4%">Extra</th>
                <th width="4%">Extra HA</th>
                <th width="4%">Net Com</th>
            </tr>
        </thead>
        <tbody>
            @foreach($commissions as $commission)

                <tr>
                    <td align="center">{{ $commission->store_id }}</td>
                    <td align="center">{{ $commission->type_store }}</td>
                    <td>{{ ($commission->name_pc) }}</td> 
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
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>