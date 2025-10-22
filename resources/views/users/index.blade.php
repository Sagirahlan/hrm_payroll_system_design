@extends('layouts.app')

@section('title', 'Users Management')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-users"></i> Users Management</h1>
         @can('create_users')
        <div class="btn-group">
            <a href="{{ route('users.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Create User
            </a>
            @if($employeesWithoutUsers > 0)
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#bulkCreateModal">
                    <i class="fas fa-users-cog"></i> Auto Create Users ({{ $employeesWithoutUsers }})
                </button>
                <a href="{{ route('users.employees-without-users') }}" class="btn btn-info">
                    <i class="fas fa-eye"></i> View Employees Without Users
                </a>
            @endif
        </div>
        @endcan
    </div>

    <!-- Filters and Search -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('users.index') }}" class="row g-3">
                <div class="col-md-4">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" name="search" id="search" class="form-control" 
                           placeholder="Search by username, email, or employee name..." 
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <label for="filter" class="form-label">Filter by Role</label>
                    <select name="filter" id="filter" class="form-select">
                        <option value="">All Roles</option>
                        @foreach($roles as $role)
                            <option value="{{ $role }}" {{ request('filter') == $role ? 'selected' : '' }}>
                                {{ ucfirst($role) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Search
                    </button>
                    <a href="{{ route('users.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Clear
                    </a>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <small class="text-muted">
                        Showing {{ $users->count() }} of {{ $users->total() }} users
                    </small>
                </div>
            </form>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-light">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="text-dark">{{ $users->total() }}</h4>
                            <p class="mb-0 text-dark">Total Users</p>
                        </div>
                        <div class="align-self-center text-dark">
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-light">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="text-dark">{{ $employeesWithoutUsers }}</h4>
                            <p class="mb-0 text-dark">Employees Without Users</p>
                        </div>
                        <div class="align-self-center text-dark">
                            <i class="fas fa-user-plus fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-light">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="text-dark">{{ $roles->count() }}</h4>
                            <p class="mb-0 text-dark">Available Roles</p>
                        </div>
                        <div class="align-self-center text-dark">
                            <i class="fas fa-user-tag fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-light">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="text-dark">{{ request('search') ? $users->count() : $users->total() }}</h4>
                            <p class="mb-0 text-dark">{{ request('search') ? 'Search Results' : 'Active Users' }}</p>
                        </div>
                        <div class="align-self-center text-dark">
                            <i class="fas fa-chart-line fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-list"></i> Users List
                @if(request('search'))
                    <small class="text-muted">- Search results for "{{ request('search') }}"</small>
                @endif
                @if(request('filter'))
                    <small class="text-muted">- Filtered by role: {{ ucfirst(request('filter')) }}</small>
                @endif
            </h5>
        </div>
        <div class="card-body p-0">
            @if($users->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>Employee</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Created</th>
                                <th width="200">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                            <tr class="align-middle">
                                <td>
                                    @if($user->employee)
                                        <strong>{{ $user->employee->first_name }} {{ $user->employee->surname }}</strong><br>
                                        <small class="text-muted">ID: {{ $user->employee->employee_id }}</small>
                                    @else
                                        <span class="text-muted">No employee linked</span>
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ $user->username }}</strong>
                                </td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    @if ($user->roles->isNotEmpty())
                                        <span class="badge bg-primary">{{ ucfirst($user->roles->first()->name) }}</span>
                                    @else
                                        <span class="badge bg-secondary">No Role</span>
                                    @endif
                                </td>
                                <td>{{ $user->created_at->format('M d, Y') }}</td>
                                <td>
                                     @can('create_users')
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="userActionsDropdown{{ $user->user_id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                            Actions
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="userActionsDropdown{{ $user->user_id }}">
                                            <li>
                                                <button type="button" class="dropdown-item update-role-btn"
                                                    title="Update Role"
                                                    data-user-id="{{ $user->user_id }}"
                                                    data-username="{{ $user->username }}"
                                                    data-current-role="{{ $user->roles->isNotEmpty() ? $user->roles->first()->name : '' }}">
                                                    <i class="fas fa-user-tag"></i> Update Role
                                                </button>
                                            </li>
                                            <li>
                                                <button type="button" class="dropdown-item reset-password-btn"
                                                    title="Reset Password"
                                                    data-user-id="{{ $user->user_id }}"
                                                    data-username="{{ $user->username }}">
                                                    <i class="fas fa-key"></i> Reset Password
                                                </button>
                                            </li>
                                            @if(Auth::id() !== $user->id)
                                            <li>
                                                <form action="{{ route('users.destroy', $user) }}"
                                                      method="POST"
                                                      onsubmit="return confirm('Are you sure you want to delete this user?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger" title="Delete">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </button>
                                                </form>
                                            </li>
                                            @endif
                                        </ul>
                                    </div>
                                    @endcan
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                    <h5>No Users Found</h5>
                    <p class="text-muted">
                        @if(request('search') || request('filter'))
                            No users match your search criteria. Try adjusting your filters.
                        @else
                            No users have been created yet. Click "Create User" to add the first user.
                        @endif
                    </p>
                </div>
            @endif
        </div>
        
        @if($users->hasPages())
        <div class="card-footer">
            {{ $users->links('pagination::bootstrap-5') }}
        </div>
        @endif
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit User: <span id="editModalUsername"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editUserForm" method="POST" action="">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_username" class="form-label">Username</label>
                        <input type="text" name="username" id="edit_username" class="form-control" required>
                        @error('username')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="edit_email" class="form-label">Email</label>
                        <input type="email" name="email" id="edit_email" class="form-control" required>
                        @error('email')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Update Role Modal -->
<div class="modal fade" id="updateRoleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Role for <span id="modalUsername"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="updateRoleForm" method="POST" action="">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="role_name" class="form-label">Select Role</label>
                        <select name="role_name" id="role_name" class="form-select" required>
                            @foreach($roles as $role)
                                <option value="{{ $role }}">{{ ucfirst($role) }}</option>
                            @endforeach
                        </select>
                        @error('role_name')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Role</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reset Password Modal -->
<div class="modal fade" id="resetPasswordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">Reset Password for <span id="resetModalUsername"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Warning:</strong> This will reset the user's password to the default: <code>12345678</code>. The user should change their password after logging in.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="resetPasswordForm" method="POST" action="" style="display: inline;">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-warning">Reset Password</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Create Users Modal -->
@if($employeesWithoutUsers > 0)
<div class="modal fade" id="bulkCreateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-users-cog"></i> Auto Create User Accounts
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <strong>This will create user accounts for {{ $employeesWithoutUsers }} employees:</strong>
                </div>
                <ul class="list-unstyled">
                    <li><i class="fas fa-check text-success"></i> Username will be generated from employee email</li>
                    <li><i class="fas fa-check text-success"></i> Default password: <code>12345678</code></li>
                    <li><i class="fas fa-check text-success"></i> Default role: <strong>Employee</strong></li>
                    <li><i class="fas fa-check text-success"></i> Only employees with email addresses will be processed</li>
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
                    <button type="submit" class="btn btn-success" onclick="return confirm('Are you sure you want to create user accounts for all employees without users?')">
                        <i class="fas fa-users-cog"></i> Create {{ $employeesWithoutUsers }} User Accounts
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-submit form when filter changes
    document.getElementById('filter').addEventListener('change', function() {
        if (this.value !== '') {
            this.form.submit();
        }
    });
    
    // Clear search on escape key
    document.getElementById('search').addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            this.value = '';
            this.form.submit();
        }
    });

    // Handle edit user button clicks
    document.querySelectorAll('.edit-user-btn').forEach(function(button) {
        button.addEventListener('click', function() {
            const userId = this.getAttribute('data-user-id');
            const username = this.getAttribute('data-username');
            const email = this.getAttribute('data-email');
            
            openEditUserModal(userId, username, email);
        });
    });

    // Handle update role button clicks
    document.querySelectorAll('.update-role-btn').forEach(function(button) {
        button.addEventListener('click', function() {
            const userId = this.getAttribute('data-user-id');
            const username = this.getAttribute('data-username');
            const currentRole = this.getAttribute('data-current-role');
            
            openUpdateRoleModal(userId, username, currentRole);
        });
    });

    // Handle reset password button clicks
    document.querySelectorAll('.reset-password-btn').forEach(function(button) {
        button.addEventListener('click', function() {
            const userId = this.getAttribute('data-user-id');
            const username = this.getAttribute('data-username');
            
            openResetPasswordModal(userId, username);
        });
    });
});

