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
                    <th width="8%" colspan="2">Month</th>
                    <th width="15%">Upload</th>
                    <th width="8%">Sale In</th>
                    <th width="12%" colspan="2">Price</th>
                    <th width="12%">Sale Out</th>
                    <th width="12%">Commission</th>
                    <th width="15%" colspan="2">Target</th>
                    <th width="18%">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($monthlyData as $data)
                    <tr>
                        <td width="5%">
                            @if(in_array('Edit_qty', $permissions) && ($data['show_link_enabled'] =='0'))
                            <a href="{{ $data['disabled'] ? '#' : $data['show_link'] }}" 
                                class="{{ $data['disabled'] ? 'disabled' : '' }}" 
                                style="{{ $data['disabled'] ? 'pointer-events: none; color: gray;' : '' }}">
                                    {{ $data['month'] }}
                            </a>
                            @else
                                {{ $data['month'] }}
                            @endif
                        </td>
                        <td width="3%"><br>
                            @if(in_array('switch_on_off_enabled', $permissions))
                            <form method="POST" action="{{ route('update-access', ['month' => $data['var_month'], 'year' => $data['var_year']]) }}"> 
                                @csrf
                                <input type="hidden" name="field" value="show_link_enabled">
                                <input type="hidden" name="month" value="{{ $data['var_month'] }}">
                                <input type="hidden" name="year" value="{{ $data['var_year'] }}">

                                <label class="switch">
                                    <input type="checkbox" name="isEnabled" value="true" {{ $data['show_link_enabled'] ? 'checked' : '' }} onchange="this.form.submit()">
                                    <span class="slider round"></span>
                                </label>
                            </form>
                            @else
                                <label class="switch">
                                    <input type="checkbox" disabled name="isEnabled" value="true" {{ $data['show_link_enabled'] ? 'checked' : '' }} >
                                    <span class="slider round"></span>
                                </label>
                            @endif
                        </td>
                        <td>
                            @if(in_array('Upload_file', $permissions))
                            <a href="{{ $data['disabled'] ? '#' : $data['import_link'] }}" 
                            class="{{ $data['disabled'] ? 'disabled' : '' }}"
                            style="{{ $data['disabled'] ? 'pointer-events: none; color: gray;' : '' }}">
                            <button type="button" class="btn btn-outline-primary"><img src="{{ asset('import.jpg') }}" width="17" height="17" alt="Import"> Upload</button>
                            </a>
                            @else
                            <button type="button" class="btn btn-outline-primary"><img src="{{ asset('import.jpg') }}" width="17" height="17" alt="Import"> Upload</button>
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
                        <td width="8%">
                            @if(in_array('Upload_price', $permissions) && ($data['price_link_enabled'] =='0'))
                            <a href="{{ $data['disabled'] ? '#' : $data['price_link'] }}" 
                            class="{{ $data['disabled'] ? 'disabled' : '' }}"
                            style="{{ $data['disabled'] ? 'pointer-events: none; color: gray;' : '' }}">
                            <button type="button" class="btn btn-outline-success"><img src="{{ asset('money.png') }}" width="35" height="28" alt="Import_price"> Price</button>
                            </a>
                            @else
                            <button type="button" class="btn btn-outline-success"><img src="{{ asset('money.png') }}" width="35" height="28" alt="Import_price"> Price</button>
                            @endif
                        </td>
                        <td width="4%">
                            @if(in_array('switch_on_off_price', $permissions))
                            <form method="POST" action="{{ route('update-access', ['month' => $data['var_month'], 'year' => $data['var_year']]) }}"> 
                                @csrf
                                <input type="hidden" name="field" value="price_link_enabled">
                                <input type="hidden" name="month" value="{{ $data['var_month'] }}">
                                <input type="hidden" name="year" value="{{ $data['var_year'] }}">
                                <label class="switch">
                                    <input type="checkbox" name="isEnabled" value="true" {{ $data['price_link_enabled'] ? 'checked' : '' }} onchange="this.form.submit()">
                                    <span class="slider round"></span>
                                </label>
                            </form>
                            @else
                                <label class="switch">
                                    <input type="checkbox" disabled name="isEnabled" value="true" {{ $data['price_link_enabled'] ? 'checked' : '' }} >
                                    <span class="slider round"></span>
                                </label>
                            @endif
                        </td>
                        <td>{{ number_format($data['sale_out'], 0) }}</td>
                        <td>{{ number_format($data['pay_com'], 0) }}</td>
                        <td width="11%">
                            @if(in_array('Edit_target', $permissions) && ($data['target_link_enabled'] =='0'))
                            <a href="{{ $data['disabled'] ? '#' : $data['target_link'] }}" 
                            class="{{ $data['disabled'] ? 'disabled' : '' }}"
                            style="{{ $data['disabled'] ? 'pointer-events: none; color: gray;' : '' }}">
                            <img src="{{ asset('edit.jpg') }}" width="17" height="17" alt="Import"> Edit Target
                            </a>
                            @else
                            <img src="{{ asset('edit.jpg') }}" width="17" height="17" alt="Import"> Edit Target
                            @endif
                        </td>
                        <td width="4%">
                            @if(in_array('switch_on_off_target', $permissions))
                            <form method="POST" action="{{ route('update-access', ['month' => $data['var_month'], 'year' => $data['var_year']]) }}"> 
                                @csrf
                                <input type="hidden" name="field" value="target_link_enabled">
                                <input type="hidden" name="month" value="{{ $data['var_month'] }}">
                                <input type="hidden" name="year" value="{{ $data['var_year'] }}">
                                <label class="switch">
                                    <input type="checkbox" name="isEnabled" value="true" {{ $data['target_link_enabled'] ? 'checked' : '' }} onchange="this.form.submit()">
                                    <span class="slider round"></span>
                                </label>
                            </form>
                            @else
                                <label class="switch">
                                    <input type="checkbox" disabled name="isEnabled" value="true" {{ $data['target_link_enabled'] ? 'checked' : '' }} >
                                    <span class="slider round"></span>
                                </label>
                            @endif
                        </td>
                        <td>
                            {{-- {{ $data['status'] == 1 ? 'Completed' : 'Pending' }} --}}

                            @if ($data['status'] == 1)
                             Completed
                             <a href="{{ route('commissions.export', ['month' => $data['var_month'], 'show_month' =>$data['month'], 'year' => $data['var_year'], 'type' => 'excel']) }}" >
                             <img src="{{ asset('exportexcel.png') }}" width="35" height="35" alt="Export"> 
                             </a>
                             &nbsp;&nbsp;
                             <a href="{{ route('commissions.export', ['month' => $data['var_month'], 'show_month' =>$data['month'], 'year' => $data['var_year'], 'type' => 'pdf']) }}" >
                                <img src="{{ asset('exportpdf.png') }}" width="35" height="35" alt="Export">
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
    
    
       
    </script>
    
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


<script>
    function toggleAccess(id, field, isEnabled) {
        fetch(`/update-access/${id}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                field: field,
                isEnabled: isEnabled
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    title: 'Updated!',
                    text: 'Access has been updated successfully.',
                    icon: 'success',
                    confirmButtonText: 'OK'
                });
            } else {
                Swal.fire({
                    title: 'Error!',
                    text: 'Failed to update access.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        })
        .catch(error => console.error('Error:', error));
    }
</script>

