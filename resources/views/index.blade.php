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

        <!-- Table to display monthly commission data -->
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th width="10%">Month</th>
                    <th width="20%">Upload</th>
                    <th width="15%">Sale Out</th>
                    <th width="15%">Commission</th>
                    <th width="20%">Target</th>
                    <th width="20%">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($monthlyData as $data)
                    <tr>
                        <td>
                            {{-- <a href="{{ $data['show_link'] }}" class="{{ $data['disabled'] ? 'disabled' : '' }}">
                                {{ $data['month'] }}
                            </a> --}}
                            <a href="{{ $data['disabled'] ? '#' : $data['show_link'] }}" 
                                class="{{ $data['disabled'] ? 'disabled' : '' }}" 
                                style="{{ $data['disabled'] ? 'pointer-events: none; color: gray;' : '' }}">
                                    {{ $data['month'] }}
                            </a>
                        </td>
                        <td>
                            <a href="{{ $data['disabled'] ? '#' : $data['import_link'] }}" 
                            class="{{ $data['disabled'] ? 'disabled' : '' }}"
                            style="{{ $data['disabled'] ? 'pointer-events: none; color: gray;' : '' }}">
                            <img src="{{ asset('import.jpg') }}" width="17" height="17" alt="Import"> Upload
                            </a>
                        </td>
                        <td>{{ number_format($data['sale_in'], 0) }}</td>
                        <td>{{ number_format($data['pay_com'], 0) }}</td>
                        <td>
                            <a href="{{ $data['disabled'] ? '#' : $data['target_link'] }}" 
                            class="{{ $data['disabled'] ? 'disabled' : '' }}"
                            style="{{ $data['disabled'] ? 'pointer-events: none; color: gray;' : '' }}">
                            <img src="{{ asset('edit.jpg') }}" width="17" height="17" alt="Import"> Edit Target
                            </a>
                        </td>
                        <td>
                            {{-- {{ $data['status'] == 1 ? 'Completed' : 'Pending' }} --}}

                            @if ($data['status'] == 1)
                             Completedss
                             <a href="{{ route('commissions.export', ['month' => $data['var_month'], 'show_month' =>$data['month'], 'year' => $data['var_year'], 'type' => 'excel']) }}" >
                             <img src="{{ asset('export.png') }}" width="20" height="20" alt="Export"> 
                             </a>
                            @else
                                <form action="{{ route('status.updateOrCreate') }}" method="POST" style="display:inline;"> 
                                    @csrf
                                    <input type="hidden" name="as_of_month" value="{{ $data['var_month'] }}">
                                    <input type="hidden" name="as_of_year" value="{{ $data['var_year'] }}">
                                    <button type="submit" class="btn btn-success">Mark as Completed</button>
                                </form>
                            @endif

                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

@endsection
