<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Zamfara HR & Payroll') }} - @yield('title')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background: linear-gradient(135deg, #e0f7fa 0%, #ffffff 100%);
        }
        
        /* Dark mode body */
        [data-bs-theme="dark"] body {
            background: linear-gradient(135deg, #0c2e3d 0%, #1a1a1a 100%);
            color: #e0f7fa;
        }
        
        .navbar {
            background: rgb(255, 255, 255);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 900;
        }
        
        /* Dark mode navbar */
        [data-bs-theme="dark"] .navbar {
            background: #0c2e3d;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.5);
        }
        
        .navbar-brand {
            color: #00bcd4 !important;
            font-weight: bold;
        }
        
        /* Dark mode navbar brand */
        [data-bs-theme="dark"] .navbar-brand {
            color: #e0f7fa !important;
        }
        
        .card {
            border-radius: 1.5rem;
            background: linear-gradient(135deg, #e0f7fa 0%, #ffffff 100%);
        }
        
        /* Dark mode card */
        [data-bs-theme="dark"] .card {
            background: linear-gradient(135deg, #0c2e3d 0%, #1a1a1a 100%);
            color: #e0f7fa;
        }
        
        .card-header {
            background: #00bcd4 !important;
            border-radius: 1.5rem 1.5rem 0 0 !important;
            color: white !important;
            font-weight: bold;
        }
        
        /* Dark mode card header */
        [data-bs-theme="dark"] .card-header {
            background: #00838f !important;
            color: #e0f7fa !important;
        }
        
        .form-label {
            font-weight: bold;
            color: #00bcd4;
        }
        
        /* Dark mode form label */
        [data-bs-theme="dark"] .form-label {
            color: #e0f7fa;
        }
        
        .form-control,
        .form-select {
            border-radius: 10px;
            border: 1px solid #b2ebf2;
            background: #f7fafc;
        }
        
        /* Dark mode form controls */
        [data-bs-theme="dark"] .form-control,
        [data-bs-theme="dark"] .form-select {
            background: #2a434a;
            border: 1px solid #00838f;
            color: #e0f7fa;
        }
        
        .form-control:focus,
        .form-select:focus {
            border-color: #00bcd4;
            box-shadow: 0 0 0 0.2rem rgba(0,188,212,.15);
        }
        
        /* Dark mode form control focus */
        [data-bs-theme="dark"] .form-control:focus,
        [data-bs-theme="dark"] .form-select:focus {
            border-color: #00bcd4;
            box-shadow: 0 0 0 0.2rem rgba(0,188,212,.25);
        }
        
        .btn-primary {
            background: #00bcd4;
            border: none;
            color: #fff;
            font-weight: bold;
            border-radius: 10px;
        }
        
        /* Dark mode primary button */
        [data-bs-theme="dark"] .btn-primary {
            background: #00838f;
            border: none;
            color: #e0f7fa;
        }
        
        .btn-primary:hover {
            background: #0097a7;
            color: #fff;
        }
        
        /* Dark mode primary button hover */
        [data-bs-theme="dark"] .btn-primary:hover {
            background: #006064;
            color: #e0f7fa;
        }
        
        footer {
            background: #e0f7fa;
            color: rgb(25, 51, 224);
            padding: 15px 0;
            text-align: center;
            margin-top: auto;
        }
        
        /* Dark mode footer */
        [data-bs-theme="dark"] footer {
            background: #0c2e3d;
            color: #e0f7fa;
        }
        
        .alert {
            border-radius: 10px;
        }
        
        /* Dark mode alerts */
        [data-bs-theme="dark"] .alert-success {
            background: #0c2e3d;
            border: 1px solid #00838f;
            color: #e0f7fa;
        }
        
        [data-bs-theme="dark"] .alert-danger {
            background: #3d0c0c;
            border: 1px solid #8f0000;
            color: #f8d7da;
        }
        
        /* Dark mode toggle button */
        .dark-mode-toggle {
            background: transparent;
            border: none;
            color: #00bcd4;
            font-size: 1.2rem;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .dark-mode-toggle:hover {
            background: rgba(0, 188, 212, 0.1);
        }
        
        [data-bs-theme="dark"] .dark-mode-toggle {
            color: #e0f7fa;
        }
        
        [data-bs-theme="dark"] .dark-mode-toggle:hover {
            background: rgba(224, 247, 250, 0.1);
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
                    <!-- Dark mode toggle button -->
                    <li class="nav-item d-flex align-items-center me-2">
                        <button class="dark-mode-toggle" id="darkModeToggle" title="Toggle dark mode">
                            <i class="fas fa-moon"></i>
                        </button>
                    </li>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Dark mode toggle functionality
        const darkModeToggle = document.getElementById('darkModeToggle');
        const htmlElement = document.documentElement;
        
        // Check for saved theme preference or default to light mode
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme) {
            htmlElement.setAttribute('data-bs-theme', savedTheme);
            updateDarkModeIcon(savedTheme);
        } else {
            // Default to light mode
            htmlElement.setAttribute('data-bs-theme', 'light');
            updateDarkModeIcon('light');
        }
        
        // Toggle dark mode when button is clicked
        darkModeToggle.addEventListener('click', () => {
            const currentTheme = htmlElement.getAttribute('data-bs-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            
            htmlElement.setAttribute('data-bs-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateDarkModeIcon(newTheme);
        });
        
        // Update the dark mode icon based on current theme
        function updateDarkModeIcon(theme) {
            const icon = darkModeToggle.querySelector('i');
            if (theme === 'dark') {
                icon.classList.remove('fa-moon');
                icon.classList.add('fa-sun');
            } else {
                icon.classList.remove('fa-sun');
                icon.classList.add('fa-moon');
            }
        }
    </script>
    @yield('scripts')
</body>
</html>