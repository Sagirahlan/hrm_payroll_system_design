<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Zamfara HR & Payroll') }} - @yield('title')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background: linear-gradient(135deg, #e0f7fa 0%, #ffffff 100%);
        }
        
        .navbar {
            background: rgb(255, 255, 255);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 900;
        }
        
        .navbar-brand {
            color: #00bcd4 !important;
            font-weight: bold;
        }
        
        .card {
            border-radius: 1.5rem;
            background: linear-gradient(135deg, #e0f7fa 0%, #ffffff 100%);
        }
        
        .card-header {
            background: #00bcd4 !important;
            border-radius: 1.5rem 1.5rem 0 0 !important;
            color: white !important;
            font-weight: bold;
        }
        
        .form-label {
            font-weight: bold;
            color: #00bcd4;
        }
        
        .form-control,
        .form-select {
            border-radius: 10px;
            border: 1px solid #b2ebf2;
            background: #f7fafc;
        }
        
        .form-control:focus,
        .form-select:focus {
            border-color: #00bcd4;
            box-shadow: 0 0 0 0.2rem rgba(0,188,212,.15);
        }
        
        .btn-primary {
            background: #00bcd4;
            border: none;
            color: #fff;
            font-weight: bold;
            border-radius: 10px;
        }
        
        .btn-primary:hover {
            background: #0097a7;
            color: #fff;
        }
        
        footer {
            background: #e0f7fa;
            color: rgb(25, 51, 224);
            padding: 15px 0;
            text-align: center;
            margin-top: auto;
        }
        
        .alert {
            border-radius: 10px;
        }
    </style>
    @yield('styles')
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="{{ url('/') }}">
                <i class="fas fa-user-clock me-2"></i>{{ config('app.name', 'Zamfara HR & Payroll') }}
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    @auth
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <img src="{{ auth()->user()->employee && auth()->user()->employee->photo_path ? asset('storage/' . auth()->user()->employee->photo_path) : asset('images/default-image.png') }}" alt="Profile" class="rounded-circle me-2" style="width:32px; height:32px; object-fit:cover; border:2px solid #00bcd4;">
                                <span>{{ auth()->user()->username }}</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="userDropdown">
                                <li>
                                    <a class="dropdown-item d-flex align-items-center" href="{{ route('profile') }}">
                                        <i class="fas fa-user-circle me-2 text-primary"></i> Profile
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item d-flex align-items-center">
                                            <i class="fas fa-sign-out-alt me-2 text-danger"></i> Logout
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                   
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-grow-1">
        <div class="container my-4">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            
            @yield('content')
        </div>
    </main>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p class="mb-0">© {{ date('Y') }} {{ config('app.name', 'Zamfara HR & Payroll') }}. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>
</html>