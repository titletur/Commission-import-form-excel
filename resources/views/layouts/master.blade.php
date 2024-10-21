<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title')</title>
    <link rel="icon" href="{{ asset('icon.jpg') }}" type="image/x-icon">
    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/cssfont.css') }}">
    <link rel="stylesheet" href="{{ asset('css/chosen.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    {{-- <link rel="stylesheet" href="{{ asset('css/all.min.css') }}"> --}}
    <link rel="stylesheet" href="{{ asset('css/jquery.datatables.min.css') }}">
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('js/popper.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('js/chosen.jquery.min.js') }}"></script>
    <script src="{{ asset('js/jquery.dataTables.min.js') }}"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* Loading overlay styles */
        .loading-overlay {
            display: none; /* Hidden by default */
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.8); /* Semi-transparent background */
            z-index: 9999; /* High z-index to cover everything */
            justify-content: center;
            align-items: center;
        }
    
        .loading-spinner {
            border: 16px solid #00ff4c; /* Light grey */
            border-top: 16px solid #1195ed; /* Blue */
            border-radius: 50%;
            width: 120px;
            height: 120px;
            animation: spin 2s linear infinite;
        }
    
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
    
    <style>
        body {
            padding-top: 20px; /* Adjust based on navbar height */
            font-family: 'Sarabun', sans-serif;
            font-size: 16px;
            background-color: #f4f4f9;
            color: #333;
        }
        .navbar-nav {
            flex-direction: row;
        }
        .nav-item {
            padding-left: 10px;
            padding-right: 10px;
        }
        .navbar {
            z-index: 1030; /* Make sure it's above other content */
        }
        .logout-btn {
            color: #fff;
            border: none;
            background: none;
        }
        .logout-btn:hover {
            color: #dc3545;
        }
        <style>
        .table thead th {
            background-color: #f8f9fa;
            color: #495057;
        }
        .table td, .table th {
            vertical-align: middle;
        }
        .btn-disabled {
            pointer-events: none;
            opacity: 0.6;
        }
        .year-selector {
            margin-bottom: 20px;
        }
        .status-completed {
            color: green;
            font-weight: bold;
        }
        .status-pending {
            color: red;
            font-weight: bold;
        }
        .container-fluid {
            padding-left: 5%;
            padding-right: 5%;
        }
    </style>
</head>
<body>
    <div id="loading-overlay" class="loading-overlay">
        <div class="loading-spinner"></div>
    </div>

    @if (session('no_permission'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'Error!',
                    text: "{{ session('no_permission') }}",
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            });
        </script>
    @endif
    @php
    $permissions = json_decode(Auth::user()->permissions, true); // แปลง JSON เป็น array
    @endphp
<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <a class="navbar-brand" href="#"><img src="{{ asset('logo.png') }}" width="100" height="25" alt="Logo"></a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav mr-auto">
            <li class="nav-item @yield('home')">
                {{-- <a class="nav-link" href="{{ route('import') }}">Home</a> --}}
                <a class="nav-link" href="{{ route('commissions.index') }}">Home</a>
            </li>
            <li class="nav-item @yield('store')">
                <a class="nav-link" href="{{ route('stores.index') }}">Store</a>
            </li>
            <li class="nav-item @yield('pc')">
                <a class="nav-link" href="{{ route('pc.index') }}">PC</a>
            </li>
            <li class="nav-item @yield('sale')">
                <a class="nav-link" href="{{ route('sale.index') }}">Sale</a>
            </li>
            <li class="nav-item @yield('product')">
                <a class="nav-link" href="{{ route('product.index') }}">Product</a>
            </li>
            @if(in_array('Add_user', $permissions))
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    Admin
                </a>
                <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                    <a class="dropdown-item" href="{{ route('users.index') }}">User</a>
                </div>
            </li>
            @endif
        </ul>
        <ul class="navbar-nav ml-auto">
            <li class="nav-item dropdown @yield('user')">
                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    {{ Auth::user()->name }}
                </a>
                <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                    <a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="fa fa-user"></i> Profile</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                    <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();">
                        <i class="fa fa-sign-out-alt"></i> Logout
                    </a>
                    </form>
                </div>
            </li>
        </ul>
    </div>
</nav>

<div class="container-fluid  mt-5">
    @yield('content')
</div>



<script>
    // Show loading overlay on AJAX requests
    document.addEventListener('DOMContentLoaded', function () {
        const loadingOverlay = document.getElementById('loading-overlay');

        // Show overlay on AJAX request start
        document.addEventListener('ajaxStart', function () {
            loadingOverlay.style.display = 'flex';
        });

        // Hide overlay on AJAX request complete
        document.addEventListener('ajaxComplete', function () {
            loadingOverlay.style.display = 'none';
        });
        
        // Show overlay on form submission
        document.querySelector('form').addEventListener('submit', function() {
            loadingOverlay.style.display = 'flex';
        });
    });

    // Simulating AJAX events (you can remove these lines if you're using real AJAX)
    document.addEventListener('DOMContentLoaded', function () {
        // Simulate an AJAX request
        setTimeout(() => {
            const event = new Event('ajaxStart');
            document.dispatchEvent(event);

            // Simulate request completion
            setTimeout(() => {
                const event = new Event('ajaxComplete');
                document.dispatchEvent(event);
            }, 100); // Adjust timeout to simulate request time
        }, 100); // Simulate request delay
    });

</script>
<script type="text/javascript">
    $(document).ready(function(){
        $('.chosen-select').chosen({
            width: "95%"  // กำหนดความกว้างให้เต็ม
        });
    });
</script>
</body>
</html>
