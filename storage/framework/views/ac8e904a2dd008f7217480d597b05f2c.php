<?php $__env->startSection('title', 'Users Management'); ?>

<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-users"></i> Users Management</h1>
         <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create_users')): ?>
        <div class="btn-group">
            <a href="<?php echo e(route('users.create')); ?>" class="btn btn-primary">
                <i class="fas fa-plus"></i> Create User
            </a>
            <?php if($employeesWithoutUsers > 0): ?>
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#bulkCreateModal">
                    <i class="fas fa-users-cog"></i> Auto Create Users (<?php echo e($employeesWithoutUsers); ?>)
                </button>
                <a href="<?php echo e(route('users.employees-without-users')); ?>" class="btn btn-info">
                    <i class="fas fa-eye"></i> View Employees Without Users
                </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Filters and Search -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="<?php echo e(route('users.index')); ?>" class="row g-3">
                <div class="col-md-4">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" name="search" id="search" class="form-control" 
                           placeholder="Search by username, email, or employee name..." 
                           value="<?php echo e(request('search')); ?>">
                </div>
                <div class="col-md-3">
                    <label for="filter" class="form-label">Filter by Role</label>
                    <select name="filter" id="filter" class="form-select">
                        <option value="">All Roles</option>
                        <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($role); ?>" <?php echo e(request('filter') == $role ? 'selected' : ''); ?>>
                                <?php echo e(ucfirst($role)); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Search
                    </button>
                    <a href="<?php echo e(route('users.index')); ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Clear
                    </a>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <small class="text-muted">
                        Showing <?php echo e($users->count()); ?> of <?php echo e($users->total()); ?> users
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
                            <h4 class="text-dark"><?php echo e($users->total()); ?></h4>
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
                            <h4 class="text-dark"><?php echo e($employeesWithoutUsers); ?></h4>
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
                            <h4 class="text-dark"><?php echo e($roles->count()); ?></h4>
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
                            <h4 class="text-dark"><?php echo e(request('search') ? $users->count() : $users->total()); ?></h4>
                            <p class="mb-0 text-dark"><?php echo e(request('search') ? 'Search Results' : 'Active Users'); ?></p>
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
                <?php if(request('search')): ?>
                    <small class="text-muted">- Search results for "<?php echo e(request('search')); ?>"</small>
                <?php endif; ?>
                <?php if(request('filter')): ?>
                    <small class="text-muted">- Filtered by role: <?php echo e(ucfirst(request('filter'))); ?></small>
                <?php endif; ?>
            </h5>
        </div>
        <div class="card-body p-0">
            <?php if($users->count() > 0): ?>
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
                            <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr class="align-middle">
                                <td>
                                    <?php if($user->employee): ?>
                                        <strong><?php echo e($user->employee->first_name); ?> <?php echo e($user->employee->surname); ?></strong><br>
                                        <small class="text-muted">ID: <?php echo e($user->employee->employee_id); ?></small>
                                    <?php else: ?>
                                        <span class="text-muted">No employee linked</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?php echo e($user->username); ?></strong>
                                </td>
                                <td><?php echo e($user->email); ?></td>
                                <td>
                                    <?php if($user->roles->isNotEmpty()): ?>
                                        <span class="badge bg-primary"><?php echo e(ucfirst($user->roles->first()->name)); ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">No Role</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo e($user->created_at->format('M d, Y')); ?></td>
                                <td>
                                     <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create_users')): ?>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="userActionsDropdown<?php echo e($user->user_id); ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                            Actions
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="userActionsDropdown<?php echo e($user->user_id); ?>">
                                            <li>
                                                <button type="button" class="dropdown-item update-role-btn"
                                                    title="Update Role"
                                                    data-user-id="<?php echo e($user->user_id); ?>"
                                                    data-username="<?php echo e($user->username); ?>"
                                                    data-current-role="<?php echo e($user->roles->isNotEmpty() ? $user->roles->first()->name : ''); ?>">
                                                    <i class="fas fa-user-tag"></i> Update Role
                                                </button>
                                            </li>
                                            <li>
                                                <button type="button" class="dropdown-item reset-password-btn"
                                                    title="Reset Password"
                                                    data-user-id="<?php echo e($user->user_id); ?>"
                                                    data-username="<?php echo e($user->username); ?>"
                                                    data-employee-dob="<?php echo e($user->employee && $user->employee->date_of_birth ? \Carbon\Carbon::parse($user->employee->date_of_birth)->format('Y-m-d') : ''); ?>">
                                                    <i class="fas fa-key"></i> Reset Password
                                                </button>
                                            </li>
                                            <?php if(Auth::id() !== $user->id): ?>
                                            <li>
                                                <form action="<?php echo e(route('users.destroy', $user)); ?>"
                                                      method="POST"
                                                      onsubmit="return confirm('Are you sure you want to delete this user?')">
                                                    <?php echo csrf_field(); ?>
                                                    <?php echo method_field('DELETE'); ?>
                                                    <button type="submit" class="dropdown-item text-danger" title="Delete">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </button>
                                                </form>
                                            </li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                    <h5>No Users Found</h5>
                    <p class="text-muted">
                        <?php if(request('search') || request('filter')): ?>
                            No users match your search criteria. Try adjusting your filters.
                        <?php else: ?>
                            No users have been created yet. Click "Create User" to add the first user.
                        <?php endif; ?>
                    </p>
                </div>
            <?php endif; ?>
        </div>
        
        <?php if($users->hasPages()): ?>
        <div class="card-footer">
            <?php echo e($users->links('pagination::bootstrap-5')); ?>

        </div>
        <?php endif; ?>
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
                <?php echo csrf_field(); ?>
                <?php echo method_field('PATCH'); ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_username" class="form-label">Username</label>
                        <input type="text" name="username" id="edit_username" class="form-control" required>
                        <?php $__errorArgs = ['username'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="text-danger"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <div class="mb-3">
                        <label for="edit_email" class="form-label">Email</label>
                        <input type="email" name="email" id="edit_email" class="form-control" required>
                        <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="text-danger"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
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
                <?php echo csrf_field(); ?>
                <?php echo method_field('PATCH'); ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="role_name" class="form-label">Select Role</label>
                        <select name="role_name" id="role_name" class="form-select" required>
                            <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($role); ?>"><?php echo e(ucfirst($role)); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <?php $__errorArgs = ['role_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="text-danger"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
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
                    <strong>Warning:</strong> This will reset the user's password to <span id="resetPasswordInfo">the default: <code>12345678</code></span>. The user should change their password after logging in.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="resetPasswordForm" method="POST" action="" style="display: inline;">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PATCH'); ?>
                    <button type="submit" class="btn btn-warning">Reset Password</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Create Users Modal -->
