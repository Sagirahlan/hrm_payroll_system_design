<?php $__env->startSection('title', 'Employees Without User Accounts'); ?>

<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1><i class="fas fa-user-plus"></i> Employees Without User Accounts</h1>
            <p class="text-muted mb-0">Manage employees who don't have user accounts yet</p>
        </div>
        <div class="btn-group">
            <a href="<?php echo e(route('users.index')); ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Users
            </a>
            <?php if($employees->total() > 0): ?>
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#bulkCreateModal">
                    <i class="fas fa-users-cog"></i> Create All Users
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="<?php echo e(route('users.employees-without-users')); ?>" class="row g-3">
                <div class="col-md-4">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" name="search" id="search" class="form-control"
                           placeholder="Search by name, email, or ID..."
                           value="<?php echo e(request('search')); ?>">
                </div>

               <?php if($departments->count() > 0): ?>
    <div class="col-md-3">
        <label for="department_id" class="form-label">Department</label>
        <select name="department_id" id="department_id" class="form-select">
            <option value="">All Departments</option>
            <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $department): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($department->department_id); ?>" <?php echo e(request('department_id') == $department->department_id ? 'selected' : ''); ?>>
                    <?php echo e($department->department_name); ?>


                </option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
<?php endif; ?>


                <div class="col-md-2">
                    <label for="email_filter" class="form-label">Email Status</label>
                    <select name="email_filter" id="email_filter" class="form-select">
                        <option value="">All</option>
                        <option value="with_email" <?php echo e(request('email_filter') == 'with_email' ? 'selected' : ''); ?>>With Email</option>
                        <option value="without_email" <?php echo e(request('email_filter') == 'without_email' ? 'selected' : ''); ?>>No Email</option>
                    </select>
                </div>

                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Search
                    </button>
                    <a href="<?php echo e(route('users.employees-without-users')); ?>" class="btn btn-secondary">
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
                            <h4 class="text-dark"><?php echo e($employees->total()); ?></h4>
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
                            <h4 class="text-dark"><?php echo e($employees->where('email', '!=', null)->count()); ?></h4>
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
                            <h4 class="text-dark"><?php echo e($employees->where('email', '==', null)->count()); ?></h4>
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
                            <h4 class="text-dark"><?php echo e($departments->count()); ?></h4> 

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
                <?php if(request('search')): ?>
                    <small class="text-muted">- Search results for "<?php echo e(request('search')); ?>"</small>
                <?php endif; ?>
                <?php if(request('department')): ?>
                    <small class="text-muted">- Department: <?php echo e(request('department')); ?></small>
                <?php endif; ?>
            </h5>
        </div>
        <div class="card-body p-0">
            <?php if($employees->count() > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>Staff No</th>
                                <th>Name</th>
                                <th>Email</th>
                                <?php if($departments->count() > 0): ?>
                                    <th>Department</th>
                                <?php endif; ?>
                                <th>Status</th>
                                <th width="120">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td>
                                    <strong><?php echo e($employee->staff_no); ?></strong>
                                </td>
                                <td>
                                    <strong><?php echo e($employee->first_name); ?> <?php echo e($employee->surname); ?></strong>
                                </td>
                                <td>
                                    <?php if($employee->email): ?>
                                        <span class="text-success">
                                            <i class="fas fa-envelope"></i> <?php echo e($employee->email); ?>

                                        </span>
                                    <?php else: ?>
                                        <span class="text-warning">
                                            <i class="fas fa-exclamation-triangle"></i> No email
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <?php if($departments->count() > 0): ?>
                                <td>
                                    <?php echo e($employee->department->department_name ?? 'Not specified'); ?>

                                </td>
                                <?php endif; ?>
                                <td>
                                    <?php if($employee->email): ?>
                                        <span class="badge bg-success">Ready for user creation</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning">Email required</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($employee->email): ?>
                                        <button type="button" class="btn btn-sm btn-primary"
                                                data-bs-toggle="modal"
                                                data-bs-target="#createUserModal<?php echo e($employee->employee_id); ?>"
                                                title="Create User Account">
                                            <i class="fas fa-user-plus"></i>
                                        </button>
                                    <?php else: ?>
                                        <span class="text-muted" title="Email required to create user">
                                            <i class="fas fa-ban"></i>
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>

                            <!-- Individual Create User Modal -->
                            <?php if($employee->email): ?>
                            <div class="modal fade" id="createUserModal<?php echo e($employee->employee_id); ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header bg-primary text-white">
                                            <h5 class="modal-title">
                                                <i class="fas fa-user-plus"></i> Create User for <?php echo e($employee->first_name); ?> <?php echo e($employee->surname); ?>

                                            </h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form action="<?php echo e(route('users.store')); ?>" method="POST">
                                            <?php echo csrf_field(); ?>
                                            <div class="modal-body">
                                                <input type="hidden" name="employee_id" value="<?php echo e($employee->employee_id); ?>">

                                                <div class="mb-3">
                                                    <label for="username<?php echo e($employee->employee_id); ?>" class="form-label">Username</label>
                                                    <input type="text" name="username" id="username<?php echo e($employee->employee_id); ?>"
                                                           class="form-control" value="<?php echo e(strtolower(explode('@', $employee->email)[0])); ?>" required>
                                                </div>

                                                <div class="mb-3">
                                                    <label for="email<?php echo e($employee->employee_id); ?>" class="form-label">Email</label>
                                                    <input type="email" name="email" id="email<?php echo e($employee->employee_id); ?>"
                                                           class="form-control" value="<?php echo e($employee->email); ?>" readonly>
                                                </div>

                                                <div class="mb-3">
                                                    <label for="password<?php echo e($employee->employee_id); ?>" class="form-label">Password</label>
                                                    <input type="password" name="password" id="password<?php echo e($employee->employee_id); ?>"
                                                           class="form-control" value="<?php echo e($employee->date_of_birth); ?>" required>
                                                </div>

                                                <div class="mb-3">
                                                    <label for="password_confirmation<?php echo e($employee->employee_id); ?>" class="form-label">Confirm Password</label>
                                                    <input type="password" name="password_confirmation" id="password_confirmation<?php echo e($employee->employee_id); ?>"
                                                           class="form-control" value="<?php echo e($employee->date_of_birth); ?>" required>
                                                </div>

                                                <div class="mb-3">
                                                    <label for="role<?php echo e($employee->employee_id); ?>" class="form-label">Role</label>
                                                    <select name="role" id="role<?php echo e($employee->employee_id); ?>" class="form-select" required>
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
                            <?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                    <h5>All Employees Have User Accounts!</h5>
                    <p class="text-muted">
                        <?php if(request('search') || request('department') || request('email_filter')): ?>
                            No employees match your search criteria.
                        <?php else: ?>
                            Every employee in the system has a user account created.
                        <?php endif; ?>
                    </p>
                </div>
            <?php endif; ?>
        </div>

        <?php if($employees->hasPages()): ?>
        <div class="card-footer">
            <?php echo e($employees->links('pagination::bootstrap-5')); ?>

        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Bulk Create Users Modal -->
<?php if($employees->where('email', '!=', null)->count() > 0): ?>
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
                    <strong>This will create user accounts for <?php echo e($employees->where('email', '!=', null)->count()); ?> employees with email addresses:</strong>
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
                <form action="<?php echo e(route('users.bulk-create')); ?>" method="POST" style="display: inline;">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="btn btn-success" onclick="return confirm('Are you sure you want to create user accounts for all employees with email addresses?')">
                        <i class="fas fa-users-cog"></i> Create <?php echo e($employees->where('email', '!=', null)->count()); ?> User Accounts
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

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
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Rowwww\Herd\hrm_payroll_system_design\resources\views/users/employees-without-users.blade.php ENDPATH**/ ?>