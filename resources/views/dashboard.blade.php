@extends('layouts.master')

@section('title', 'Home')
@section('home', 'active')

@section('content')

        <h1>Commissions PC for {{ $year }}</h1>
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
                            <a href="{{ $data['import_link'] }}" class="{{ $data['disabled'] ? 'disabled' : '' }}">
                                Upload
                            </a>
                        </td>
                        <td>{{ number_format($data['sale_in'], 2) }}</td>
                        <td>{{ number_format($data['pay_com'], 2) }}</td>
                        <td>
                            <a href="{{ $data['target_link'] }}" class="{{ $data['disabled'] ? 'disabled' : '' }}">
                                Edit Target
                            </a>
                        </td>
                        <td>
                            {{ $data['status'] == 1 ? 'Completed' : 'Pending' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

@endsection
