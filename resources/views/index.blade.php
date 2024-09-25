@extends('layouts.master')

@section('title', 'Home')
@section('home', 'active')

@section('content')

        <h1 align="center">Commissions PC for {{ $year }}</h1>
        <div align="right">
        <form action="{{ route('commissions.index') }}" method="get">
            <label for="year">เลือกปี:</label>
            <select name="year" id="year" onchange="this.form.submit()">
                @foreach ($years as $yr)
                    <option value="{{ $yr }}" {{ $yr == $year ? 'selected' : '' }}>{{ $yr }}</option>
                @endforeach
            </select>
        </form>
        </div>

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
        
        @php
        $permissions = json_decode(Auth::user()->permissions, true); // แปลง JSON เป็น array
        @endphp
        <!-- Table to display monthly commission data -->
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th width="10%">Month</th>
                    <th width="20%">Upload</th>
                    <th width="13%">Sale In</th>
                    <th width="13%">Sale Out</th>
                    <th width="14%">Commission</th>
                    <th width="15%">Target</th>
                    <th width="15%">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($monthlyData as $data)
                    <tr>
                        <td>
                            {{-- <a href="{{ $data['show_link'] }}" class="{{ $data['disabled'] ? 'disabled' : '' }}">
                                {{ $data['month'] }}
                            </a> --}}
                            @if(in_array('Edit_qty', $permissions))
                            <a href="{{ $data['disabled'] ? '#' : $data['show_link'] }}" 
                                class="{{ $data['disabled'] ? 'disabled' : '' }}" 
                                style="{{ $data['disabled'] ? 'pointer-events: none; color: gray;' : '' }}">
                                    {{ $data['month'] }}
                            </a>
                            @else
                                {{ $data['month'] }}
                            @endif
                        </td>
                        <td>
                            @if(in_array('Upload_file', $permissions))
                            <a href="{{ $data['disabled'] ? '#' : $data['import_link'] }}" 
                            class="{{ $data['disabled'] ? 'disabled' : '' }}"
                            style="{{ $data['disabled'] ? 'pointer-events: none; color: gray;' : '' }}">
                            <img src="{{ asset('import.jpg') }}" width="17" height="17" alt="Import"> Upload
                            </a>
                            @else
                            <img src="{{ asset('import.jpg') }}" width="17" height="17" alt="Import"> Upload
                            @endif
                        </td>
                        <td>
                            @if(in_array('sale_in', $permissions))
                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#saleInModal" data-month="{{ $data['var_month'] }}" data-year="{{ $data['var_year'] }}">
                                {{ number_format($data['sale_in'], 0) }}
                            </button>
                            @else
                            <button type="button" class="btn btn-primary">
                                {{ number_format($data['sale_in'], 0) }}
                            </button>
                            @endif
                        </td>
                        <td>{{ number_format($data['sale_out'], 0) }}</td>
                        <td>{{ number_format($data['pay_com'], 0) }}</td>
                        <td>
                            @if(in_array('Edit_target', $permissions))
                            <a href="{{ $data['disabled'] ? '#' : $data['target_link'] }}" 
                            class="{{ $data['disabled'] ? 'disabled' : '' }}"
                            style="{{ $data['disabled'] ? 'pointer-events: none; color: gray;' : '' }}">
                            <img src="{{ asset('edit.jpg') }}" width="17" height="17" alt="Import"> Edit Target
                            </a>
                            @else
                            <img src="{{ asset('edit.jpg') }}" width="17" height="17" alt="Import"> Edit Target
                            @endif
                        </td>
                        <td>
                            {{-- {{ $data['status'] == 1 ? 'Completed' : 'Pending' }} --}}

                            @if ($data['status'] == 1)
                             Completed
                             <a href="{{ route('commissions.export', ['month' => $data['var_month'], 'show_month' =>$data['month'], 'year' => $data['var_year'], 'type' => 'excel']) }}" >
                             <img src="{{ asset('export.png') }}" width="20" height="20" alt="Export"> 
                             </a>
                            @else
                                @if(in_array('Make_completed', $permissions))
                                <form action="{{ route('status.updateOrCreate') }}" method="POST" style="display:inline;"> 
                                    @csrf
                                    <input type="hidden" name="as_of_month" value="{{ $data['var_month'] }}">
                                    <input type="hidden" name="as_of_year" value="{{ $data['var_year'] }}">
                                    <button type="submit" class="btn btn-success">Mark as Completed</button>
                                </form>
                                @endif
                            @endif

                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="modal fade" id="saleInModal" tabindex="-1" role="dialog" aria-labelledby="saleInModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="saleInModalLabel">บันทึก Sale In</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('sales_in.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="month" id="modalMonth">
                        <input type="hidden" name="year" id="modalYear">
                        <div class="form-group">
                            <label for="sale_in">Sale In</label>
                            <input type="number" step="0.01" class="form-control" id="sale_in" name="sale_in" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">ปิด</button>
                        <button type="submit" class="btn btn-primary">บันทึก</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        $('#saleInModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget); 
            var month = button.data('month'); 
            var year = button.data('year'); 
    
            var modal = $(this);
            modal.find('#modalMonth').val(month);
            modal.find('#modalYear').val(year);
        });
    </script>
@endsection
