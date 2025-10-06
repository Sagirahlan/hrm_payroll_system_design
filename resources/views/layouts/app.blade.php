<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Kundi HR') }} - @yield('title')</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    
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
            --primary-color-darker: #2c3e50; /* Also update for dark mode if needed */
            --primary-color-lighter: #e0f7fa;
            --text-color-light: #e9ecef;
            --text-color-dark: #0c2e3d;
            --body-bg-light: #0c1e29;
            --card-bg-light: #162c3a;
            --border-color-light: #2a434a;
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
            padding-top: 56px; /* Space for top navbar on mobile */
        }
        
        .wrapper {
            display: flex;
            width: 100%;
            align-items: stretch;
            flex-grow: 1;
        }
        
        /* Ensure proper scrolling on small devices */
        @media (max-width: 991.98px) {
            .wrapper {
                overflow: hidden;
                flex-direction: column;
            }
            
            body {
                padding-top: 0;
            }
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
            /* Add scrollbar for small devices */
            overflow-y: auto;
            max-height: 100vh;
        }
        
        /* Custom scrollbar for sidebar */
        .sidebar::-webkit-scrollbar {
            width: 8px;
        }
        
        .sidebar::-webkit-scrollbar-track {
            background: rgba(0,0,0,0.1);
        }
        
        .sidebar::-webkit-scrollbar-thumb {
            background: var(--primary-color);
            border-radius: 4px;
        }
        
        .sidebar::-webkit-scrollbar-thumb:hover {
            background: var(--primary-color-lighter);
        }
        
        /* Mobile sidebar improvements */
        @media (max-width: 991.98px) {
            .sidebar {
                max-height: 100vh;
                position: fixed;
                top: 0;
                bottom: 0;
                z-index: 1050; /* Above content but below offcanvas backdrop */
                transform: translateX(-100%);
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
        }
        
        .sidebar .sidebar-header {
            padding: 1.25rem;
            text-align: center;
            background: rgba(0,0,0,0.1); /* Changed to be slightly darker than sidebar */
        }
        
        /* Compact sidebar header on mobile */
        @media (max-width: 991.98px) {
            .sidebar .sidebar-header {
                padding: 0.75rem;
            }
            
            .sidebar .sidebar-header img {
                max-width: 100px;
            }
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
        
        .sidebar .nav-link:hover {
            background: var(--primary-color);
            color: var(--text-color-light);
            transform: translateX(5px);
        }

        .sidebar .nav-link.active {
            background: var(--text-color-light);
            color: var(--primary-color-darker);
            font-weight: 600;
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
        .sidebar .collapse .nav-link:hover {
            background-color: var(--primary-color);
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
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1040;
        }
        
        @media (max-width: 991.98px) {
            .top-navbar {
                position: relative;
                margin-bottom: 1rem;
            }
        }
        
        [data-bs-theme="dark"] .top-navbar .nav-link,
        [data-bs-theme="dark"] .top-navbar .dropdown-item {
            color: var(--text-color-light);
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

        /* ================================
        Cards, Forms & UI Components
        ================================
        */
        .card {
            border-radius: var(--border-radius);
            border: none;
            box-shadow: var(--box-shadow);
            background-color: var(--card-bg-light);
            margin-bottom: 1.5rem;
        }

        .card-header {
            background: var(--primary-color);
            color: var(--text-color-light);
            font-weight: 600;
            border-bottom: none;
            border-radius: var(--border-radius) var(--border-radius) 0 0 !important;
        }
        
        .form-control, .form-select {
            border-radius: var(--border-radius);
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
            color: var(--text-color-light);
        }

        /* ================================
        Mobile-specific improvements
        ================================
        */
        @media (max-width: 767.98px) {
            .container, .container-fluid {
                padding-left: 10px;
                padding-right: 10px;
            }
            
            .card-body {
                padding: 1rem;
            }
            
            .top-navbar .navbar-text {
                font-size: 0.9rem;
            }
            
            .user-dropdown-toggle img {
                width: 30px;
                height: 30px;
            }
            
            .sidebar .nav-link {
                padding: 0.7rem 1rem;
                font-size: 0.9rem;
            }
            
            .sidebar .nav-link i {
                margin-right: 0.75rem;
            }
            
            .form-label {
                font-size: 0.9rem;
            }
            
            .btn {
                font-size: 0.875rem;
                padding: 0.375rem 0.75rem;
            }
        }
        
        @media (max-width: 575.98px) {
            .top-navbar .navbar-text {
                display: none;
            }
            
            .sidebar .nav-link {
                padding: 0.6rem 0.8rem;
                font-size: 0.85rem;
            }
            
            .card-header h4, .card-header h5, .card-header h6 {
                font-size: 1.1rem;
            }
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
                <img src="{{ asset('images/logo-white.png') }}" alt="Logo" class="img-fluid">
            </a>
        </div>
        
        <div class="overflow-auto flex-grow-1">
            <ul class="nav nav-pills flex-column mb-auto mt-4">
            <li>
                <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </li>

            @if(auth()->user() && (auth()->user()->hasPermissionTo('manage_employees') || auth()->user()->hasPermissionTo('view_employees')))
            <li>
                <a class="nav-link dropdown-toggle {{ request()->routeIs('employees.*', 'pending-changes.*', 'promotions.*') ? 'active' : '' }}" href="#employeesSubmenu" data-bs-toggle="collapse" role="button" aria-expanded="{{ request()->routeIs('employees.*', 'pending-changes.*', 'promotions.*') ? 'true' : 'false' }}">
                    <i class="fas fa-users"></i> Employees
                </a>
                <div class="collapse {{ request()->routeIs('employees.*', 'pending-changes.*', 'promotions.*') ? 'show' : '' }}" id="employeesSubmenu">
                    <ul class="nav flex-column ms-1">
                        <li><a class="nav-link {{ request()->routeIs('employees.index') ? 'active' : '' }}" href="{{ route('employees.index') }}">Employee List</a></li>
                        @can('manage_employees')
                        <li><a class="nav-link {{ request()->routeIs('employees.create') ? 'active' : '' }}" href="{{ route('employees.create') }}">Add Employee</a></li>
                        <li><a class="nav-link {{ request()->routeIs('promotions.index') ? 'active' : '' }}" href="{{ route('promotions.index') }}">Promotions/Demotions</a></li>
                        <li><a class="nav-link {{ request()->routeIs('pending-changes.*') ? 'active' : '' }}" href="{{ route('pending-changes.index') }}">Pending Changes</a></li>
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
                        <li><a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">Users</a></li>
                        <li><a class="nav-link {{ request()->routeIs('roles.*') ? 'active' : '' }}" href="{{ route('roles.index') }}">Roles</a></li>
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
                        <li><a class="nav-link {{ request()->routeIs('departments.index') ? 'active' : '' }}" href="{{ route('departments.index') }}">Department List</a></li>
                        <li><a class="nav-link {{ request()->routeIs('departments.create') ? 'active' : '' }}" href="{{ route('departments.create') }}">Add Department</a></li>
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
                        <li><a class="nav-link {{ request()->routeIs('disciplinary.index') ? 'active' : '' }}" href="{{ route('disciplinary.index') }}">Disciplinary List</a></li>
                        <li><a class="nav-link {{ request()->routeIs('disciplinary.create') ? 'active' : '' }}" href="{{ route('disciplinary.create') }}">Log Action</a></li>
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
                        <li><a class="nav-link {{ request()->routeIs('retirements.index') ? 'active' : '' }}" href="{{ route('retirements.index') }}">Retirement List</a></li>
                        @can('manage_retirement')
                        <li><a class="nav-link {{ request()->routeIs('retirements.create') ? 'active' : '' }}" href="{{ route('retirements.create') }}">Add Retirement</a></li>
                        @endcan
                    </ul>
                </div>
            </li>
            @endif

            @can('manage_employees')
            <li>
                <a class="nav-link {{ request()->routeIs('pensioners.index') ? 'active' : '' }}" href="{{ route('pensioners.index') }}">
                    <i class="fas fa-user-shield"></i> Pensioners
                </a>
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
                <a class="nav-link dropdown-toggle {{ request()->routeIs(['payroll.*', 'salary-scales.*', 'deduction-types.*', 'addition-types.*', 'loans.*']) ? 'active' : '' }}" href="#payrollSubmenu" data-bs-toggle="collapse" role="button" aria-expanded="{{ request()->routeIs(['payroll.*', 'salary-scales.*', 'deduction-types.*', 'addition-types.*', 'loans.*']) ? 'true' : 'false' }}">
                    <i class="fas fa-money-check-alt"></i> Payroll
                </a>
                <div class="collapse {{ request()->routeIs(['payroll.*', 'salary-scales.*', 'deduction-types.*', 'addition-types.*', 'loans.*']) ? 'show' : '' }}" id="payrollSubmenu">
                    <ul class="nav flex-column ms-1">
                        <li><a class="nav-link {{ request()->routeIs('payroll.index') ? 'active' : '' }}" href="{{ route('payroll.index') }}">Process Payroll</a></li>
                        <li><a class="nav-link {{ request()->routeIs('payroll.additions') ? 'active' : '' }}" href="{{ route('payroll.additions') }}">Bulk Additions</a></li>
                        <li><a class="nav-link {{ request()->routeIs('payroll.deductions') ? 'active' : '' }}" href="{{ route('payroll.deductions') }}">Bulk Deductions</a></li>
                        <li><a class="nav-link {{ request()->routeIs('payroll.adjustments.manage') ? 'active' : '' }}" href="{{ route('payroll.adjustments.manage') }}">Employee Adjustments</a></li>
                        <li><a class="nav-link {{ request()->routeIs('loans.*') ? 'active' : '' }}" href="{{ route('loans.index') }}">Loans Deduction</a></li>
                        <li><a class="nav-link {{ request()->routeIs('addition-types.*') ? 'active' : '' }}" href="{{ route('addition-types.index') }}">Addition Types</a></li>
                        <li><a class="nav-link {{ request()->routeIs('deduction-types.*') ? 'active' : '' }}" href="{{ route('deduction-types.index') }}">Deduction Types</a></li>
                        <li><a class="nav-link {{ request()->routeIs('salary-scales.*') ? 'active' : '' }}" href="{{ route('salary-scales.index') }}">Salary Scales</a></li>
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
        </div>
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
            @yield('content')
        </main>
    </div>
</div>

<footer>
    <div class="container">
        <p class="mb-0">© {{ date('Y') }} Powered by Steadfast Tech. All rights reserved.</p>
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
        
        // Handle sidebar scrolling for small devices
        const sidebar = document.getElementById('sidebar');
        if (sidebar) {
            // Ensure sidebar content is scrollable on small devices
            sidebar.style.overflowY = 'auto';
            
            // Add touch support for better scrolling on mobile devices
            sidebar.addEventListener('touchstart', function() {
                this.style.overflowY = 'auto';
            });
            
            // Ensure momentum scrolling on iOS devices
            sidebar.style.webkitOverflowScrolling = 'touch';
        }
        
        // Handle mobile sidebar toggle
        const sidebarToggle = document.querySelector('[data-bs-toggle="offcanvas"]');
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function() {
                if (sidebar) {
                    sidebar.classList.toggle('show');
                }
            });
        }
    });
</script>
@stack('scripts')
</body>
</html>