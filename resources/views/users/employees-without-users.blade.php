@extends('layouts.app')

@section('title', 'Employees Without User Accounts')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1><i class="fas fa-user-plus"></i> Employees Without User Accounts</h1>
            <p class="text-muted mb-0">Manage employees who don't have user accounts yet</p>
        </div>
        <div class="btn-group">
            <a href="{{ route('users.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Users
            </a>
            @if($employees->total() > 0)
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#bulkCreateModal">
                    <i class="fas fa-users-cog"></i> Create All Users
                </button>
            @endif
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('users.employees-without-users') }}" class="row g-3">
                <div class="col-md-4">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" name="search" id="search" class="form-control"
                           placeholder="Search by name, email, or ID..."
                           value="{{ request('search') }}">
                </div>

               @if($departments->count() > 0)
    <div class="col-md-3">
        <label for="department_id" class="form-label">Department</label>
        <select name="department_id" id="department_id" class="form-select">
            <option value="">All Departments</option>
            @foreach($departments as $department)
                <option value="{{ $department->department_id }}" {{ request('department_id') == $department->department_id ? 'selected' : '' }}>
                    {{ $department->department_name }}

                </option>
            @endforeach
        </select>
    </div>
@endif


                <div class="col-md-2">
                    <label for="email_filter" class="form-label">Email Status</label>
                    <select name="email_filter" id="email_filter" class="form-select">
                        <option value="">All</option>
                        <option value="with_email" {{ request('email_filter') == 'with_email' ? 'selected' : '' }}>With Email</option>
                        <option value="without_email" {{ request('email_filter') == 'without_email' ? 'selected' : '' }}>No Email</option>
                    </select>
                </div>

                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Search
                    </button>
                    <a href="{{ route('users.employees-without-users') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="text-dark">{{ $employees->total() }}</h4>
                            <p class="mb-0 text-dark">Total Without Users</p>
                        </div>
                        <div class="align-self-center text-dark">
                            <i class="fas fa-user-plus fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="text-dark">{{ $employees->where('email', '!=', null)->count() }}</h4>
                            <p class="mb-0 text-dark">With Email</p>
                        </div>
                        <div class="align-self-center text-dark">
                            <i class="fas fa-envelope fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="text-dark">{{ $employees->where('email', '==', null)->count() }}</h4>
                            <p class="mb-0 text-dark">Without Email</p>
                        </div>
                        <div class="align-self-center text-dark">
                            <i class="fas fa-envelope-open fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="text-dark">{{ $departments->count() }}</h4> {{-- ✅ because it's a collection --}}

                            <p class="mb-0 text-dark">Departments</p>
                        </div>
                        <div class="align-self-center text-dark">
                            <i class="fas fa-building fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Employees Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-list"></i> Employees Without User Accounts
                @if(request('search'))
                    <small class="text-muted">- Search results for "{{ request('search') }}"</small>
                @endif
                @if(request('department'))
                    <small class="text-muted">- Department: {{ request('department') }}</small>
                @endif
            </h5>
        </div>
        <div class="card-body p-0">
            @if($employees->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>Staff No</th>
                                <th>Name</th>
                                <th>Email</th>
                                @if($departments->count() > 0)
                                    <th>Department</th>
                                @endif
                                <th>Status</th>
                                <th width="120">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($employees as $employee)
                            <tr>
                                <td>
                                    <strong>{{ $employee->staff_no }}</strong>
                                </td>
                                <td>
                                    <strong>{{ $employee->first_name }} {{ $employee->surname }}</strong>
                                </td>
                                <td>
                                    @if($employee->email)
                                        <span class="text-success">
                                            <i class="fas fa-envelope"></i> {{ $employee->email }}
                                        </span>
                                    @else
                                        <span class="text-warning">
                                            <i class="fas fa-exclamation-triangle"></i> No email
                                        </span>
                                    @endif
                                </td>
                                @if($departments->count() > 0)
                                <td>
                                    {{ $employee->department->department_name ?? 'Not specified' }}
                                </td>
                                @endif
                                <td>
                                    @if($employee->email)
                                        <span class="badge bg-success">Ready for user creation</span>
                                    @else
                                        <span class="badge bg-warning">Email required</span>
                                    @endif
                                </td>
                                <td>
                                    @if($employee->email)
                                        <button type="button" class="btn btn-sm btn-primary"
                                                data-bs-toggle="modal"
                                                data-bs-target="#createUserModal{{ $employee->employee_id }}"
                                                title="Create User Account">
                                            <i class="fas fa-user-plus"></i>
                                        </button>
                                    @else
                                        <span class="text-muted" title="Email required to create user">
                                            <i class="fas fa-ban"></i>
                                        </span>
                                    @endif
                                </td>
                            </tr>

                            <!-- Individual Create User Modal -->
                            @if($employee->email)
                            <div class="modal fade" id="createUserModal{{ $employee->employee_id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header bg-primary text-white">
                                            <h5 class="modal-title">
                                                <i class="fas fa-user-plus"></i> Create User for {{ $employee->first_name }} {{ $employee->surname }}
                                            </h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form action="{{ route('users.store') }}" method="POST">
                                            @csrf
                                            <div class="modal-body">
                                                <input type="hidden" name="employee_id" value="{{ $employee->employee_id }}">

                                                <div class="mb-3">
                                                    <label for="username{{ $employee->employee_id }}" class="form-label">Username</label>
                                                    <input type="text" name="username" id="username{{ $employee->employee_id }}"
                                                           class="form-control" value="{{ strtolower(explode('@', $employee->email)[0]) }}" required>
                                                </div>

                                                <div class="mb-3">
                                                    <label for="email{{ $employee->employee_id }}" class="form-label">Email</label>
                                                    <input type="email" name="email" id="email{{ $employee->employee_id }}"
                                                           class="form-control" value="{{ $employee->email }}" readonly>
                                                </div>

                                                <div class="mb-3">
                                                    <label for="password{{ $employee->employee_id }}" class="form-label">Password</label>
                                                    <input type="password" name="password" id="password{{ $employee->employee_id }}"
                                                           class="form-control" value="{{ $employee->date_of_birth }}" required>
                                                </div>

                                                <div class="mb-3">
                                                    <label for="password_confirmation{{ $employee->employee_id }}" class="form-label">Confirm Password</label>
                                                    <input type="password" name="password_confirmation" id="password_confirmation{{ $employee->employee_id }}"
                                                           class="form-control" value="{{ $employee->date_of_birth }}" required>
                                                </div>

                                                <div class="mb-3">
                                                    <label for="role{{ $employee->employee_id }}" class="form-label">Role</label>
                                                    <select name="role" id="role{{ $employee->employee_id }}" class="form-select" required>
                                                        <option value="employee" selected>Employee</option>
                                                        <!-- Add other roles if needed -->
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="fas fa-user-plus"></i> Create User
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                    <h5>All Employees Have User Accounts!</h5>
                    <p class="text-muted">
                        @if(request('search') || request('department') || request('email_filter'))
                            No employees match your search criteria.
                        @else
                            Every employee in the system has a user account created.
                        @endif
                    </p>
                </div>
            @endif
        </div>

        @if($employees->hasPages())
        <div class="card-footer">
            {{ $employees->links('pagination::bootstrap-5') }}
        </div>
        @endif
    </div>
</div>

<!-- Bulk Create Users Modal -->
@if($employees->where('email', '!=', null)->count() > 0)
<div class="modal fade" id="bulkCreateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-users-cog"></i> Bulk Create User Accounts
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <strong>This will create user accounts for {{ $employees->where('email', '!=', null)->count() }} employees with email addresses:</strong>
                </div>
                <ul class="list-unstyled">
                    <li><i class="fas fa-check text-success"></i> Username will be generated from employee email</li>
                    <li><i class="fas fa-check text-success"></i> Default password: <code>Employee's Date of Birth</code></li>
                    <li><i class="fas fa-check text-success"></i> Default role: <strong>Employee</strong></li>
                    <li><i class="fas fa-exclamation-triangle text-warning"></i> Employees without email addresses will be skipped</li>
                </ul>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Note:</strong> Users should change their passwords after first login.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('users.bulk-create') }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-success" onclick="return confirm('Are you sure you want to create user accounts for all employees with email addresses?')">
                        <i class="fas fa-users-cog"></i> Create {{ $employees->where('email', '!=', null)->count() }} User Accounts
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-submit form when filters change
    document.getElementById('department').addEventListener('change', function() {
        this.form.submit();
    });

    document.getElementById('email_filter').addEventListener('change', function() {
        this.form.submit();
    });

    // Clear search on escape key
    document.getElementById('search').addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            this.value = '';
            this.form.submit();
        }
    });
});
</script>
@endsection