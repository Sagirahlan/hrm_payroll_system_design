<?php $__env->startSection('title', isset($retiredEmployees) ? 'Retired Employees' : 'Retirement Records'); ?>

<?php $__env->startSection('content'); ?>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create_retirement')): ?>
            <a href="<?php echo e(route('retirements.create')); ?>" class="btn btn-primary btn-lg rounded-3 fw-bold shadow">
                <i class="bi bi-plus-circle me-2"></i> Confirm Retirement
            </a>
        <?php endif; ?>
        <div>
            <?php if(isset($retiredEmployees)): ?>
                <a href="<?php echo e(route('retirements.index')); ?>" class="btn btn-info">Approaching Retirement</a>
            <?php else: ?>
                <a href="<?php echo e(route('retirements.retired')); ?>" class="btn btn-info">View Retired Employees</a>
            <?php endif; ?>
        </div>
    </div>
    <div class="card shadow">
        <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><?php echo e(isset($retiredEmployees) ? 'Retired Employees' : 'Employees Retiring Within 6 Months'); ?></h5>
        </div>
        <div class="card-body">
            <?php if(session('success')): ?>
                <div class="alert alert-success"><?php echo e(session('success')); ?></div>
            <?php endif; ?>
            
            <!-- Search and Filter Form -->
            <form method="GET" class="mb-3">
                <div class="row g-2">
                    <div class="col-md-5">
                        <input type="text" name="search" class="form-control" placeholder="Search Staff No or name" value="<?php echo e(request('search')); ?>">
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="Retired" <?php echo e(request('status') == 'Retired' ? 'selected' : ''); ?>>Retired</option>
                            <option value="Active" <?php echo e(request('status') == 'Active' ? 'selected' : ''); ?>>Active</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-outline-secondary w-100">Search</button>
                    </div>
                    <div class="col-md-2">
                        <a href="<?php echo e(route('retirements.index')); ?>" class="btn btn-outline-secondary w-100">Clear</a>
                    </div>
                </div>
            </form>
            
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Staff No</th>
                            <th>Name</th>
                            <th><?php echo e(isset($retiredEmployees) ? 'Retirement Date' : 'Calculated Retirement Date'); ?></th>
                            <th>Expected Date of Retirement</th>
                            <th>Years of Service</th>
                            <th>Age</th>
                            <th>Retirement Reason</th>
                            <th>Status</th>
                            <?php if(isset($retiredEmployees)): ?>
                                <th>Gratuity Amount</th>
                                <th>Actions</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            $items = isset($retiredEmployees) ? $retiredEmployees : $retirements;
                        ?>
                        <?php $__empty_1 = true; $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $employee = isset($retiredEmployees) ? $item->employee : $item;
                            ?>
                            <tr>
                                <td><?php echo e($loop->iteration); ?></td>
                                <td><?php echo e($employee->staff_no); ?></td>
                                <td><?php echo e($employee->first_name); ?> <?php echo e($employee->surname); ?></td>
                                <td>
                                    <?php if(isset($retiredEmployees)): ?>
                                        <?php echo e(\Carbon\Carbon::parse($item->retirement_date)->format('Y-m-d')); ?>

                                    <?php else: ?>
                                        <?php echo e($employee->calculated_retirement_date ? $employee->calculated_retirement_date->format('Y-m-d') : 'N/A'); ?>

                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if(!isset($retiredEmployees)): ?>
                                        <?php echo e($employee->expected_retirement_date); ?>

                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if(!isset($retiredEmployees)): ?>
                                        <?php echo e($employee->years_of_service); ?> years
                                    <?php else: ?>
                                        <?php echo e(\Carbon\Carbon::parse($employee->date_of_first_appointment)->diffInYears(\Carbon\Carbon::now())); ?> years
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if(!isset($retiredEmployees)): ?>
                                        <?php echo e($employee->age); ?>

                                    <?php else: ?>
                                        <?php echo e(\Carbon\Carbon::parse($employee->date_of_birth)->age); ?>

                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if(!isset($retiredEmployees)): ?>
                                        <?php echo e($employee->retirement_reason); ?>

                                    <?php else: ?>
                                        <?php echo e($item->status ?? 'N/A'); ?>

                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo e($employee->status == 'Retired' ? 'success' : 'warning'); ?>">
                                        <?php echo e($employee->status); ?>

                                    </span>
                                </td>
                                <?php if(isset($retiredEmployees)): ?>
                                    <td>₦<?php echo e(number_format($item->gratuity_amount, 2)); ?></td>
                                    <td>
                                        <a href="<?php echo e(route('employees.show', $employee->employee_id)); ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="<?php echo e(isset($retiredEmployees) ? 9 : 10); ?>" class="text-center">No records found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-center">
                <?php echo e($items->appends(request()->query())->links('pagination::bootstrap-5')); ?>

            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Rowwww\Herd\hrm_payroll_system_design\resources\views/retirements/index.blade.php ENDPATH**/ ?>