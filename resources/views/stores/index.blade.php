<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Store manage</title>
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
    @section('store', 'active')
   
    @section('content')
        <div align="center">
        <h1>Stores List &nbsp;
        <button class="btn btn-primary add-btn" data-bs-toggle="modal" data-bs-target="#addModal">Add Store</button>
        </h1>
        </div>
        
        <div class="mb-3 text-right">
            <a href="{{ route('stores.export', ['type' => 'excel']) }}" class="btn btn-success">Export to Excel</a>
            {{-- <a href="{{ route('stores.export', ['type' => 'pdf']) }}" target="_blank" class="btn btn-danger" >Export to PDF</a> --}}
        </div>

        <table id="data-table" class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Supplier Code</th>
                    <th>Store ID</th>
                    <th>Store</th>
                    <th>Type Store</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($stores as $store)
                    <tr>
                        <td>{{ $store->id }}</td>
                        <td>{{ $store->suppliercode }}</td>
                        <td>{{ $store->store_id }}</td>
                        <td>{{ $store->store }}</td>
                        <td>{{ $store->type_store }}</td>
                        <td>
                            <button class="btn btn-warning edit-btn" data-id="{{ $store->id }}" data-suppliercode="{{ $store->suppliercode }}" data-store_id="{{ $store->store_id }}" data-store="{{ $store->store }}" data-type_store="{{ $store->type_store }}">Edit</button>
                            &nbsp;
                            <button class="btn btn-danger delete-btn" data-id="{{ $store->id }}">Delete</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    <!-- Modal for Add Store -->
    <div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addModalLabel">Add Store</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="addForm" method="POST" action="{{ route('stores.store') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="add_suppliercode" class="form-label">Supplier Code</label>
                            <input type="text" class="form-control" id="add_suppliercode" name="suppliercode" required>
                        </div>
                        <div class="mb-3">
                            <label for="add_store_id" class="form-label">Store ID</label>
                            <input type="text" class="form-control" id="add_store_id" name="store_id">
                        </div>
                        <div class="mb-3">
                            <label for="add_store" class="form-label">Store</label>
                            <input type="text" class="form-control" id="add_store" name="store">
                        </div>
                        <div class="mb-3">
                            <label for="add_type_store" class="form-label">Type Store</label>
                            {{-- <input type="text" class="form-control" id="add_type_store" name="type_store"> --}}
                            <select id="add_type_store" name="type_store" class="form-control chosen-select" required>
                                <option value="">Select Type Store</option>
                                <option value="A">Type A</option>
                                <option value="B">Type B</option>
                                <option value="C">Type C</option>
                                <!-- Add more options as needed -->
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
                    <h5 class="modal-title" id="editModalLabel">Edit Store</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editForm" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="suppliercode" class="form-label">Supplier Code</label>
                            <input type="text" class="form-control" id="suppliercode" name="suppliercode" required>
                        </div>
                        <div class="mb-3">
                            <label for="store_id" class="form-label">Store ID</label>
                            <input type="text" class="form-control" id="store_id" name="store_id">
                        </div>
                        <div class="mb-3">
                            <label for="store" class="form-label">Store</label>
                            <input type="text" class="form-control" id="store" name="store">
                        </div>
                        <div class="mb-3">
                            <label for="type_store" class="form-label">Type Store</label>
                            {{-- <input type="text" class="form-control" id="type_store" name="type_store"> --}}
                            <select id="type_store" name="type_store" class="form-control chosen-select" required>
                                <option value="">Select Type Store</option>
                                <option value="A">Type A</option>
                                <option value="B">Type B</option>
                                <option value="C">Type C</option>
                            </select>
                        </div>
                        <input type="hidden" id="store_id_hidden" name="id">
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
                    Are you sure you want to Delete this store?
                </div>
                <div class="modal-footer">
                    <form id="deleteForm" method="POST">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" id="store_id_delete" name="id">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Yes, Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('js/popper.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('js/chosen.jquery.min.js') }}"></script>
    <script src="{{ asset('js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('js/sweetalert2@11.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('.add-btn').on('click', function() {
                $('#addModal').modal('show');
            });
            // Edit button click event
            $('.edit-btn').on('click', function() {
                var id = $(this).data('id');
                var suppliercode = $(this).data('suppliercode');
                var store_id = $(this).data('store_id');
                var store = $(this).data('store');
                var type_store = $(this).data('type_store');
                
                $('#suppliercode').val(suppliercode);
                $('#store_id').val(store_id);
                $('#store').val(store);
                $('#type_store').val(type_store).trigger("chosen:updated");
                $('#store_id_hidden').val(id);
                
                // $('#editForm').attr('action', '/stores/' + id);
                $('#editForm').attr('action', '/stores/' + id);
                $('#editModal').modal('show');
            });

            // Delete button click event
            $('.delete-btn').on('click', function() {
                var id = $(this).data('id');
                $('#store_id_delete').val(id);
                $('#deleteForm').attr('action', '/stores/' + id + '/status');
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
    <script type="text/javascript">
        $(document).ready(function(){
            $('.chosen-select').chosen({
                width: "100%"  // กำหนดความกว้างให้เต็ม
            });
        });
    </script>
@endsection