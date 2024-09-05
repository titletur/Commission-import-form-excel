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
        @if(session('user_permission') == 'admin')
        <li class="nav-item dropdown @yield('admin')">
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

</body>
</html>