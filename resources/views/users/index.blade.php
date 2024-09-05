<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Manage</title>
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
    @section('users', 'active')
   
    @section('content')
        <div align="center">
        <h1>User List  &nbsp;
        <button class="btn btn-primary add-btn" data-bs-toggle="modal" data-bs-target="#addModal">Add User</button>
        </h1>
        </div>
        
        <div class="mb-3 text-right">
            {{-- <a href="{{ route('user.export', ['type' => 'excel']) }}" class="btn btn-success">Export to Excel</a> --}}
            {{-- <a href="{{ route('stores.export', ['type' => 'pdf']) }}" target="_blank" class="btn btn-danger" >Export to PDF</a> --}}
        </div>

        <table id="data-table" class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>name</th>
                    <th>email</th>
                    <th>Department</th>
                    <th>permissions</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->department }}</td>
                        <td>{{ $user->permissions }}</td>
                        <td>
                            <button class="btn btn-warning edit-btn" 
                            data-id="{{ $user->id }}" data-name="{{ $user->name }}" 
                            data-email="{{ $user->email }}" data-department="{{ $user->department }}" 
                            data-permission="{{ $user->permissions }}">Edit</button>
                            &nbsp;
                            <button class="btn btn-danger delete-btn" data-id="{{ $user->id }}">Delete</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    <!-- Modal for Add USER -->
    <div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addModalLabel">Add product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="addForm" method="POST" action="{{ route('users.store') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-md-12 mb-12">
                                    <label for="add_name" class="form-label">name</label>
                                    <input type="text" class="form-control" id="add_name" name="name" >
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 mb-12">
                                    <label for="add_email" class="form-label">Email</label>
                                    <input type="text" class="form-control" id="add_email" name="email" >
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 mb-12">
                                    <label for="add_password" class="form-label">Password</label>
                                    <input type="text" class="form-control" id="add_password" name="password" >
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 mb-12">
                                    <label for="add_password_confirmation" class="form-label">Confirm Password</label>
                                    <input type="text" class="form-control" id="add_password_confirmation" name="password_confirmation" >
                                </div>
                                
                            </div>
                            <div class="row">
                                <div class="col-md-12 mb-12">
                                    <label for="add_department" class="form-label">Department</label>
                                    <input type="text" class="form-control" id="add_department" name="department" >
                                </div>
                            </div>
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
        <div class="modal-dialog ">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Store</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-md-12 mb-12">
                                    <label for="name" class="form-label">name</label>
                                    <input type="text" class="form-control" id="name" name="name" >
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 mb-12">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="text" class="form-control" id="email" name="email" readonly>
                                </div>
                            </div>
                            {{-- <div class="row">
                                <div class="col-md-12 mb-12">
                                    <label for="password" class="form-label">Password</label>
                                    <input type="text" class="form-control" id="password" name="password" >
                                </div>
                                <div class="col-md-12 mb-12">
                                    <label for="password_confirmation" class="form-label">Confirm Password</label>
                                    <input type="text" class="form-control" id="password_confirmation" name="password_confirmation" >
                                </div>
                                
                            </div> --}}
                            <div class="row">
                                <div class="col-md-12 mb-12">
                                    <label for="department" class="form-label">Department</label>
                                    <input type="text" class="form-control" id="department" name="department" >
                                </div>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" id="user_id_hidden" name="id">
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
                    Are you sure you want to Delete this User?
                </div>
                <div class="modal-footer">
                    <form id="deleteForm" method="POST">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" id="user_id_delete" name="id">
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
                var name = $(this).data('name');
                var email = $(this).data('email');
                var department = $(this).data('department');
                

                $('#name').val(name);
                $('#email').val(email);
                $('#department').val(department);
                
                $('#user_id_hidden').val(id);
                
                $('#editForm').attr('action', '/users/' + id);
                $('#editModal').modal('show');
            });

            // Delete button click event
            $('.delete-btn').on('click', function() {
                var id = $(this).data('id');
                $('#user_id_delete').val(id);
                $('#deleteForm').attr('action', '/users/' + id + '/status');
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