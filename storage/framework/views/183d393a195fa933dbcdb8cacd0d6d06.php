<?php $__env->startSection('title', 'Departments'); ?>

<?php $__env->startSection('content'); ?>
<div class="container mt-4">
    <h1 class="mb-4 text-primary border-bottom border-3 border-primary pb-2">Departments</h1>
   
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create_departments')): ?>
    <a href="<?php echo e(route('departments.create')); ?>" class="btn btn-primary mb-3">
        <i class="fas fa-plus"></i> Add Department
    </a>
    <?php endif; ?>

    <?php if(session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show border border-success" role="alert">
            <?php echo e(session('success')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if(session('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show border border-danger" role="alert">
            <?php echo e(session('error')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card border-primary shadow-sm">
        <div class="card-body">
            <table class="table table-striped table-bordered align-middle">
                <thead class="table-primary">
                    <tr>
                        <th class="border-primary">Name</th>
                        <th class="border-primary">Description</th>
                        <th class="border-primary">Employees</th>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['edit_departments', 'delete_departments'])): ?>
                        <th class="border-primary">Actions</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $department): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="border-primary"><?php echo e($department->department_name); ?></td>
                            <td class="border-primary"><?php echo e($department->description ?? 'N/A'); ?></td>
                            <td class="border-primary">
                                <button 
                                    type="button" 
                                    class="btn btn-link p-0 text-decoration-none" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#employeesModal<?php echo e($department->id); ?>"
                                >
                                    <?php echo e($department->employees()->count()); ?>

                                </button>

                                <!-- Employees Modal -->
                                <div class="modal fade" id="employeesModal<?php echo e($department->id); ?>" tabindex="-1" aria-labelledby="employeesModalLabel<?php echo e($department->id); ?>" aria-hidden="true">
                                    <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="employeesModalLabel<?php echo e($department->id); ?>">
                                                    Employees in <?php echo e($department->department_name); ?>

                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <?php
                                                    $employees = $department->employees()->get();
                                                ?>
                                                <?php if($employees->count()): ?>
                                                    <table class="table table-sm table-bordered align-middle">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th>#</th>
                                                                <th>Employee ID</th>
                                                                <th>Name</th>
                                                                <th>Status</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                <tr>
                                                                    <td><?php echo e($index + 1); ?></td>
                                                                    <td><?php echo e($employee->employee_id); ?></td>
                                                                    <td><?php echo e($employee->first_name); ?> <?php echo e($employee->surname); ?></td>
                                                                    <td>
                                                                        <span class="badge bg-<?php echo e($employee->status === 'Active' ? 'success' : 'secondary'); ?>">
                                                                            <?php echo e($employee->status); ?>

                                                                        </span>
                                                                    </td>
                                                                </tr>
                                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                        </tbody>
                                                    </table>
                                                <?php else: ?>
                                                    <div class="text-muted text-center py-3">
                                                        No employees found in this department.
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['edit_departments', 'delete_departments'])): ?>
                            <td class="border-primary">
                                <div class="dropdown">
                                    <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" id="actionsDropdown<?php echo e($department->id); ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                        Actions
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="actionsDropdown<?php echo e($department->id); ?>">
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('edit_departments')): ?>
                                        <li>
                                            <a href="<?php echo e(route('departments.edit', $department)); ?>" class="dropdown-item">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                        </li>
                                        <?php endif; ?>
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete_departments')): ?>
                                        <li>
                                            <form action="<?php echo e(route('departments.destroy', $department)); ?>" method="POST" onsubmit="return confirm('Are you sure you want to delete this department?')" style="display:inline;">
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
                            <td colspan="4" class="text-center text-muted">No departments found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <div class="d-flex justify-content-center">
                <?php echo e($departments->links()); ?>

            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Rowwww\Herd\hrm_payroll_system_design\resources\views/departments/index.blade.php ENDPATH**/ ?>