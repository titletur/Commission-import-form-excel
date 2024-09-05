@extends('layouts.master')

@section('title', 'Home')
@section('home', 'active')

@section('content')

    <h2 class="text-center" >Import Data for {{ $month }} {{ $year }}</h2>
    <form action="{{ route('import.import') }}" id="import-form"  method="POST" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="var_year" value="{{ $year }}">
        <input type="hidden" name="var_month" value="{{ $var_month }}">
        <input type="hidden" name="short_month" value="{{ $month }}">
        <div class="form-group">
            <label for="excel_file">Choose Excel File:</label>
            <input type="file" class="form-control" name="excel_file" required>
        </div>
        <button type="submit" class="btn btn-primary">Import</button>
    </form>

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
