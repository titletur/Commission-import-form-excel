<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Manage</title>
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
    @section('product', 'active')
    @php
    $permissions = json_decode(Auth::user()->permissions, true); // แปลง JSON เป็น array
    @endphp
    @section('content')
        <div align="center">
        <h1>Product List  &nbsp;
        @if(in_array('Add_product', $permissions))
        <button class="btn btn-primary add-btn" data-bs-toggle="modal" data-bs-target="#addModal">Add product</button>
        @endif
        </h1>
        </div>
        

        <div class="mb-6 text-right">
            @if(in_array('Switch_product', $permissions))
            <form action="{{ route('toggleEditMode') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-info">
                    @if($editMode)
                        Disable Edit 
                    @else
                        Enable Edit 
                    @endif
                </button>
            </form> 
            @endif    
            <a href="{{ route('products.export', ['type' => 'excel']) }}" class="btn btn-success d-inline ml-2">Export to Excel</a>
            {{-- <a href="{{ route('stores.export', ['type' => 'pdf']) }}" target="_blank" class="btn btn-danger ml-2">Export to PDF</a> --}}
            
        </div>

        <table id="data-table" class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Supplier</th>
                    <th>sub dept</th>
                    <th>sub dept name</th>
                    <th>product model</th>
                    <th>skucode</th>
                    <th>product name</th>
                    <th>Type Product</th>
                    <th>Price</th>
                    <th>Price(vat)</th>
                    <th>Com</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $i = 1;
                @endphp
                @foreach($products as $product)
                    <tr>
                        {{-- <td>{{ $product->id }}</td> --}}
                        <td>{{ $i++; }}</td>
                        <td>{{ $product->supplier }}</td>
                        <td>{{ $product->sub_dept }}</td>
                        <td>{{ $product->sub_dept_name }}</td>
                        <td>{{ $product->pro_model }}</td>
                        <td>{{ $product->skucode }}</td>
                        <td>{{ $product->pro_name }}</td>
                        <td>{{ $product->type_product }}</td>
                        <td>{{ $product->price }}</td>
                        <td>{{ $product->price_vat }}</td>
                        <td>{{ $product->com }}</td>
                        <td>
                            @if($editMode && in_array('Edit_product', $permissions))
                            <button class="btn btn-warning edit-btn" 
                            data-id="{{ $product->id }}" data-supplier="{{ $product->supplier }}" 
                            data-sub_dept="{{ $product->sub_dept }}" data-sub_dept_name="{{ $product->sub_dept_name }}" 
                            data-pro_model="{{ $product->pro_model }}" data-skucode="{{ $product->skucode }}" 
                            data-pro_name="{{ $product->pro_name }}" 
                            data-type_product="{{ $product->type_product }}" data-price="{{ $product->price }}"
                            data-price_vat="{{ $product->price_vat }}" data-com="{{ $product->com }}">Edit</button>
                            &nbsp;
                            @endif
                            @if($editMode && in_array('Del_product', $permissions))
                            <button class="btn btn-danger delete-btn" data-id="{{ $product->id }}">Delete</button>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    <!-- Modal for Add product -->
    <div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addModalLabel">Add product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="addForm" method="POST" action="{{ route('product.store') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="add_supplier" class="form-label">Supplier</label>
                                    <input type="text" class="form-control" id="add_supplier" name="supplier" >
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="add_sub_dept" class="form-label">Sub dept</label>
                                    <input type="text" class="form-control" id="add_sub_dept" name="sub_dept" >
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="add_sub_dept_name" class="form-label">Sub dept name</label>
                                    <input type="text" class="form-control" id="add_sub_dept_name" name="sub_dept_name" >
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="add_pro_model" class="form-label">Product model</label>
                                    <input type="text" class="form-control" id="add_pro_model" name="pro_model" >
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="add_skucode" class="form-label">skucode</label>
                                    <input type="text" class="form-control" id="add_skucode" name="skucode" >
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="add_pro_name" class="form-label">Product name</label>
                                    <input type="text" class="form-control" id="add_pro_name" name="pro_name" >
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="add_type_product" class="form-label">Type Product</label>
                                    {{-- <input type="text" class="form-control" id="add_type_product" name="type_product" required> --}}
                                    <select id="add_type_product" name="type_product" class="form-control chosen-select" required>
                                        <option value="">Select Type Product</option>
                                        <option value="TV">TV</option>
                                        <option value="AV">AV</option>
                                        <option value="HA">HA</option>
                                        <!-- Add more options as needed -->
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="add_price" class="form-label">Price</label>
                                    <input type="text"  class="form-control" id="add_price" name="price" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="add_price_vat" class="form-label">Price (VAT)</label>
                                    <input type="text"  class="form-control" id="add_price_vat" name="price_vat" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="add_com" class="form-label">Com</label>
                                    <input type="text"  class="form-control" id="add_com" name="com" required>
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
        <div class="modal-dialog modal-lg">
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
                                <div class="col-md-6 mb-3">
                                    <label for="supplier" class="form-label">Supplier</label>
                                    <input type="text" class="form-control" id="supplier" name="supplier" >
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="sub_dept" class="form-label">Sub dept</label>
                                    <input type="text" class="form-control" id="sub_dept" name="sub_dept" >
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="sub_dept_name" class="form-label">Sub dept name</label>
                                    <input type="text" class="form-control" id="sub_dept_name" name="sub_dept_name" >
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="pro_model" class="form-label">Product model</label>
                                    <input type="text" class="form-control" id="pro_model" name="pro_model" >
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="skucode" class="form-label">skucode</label>
                                    <input type="text" class="form-control" id="skucode" name="skucode" >
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="pro_name" class="form-label">Product name</label>
                                    <input type="text" class="form-control" id="pro_name" name="pro_name" >
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="type_product" class="form-label">Type Product</label>
                                    {{-- <input type="text" class="form-control" id="type_product" name="type_product" required> --}}
                                    <select id="type_product" name="type_product" class="form-control chosen-select" required>
                                        <option value="TV">TV</option>
                                        <option value="AV">AV</option>
                                        <option value="HA">HA</option>
                                        <!-- Add more options as needed -->
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="price" class="form-label">Price</label>
                                    <input type="text"  class="form-control" id="price" name="price" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="price_vat" class="form-label">Price (VAT)</label>
                                    <input type="text"  class="form-control" id="price_vat" name="price_vat" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="com" class="form-label">Com</label>
                                    <input type="text"  class="form-control" id="com" name="com" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" id="product_id_hidden" name="id">
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
                    Are you sure you want to Delete this product?
                </div>
                <div class="modal-footer">
                    <form id="deleteForm" method="POST">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" id="product_id_delete" name="id">
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
                var supplier = $(this).data('supplier');
                var sub_dept = $(this).data('sub_dept');
                var sub_dept_name = $(this).data('sub_dept_name');
                var pro_model = $(this).data('pro_model');
                var skucode = $(this).data('skucode');
                var pro_name = $(this).data('pro_name');
                var type_product = $(this).data('type_product');
                var price = $(this).data('price');
                var price_vat = $(this).data('price_vat');
                var com = $(this).data('com');

                $('#supplier').val(supplier);
                $('#sub_dept').val(sub_dept);
                $('#sub_dept_name').val(sub_dept_name);
                $('#pro_model').val(pro_model);
                $('#skucode').val(skucode);
                $('#pro_name').val(pro_name);
                $('#type_product').val(type_product).trigger("chosen:updated");
                $('#price').val(price);
                $('#price_vat').val(price_vat);
                $('#com').val(com);
                $('#product_id_hidden').val(id);
                
                $('#editForm').attr('action', '/product/' + id);
                $('#editModal').modal('show');
            });

            // Delete button click event
            $('.delete-btn').on('click', function() {
                var id = $(this).data('id');
                $('#product_id_delete').val(id);
                $('#deleteForm').attr('action', '/product/' + id + '/status');
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