// Function to open and populate the edit user modal
function openEditUserModal(userId, username, email) {
    // Set the modal title with the username
    document.getElementById('editModalUsername').textContent = username;
    
    // Populate form fields
    document.getElementById('edit_username').value = username;
    document.getElementById('edit_email').value = email;
    
    // Set the form action URL - FIXED: Using correct route generation
    const form = document.getElementById('editUserForm');
    form.action = '/users/' + userId;
    
    // Show the modal
    const modal = new bootstrap.Modal(document.getElementById('editUserModal'));
    modal.show();
}

// Function to open and populate the update role modal
function openUpdateRoleModal(userId, username, currentRole) {
    // Set the modal title with the username
    document.getElementById('modalUsername').textContent = username;
    
    // Set the form action URL - FIXED: Using correct route for role update
    const form = document.getElementById('updateRoleForm');
    form.action = '/users/' + userId + '/role';
    
    // Set the current role as selected
    const roleSelect = document.getElementById('role_name');
    roleSelect.selectedIndex = -1;
    
    if (currentRole && currentRole !== '') {
        for (let i = 0; i < roleSelect.options.length; i++) {
            if (roleSelect.options[i].value === currentRole) {
                roleSelect.selectedIndex = i;
                break;
            }
        }
    } else {
        roleSelect.selectedIndex = 0;
    }
    
    // Show the modal
    const modal = new bootstrap.Modal(document.getElementById('updateRoleModal'));
    modal.show();
}

// Function to open and populate the reset password modal
function openResetPasswordModal(userId, username) {
    // Set the modal title with the username
    document.getElementById('resetModalUsername').textContent = username;
    
    // Set the form action URL - FIXED: Using correct route for password reset
    const form = document.getElementById('resetPasswordForm');
    form.action = '/users/' + userId + '/reset-password';
    
    // Show the modal
    const modal = new bootstrap.Modal(document.getElementById('resetPasswordModal'));
    modal.show();
}
</script>
@endsection