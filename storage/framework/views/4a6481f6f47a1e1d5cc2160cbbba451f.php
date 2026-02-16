<?php $__env->startSection('title', 'Retired Employees'); ?>

<?php $__env->startSection('content'); ?>
<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">List of Retired Employees</h5>
            <a href="<?php echo e(route('retirements.index')); ?>" class="btn btn-light btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Back to Approaching Retirement
            </a>
        </div>
        <div class="card-body">
            <!-- Search and Filter Form -->
            <form method="GET" class="mb-3">
                <div class="row g-2">
                    <div class="col-md-6">
                        <input type="text" name="search" class="form-control" placeholder="Search Staff No or name" value="<?php echo e(request('search')); ?>">
                    </div>
                    <div class="col-md-4">
                        <input type="date" name="retirement_date" class="form-control" value="<?php echo e(request('retirement_date')); ?>" placeholder="Retirement Date">
                    </div>
                    <div class="col-md-2">
                        <div class="btn-group w-100" role="group">
                            <button class="btn btn-outline-secondary" type="submit">Filter</button>
                            <a href="<?php echo e(route('retirements.retired')); ?>" class="btn btn-outline-secondary">Clear</a>
                        </div>
                    </div>
                </div>
            </form>
            
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Staff No</th>
                            <th>Name</th>
                            <th>Date of Birth</th>
                            <th>Age</th>
                            <th>Years of Service</th>
                            <th>Rank</th>
                            <th>Grade Level/Step</th>
                            <th>Department</th>
                            <th>Retirement Date</th>
                            <th>Retire Reason</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $retiredEmployees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e($loop->iteration); ?></td>
                                <td><?php echo e($employee->staff_no); ?></td>
                                <td><?php echo e($employee->first_name); ?> <?php echo e($employee->surname); ?></td>
                                <td><?php echo e($employee->date_of_birth ? \Carbon\Carbon::parse($employee->date_of_birth)->format('Y-m-d') : 'N/A'); ?></td>
                                <td><?php echo e($employee->date_of_birth ? \Carbon\Carbon::parse($employee->date_of_birth)->age : 'N/A'); ?></td>
                                <td><?php echo e($employee->date_of_first_appointment ? round(\Carbon\Carbon::parse($employee->date_of_first_appointment)->diffInYears(\Carbon\Carbon::parse($employee->retirement->retirement_date ?? now()))) : 'N/A'); ?></td>
                                <td><?php echo e($employee->rank ? $employee->rank->name : 'N/A'); ?></td>
                                <td><?php echo e($employee->gradeLevel ? $employee->gradeLevel->name : 'N/A'); ?>-<?php echo e($employee->step ? $employee->step->name : 'N/A'); ?></td>
                                <td><?php echo e($employee->department ? $employee->department->department_name : 'N/A'); ?></td>
                                <td>
                                    <?php if($employee->retirement && $employee->retirement->retirement_date): ?>
                                        <?php echo e(\Carbon\Carbon::parse($employee->retirement->retirement_date)->format('Y-m-d')); ?>

                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($employee->retirement && $employee->retirement->retire_reason): ?>
                                        <?php echo e($employee->retirement->retire_reason); ?>

                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?php echo e(route('employees.show', $employee->employee_id)); ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="12" class="text-center">No retired employees found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-center">
                <?php echo e($retiredEmployees->appends(request()->query())->links('pagination::bootstrap-5')); ?>

            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Rowwww\Herd\hrm_payroll_system_design\resources\views/retirements/retired.blade.php ENDPATH**/ ?>