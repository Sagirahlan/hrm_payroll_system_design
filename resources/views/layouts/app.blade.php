<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
        
        /* Dark mode styles */
        [data-bs-theme="dark"] body {
            background: linear-gradient(135deg, #0c2e3d 0%, #1a1a1a 100%);
            color: #e0f7fa;
        }
        
        .sidebar {
            min-width: 180px;
            max-width: 180px;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            background: #00bcd4;
            color: #ffffff;
            transition: transform 0.3s ease;
            z-index: 1000;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
        }
        
        /* Dark mode sidebar */
        [data-bs-theme="dark"] .sidebar {
            background: #00838f;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.5);
        }
        
        .sidebar.collapsed {
            transform: translateX(-180px);
        }
        .sidebar-header {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid #b2ebf2;
            background: #00bcd4;
        }
        
        /* Dark mode sidebar header */
        [data-bs-theme="dark"] .sidebar-header {
            border-bottom: 1px solid #00838f;
            background: #00838f;
        }
        
        .sidebar-header img {
            max-width: 100px;
        }
        .sidebar .nav-link {
            color: rgb(25, 0, 136);
            padding: 12px 20px;
            display: flex;
            align-items: center;
            font-size: 0.8rem;
            transition: background-color 0.2s ease;
            border-radius: 20px;
            margin: 4px 8px;
        }
        
        /* Dark mode sidebar links */
        [data-bs-theme="dark"] .sidebar .nav-link {
            color: #e0f7fa;
        }
        
        .sidebar .nav-link:hover {
            background: #b2ebf2;
            color: #00bcd4;
        }
        
        /* Dark mode sidebar link hover */
        [data-bs-theme="dark"] .sidebar .nav-link:hover {
            background: #00838f;
            color: #e0f7fa;
        }
        
        .sidebar .nav-link.active {
            background: #e0f7fa;
            color: #00bcd4;
            border-left: 3px solid #00bcd4;
            font-size: 0.75rem;
            padding: 8px 16px;
        }
        
        /* Dark mode active sidebar link */
        [data-bs-theme="dark"] .sidebar .nav-link.active {
            background: #00838f;
            color: #e0f7fa;
            border-left: 3px solid #e0f7fa;
        }
        
        .sidebar .nav-link i {
            margin-right: 12px;
            width: 20px;
            text-align: center;
        }
        .content {
            margin-left: 180px;
            flex: 1;
            padding: 20px;
            transition: margin-left 0.3s ease;
        }
        .content.collapsed {
            margin-left: 0;
        }
        .navbar {
            background: rgb(255, 255, 255);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            left: 180px;
            z-index: 900;
            width: calc(100% - 180px);
        }
        
        /* Dark mode navbar */
        [data-bs-theme="dark"] .navbar {
            background: #0c2e3d;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.5);
        }
        
        @media (max-width: 768px) {
            .navbar {
                left: 0;
                width: 100%;
            }
        }
        .navbar-brand img {
            height: 40px;
            margin-right: 10px;
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
        }
        
        /* Dark mode card header */
        [data-bs-theme="dark"] .card-header {
            background: #00838f !important;
        }
        
        .card-header h4 {
            color: #fff !important;
            font-weight: bold;
        }
        
        /* Dark mode card header text */
        [data-bs-theme="dark"] .card-header h4 {
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
            border-radius: 2rem;
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
        
        .btn-info {
            background: #00bcd4;
            border: none;
            color: #fff;
            font-weight: bold;
        }
        
        /* Dark mode info button */
        [data-bs-theme="dark"] .btn-info {
            background: #00838f;
            border: none;
            color: #e0f7fa;
        }
        
        .btn-info:hover {
            background: #0097a7;
            color: #fff;
        }
        
        /* Dark mode info button hover */
        [data-bs-theme="dark"] .btn-info:hover {
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
        
        /* Dark mode dropdown */
        [data-bs-theme="dark"] .dropdown-menu {
            background: #0c2e3d;
            color: #e0f7fa;
            border: 1px solid #00838f;
        }
        
        [data-bs-theme="dark"] .dropdown-item {
            color: #e0f7fa;
        }
        
        [data-bs-theme="dark"] .dropdown-item:hover {
            background: #00838f;
        }
        
        /* Dark mode alerts */
        [data-bs-theme="dark"] .alert-success {
            background: #0c2e3d;
            border: 1px solid #00838f;
            color: #e0f7fa;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-180px);
            }
            .sidebar.active {
                transform: translateX(0);
            }
            .content {
                margin-left: 0;
            }
            .content.collapsed {
                margin-left: 0;
            }
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
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <button class="btn btn-outline-light me-2 d-lg-none" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>
            <!-- Search & Filter Form -->
            <form class="d-flex me-auto ms-3" method="GET" action="{{ url()->current() }}">
                <input class="form-control me-2" type="search" name="search" placeholder="Search..." value="{{ request('search') }}" aria-label="Search">
                <select class="form-select me-2" name="filter" style="width: auto;">
                    @yield('filter-options')
                    <option value="">All</option>
                    <option value="active" {{ request('filter') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('filter') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
                <button class="btn btn-outline-primary" type="submit"><i class="fas fa-search"></i></button>
            </form>
            <!-- End Search & Filter Form -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <!-- Dark mode toggle button -->
                    <li class="nav-item d-flex align-items-center">
                        <button class="dark-mode-toggle" id="darkModeToggle" title="Toggle dark mode">
                            <i class="fas fa-moon"></i>
                        </button>
                    </li>
                    @auth
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="color: #3288d3;">
                                <img src="{{ auth()->user()->employee && auth()->user()->employee->photo_path ? asset('storage/' . auth()->user()->employee->photo_path) : asset('images/default-image.png') }}" alt="Profile" class="rounded-circle me-2" style="width:32px; height:32px; object-fit:cover; border:2px solid #3288d3;">
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
                    @else
                        <li class="nav-item">
                            <a class="nav-link" style="color: #3288d3;" href="{{ route('login') }}"><i class="fas fa-sign-in-alt me-1"></i> Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" style="color: #3288d3;" href="{{ route('register') }}"><i class="fas fa-user-plus me-1"></i> Register</a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <img src="{{ asset('images/logo-white.png') }}" alt="Logo">
        </div>
        <nav class="nav flex-column">
            <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            @if(auth()->user() && (auth()->user()->hasPermissionTo('manage_employees') || auth()->user()->hasPermissionTo('view_employees')))
            <div class="nav-item dropdown">
                <a class="nav-link dropdown-toggle d-flex align-items-center {{ request()->routeIs('employees.*') ? 'active' : '' }}" href="#" id="employeesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="color: #000;">
                    <i class="fas fa-users me-2"></i> Employees
                </a>
                <ul class="dropdown-menu bg-light border-0 shadow" aria-labelledby="employeesDropdown" style="min-width: 140px; font-size: 0.85rem;">
                    <li>
                        <a class="dropdown-item d-flex align-items-center {{ request()->routeIs('employees.index') ? 'active' : '' }}" href="{{ route('employees.index') }}" style="padding: 6px 12px;">
                            <i class="fas fa-list me-2 text-primary" style="font-size: 0.9em;"></i> Index
                        </a>
                    </li>
                    @if(auth()->user()->hasPermissionTo('manage_employees'))
                    <li>
                        <a class="dropdown-item d-flex align-items-center {{ request()->routeIs('employees.create') ? 'active' : '' }}" href="{{ route('employees.create') }}" style="padding: 6px 12px;">
                            <i class="fas fa-plus me-2 text-primary" style="font-size: 0.9em;"></i> Create
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center {{ request()->routeIs('pending-changes.*') ? 'active' : '' }}" href="{{ route('pending-changes.index') }}" style="padding: 6px 12px;">
                            <i class="fas fa-hourglass-half me-2 text-primary" style="font-size: 0.9em;"></i> Pending Changes
                        </a>
                    </li>
                    @endif
                </ul>
            </div>
            @endif
            

            @if(auth()->user() && auth()->user()->hasPermissionTo('manage_users'))
            <div class="nav-item dropdown">
                <a class="nav-link dropdown-toggle d-flex align-items-center {{ request()->routeIs('users.*') || request()->routeIs('roles.*') ? 'active' : '' }}" href="#" id="usersDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="color: #000;">
                    <i class="fas fa-user me-2"></i> Users
                </a>
                <ul class="dropdown-menu bg-light border-0 shadow" aria-labelledby="usersDropdown" style="min-width: 140px; font-size: 0.85rem;">
                    <li>
                        <a class="dropdown-item d-flex align-items-center {{ request()->routeIs('users.index') ? 'active' : '' }}" href="{{ route('users.index') }}" style="padding: 6px 12px;">
                            <i class="fas fa-list me-2 text-primary" style="font-size: 0.9em;"></i> Index
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center {{ request()->routeIs('users.create') ? 'active' : '' }}" href="{{ route('users.create') }}" style="padding: 6px 12px;">
                            <i class="fas fa-plus me-2 text-primary" style="font-size: 0.9em;"></i> Create
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center {{ request()->routeIs('roles.index') ? 'active' : '' }}" href="{{ route('roles.index') }}" style="padding: 6px 12px;">
                            <i class="fas fa-user-tag me-2 text-primary" style="font-size: 0.9em;"></i> Roles
                        </a>
                    </li>
                </ul>
            </div>
            @endif

            @if(auth()->user() && auth()->user()->hasPermissionTo('manage_departments'))
            <div class="nav-item dropdown">
                <a class="nav-link dropdown-toggle d-flex align-items-center {{ request()->routeIs('departments.*') ? 'active' : '' }}" href="#" id="departmentsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="color: #000;">
                    <i class="fas fa-building me-2"></i> Departments
                </a>
                <ul class="dropdown-menu bg-light border-0 shadow" aria-labelledby="departmentsDropdown" style="min-width: 140px; font-size: 0.85rem;">
                    <li>
                        <a class="dropdown-item d-flex align-items-center {{ request()->routeIs('departments.index') ? 'active' : '' }}" href="{{ route('departments.index') }}" style="padding: 6px 12px;">
                            <i class="fas fa-list me-2 text-primary" style="font-size: 0.9em;"></i> Index
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center {{ request()->routeIs('departments.create') ? 'active' : '' }}" href="{{ route('departments.create') }}" style="padding: 6px 12px;">
                            <i class="fas fa-plus me-2 text-primary" style="font-size: 0.9em;"></i> Create
                        </a>
                    </li>
                </ul>
            </div>
            @endif

            @if(auth()->user() && auth()->user()->hasPermissionTo('manage_biometrics'))
            <a class="nav-link {{ request()->routeIs('biometrics.*') ? 'active' : '' }}" href="{{ route('biometrics.index') }}">
                <i class="fas fa-fingerprint"></i> Biometrics 
            </a>
            @endif

            @if(auth()->user() && auth()->user()->hasPermissionTo('view_audit_logs'))
            <a class="nav-link {{ request()->routeIs('audit-trails.*') ? 'active' : '' }}" href="{{ route('audit-trails.index') }}">
                <i class="fas fa-history"></i> Audit Trail
            </a>
            @endif

            @if(auth()->user() && auth()->user()->hasPermissionTo('manage_disciplinary'))
            <div class="nav-item dropdown">
                <a class="nav-link dropdown-toggle d-flex align-items-center {{ request()->routeIs('disciplinary.*') ? 'active' : '' }}" href="#" id="disciplinaryDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="color: #000;">
                    <i class="fas fa-gavel me-2"></i> Disciplinary
                </a>
                <ul class="dropdown-menu bg-light border-0 shadow" aria-labelledby="disciplinaryDropdown" style="min-width: 140px; font-size: 0.85rem;">
                    <li>
                        <a class="dropdown-item d-flex align-items-center {{ request()->routeIs('disciplinary.index') ? 'active' : '' }}" href="{{ route('disciplinary.index') }}" style="padding: 6px 12px;">
                            <i class="fas fa-list me-2 text-primary" style="font-size: 0.9em;"></i> Index
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center {{ request()->routeIs('disciplinary.create') ? 'active' : '' }}" href="{{ route('disciplinary.create') }}" style="padding: 6px 12px;">
                            <i class="fas fa-plus me-2 text-primary" style="font-size: 0.9em;"></i> Create
                        </a>
                    </li>
                </ul>
            </div>
            @endif

            @if(auth()->user() && (auth()->user()->hasPermissionTo('manage_retirement') || auth()->user()->hasPermissionTo('view_retirement')))
            <div class="nav-item dropdown">
                <a class="nav-link dropdown-toggle d-flex align-items-center {{ request()->routeIs('retirements.*') ? 'active' : '' }}" href="#" id="retirementsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="color: #000;">
                    <i class="fas fa-briefcase me-2"></i> Retirements
                </a>
                <ul class="dropdown-menu bg-light border-0 shadow" aria-labelledby="retirementsDropdown" style="min-width: 140px; font-size: 0.85rem;">
                    <li>
                        <a class="dropdown-item d-flex align-items-center {{ request()->routeIs('retirements.index') ? 'active' : '' }}" href="{{ route('retirements.index') }}" style="padding: 6px 12px;">
                            <i class="fas fa-list me-2 text-primary" style="font-size: 0.9em;"></i> Index
                        </a>
                    </li>
                    @if(auth()->user()->hasPermissionTo('manage_retirement'))
                    <li>
                        <a class="dropdown-item d-flex align-items-center {{ request()->routeIs('retirements.create') ? 'active' : '' }}" href="{{ route('retirements.create') }}" style="padding: 6px 12px;">
                            <i class="fas fa-plus me-2 text-primary" style="font-size: 0.9em;"></i> Create
                        </a>
                    </li>
                    @endif
                </ul>
            </div>
            @endif

            @if(auth()->user() && auth()->user()->hasPermissionTo('manage_employees'))
            <a class="nav-link {{ request()->routeIs('pensioners.index') ? 'active' : '' }}" href="{{ route('pensioners.index') }}">
                <i class="fas fa-user-shield me-2"></i> Pensioners
            </a>
            @endif

            @if(auth()->user() && auth()->user()->hasPermissionTo('manage_sms'))
            <a class="nav-link {{ request()->routeIs('sms.*') ? 'active' : '' }}" href="{{ route('sms.index') }}">
                <i class="fas fa-sms"></i> SMS Notifications 
            </a>
            @endif

            @if(auth()->user() && auth()->user()->hasPermissionTo('manage_payroll'))
                <a class="nav-link {{ request()->routeIs('payroll.*') ? 'active' : '' }}" href="{{ route('payroll.index') }}">
                    <i class="fas fa-money-check-alt me-2"></i> Payroll
                </a>
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center {{ request()->routeIs('payroll.adjustments.manage') || request()->routeIs('deduction-types.*') || request()->routeIs('addition-types.*') ? 'active' : '' }}" href="#" id="deductionsAdditionsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-users-cog me-2"></i> Manage All Deductions/Additions
                    </a>
                    <ul class="dropdown-menu bg-light border-0 shadow" aria-labelledby="deductionsAdditionsDropdown" style="min-width: 140px; font-size: 0.85rem;">
                        <li>
                            <a class="dropdown-item d-flex align-items-center {{ request()->routeIs('payroll.additions') ? 'active' : '' }}" href="{{ route('payroll.additions') }}" style="padding: 6px 12px;">
                                <i class="fas fa-plus-circle me-2 text-primary" style="font-size: 0.9em;"></i> Bulk Additions
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center {{ request()->routeIs('payroll.deductions') ? 'active' : '' }}" href="{{ route('payroll.deductions') }}" style="padding: 6px 12px;">
                                <i class="fas fa-minus-circle me-2 text-primary" style="font-size: 0.9em;"></i> Bulk Deductions
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center {{ request()->routeIs('payroll.adjustments.manage') ? 'active' : '' }}" href="{{ route('payroll.adjustments.manage') }}" style="padding: 6px 12px;">
                                <i class="fas fa-users me-2 text-primary" style="font-size: 0.9em;"></i> Employee Adjustments
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center {{ request()->routeIs('deduction-types.*') ? 'active' : '' }}" href="{{ route('deduction-types.index') }}" style="padding: 6px 12px;">
                                <i class="fas fa-minus-circle me-2 text-primary" style="font-size: 0.9em;"></i> Deduction Types
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center {{ request()->routeIs('addition-types.*') ? 'active' : '' }}" href="{{ route('addition-types.index') }}" style="padding: 6px 12px;">
                                <i class="fas fa-plus-circle me-2 text-primary" style="font-size: 0.9em;"></i> Addition Types
                            </a>
                        </li>
                    </ul>
                </div>
                <a class="nav-link {{ request()->routeIs('salary-scales.*') ? 'active' : '' }}" href="{{ route('salary-scales.index') }}">
                    <i class="fas fa-coins me-2"></i> Salary Scales
                </a>
                
            @endif

            @if(auth()->user() && auth()->user()->hasPermissionTo('manage_reports'))
            <a class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.index') }}">
                <i class="fas fa-chart-bar"></i> Reports 
            </a>
            @endif
        </nav>
    </div>

    <!-- Main Content -->
    <div class="content" id="content">
        <div class="container-fluid">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @yield('content')
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p>© {{ date('Y') }} HR & Payroll. All rights reserved. | Version 1.0.0</p>
        </div>
    </footer>

    <!-- Scripts -->
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
        
        // Existing sidebar functionality
        const sidebar = document.getElementById('sidebar');
        const content = document.getElementById('content');
        const sidebarToggle = document.getElementById('sidebarToggle');

        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('active');
            content.classList.toggle('collapsed');
        });

        // Auto-collapse sidebar on mobile
        if (window.innerWidth <= 768) {
            sidebar.classList.remove('active');
            content.classList.add('collapsed');
        }

        // Highlight active link
        document.querySelectorAll('.sidebar .nav-link').forEach(link => {
            if (link.href === window.location.href) {
                link.classList.add('active');
            }
        });
        
        // Handle nested dropdowns in sidebar
        document.querySelectorAll('.sidebar .dropdown-submenu').forEach(submenu => {
            submenu.addEventListener('mouseover', function() {
                const dropdownMenu = this.querySelector('.dropdown-menu');
                if (dropdownMenu) {
                    dropdownMenu.classList.add('show');
                }
            });
            
            submenu.addEventListener('mouseout', function() {
                const dropdownMenu = this.querySelector('.dropdown-menu');
                if (dropdownMenu) {
                    dropdownMenu.classList.remove('show');
                }
            });
        });
    </script>
    @stack('scripts')
</body>
</html>