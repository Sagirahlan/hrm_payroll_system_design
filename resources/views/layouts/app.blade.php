<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Kundi HR') }} - @yield('title')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        /* ================================
        CSS Variables for Easy Theming
        ================================
        */
        :root {
            --sidebar-width: 260px;
            --primary-color: #00bcd4;
            /* 👇 THE BACKGROUND COLOR IS CHANGED HERE 👇 */
            --primary-color-darker: #2c3e50; /* Changed from #73d4ddff to charcoal */
            --primary-color-lighter: #0ed2ecff;
            --text-color-light: #ffffff;
            --text-color-dark: #212529;
            --body-bg-light: #f4f7f6;
            --card-bg-light: #ffffff;
            --border-color-light: #dee2e6;
            --box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
            --border-radius: 0.75rem;
            --transition-speed: 0.3s;
        }

        [data-bs-theme="dark"] {
            --primary-color: #00bcd4;
            --primary-color-darker: #1a2a3a; /* Enhanced dark blue for sidebar */
            --primary-color-lighter: #4dd0e1;
            --text-color-light: #e9ecef;
            --text-color-dark: #e0e0e0; /* Improved text contrast */
            --body-bg-light: #121826; /* Deeper dark background */
            --card-bg-light: #1e293b; /* Improved card background */
            --border-color-light: #374151; /* Better border color */
        }

        /* ================================
        General Layout & Body
        ================================
        */
        body {
            background-color: var(--body-bg-light);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .wrapper {
            display: flex;
            width: 100%;
            align-items: stretch;
            flex-grow: 1;
        }

        /* ================================
        Sidebar (Off-canvas for Mobile)
        ================================
        */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--primary-color-darker);
            color: var(--text-color-light);
            transition: margin-left var(--transition-speed) ease;
            height: 100vh; /* Ensure sidebar takes full viewport height */
            overflow-y: auto; /* Add scroll for overflow content */
            box-shadow: 3px 0 10px rgba(0, 0, 0, 0.2);
        }

        /* Make sidebar sticky on desktop */
        @media (min-width: 992px) {
            .sidebar {
                position: sticky;
                top: 0;
            }
        }

        [data-bs-theme="dark"] .sidebar {
            background: linear-gradient(180deg, #1a2a3a 0%, #0f172a 100%);
            box-shadow: 3px 0 15px rgba(0, 0, 0, 0.4);
        }

        .sidebar .sidebar-header {
            padding: 1.25rem;
            text-align: center;
            background: rgba(0,0,0,0.1); /* Changed to be slightly darker than sidebar */
        }

        [data-bs-theme="dark"] .sidebar .sidebar-header {
            background: rgba(0, 0, 0, 0.2);
        }

        .sidebar .sidebar-header img {
            max-width: 120px;
        }

        /* Sidebar Navigation Links */
        .sidebar .nav-link {
            color: var(--primary-color-lighter);
            padding: 0.8rem 1.25rem;
            display: flex;
            align-items: center;
            font-size: 0.95rem;
            transition: all var(--transition-speed) ease;
            border-radius: var(--border-radius);
            margin: 0.25rem 0.5rem;
        }

        [data-bs-theme="dark"] .sidebar .nav-link {
            color: #a0aec0;
        }

        .sidebar .nav-link:hover {
            background: var(--primary-color);
            color: var(--text-color-light);
            transform: translateX(5px);
        }

        [data-bs-theme="dark"] .sidebar .nav-link:hover {
            background: #2d3748;
            color: #fff;
        }

        .sidebar .nav-link.active {
            background: var(--text-color-light);
            color: var(--primary-color-darker);
            font-weight: 600;
        }

        [data-bs-theme="dark"] .sidebar .nav-link.active {
            background: #4dd0e1;
            color: #0f172a;
        }

        .sidebar .nav-link i {
            margin-right: 1rem;
            width: 20px;
            text-align: center;
        }

        /* Accordion Menu in Sidebar */
        .sidebar .dropdown-toggle::after {
            margin-left: auto;
            transition: transform var(--transition-speed) ease;
        }
        .sidebar .dropdown-toggle[aria-expanded="true"]::after {
            transform: rotate(90deg);
        }
        .sidebar .collapse .nav-link {
            font-size: 0.85rem;
            padding-left: 3.5rem; /* Indent sub-items */
            background-color: rgba(0,0,0,0.1);
        }

        [data-bs-theme="dark"] .sidebar .collapse .nav-link {
            background-color: rgba(0, 0, 0, 0.15);
        }

        .sidebar .collapse .nav-link:hover {
            background-color: var(--primary-color);
        }

        [data-bs-theme="dark"] .sidebar .collapse .nav-link:hover {
            background-color: #2d3748;
        }

        /* ================================
        Main Content & Navbar
        ================================
        */
        #content {
            flex-grow: 1;
            padding: 1.5rem;
            transition: margin-left var(--transition-speed) ease;
            width: 100%;
        }

        .top-navbar {
            background: var(--card-bg-light);
            box-shadow: var(--box-shadow);
            border-radius: var(--border-radius);
            margin-bottom: 1.5rem;
            padding: 0.5rem 1rem;
        }

        [data-bs-theme="dark"] .top-navbar {
            background: #1e293b;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        [data-bs-theme="dark"] .top-navbar .nav-link,
        [data-bs-theme="dark"] .top-navbar .dropdown-item {
            color: var(--text-color-light);
        }

        [data-bs-theme="dark"] .top-navbar .navbar-text {
            color: #cbd5e1 !important;
        }

        .user-dropdown-toggle img {
            width: 40px;
            height: 40px;
            object-fit: cover;
            border: 2px solid var(--primary-color);
        }

        .dropdown-menu {
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            border-color: var(--border-color-light);
            background-color: var(--card-bg-light);
        }

        [data-bs-theme="dark"] .dropdown-menu {
            background-color: #1e293b;
            border-color: #374151;
        }

        [data-bs-theme="dark"] .dropdown-menu .dropdown-item {
            color: #e2e8f0;
        }

        [data-bs-theme="dark"] .dropdown-menu .dropdown-item:hover {
            background-color: #374151;
            color: #fff;
        }

        /* ================================
        Cards, Forms & UI Components
        ================================
        */
        .card {
            border-radius: var(--border-radius);
            border: none;
            box-shadow: var(--box-shadow);
            background-color: var(--card-bg-light);
        }

        [data-bs-theme="dark"] .card {
            background-color: #1e293b;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .card-header {
            background: var(--primary-color);
            color: var(--text-color-light);
            font-weight: 600;
            border-bottom: none;
            border-radius: var(--border-radius) var(--border-radius) 0 0 !important;
        }

        [data-bs-theme="dark"] .card-header {
            background: linear-gradient(135deg, #00bcd4, #00838f);
        }

        .card-body {
            background-color: transparent;
        }

        .form-control, .form-select {
            border-radius: var(--border-radius);
            background-color: var(--card-bg-light);
            border: 1px solid var(--border-color-light);
        }

        [data-bs-theme="dark"] .form-control,
        [data-bs-theme="dark"] .form-select {
            background-color: #2d3748;
            border-color: #4b5563;
            color: #e2e8f0;
        }

        [data-bs-theme="dark"] .form-control::placeholder {
            color: #94a3b8;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(0, 188, 212, 0.25);
        }

        /* ================================
        Footer
        ================================
        */
        footer {
            background: var(--card-bg-light);
            color: var(--text-color-dark);
            padding: 1rem 0;
            text-align: center;
            margin-top: auto;
            border-top: 1px solid var(--border-color-light);
        }

        [data-bs-theme="dark"] footer {
            background: #0f172a;
            color: var(--text-color-light);
            border-top-color: #374151;
        }

    </style>
    @yield('styles')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body>

<div class="wrapper">
    <nav id="sidebar" class="sidebar offcanvas-lg offcanvas-start d-flex flex-column flex-shrink-0 p-3">
        <div class="sidebar-header">
            <a href="{{ route('dashboard') }}">
                <img src="{{ asset('images/WhatsApp Image 2026-01-22 at 10.28.01 AM.jpeg') }}" alt="Logo">
            </a>
        </div>

        <ul class="nav nav-pills flex-column mb-auto mt-4">
            <li>
                <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </li>

            @if(auth()->user() && (auth()->user()->hasPermissionTo('manage_employees') || auth()->user()->hasPermissionTo('view_employees')))
            <li>
                <a class="nav-link dropdown-toggle {{ request()->routeIs('employees.*', 'pending-changes.*') ? 'active' : '' }}" href="#employeesSubmenu" data-bs-toggle="collapse" role="button" aria-expanded="{{ request()->routeIs('employees.*', 'pending-changes.*') ? 'true' : 'false' }}">
                    <i class="fas fa-users"></i> Employees
                </a>
                <div class="collapse {{ request()->routeIs('employees.*', 'pending-changes.*', 'promotions.*') ? 'show' : '' }}" id="employeesSubmenu">
                    <ul class="nav flex-column ms-1">
                         @can('view_employees')
                        <li><a class="nav-link {{ request()->routeIs('employees.index') ? 'active' : '' }}" href="{{ route('employees.index') }}">Employee List</a></li>
                        @endcan
                        @can('create_employees')
                        <li><a class="nav-link {{ request()->routeIs('employees.create') ? 'active' : '' }}" href="{{ route('employees.create') }}">Add Employee</a></li>
                        @endcan
                        @can('approve_employee_changes')
                        <li><a class="nav-link {{ request()->routeIs('pending-changes.*') ? 'active' : '' }}" href="{{ route('pending-changes.index') }}">Pending Changes</a></li>
                        @endcan
                        @can('view_promotions')
                        <li><a class="nav-link {{ request()->routeIs('promotions.index') ? 'active' : '' }}" href="{{ route('promotions.index') }}">Promotions/Demotions</a></li>
                        @endcan
                        @can('view_leaves')
                        <li><a class="nav-link {{ request()->routeIs('leaves.*') ? 'active' : '' }}" href="{{ route('leaves.index') }}">Leave Management</a></li>
                        @endcan
                        @can('manage_probation')
                        <li><a class="nav-link {{ request()->routeIs('probation.*') ? 'active' : '' }}" href="{{ route('probation.index') }}">Probation Management</a></li>
                        @endcan
                        @can('manage_bank_details')
                        <li><a class="nav-link {{ request()->routeIs('bank-details.*') ? 'active' : '' }}" href="{{ route('bank-details.index') }}">Bank Details Management</a></li>
                        @endcan

                    </ul>
                </div>
            </li>
            @endif

            @can('manage_users')
            <li>
                <a class="nav-link dropdown-toggle {{ request()->routeIs('users.*', 'roles.*') ? 'active' : '' }}" href="#usersSubmenu" data-bs-toggle="collapse" role="button" aria-expanded="{{ request()->routeIs('users.*', 'roles.*') ? 'true' : 'false' }}">
                    <i class="fas fa-user-shield"></i> User Management
                </a>
                <div class="collapse {{ request()->routeIs('users.*', 'roles.*') ? 'show' : '' }}" id="usersSubmenu">
                    <ul class="nav flex-column ms-1">
                        @can('view_users')
                        <li><a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">Users</a></li>
                        @endcan
                        @can('manage_roles')
                        <li><a class="nav-link {{ request()->routeIs('roles.*') ? 'active' : '' }}" href="{{ route('roles.index') }}">Roles</a></li>
                        @endcan
                    </ul>
                </div>
            </li>
            @endcan

            @can('manage_departments')
            <li>
                <a class="nav-link dropdown-toggle {{ request()->routeIs('departments.*') ? 'active' : '' }}" href="#departmentsSubmenu" data-bs-toggle="collapse" role="button" aria-expanded="{{ request()->routeIs('departments.*') ? 'true' : 'false' }}">
                    <i class="fas fa-building"></i> Departments
                </a>
                <div class="collapse {{ request()->routeIs('departments.*') ? 'show' : '' }}" id="departmentsSubmenu">
                    <ul class="nav flex-column ms-1">
                         @can('view_departments')
                        <li><a class="nav-link {{ request()->routeIs('departments.index') ? 'active' : '' }}" href="{{ route('departments.index') }}">Department List</a></li>
                        @endcan
                         @can('create_departments')
                        <li><a class="nav-link {{ request()->routeIs('departments.create') ? 'active' : '' }}" href="{{ route('departments.create') }}">Add Department</a></li>
                        @endcan
                    </ul>
                </div>
            </li>
            @endcan

            @can('manage_biometrics')
            <li>
                <a class="nav-link {{ request()->routeIs('biometrics.*') ? 'active' : '' }}" href="{{ route('biometrics.index') }}">
                    <i class="fas fa-fingerprint"></i> Biometrics
                </a>
            </li>
            @endcan

            @can('view_audit_logs')
            <li>
                <a class="nav-link {{ request()->routeIs('audit-trails.*') ? 'active' : '' }}" href="{{ route('audit-trails.index') }}">
                    <i class="fas fa-history"></i> Audit Trail
                </a>
            </li>
            @endcan

            @can('manage_disciplinary')
            <li>
                <a class="nav-link dropdown-toggle {{ request()->routeIs('disciplinary.*') ? 'active' : '' }}" href="#disciplinarySubmenu" data-bs-toggle="collapse" role="button" aria-expanded="{{ request()->routeIs('disciplinary.*') ? 'true' : 'false' }}">
                    <i class="fas fa-gavel"></i> Disciplinary
                </a>
                <div class="collapse {{ request()->routeIs('disciplinary.*') ? 'show' : '' }}" id="disciplinarySubmenu">
                    <ul class="nav flex-column ms-1">
                        @can('view_disciplinary')
                        <li><a class="nav-link {{ request()->routeIs('disciplinary.index') ? 'active' : '' }}" href="{{ route('disciplinary.index') }}">Disciplinary List</a></li>
                        @endcan
                         @can('create_disciplinary')
                        <li><a class="nav-link {{ request()->routeIs('disciplinary.create') ? 'active' : '' }}" href="{{ route('disciplinary.create') }}">Log Action</a></li>
                        @endcan
                    </ul>
                </div>
            </li>
            @endcan

            @if(auth()->user() && (auth()->user()->hasPermissionTo('manage_retirement') || auth()->user()->hasPermissionTo('view_retirement')))
            <li>
                <a class="nav-link dropdown-toggle {{ request()->routeIs('retirements.*') ? 'active' : '' }}" href="#retirementsSubmenu" data-bs-toggle="collapse" role="button" aria-expanded="{{ request()->routeIs('retirements.*') ? 'true' : 'false' }}">
                    <i class="fas fa-briefcase"></i> Retirements
                </a>
                <div class="collapse {{ request()->routeIs('retirements.*') ? 'show' : '' }}" id="retirementsSubmenu">
                    <ul class="nav flex-column ms-1">
                         @can('view_retirement')
                        <li><a class="nav-link {{ request()->routeIs('retirements.index') ? 'active' : '' }}" href="{{ route('retirements.index') }}">Retiring in 6 months</a></li>
                        @endcan
                        @can('create_retirement')
                        <li><a class="nav-link {{ request()->routeIs('retirements.create') ? 'active' : '' }}" href="{{ route('retirements.create') }}">Confirm Retirement</a></li>
                        @endcan
                    </ul>
                </div>
            </li>
            @endif

            @can('manage_pensioners')
            <li>
                <a class="nav-link dropdown-toggle {{ request()->routeIs('pensioners.*', 'pending-pensioner-changes.*') ? 'active' : '' }}" href="#pensionersSubmenu" data-bs-toggle="collapse" role="button" aria-expanded="{{ request()->routeIs('pensioners.*', 'pending-pensioner-changes.*') ? 'true' : 'false' }}">
                    <i class="fas fa-user-shield"></i> Pensioners
                </a>
                <div class="collapse {{ request()->routeIs('pensioners.*', 'pending-pensioner-changes.*') ? 'show' : '' }}" id="pensionersSubmenu">
                    <ul class="nav flex-column ms-1">
                        @can('view_pensioners')
                        <li><a class="nav-link {{ request()->routeIs('pensioners.index') ? 'active' : '' }}" href="{{ route('pensioners.index') }}">Pensioner List</a></li>
                        @endcan
                        
                        @can('view_pensioner_changes')
                        <li><a class="nav-link {{ request()->routeIs('pending-pensioner-changes.*') ? 'active' : '' }}" href="{{ route('pending-pensioner-changes.index') }}">Pending Changes</a></li>
                        @endcan
                    </ul>
                </div>
            </li>
            @endcan

            @can('manage_sms')
            <li>
                <a class="nav-link {{ request()->routeIs('sms.*') ? 'active' : '' }}" href="{{ route('sms.index') }}">
                    <i class="fas fa-sms"></i> SMS Notifications
                </a>
            </li>
            @endcan

            @can('manage_payroll')
            <li>
                <a class="nav-link dropdown-toggle {{ request()->routeIs(['payroll.*', 'salary-scales.*', 'deduction-types.*', 'addition-types.*']) ? 'active' : '' }}" href="#payrollSubmenu" data-bs-toggle="collapse" role="button" aria-expanded="{{ request()->routeIs(['payroll.*', 'salary-scales.*', 'deduction-types.*', 'addition-types.*']) ? 'true' : 'false' }}">
                    <i class="fas fa-money-check-alt"></i> Payroll
                </a>
                <div class="collapse {{ request()->routeIs(['payroll.*', 'salary-scales.*', 'deduction-types.*', 'addition-types.*', 'loans.*']) ? 'show' : '' }}" id="payrollSubmenu">
                    <ul class="nav flex-column ms-1">
                        @can('view_salary_scales')
                        <li><a class="nav-link {{ request()->routeIs('salary-scales.*') ? 'active' : '' }}" href="{{ route('salary-scales.index') }}">Salary Scales</a></li>
                        @endcan
                        @can('view_addition_types')
                        <li><a class="nav-link {{ request()->routeIs('addition-types.*') ? 'active' : '' }}" href="{{ route('addition-types.index') }}">Addition Types</a></li>
                        @endcan
                        @can('view_deduction_types')
                        <li><a class="nav-link {{ request()->routeIs('deduction-types.*') ? 'active' : '' }}" href="{{ route('deduction-types.index') }}">Deduction Types</a></li>
                        @endcan
                        @can('create_additions')
                        <li><a class="nav-link {{ request()->routeIs('payroll.additions') ? 'active' : '' }}" href="{{ route('payroll.additions') }}">Bulk Additions</a></li>
                        @endcan
                        @can('create_deductions')
                        <li><a class="nav-link {{ request()->routeIs('payroll.deductions') ? 'active' : '' }}" href="{{ route('payroll.deductions') }}">Bulk Deductions</a></li>
                        @endcan
                        @can('view_loans')
                        <li><a class="nav-link {{ request()->routeIs('loans.*') ? 'active' : '' }}" href="{{ route('loans.index') }}">Loan Deductions</a></li>
                        @endcan
                        @can('view_payroll')
                        <li><a class="nav-link {{ request()->routeIs('payroll.index') ? 'active' : '' }}" href="{{ route('payroll.index') }}">Process Payroll</a></li>
                        @endcan
                        @can('manage_payroll_adjustments')
                        <li><a class="nav-link {{ request()->routeIs('payroll.adjustments.manage') ? 'active' : '' }}" href="{{ route('payroll.adjustments.manage') }}">Employee Adjustments</a></li>
                        @endcan

                    </ul>
                </div>
            </li>
            @endcan

            @can('manage_reports')
            <li>
                <a class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.index') }}">
                    <i class="fas fa-chart-bar"></i> Reports
                </a>
            </li>
            @endcan
        </ul>
    </nav>

    <div id="content">
        <nav class="navbar navbar-expand-lg top-navbar">
            <div class="container-fluid">
                <button class="btn btn-primary d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebar" aria-controls="sidebar">
                    <i class="fas fa-bars"></i>
                </button>

                <div class="collapse navbar-collapse">
                    <span class="navbar-text fw-bold">
                        Welcome, {{ auth()->user()->username ?? 'Guest' }}!
                    </span>
                </div>

                <div class="d-flex align-items-center">
                    <button class="btn border-0" id="darkModeToggle" title="Toggle dark mode">
                        <i class="fas fa-moon"></i>
                    </button>

                    @auth
                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center user-dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="{{ auth()->user()->employee && auth()->user()->employee->photo_path ? asset('storage/' . auth()->user()->employee->photo_path) : asset('images/default-image.png') }}" alt="Profile" class="rounded-circle me-2">
                            <span class="d-none d-sm-inline">{{ auth()->user()->username }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('profile') }}"><i class="fas fa-user-circle me-2"></i>Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger"><i class="fas fa-sign-out-alt me-2"></i>Logout</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                    @else
                    <ul class="navbar-nav flex-row">
                        <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Login</a></li>
                        <li class="nav-item ms-3"><a class="nav-link" href="{{ route('register') }}">Register</a></li>
                    </ul>
                    @endauth
                </div>
            </div>
        </nav>

        <main>
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
        </main>
    </div>
</div>
<footer class="footer mt-auto py-3">
    <div class="container text-center">
        <p class="mb-0 fs-5">© {{ date('Y') }} Powered by Steadfast Tech. All rights reserved.</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const darkModeToggle = document.getElementById('darkModeToggle');
        const htmlElement = document.documentElement;

        // Function to update the icon based on the current theme
        const updateIcon = (theme) => {
            const icon = darkModeToggle.querySelector('i');
            if (theme === 'dark') {
                icon.classList.remove('fa-moon');
                icon.classList.add('fa-sun');
            } else {
                icon.classList.remove('fa-sun');
                icon.classList.add('fa-moon');
            }
        };

        // Check for saved theme in localStorage and apply it
        const savedTheme = localStorage.getItem('theme') || 'light';
        htmlElement.setAttribute('data-bs-theme', savedTheme);
        updateIcon(savedTheme);

        // Add click event listener to the toggle button
        darkModeToggle.addEventListener('click', () => {
            const currentTheme = htmlElement.getAttribute('data-bs-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            htmlElement.setAttribute('data-bs-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateIcon(newTheme);
        });
    });
</script>
@stack('scripts')
</body>
</html>