<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">
    <div class="mb-4">
        <a href="<?php echo e(route('promotions.index')); ?>" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Promotions List
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 text-dark fw-bold">New Promotion/Demotion</h5>
                    <p class="text-muted small mb-0">Manage employee promotions and demotions</p>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <!-- Left Column: Employee Selection & List -->
                        <div class="col-lg-5">
                            <!-- Search and Filter Section -->
                            <div class="card border mb-4">
                                <div class="card-header bg-light py-2">
                                    <h6 class="mb-0 text-dark">Search Employees</h6>
                                </div>
                                <div class="card-body">
                                    <form action="<?php echo e(route('promotions.create')); ?>" method="GET">
                                        <div class="mb-3">
                                            <input type="text" name="search" id="employeeSearch" class="form-control" placeholder="Search by name or staff ID" value="<?php echo e(request('search')); ?>">
                                        </div>
                                        <div class="mb-3">
                                            <select name="department" class="form-select">
                                                <option value="">All Departments</option>
                                                <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $department): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <option value="<?php echo e($department->department_id); ?>" <?php echo e(request('department') == $department->department_id ? 'selected' : ''); ?>>
                                                        <?php echo e($department->department_name); ?>

                                                    </option>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </select>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <button type="submit" class="btn btn-primary flex-fill">
                                                <i class="fas fa-search me-1"></i> Filter
                                            </button>
                                            <a href="<?php echo e(route('promotions.create')); ?>" class="btn btn-outline-secondary">
                                                <i class="fas fa-redo"></i>
                                            </a>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <!-- Employee List -->
                            <div class="card border">
                                <div class="card-header bg-light py-2">
                                    <h6 class="mb-0 text-dark">Active Employees</h6>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                                        <table class="table table-hover mb-0">
                                            <thead class="table-light sticky-top">
                                                <tr>
                                                    <th class="small">Name</th>
                                                    <th class="small">Staff ID</th>
                                                    <th class="small">Department</th>
                                                    <th class="small text-center">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody id="employeesTable">
                                                <?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <tr>
                                                    <td class="small"><?php echo e($employee->first_name); ?> <?php echo e($employee->surname); ?></td>
                                                    <td class="small"><?php echo e($employee->employee_id); ?></td>
                                                    <td class="small"><?php echo e($employee->department->department_name ?? 'N/A'); ?></td>
                                                    <td class="text-center">
                                                        <button type="button" class="btn btn-sm btn-outline-primary select-employee"
                                                                data-id="<?php echo e($employee->employee_id); ?>"
                                                                data-name="<?php echo e($employee->first_name); ?> <?php echo e($employee->surname); ?>"
                                                                data-grade="<?php echo e($employee->gradeLevel->name ?? 'N/A'); ?>"
                                                                data-step="<?php echo e($employee->step->name ?? 'N/A'); ?>"
                                                                data-salary-scale="<?php echo e($employee->gradeLevel->salaryScale->full_name ?? 'N/A'); ?>">
                                                            Select
                                                        </button>
                                                    </td>
                                                </tr>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="card-footer bg-white">
                                    <div class="d-flex justify-content-center">
                                        <?php echo e($employees->appends(request()->query())->links('pagination::bootstrap-4')); ?>

                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Promotion Form -->
                        <div class="col-lg-7">
                            <form action="<?php echo e(route('promotions.store')); ?>" method="POST">
                                <?php echo csrf_field(); ?>

                                <!-- Employee Selection Section -->
                                <div class="card border mb-4">
                                    <div class="card-header bg-primary text-white py-2">
                                        <h6 class="mb-0">1. Employee Information</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label for="employee_id" class="form-label fw-semibold">Selected Employee <span class="text-danger">*</span></label>
                                                <select name="employee_id" id="employee_id" class="form-select" required>
                                                    <option value="">Select an employee</option>
                                                </select>
                                                <?php $__errorArgs = ['employee_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="text-danger small"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                            </div>

                                            <div class="col-md-6">
                                                <label for="promotion_type" class="form-label fw-semibold">Type <span class="text-danger">*</span></label>
                                                <select name="promotion_type" id="promotion_type" class="form-select" required>
                                                    <option value="">Select Type</option>
                                                    <option value="promotion">Promotion</option>
                                                    <option value="demotion">Demotion</option>
                                                </select>
                                                <?php $__errorArgs = ['promotion_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="text-danger small"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Previous Position Section -->
                                <div class="card border mb-4">
                                    <div class="card-header bg-secondary text-white py-2">
                                        <h6 class="mb-0">2. Current Position Details</h6>
                                    </div>
                                    <div class="card-body bg-light">
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label for="previous_salary_scale" class="form-label fw-semibold small">Current Salary Scale</label>
                                                <input type="text" name="previous_salary_scale" id="previous_salary_scale" class="form-control bg-white" readonly>
                                                <?php $__errorArgs = ['previous_salary_scale'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="text-danger small"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                            </div>

                                            <div class="col-md-4">
                                                <label for="previous_grade_level" class="form-label fw-semibold small">Current Grade Level <span class="text-danger">*</span></label>
                                                <input type="text" name="previous_grade_level" id="previous_grade_level" class="form-control bg-white" required readonly>
                                                <?php $__errorArgs = ['previous_grade_level'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="text-danger small"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                            </div>

                                            <div class="col-md-4">
                                                <label for="previous_step" class="form-label fw-semibold small">Current Step</label>
                                                <input type="text" name="previous_step" id="previous_step" class="form-control bg-white" readonly>
                                                <?php $__errorArgs = ['previous_step'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="text-danger small"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- New Position Section -->
                                <div class="card border mb-4">
                                    <div class="card-header bg-success text-white py-2">
                                        <h6 class="mb-0">3. New Position Details</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label for="new_salary_scale" class="form-label fw-semibold">New Salary Scale <span class="text-danger">*</span></label>
                                                <select name="new_salary_scale" id="new_salary_scale" class="form-select" required>
                                                    <option value="">Select Salary Scale</option>
                                                </select>
                                                <?php $__errorArgs = ['new_salary_scale'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="text-danger small"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                            </div>

                                            <div class="col-md-4">
                                                <label for="new_grade_level" class="form-label fw-semibold">New Grade Level <span class="text-danger">*</span></label>
                                                <select name="new_grade_level" id="new_grade_level" class="form-select" required>
                                                    <option value="">Select Grade Level</option>
                                                </select>
                                                <?php $__errorArgs = ['new_grade_level'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="text-danger small"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                            </div>

                                            <div class="col-md-4">
                                                <label for="new_step" class="form-label fw-semibold">New Step</label>
                                                <select name="new_step" id="new_step" class="form-select">
                                                    <option value="">Select Step</option>
                                                </select>
                                                <?php $__errorArgs = ['new_step'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="text-danger small"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Additional Details Section -->
                                <div class="card border mb-4">
                                    <div class="card-header bg-info text-white py-2">
                                        <h6 class="mb-0">4. Additional Details</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label for="promotion_date" class="form-label fw-semibold">Promotion Date <span class="text-danger">*</span></label>
                                                <input type="date" name="promotion_date" id="promotion_date" class="form-control" required>
                                                <?php $__errorArgs = ['promotion_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="text-danger small"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                            </div>

                                            <div class="col-md-6">
                                                <label for="effective_date" class="form-label fw-semibold">Effective Date <span class="text-danger">*</span></label>
                                                <input type="date" name="effective_date" id="effective_date" class="form-control" required>
                                                <?php $__errorArgs = ['effective_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="text-danger small"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                            </div>

                                            <div class="col-12">
                                                <label for="approving_authority" class="form-label fw-semibold">Approving Authority</label>
                                                <input type="text" name="approving_authority" id="approving_authority" class="form-control" placeholder="Enter approving authority name">
                                                <?php $__errorArgs = ['approving_authority'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="text-danger small"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                            </div>

                                            <div class="col-12">
                                                <label for="reason" class="form-label fw-semibold">Reason <span class="text-danger">*</span></label>
                                                <textarea name="reason" id="reason" class="form-control" rows="4" placeholder="Enter the reason for this promotion/demotion" required><?php echo e(old('reason')); ?></textarea>
                                                <?php $__errorArgs = ['reason'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="text-danger small"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="d-flex gap-2 justify-content-end">
                                    <a href="<?php echo e(route('promotions.index')); ?>" class="btn btn-outline-secondary px-4">
                                        <i class="fas fa-times me-1"></i> Cancel
                                    </a>
                                    <button type="submit" class="btn btn-primary px-4">
                                        <i class="fas fa-save me-1"></i> Save Promotion/Demotion
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .card {
        border-radius: 8px;
    }

    .card-header {
        border-radius: 8px 8px 0 0 !important;
    }

    .form-select, .form-control {
        border-radius: 6px;
    }

    .btn {
        border-radius: 6px;
    }

    .table > :not(caption) > * > * {
        padding: 0.5rem;
    }

    @media (max-width: 991px) {
        .col-lg-5, .col-lg-7 {
            margin-bottom: 1rem;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Select employee functionality
        document.querySelectorAll('.select-employee').forEach(button => {
            button.addEventListener('click', function() {
                const employeeId = this.getAttribute('data-id');
                const employeeName = this.getAttribute('data-name');

                // Set the selected employee in the dropdown
                const select = document.getElementById('employee_id');

                // Check if option already exists, if not create it
                let option = select.querySelector(`option[value="${employeeId}"]`);
                if (!option) {
                    option = document.createElement('option');
                    option.value = employeeId;
                    option.textContent = employeeName + ' (' + employeeId + ')';
                    select.appendChild(option);
                }

                // Select the option
                option.selected = true;

                // Fetch and update employee details
                fetchEmployeeDetails(employeeId);

                // Scroll to the form section
                document.querySelector('#employee_id').scrollIntoView({ behavior: 'smooth', block: 'center' });
            });
        });

        // Function to fetch and display employee details
        function fetchEmployeeDetails(employeeId) {
            fetch('/employees/' + employeeId)
                .then(response => response.json())
                .then(response => {
                    const employee = response.data;

                    // Update previous grade level, step, and salary scale fields
                    if (employee.grade_level && employee.grade_level.name) {
                        document.getElementById('previous_grade_level').value = employee.grade_level.name;
                    }

                    if (employee.step && employee.step.name) {
                        document.getElementById('previous_step').value = employee.step.name;
                    }

                    if (employee.grade_level && employee.grade_level.salary_scale && employee.grade_level.salary_scale.full_name) {
                        document.getElementById('previous_salary_scale').value = employee.grade_level.salary_scale.full_name;
                    }
                })
                .catch(error => {
                    console.error('Error fetching employee details:', error);
                    const button = document.querySelector(`.select-employee[data-id="${employeeId}"]`);
                    if (button) {
                        const grade = button.getAttribute('data-grade');
                        const step = button.getAttribute('data-step');
                        const salaryScale = button.getAttribute('data-salary-scale');

                        if (grade && grade !== 'N/A') {
                            document.getElementById('previous_grade_level').value = grade;
                        }

                        if (step && step !== 'N/A') {
                            document.getElementById('previous_step').value = step;
                        }

                        if (salaryScale && salaryScale !== 'N/A') {
                            document.getElementById('previous_salary_scale').value = salaryScale;
                        }
                    }
                });
        }

        // Load salary scales when the page loads
        loadSalaryScales();

        // Load salary scales
        function loadSalaryScales() {
            fetch('/api/salary-scales')
                .then(response => response.json())
                .then(salaryScales => {
                    const salaryScaleSelect = document.getElementById('new_salary_scale');
                    salaryScaleSelect.innerHTML = '<option value="">Select Salary Scale</option>';

                    salaryScales.forEach(salaryScale => {
                        const option = document.createElement('option');
                        option.value = salaryScale.id;
                        option.textContent = salaryScale.full_name;
                        salaryScaleSelect.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Error loading salary scales:', error);
                });
        }

        // When salary scale is selected, load corresponding grade levels
        document.getElementById('new_salary_scale').addEventListener('change', function() {
            const salaryScaleId = this.value;
            const gradeLevelSelect = document.getElementById('new_grade_level');

            gradeLevelSelect.innerHTML = '<option value="">Select Grade Level</option>';
            const stepSelect = document.getElementById('new_step');
            stepSelect.innerHTML = '<option value="">Select Step</option>';

            if (salaryScaleId) {
                fetch(`/api/salary-scales/${salaryScaleId}/grade-levels`)
                    .then(response => response.json())
                    .then(gradeLevels => {
                        gradeLevels.forEach(gradeLevel => {
                            const option = document.createElement('option');
                            option.value = gradeLevel.name;
                            option.textContent = gradeLevel.name;
                            gradeLevelSelect.appendChild(option);
                        });
                    })
                    .catch(error => {
                        console.error('Error loading grade levels:', error);
                    });
            }
        });

        // When grade level is selected, load corresponding steps
        document.getElementById('new_grade_level').addEventListener('change', function() {
            const gradeLevelName = this.value;
            const salaryScaleId = document.getElementById('new_salary_scale').value;
            const stepSelect = document.getElementById('new_step');

            stepSelect.innerHTML = '<option value="">Select Step</option>';

            if (gradeLevelName && salaryScaleId) {
                fetch(`/api/salary-scales/${salaryScaleId}/grade-levels/${gradeLevelName}/steps`)
                    .then(response => response.json())
                    .then(steps => {
                        steps.forEach(step => {
                            const option = document.createElement('option');
                            option.value = step.name;
                            option.textContent = step.name;
                            stepSelect.appendChild(option);
                        });
                    })
                    .catch(error => {
                        console.error('Error loading steps:', error);
                    });
            }
        });
    });
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Rowwww\Herd\hrm_payroll_system_design\resources\views/promotions/create.blade.php ENDPATH**/ ?>