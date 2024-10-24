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
                    <th>supplier number</th>
                    <th>item number</th>
                    <th>barcode</th>
                    <th>item Description</th>
                    <th>type product</th>
                    <th>pack_type</th>
                    <th>price(vat)</th>
                    <th>com</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $i = 1;
                @endphp
                @foreach($products as $product)
                    <tr>
                        <td>{{ $i++; }}</td>
                        <td>{{ $product->supplier_number }}</td>
                        <td>{{ $product->item_number }}</td>
                        <td>{{ $product->barcode }}</td>
                        <td>{{ $product->item_des }}</td>
                        <td>{{ $product->type_product }}</td>
                        <td>{{ $product->pack_type }}</td>
                        <td>{{ $product->price_vat }}</td>
                        <td>{{ $product->com }}</td>
                        <td>
                            @if($editMode && in_array('Edit_product', $permissions))
                            <button class="btn btn-warning edit-btn" 
                            data-id="{{ $product->id }}" data-supplier_number="{{ $product->supplier_number }}" 
                            data-item_number="{{ $product->item_number }}" data-barcode="{{ $product->barcode }}" 
                            data-item_des="{{ $product->item_des }}" data-type_product="{{ $product->type_product }}" 
                            data-pack_type="{{ $product->pack_type }}" data-price_vat="{{ $product->price_vat }}"
                            data-com="{{ $product->com }}">Edit</button>
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
                                    <label for="add_supplier_number" class="form-label">Supplier number</label>
                                    <input type="text" class="form-control" id="add_supplier_number" name="supplier_number" >
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="add_item_number" class="form-label">item number</label>
                                    <input type="text" class="form-control" id="add_item_number" name="item_number" >
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="add_barcode" class="form-label">Barcode</label>
                                    <input type="text" class="form-control" id="add_barcode" name="barcode" >
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="add_item_des" class="form-label">item Description</label>
                                    <input type="text" class="form-control" id="add_item_des" name="item_des" >
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="add_type_product" class="form-label">Type product</label>
                                    <select id="add_type_product" name="type_product" class="form-control chosen-select" required>
                                        <option value="TV">TV</option>
                                        <option value="AV">AV</option>
                                        <option value="HA">HA</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="add_pack_type" class="form-label">Pack types</label>
                                    <input type="text" class="form-control" id="add_pack_type" name="pack_type" >
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="add_price_vat" class="form-label">Price(Vat)</label>
                                    <input type="text" class="form-control" id="add_price_vat" name="price_vat" >
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="add_com" class="form-label">Com</label>
                                    <input type="text" class="form-control" id="add_com" name="com" >
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
                                    <label for="supplier_number" class="form-label">Supplier number</label>
                                    <input type="text" class="form-control" id="supplier_number" name="supplier_number" >
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="item_number" class="form-label">item number</label>
                                    <input type="text" class="form-control" id="item_number" name="item_number" >
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="barcode" class="form-label">Barcode</label>
                                    <input type="text" class="form-control" id="barcode" name="barcode" >
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="item_des" class="form-label">item Description</label>
                                    <input type="text" class="form-control" id="item_des" name="item_des" >
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="type_product" class="form-label">Type product</label>
                                    <select id="type_product" name="type_product" class="form-control chosen-select" required>
                                        <option value="TV">TV</option>
                                        <option value="AV">AV</option>
                                        <option value="HA">HA</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="pack_type" class="form-label">Pack types</label>
                                    <input type="text" class="form-control" id="pack_type" name="pack_type" >
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="price_vat" class="form-label">Price(Vat)</label>
                                    <input type="text" class="form-control" id="price_vat" name="price_vat" >
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="com" class="form-label">Com</label>
                                    <input type="text" class="form-control" id="com" name="com" >
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
                var supplier_number = $(this).data('supplier_number');
                var item_number = $(this).data('item_number');
                var barcode = $(this).data('barcode');
                var item_des = $(this).data('item_des');
                var pack_type = $(this).data('pack_type');
                var type_product = $(this).data('type_product');
                var price_vat = $(this).data('price_vat');
                var com = $(this).data('com');

                $('#supplier_number').val(supplier_number);
                $('#item_number').val(item_number);
                $('#barcode').val(barcode);
                $('#item_des').val(item_des);
                $('#pack_type').val(pack_type);
                $('#type_product').val(type_product).trigger("chosen:updated");
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