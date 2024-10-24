@extends('layouts.master')

@section('title', 'Home')
@section('home', 'active')

@section('content')

    <h1 align="center">Update Target PC for {{ $month }} {{ $year }}</h1>

    <form action="{{ route('updateTarget') }}" method="POST">
        @csrf
        

        <input type="hidden" name="var_month" value="{{ $var_month }}">
        <input type="hidden" name="year" value="{{ $year }}">

        <table id="data-table" class="table table-bordered">
            <thead>
                <tr>
                    <th>Store ID</th>
                    <th>Type Store</th>
                    <th>Code PC</th>
                    <th>Name PC</th>
                    <th>Type PC</th>
                    <th>Target</th>
                    <th>Salary</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($pcs as $pc)

                    <tr>
                        <td align="right">{{ $pc->store_id }}</td>
                        <td align="center">{{ $pc->type_store }}</td>
                        <td>{{ $pc->code_pc }}</td>
                        <td>{{ $pc->name_pc }}</td>
                        <td>
                            <select id="type_pc" name="type_pc[{{ $pc->id }}]" class="form-control chosen-select" required>
                                <option value="PC" {{ $pc->type_pc == 'PC' ? 'selected' : '' }}>PC</option>
                                <option value="PC_HA" {{ $pc->type_pc == 'PC_HA' ? 'selected' : '' }}>PC HA</option>
                                <option value="Freelance" {{ $pc->type_pc == 'Freelance' ? 'selected' : '' }}>Freelance</option>
                                <option value="Freelance_plus" {{ $pc->type_pc == 'Freelance_plus' ? 'selected' : '' }}>Freelance plus</option>
                                <option value="PC_pomotion" {{ $pc->type_pc == 'PC_pomotion' ? 'selected' : '' }}>PC pomotion</option>
                                <option value="No PC" {{ $pc->type_pc == 'No PC' ? 'selected' : '' }}>No PC</option>
                            </select>
                        </td>
                        <td align="center">
                            <input type="text" name="tarket[{{ $pc->id }}]" value="{{ number_format($pc->tarket,0) }}">
                        </td>
                        <td align="right">{{  number_format($pc->salary,0) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="text-center mt-3">
            <button type="submit" class="btn btn-success">Submit</button>
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
