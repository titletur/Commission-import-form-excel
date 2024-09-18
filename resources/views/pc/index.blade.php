<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PC Manage</title>
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
    @section('pc', 'active')
    @php
    $permissions = json_decode(Auth::user()->permissions, true); // แปลง JSON เป็น array
    @endphp
    @section('content')
        <div align="center">
        <h1>PC List  &nbsp;
        @if(in_array('Add_pc', $permissions))
        <button class="btn btn-primary add-btn" data-bs-toggle="modal" data-bs-target="#addModal">Add PC</button>
        @endif    
        </h1>
        </div>
        
        <div class="mb-3 text-right">
            <a href="{{ route('pcs.export', ['type' => 'excel']) }}" class="btn btn-success">Export to Excel</a>
            {{-- <a href="{{ route('stores.export', ['type' => 'pdf']) }}" target="_blank" class="btn btn-danger" >Export to PDF</a> --}}
        </div>
       

        <table id="data-table" class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Store ID</th>
                    <th>Type Store</th>
                    <th>Code PC</th>
                    <th>Name</th>
                    <th>Type PC</th>
                    <th>Target</th>
                    <th>Salary</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pcs as $pc)
                    <tr>
                        <td>{{ $pc->id }}</td>
                        <td>{{ $pc->store_id }}</td>
                        <td>{{ $pc->type_store }}</td>
                        <td>{{ $pc->code_pc }}</td>
                        <td>{{ $pc->name_pc }}</td>
                        <td>{{ $pc->type_pc }}</td>
                        <td>{{ $pc->tarket }}</td>
                        <td>{{ $pc->salary }}</td>
                        <td>
                            @if(in_array('Edit_pc', $permissions))
                            <button class="btn btn-warning edit-btn" data-id="{{ $pc->id }}" data-store_id="{{ $pc->store_id }}" data-type_store="{{ $pc->type_store }}" data-code_pc="{{ $pc->code_pc }}" data-name_pc="{{ $pc->name_pc }}" data-type_pc="{{ $pc->type_pc }}" data-tarket="{{ $pc->tarket }}" data-salary="{{ $pc->salary }}">Edit</button>
                            @endif
                            &nbsp;
                            @if(in_array('Del_pc', $permissions))
                            <button class="btn btn-danger delete-btn" data-id="{{ $pc->id }}">Delete</button>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    <!-- Modal for Add PC -->
    <div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addModalLabel">Add PC</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="addForm" method="POST" action="{{ route('pc.store') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="add_store_id" class="form-label">Store ID</label>
                            <input type="text" class="form-control" id="add_store_id" name="store_id">
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
                        <div class="mb-3">
                            <label for="add_code_pc" class="form-label">Code PC </label>
                            <input type="text" class="form-control" id="add_code_pc" name="code_pc" required>
                        </div>
                        <div class="mb-3">
                            <label for="add_name_pc" class="form-label">Name PC</label>
                            <input type="text" class="form-control" id="add_name_pc" name="name_pc">
                        </div>
                        <div class="mb-3">
                            <label for="add_type_pc" class="form-label">Type PC</label>
                            {{-- <input type="text" class="form-control" id="add_type_pc" name="type_pc"> --}}
                            <select id="add_type_pc" name="type_pc" class="form-control chosen-select" required>
                                <option value="">Select Type PC</option>
                                <option value="PC">PC</option>
                                <option value="PC_HA">PC HA</option>
                                <option value="Freelance">Freelance</option>
                                <option value="Freelance_plus">Freelance plus</option>
                                <option value="No PC">No PC</option>
                                <!-- Add more options as needed -->
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="add_tarket" class="form-label">Target</label>
                            <input type="text" class="form-control" id="add_tarket" name="tarket" value="0">
                        </div>
                        <div class="mb-3">
                            <label for="add_salary" class="form-label">Salary</label>
                            <input type="text" class="form-control" id="add_salary" name="salary" value="0">
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
                            <label for="store_id" class="form-label">Store ID</label>
                            <input type="text" class="form-control" id="store_id" name="store_id">
                        </div>
                        <div class="mb-3">
                            <label for="type_store" class="form-label">Type Store</label>
                            {{-- <input type="text" class="form-control" id="type_store" name="type_store"> --}}
                            <select id="type_store" name="type_store" class="form-control chosen-select" required>
                                <option value="A">Type A</option>
                                <option value="B">Type B</option>
                                <option value="C">Type C</option>
                                <!-- Add more options as needed -->
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="code_pc" class="form-label">Code PC </label>
                            <input type="text" class="form-control" id="code_pc" name="code_pc" required>
                        </div>
                        <div class="mb-3">
                            <label for="name_pc" class="form-label">Name PC</label>
                            <input type="text" class="form-control" id="name_pc" name="name_pc">
                        </div>
                        <div class="mb-3">
                            <label for="type_pc" class="form-label">Type PC</label>
                            {{-- <input type="text" class="form-control" id="type_pc" name="type_pc"> --}}
                            <select id="type_pc" name="type_pc" class="form-control chosen-select" required>
                                <option value="">Select Type PC</option>
                                <option value="PC">PC</option>
                                <option value="PC_HA">PC HA</option>
                                <option value="Freelance">Freelance</option>
                                <option value="Freelance_plus">Freelance plus</option>
                                <option value="No PC">No PC</option>
                                <!-- Add more options as needed -->
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="tarket" class="form-label">Target</label>
                            <input type="text" class="form-control" id="tarket" name="tarket">
                        </div>
                        <div class="mb-3">
                            <label for="salary" class="form-label">Salary</label>
                            <input type="text" class="form-control" id="salary" name="salary">
                        </div>
                        <input type="hidden" id="pc_id_hidden" name="id">
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
                    Are you sure you want to Delete this PC?
                </div>
                <div class="modal-footer">
                    <form id="deleteForm" method="POST">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" id="pc_id_delete" name="id">
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
                var store_id = $(this).data('store_id');
                var type_store = $(this).data('type_store');
                var code_pc = $(this).data('code_pc');
                var name_pc = $(this).data('name_pc');
                var type_pc = $(this).data('type_pc');
                var tarket = $(this).data('tarket');
                var salary = $(this).data('salary');

                $('#store_id').val(store_id);
                $('#type_store').val(type_store).trigger("chosen:updated");
                $('#code_pc').val(code_pc);
                $('#name_pc').val(name_pc);
                $('#type_pc').val(type_pc).trigger("chosen:updated");
                $('#tarket').val(tarket);
                $('#salary').val(salary);
                $('#pc_id_hidden').val(id);
                
                $('#editForm').attr('action', '/pc/' + id);
                $('#editModal').modal('show');
            });

            // Delete button click event
            $('.delete-btn').on('click', function() {
                var id = $(this).data('id');
                $('#pc_id_delete').val(id);
                $('#deleteForm').attr('action', '/pc/' + id + '/status');
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