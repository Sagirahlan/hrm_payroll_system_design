<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage_biometrics')): ?>
    <div class="card border-primary shadow">
        <div class="card-header" style="background-color: skyblue; color: white;">
            <h5 class="mb-0">Biometric Data</h5>
        </div>
        <div class="card-body">
            <!-- Search and Filter Form -->
            <div class="row mb-4">
                <div class="col-md-8">
                    <form method="GET" action="<?php echo e(route('biometrics.index')); ?>" class="row g-3">
                        <div class="col-md-8">
                            <input type="text" class="form-control" name="search" placeholder="Search by employee name or ID" value="<?php echo e(request('search')); ?>">
                        </div>
                        <div class="col-md-4">
                            <select name="status" class="form-select">
                                <option value="">All Statuses</option>
                                <option value="registered" <?php echo e(request('status') == 'registered' ? 'selected' : ''); ?>>Registered</option>
                                <option value="unregistered" <?php echo e(request('status') == 'unregistered' ? 'selected' : ''); ?>>Not Registered</option>
                            </select>
                        </div>
                        <div class="col-md-12 mt-2">
                            <button type="submit" class="btn btn-primary">Search</button>
                            <a href="<?php echo e(route('biometrics.index')); ?>" class="btn btn-secondary">Clear</a>
                        </div>
                    </form>
                </div>
                <div class="col-md-4 text-end">
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create_biometrics')): ?>
                    <a href="<?php echo e(route('biometrics.create')); ?>" class="btn btn-primary">Add Biometric Data</a>
                    <?php endif; ?>
                </div>
            </div>

            <?php if(session('success')): ?>
                <div class="alert alert-success"><?php echo e(session('success')); ?></div>
            <?php endif; ?>

            <div class="table-responsive">
                <table class="table table-striped table-bordered align-items-center mb-0">
                    <thead class="table-primary">
                        <tr>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Staff No</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Employee Name</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Department</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Biometric Status</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Verification Status</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Verification Date</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e($employee->staff_no); ?></td>
                                <td class="fw-bold"><?php echo e($employee->first_name); ?> <?php echo e($employee->middle_name); ?> <?php echo e($employee->surname); ?></td>
                                <td><?php echo e($employee->department->department_name ?? 'N/A'); ?></td>
                                <td>
                                    <?php if($employee->biometricData): ?>
                                        <span class="badge bg-success">Registered</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Not Registered</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($employee->biometricData): ?>
                                        <span class="badge bg-<?php echo e($employee->biometricData->verification_status == 'Verified' ? 'success' : 'warning'); ?>">
                                            <?php echo e($employee->biometricData->verification_status); ?>

                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($employee->biometricData): ?>
                                        <span class="badge bg-secondary">
                                            <?php echo e($employee->biometricData->verification_date ?? 'N/A'); ?>

                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create_biometrics')): ?>
                                    <?php if($employee->biometricData): ?>
                                        <span class="text-muted">Registered</span>
                                    <?php else: ?>
                                        <a href="<?php echo e(route('biometrics.create', ['employee_id' => $employee->employee_id])); ?>" class="btn btn-sm btn-primary">Register</a>
                                    <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="7" class="text-center">No employees found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="mt-3 d-flex justify-content-center">
                <?php echo e($employees->links('pagination::bootstrap-4')); ?>

            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="alert alert-warning">
        You don't have permission to manage biometric data.
    </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Rowwww\Herd\hrm_payroll_system_design\resources\views/biometrics/index.blade.php ENDPATH**/ ?>