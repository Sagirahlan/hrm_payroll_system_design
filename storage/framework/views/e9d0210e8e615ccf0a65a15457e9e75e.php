<?php $__env->startSection('title', 'Disciplinary Actions'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage_disciplinary')): ?>
    <div class="card border-primary shadow">
        <div class="card-header" style="background-color: skyblue; color: white;">
        </div>
        <div class="card-body">
            <div class="d-flex justify-content-between mb-3">
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create_disciplinary')): ?>
                <a href="<?php echo e(route('disciplinary.create')); ?>" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Disciplinary Action
                </a>
                <?php endif; ?>
                <form action="<?php echo e(route('disciplinary.index')); ?>" method="GET" class="d-flex">
                    <input type="text" name="search" class="form-control me-2" placeholder="Search by employee, action type, or status" value="<?php echo e(request('search')); ?>">
                    <button type="submit" class="btn btn-outline-primary me-2">
                        <i class="fas fa-search"></i> Search
                    </button>
                    <?php if(request('search')): ?>
                        <a href="<?php echo e(route('disciplinary.index')); ?>" class="btn btn-outline-secondary">Reset</a>
                    <?php endif; ?>
                </form>
            </div>
            <div class="mb-3">
                <form action="<?php echo e(route('disciplinary.index')); ?>" method="GET" class="d-flex flex-wrap gap-2">
                    <select name="employee_id" class="form-select">
                        <option value="">All Employees</option>
                        <?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($employee->employee_id); ?>" <?php echo e(request('employee_id') == $employee->employee_id ? 'selected' : ''); ?>>
                                <?php echo e($employee->first_name); ?> <?php echo e($employee->surname); ?> (<?php echo e($employee->staff_no); ?>)
                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <select name="action_type" class="form-select">
                        <option value="">All Action Types</option>
                        <?php $__currentLoopData = $actionTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $actionType): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($actionType); ?>" <?php echo e(request('action_type') == $actionType ? 'selected' : ''); ?>><?php echo e($actionType); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <select name="department" class="form-select">
                        <option value="">All Departments</option>
                        <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $department): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($department->department_id); ?>" <?php echo e(request('department') == $department->department_id ? 'selected' : ''); ?>><?php echo e($department->department_name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($status); ?>" <?php echo e(request('status') == $status ? 'selected' : ''); ?>><?php echo e($status); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <button type="submit" class="btn btn-outline-primary">Filter</button>
                    <?php if(request('department') || request('status') || request('action_type') || request('employee_id')): ?>
                        <a href="<?php echo e(route('disciplinary.index')); ?>" class="btn btn-outline-secondary ms-2">Clear Filters</a>
                    <?php endif; ?>
                </form>
            </div>

            <?php if(session('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo e(session('success')); ?>

                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <?php if(session('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo e(session('error')); ?>

                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="card border-primary mb-3">
                <div class="card-header" style="background-color: skyblue; color: white;">
                    <strong>All Disciplinary Actions</strong>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered align-items-center mb-0">
                            <thead class="table-primary">
                                <tr>
                                    <th>Employee</th>
                                    <th>Staff No</th>
                                    <th>Department</th>
                                    <th>Action Type</th>
                                    <th>Description</th>
                                    <th>Action Date</th>
                                    <th>Resolution Date</th>
                                    <th>Days Counted</th>
                                    <th>Status</th>
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['view_disciplinary', 'edit_disciplinary', 'delete_disciplinary'])): ?>
                                    <th>Actions</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $actions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $action): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><?php echo e($action->employee ? $action->employee->first_name . ' ' . $action->employee->surname : 'N/A'); ?></td>
                                        <td><?php echo e($action->employee->staff_no ?? 'N/A'); ?></td>
                                        <td><?php echo e($action->employee ? $action->employee->department->department_name : 'N/A'); ?></td>
                                        <td><?php echo e($action->action_type); ?></td>
                                        <td><?php echo e($action->description ?? 'N/A'); ?></td>
                                        <td><?php echo e($action->action_date); ?></td>
                                        <td><?php echo e($action->resolution_date ?? 'N/A'); ?></td>
                                        <td><?php echo e($action->days_counted); ?></td>
                                        <td>
                                            <span class="badge bg-info text-dark"><?php echo e($action->status); ?></span>
                                        </td>
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['view_disciplinary', 'edit_disciplinary', 'delete_disciplinary'])): ?>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" id="actionDropdown<?php echo e($action->action_id); ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                                    Actions
                                                </button>
                                                <ul class="dropdown-menu" aria-labelledby="actionDropdown<?php echo e($action->action_id); ?>">
                                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view_disciplinary')): ?>
                                                    <li>
                                                        <a class="dropdown-item" href="<?php echo e(route('disciplinary.show', $action->action_id)); ?>">
                                                            <i class="fas fa-eye"></i> View
                                                        </a>
                                                    </li>
                                                    <?php endif; ?>
                                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('edit_disciplinary')): ?>
                                                    <li>
                                                        <a class="dropdown-item" href="<?php echo e(route('disciplinary.edit', $action->action_id)); ?>">
                                                            <i class="fas fa-edit"></i> Edit
                                                        </a>
                                                    </li>
                                                    <?php endif; ?>
                                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete_disciplinary')): ?>
                                                    <li>
                                                        <form action="<?php echo e(route('disciplinary.destroy', $action->action_id)); ?>" method="POST" onsubmit="return confirm('Are you sure you want to delete this disciplinary action?')">
                                                            <?php echo csrf_field(); ?>
                                                            <?php echo method_field('DELETE'); ?>
                                                            <button type="submit" class="dropdown-item text-danger">
                                                                <i class="fas fa-trash"></i> Delete
                                                            </button>
                                                        </form>
                                                    </li>
                                                    <?php endif; ?>
                                                </ul>
                                            </div>
                                        </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="9">No disciplinary actions found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-center mt-3">
                        <?php echo e($actions->links('pagination::bootstrap-5')); ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="alert alert-warning">
        You don't have permission to manage disciplinary actions.
    </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Rowwww\Herd\hrm_payroll_system_design\resources\views/disciplinary/index.blade.php ENDPATH**/ ?>