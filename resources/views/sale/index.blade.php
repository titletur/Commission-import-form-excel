<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sale List</title>
    <link rel="icon" href="{{ asset('bigc.jpg') }}" type="image/x-icon">
    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/cssfont.css') }}">
    <link rel="stylesheet" href="{{ asset('css/chosen.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/jquery.datatables.min.css') }}">
    
   
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @if (session('success'))
                    Swal.fire({
                        title: 'Success!',
                        text: "{{ session('success') }}",
                        icon: 'success',
                        confirmButtonText: 'OK'
                    });
            @endif
            @if ($errors->any())
                    Swal.fire({
                        title: 'Error!',
                        text: "{{ $errors->first('error') }}",
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
            @endif
            @if (session('no_permission'))
                Swal.fire({
                    title: 'Error!',
                    text: "{{ session('no_permission') }}",
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            @endif
        });
    </script>
    
    @extends('layouts.nav_bar')
    @section('sale', 'active')
   
    @section('content')
        <div align="center">
        <h1>Sale Manage  &nbsp;
        <button class="btn btn-primary add-btn" data-bs-toggle="modal" data-bs-target="#addModal">Add Sale</button>
        </h1>
        </div>
        
        <div class="mb-3 text-right">
            <a href="{{ route('sales.export', ['type' => 'excel']) }}" class="btn btn-success">Export to Excel</a>
            {{-- <a href="{{ route('stores.export', ['type' => 'pdf']) }}" target="_blank" class="btn btn-danger" >Export to PDF</a> --}}
        </div>

        <table id="data-table" class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Target</th>
                    <th>Base_com</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sales as $sale)
                    <tr>
                        <td>{{ $sale->id_sale }}</td>
                        <td>{{ $sale->code_sale }}</td>
                        <td>{{ $sale->name_sale }}</td>
                        <td>{{ $sale->target }}</td>
                        <td>{{ $sale->base_com }}</td>
                        <td>
                            <button class="btn btn-warning edit-btn" data-id="{{ $sale->id_sale }}" data-code_sale="{{ $sale->code_sale }}" data-name_sale="{{ $sale->name_sale }}" data-target="{{ $sale->target }}" data-base_com="{{ $sale->base_com }}">Edit</button>
                            &nbsp;
                            <button class="btn btn-danger delete-btn" data-id="{{ $sale->id_sale }}">Delete</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    <!-- Modal for Add sale -->
    <div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addModalLabel">Add sale</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="addForm" method="POST" action="{{ route('sale.store') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="add_code_sale" class="form-label">Code</label>
                            <input type="text" class="form-control" id="add_code_sale" name="code_sale" required>
                        </div>
                        <div class="mb-3">
                            <label for="add_name_sale" class="form-label">Name</label>
                            <input type="text" class="form-control" id="add_name_sale" name="name_sale" required>
                        </div>
                        <div class="mb-3">
                            <label for="add_target" class="form-label">Target</label>
                            <input type="number" class="form-control" id="add_target" name="target" value="0">
                        </div>
                        <div class="mb-3">
                            <label for="add_base_com" class="form-label">Base Commission</label>
                            <input type="number" class="form-control" id="add_base_com" name="base_com" value="0">
                        </div>
                        <div class="mb-3">
                            <label for="add_store_id" class="form-label">Storess</label>
                            <select multiple class="form-control chosen-select" id="add_store_id" name="store_ids[]">
                                @foreach($stores as $store)
                                    <option value="{{ $store->store_id }}">{{ $store->store }}</option>
                                @endforeach
                            </select>
                            
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal for Edit -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit sale</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_code_sale" class="form-label">Code</label>
                            <input type="text" class="form-control" id="edit_code_sale" name="code_sale" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_name_sale" class="form-label">Name</label>
                            <input type="text" class="form-control" id="edit_name_sale" name="name_sale" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_target" class="form-label">Target</label>
                            <input type="number" class="form-control" id="edit_target" name="target">
                        </div>
                        <div class="mb-3">
                            <label for="edit_base_com" class="form-label">Base Commission</label>
                            <input type="number" class="form-control" id="edit_base_com" name="base_com">
                        </div>
                        <div class="mb-3">
                            <label for="edit_store_id" class="form-label">Stores</label>
                            <select multiple class="form-control chosen-select" id="edit_store_id" name="store_ids[]">
                                <!-- Options will be populated by JavaScript -->
                            </select>
                        </div>
                        
                        <input type="hidden" id="sale_id_hidden" name="id_sale">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal for Delete Confirmation -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to update the status of this sale?
                </div>
                <div class="modal-footer">
                    <form id="deleteForm" method="POST">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" id="sale_id_delete" name="id_sale">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Yes, Update</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('js/popper.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('js/chosen.jquery.min.js') }}"></script>
    {{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.jquery.min.js"></script> --}}
    <script src="{{ asset('js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('js/sweetalert2@11.js') }}"></script>
    
    <script>
        $(document).ready(function() {
            $('.chosen-select').chosen({
                placeholder_text_multiple: "Select some stores", // Placeholder text for multiple select
                no_results_text: "No results matched", // Text for when no options are matched
                width: '100%' // Adjust width to fit your layout
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            $('.add-btn').on('click', function() {
                $('#addModal').modal('show');
            });
            // Edit button click event
            $('.edit-btn').on('click', function() {
                var id_sale = $(this).data('id'); // ใช้ data-id แทน data-id_sale
                var code_sale = $(this).data('code_sale');
                var name_sale = $(this).data('name_sale');
                var target = $(this).data('target');
                var base_com = $(this).data('base_com');
                
                $('#edit_code_sale').val(code_sale);
                $('#edit_name_sale').val(name_sale);
                $('#edit_target').val(target);
                $('#edit_base_com').val(base_com);
                $('#sale_id_hidden').val(id_sale);
                
                // ดึงข้อมูล store สำหรับ sale นี้
                $.ajax({
                    url: '/sales/' + id_sale + '/stores',
                    method: 'GET',
                    success: function(response) {
                        var select = $('#edit_store_id');
                        select.empty(); // Clear previous options

                        $.each(response.stores, function(index, store) {
                            var selected = store.selected ? 'selected' : '';
                            select.append('<option value="' + store.store_id + '" ' + selected + '>' + store.store + '</option>');
                        });
                        select.trigger('chosen:updated');

                        $('#editModal').modal('show');
                    }
                });

                $('#editForm').attr('action', '/sale/' + id_sale);
            });

            // Delete button click event
            $('.delete-btn').on('click', function() {
                var id_sale = $(this).data('id');
                $('#sale_id_delete').val(id_sale);
                $('#deleteForm').attr('action', '/sale/' + id_sale + '/status');
                $('#deleteModal').modal('show');
            });
        });
    </script>
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