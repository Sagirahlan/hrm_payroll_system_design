<?php $__env->startSection('title', 'Manage Employee Bank Details'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Manage Employee Bank Details</h4>
                    <p class="card-category">Update employee bank details</p>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <form action="<?php echo e(route('bank-details.search')); ?>" method="POST" class="d-flex">
                            <?php echo csrf_field(); ?>
                            <input type="text" name="query" class="form-control me-2" placeholder="Search employees by name, ID, or staff number..." value="<?php echo e(request('query', '')); ?>">
                            <button class="btn btn-outline-primary" type="submit">Search</button>
                            <a href="<?php echo e(route('bank-details.index')); ?>" class="btn btn-outline-secondary ms-2">Reset</a>
                        </form>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead>
                                <tr>
                                    <th>Staff No</th>
                                    <th>Full Name</th>
                                    <th>Department</th>
                                    <th>Bank Name</th>
                                    <th>Bank Code</th>
                                    <th>Account Number</th>
                                    <th>Account Name</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><?php echo e($employee->staff_no); ?></td>
                                        <td><?php echo e($employee->first_name); ?> <?php echo e($employee->middle_name); ?> <?php echo e($employee->surname); ?></td>
                                        <td><?php echo e($employee->department->department_name ?? 'N/A'); ?></td>
                                        <td><?php echo e($employee->bank->bank_name ?? 'Not Set'); ?></td>
                                        <td><?php echo e($employee->bank->bank_code ?? 'Not Set'); ?></td>
                                        <td><?php echo e($employee->bank->account_no ?? 'Not Set'); ?></td>
                                        <td><?php echo e($employee->bank->account_name ?? 'Not Set'); ?></td>
                                        <td>
                                            <a href="<?php echo e(route('bank-details.show', $employee->employee_id)); ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i> Update
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="8" class="text-center">No employees found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-end mt-3">
                        <?php echo e($employees->withQueryString()->links('pagination::bootstrap-5')); ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Rowwww\Herd\hrm_payroll_system_design\resources\views/bank-details/index.blade.php ENDPATH**/ ?>