<?php if($employeesWithoutUsers > 0): ?>
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
                    <strong>This will create user accounts for <?php echo e($employeesWithoutUsers); ?> employees:</strong>
                </div>
                <ul class="list-unstyled">
                    <li><i class="fas fa-check text-success"></i> Username will be generated from employee email</li>
                    <li><i class="fas fa-check text-success"></i> Default password: Employee's date of birth (YYYY-MM-DD format)</li>
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
                <form action="<?php echo e(route('users.bulk-create')); ?>" method="POST" style="display: inline;">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="btn btn-success" onclick="return confirm('Are you sure you want to create user accounts for all employees without users?')">
                        <i class="fas fa-users-cog"></i> Create <?php echo e($employeesWithoutUsers); ?> User Accounts
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

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
            const employeeDob = this.getAttribute('data-employee-dob');
            
            openResetPasswordModal(userId, username, employeeDob);
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
function openResetPasswordModal(userId, username, employeeDob) {
    // Set the modal title with the username
    document.getElementById('resetModalUsername').textContent = username;
    
    // Set the form action URL - FIXED: Using correct route for password reset
    const form = document.getElementById('resetPasswordForm');
    form.action = '/users/' + userId + '/reset-password';
    
    // Update the password info based on whether the user has an employee linked
    const passwordInfo = document.getElementById('resetPasswordInfo');
    if (employeeDob && employeeDob !== '') {
        passwordInfo.innerHTML = 'the employee\'s date of birth: <code>' + employeeDob + '</code>';
    } else {
        passwordInfo.innerHTML = 'the default: <code>12345678</code>';
    }
    
    // Show the modal
    const modal = new bootstrap.Modal(document.getElementById('resetPasswordModal'));
    modal.show();
}
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Rowwww\Herd\hrm_payroll_system_design\resources\views/users/index.blade.php ENDPATH**/ ?>