<?php $__env->startSection('title', 'Eligible for Retirement'); ?>

<?php $__env->startSection('content'); ?>
<div class="container mt-4">
    <div class="mb-3">
        <a href="<?php echo e(route('retirements.index')); ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Retirements
        </a>
    </div>
    
    <div class="card shadow">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0">Employees Eligible for Retirement</h5>
        </div>
        <div class="card-body">
            <?php if(session('success')): ?>
                <div class="alert alert-success"><?php echo e(session('success')); ?></div>
            <?php endif; ?>
            <?php if(session('error')): ?>
                <div class="alert alert-danger"><?php echo e(session('error')); ?></div>
            <?php endif; ?>

            <!-- Search and Filter Form -->
            <form method="GET" action="<?php echo e(route('retirements.create')); ?>" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" name="search" class="form-control" 
                                   placeholder="Search Staff No or name..." 
                                   value="<?php echo e(request('search')); ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select name="department_id" class="form-select">
                            <option value="">All Departments</option>
                            <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($dept->department_id); ?>" <?php echo e(request('department_id') == $dept->department_id ? 'selected' : ''); ?>>
                                    <?php echo e($dept->department_name); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="eligibility_reason" class="form-select">
                            <option value="">All Eligibility Reasons</option>
                            <option value="By Old Age" <?php echo e(request('eligibility_reason') == 'By Old Age' ? 'selected' : ''); ?>>By Old Age</option>
                            <option value="By Years of Service" <?php echo e(request('eligibility_reason') == 'By Years of Service' ? 'selected' : ''); ?>>By Years of Service</option>
                            <option value="Deceased" <?php echo e(request('eligibility_reason') == 'Deceased' ? 'selected' : ''); ?>>Deceased</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <div class="btn-group w-100" role="group">
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-filter me-1"></i> Filter
                            </button>
                            <a href="<?php echo e(route('retirements.create')); ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i> Clear
                            </a>
                        </div>
                    </div>
                </div>
                
                <?php if(request()->hasAny(['search', 'department_id', 'eligibility_reason'])): ?>
                    <div class="mt-2">
                        <small class="text-muted">Active filters:</small>
                        <?php if(request('search')): ?>
                            <span class="badge bg-info ms-1">Search: "<?php echo e(request('search')); ?>"</span>
                        <?php endif; ?>
                        <?php if(request('department_id')): ?>
                            <span class="badge bg-primary ms-1">Department: <?php echo e($departments->find(request('department_id'))->department_name ?? 'Unknown'); ?></span>
                        <?php endif; ?>
                        <?php if(request('eligibility_reason')): ?>
                            <span class="badge bg-warning text-dark ms-1">Reason: <?php echo e(request('eligibility_reason')); ?></span>
                        <?php endif; ?>
                        <span class="badge bg-secondary ms-2"><?php echo e($eligibleEmployees->total()); ?> employee(s) found</span>
                    </div>
                <?php endif; ?>
            </form>

            <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Staff ID</th>
                        <th>Name</th>
                        <th>Date of Birth</th>
                        <th>Age</th>
                        <th>Appointment Date</th>
                        <th>Expected Retirement Date</th>
                        <th>Years of Service</th>
                        <th>GL/Step</th>
                        <th>Department</th>
                        <th>Rank</th>
                        <th>Eligibility Reason</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $eligibleEmployees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($loop->iteration); ?></td>
                            <td><?php echo e($employee->staff_no); ?></td>
                            <td><?php echo e($employee->first_name); ?> <?php echo e($employee->surname); ?></td>
                            <td><?php echo e(\Carbon\Carbon::parse($employee->date_of_birth)->format('Y-m-d')); ?></td>
                            <td><?php echo e(\Carbon\Carbon::parse($employee->date_of_birth)->age); ?></td>
                            <td><?php echo e(\Carbon\Carbon::parse($employee->date_of_first_appointment)->format('Y-m-d')); ?></td>
                            <td><?php echo e($employee->expected_retirement_date ? \Carbon\Carbon::parse($employee->expected_retirement_date)->format('Y-m-d') : 'N/A'); ?></td>
                            <td><?php echo e(round(\Carbon\Carbon::parse($employee->date_of_first_appointment)->diffInYears(\Carbon\Carbon::now()))); ?> years</td>
                            <td><?php echo e($employee->gradeLevel ? $employee->gradeLevel->name : 'N/A'); ?>-<?php echo e($employee->step ? $employee->step->name : 'N/A'); ?></td>
                            <td><?php echo e($employee->department->department_name ?? 'N/A'); ?></td>
                            <td><?php echo e($employee->rank ? $employee->rank->name : 'N/A'); ?></td>
                            <td>
                                <?php
                                    $retireReason = 'N/A';
                                    if ($employee->status === 'Deceased') {
                                        $retireReason = 'Death in Service';
                                    } elseif ($employee->gradeLevel && $employee->gradeLevel->salaryScale) {
                                        $retirementAge = (int) $employee->gradeLevel->salaryScale->max_retirement_age;
                                        $yearsOfService = (int) $employee->gradeLevel->salaryScale->max_years_of_service;
                                        $age = \Carbon\Carbon::parse($employee->date_of_birth)->age;
                                        $serviceDuration = \Carbon\Carbon::parse($employee->date_of_first_appointment)->diffInYears(\Carbon\Carbon::now());

                                        // Check if the employee has reached the maximum years of service first
                                        if ($serviceDuration >= $yearsOfService) {
                                            $retireReason = 'By Years of Service';
                                        } elseif ($age >= $retirementAge) {
                                            $retireReason = 'By Old Age';
                                        } else {
                                            // If neither condition is met, determine by which will happen first
                                            $actualRetirementDate = \Carbon\Carbon::parse($employee->date_of_birth)->addYears($retirementAge)->min(\Carbon\Carbon::parse($employee->date_of_first_appointment)->addYears($yearsOfService));
                                            if ($actualRetirementDate->eq(\Carbon\Carbon::parse($employee->date_of_birth)->addYears($retirementAge))) {
                                                $retireReason = 'By Old Age';
                                            } else {
                                                $retireReason = 'By Years of Service';
                                            }
                                        }
                                    } else {
                                        $retireReason = 'Missing grade/salary scale information';
                                    }
                                ?>
                                <?php echo e($retireReason); ?>

                            </td>
                            <td>
                                <div class="btn-group" role="group" aria-label="Action buttons">
                                  
                                    <a href="<?php echo e(route('retirements.pension-compute', $employee->employee_id)); ?>" class="btn btn-primary btn-sm">
                                        Pension Compute
                                    </a>
                                </div>
                            </td>
                        </tr>

                        <!-- Retire Modal -->
                        <div class="modal fade" id="retireModal<?php echo e($employee->employee_id); ?>" tabindex="-1" aria-labelledby="retireModalLabel<?php echo e($employee->employee_id); ?>" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="retireModalLabel<?php echo e($employee->employee_id); ?>">Confirm Retirement for <?php echo e($employee->first_name); ?> <?php echo e($employee->surname); ?></h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form action="<?php echo e(route('retirements.store')); ?>" method="POST">
                                        <div class="modal-body">
                                            <?php echo csrf_field(); ?>
                                            <input type="hidden" name="employee_id" value="<?php echo e($employee->employee_id); ?>">
                                            <input type="hidden" name="retirement_date" value="<?php echo e(now()->toDateString()); ?>">
                                            <input type="hidden" name="status" value="complete">
                                            <div class="mb-3">
                                                <label for="retire_reason_<?php echo e($employee->employee_id); ?>" class="form-label">Retire Reason</label>
                                                <?php
                                                    $retireReason = 'N/A';
                                                    if ($employee->status === 'Deceased') {
                                                        $retireReason = 'Death in Service';
                                                    } elseif ($employee->gradeLevel && $employee->gradeLevel->salaryScale) {
                                                        $retirementAge = (int) $employee->gradeLevel->salaryScale->max_retirement_age;
                                                        $yearsOfService = (int) $employee->gradeLevel->salaryScale->max_years_of_service;
                                                        $age = \Carbon\Carbon::parse($employee->date_of_birth)->age;
                                                        $serviceDuration = \Carbon\Carbon::parse($employee->date_of_first_appointment)->diffInYears(\Carbon\Carbon::now());

                                                        // Check if the employee has reached the maximum years of service first
                                                        if ($serviceDuration >= $yearsOfService) {
                                                            $retireReason = 'By Years of Service';
                                                        } elseif ($age >= $retirementAge) {
                                                            $retireReason = 'By Old Age';
                                                        } else {
                                                            // If neither condition is met, determine by which will happen first
                                                            $actualRetirementDate = \Carbon\Carbon::parse($employee->date_of_birth)->addYears($retirementAge)->min(\Carbon\Carbon::parse($employee->date_of_first_appointment)->addYears($yearsOfService));
                                                            if ($actualRetirementDate->eq(\Carbon\Carbon::parse($employee->date_of_birth)->addYears($retirementAge))) {
                                                                $retireReason = 'By Old Age';
                                                            } else {
                                                                $retireReason = 'By Years of Service';
                                                            }
                                                        }
                                                    } else {
                                                        $retireReason = 'Missing grade/salary scale information';
                                                    }
                                                ?>
                                                <input type="text" name="retire_reason" id="retire_reason_<?php echo e($employee->employee_id); ?>" class="form-control" value="<?php echo e($retireReason); ?>" readonly>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-success">Confirm Retirement</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="13" class="text-center">No employees are currently eligible for retirement.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            </div>
            
            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                <?php echo e($eligibleEmployees->links('pagination::bootstrap-5')); ?>

            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Rowwww\Herd\hrm_payroll_system_design\resources\views/retirements/create.blade.php ENDPATH**/ ?>