<?php $__env->startSection('title', 'Request Leave'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Request Leave</h4>
                </div>
                <div class="card-body">
                    <form action="<?php echo e(route('leaves.store')); ?>" method="POST">
                        <?php echo csrf_field(); ?>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="employee_id" class="form-label">Employee</label>
                                    <input type="text" id="employee_search" class="form-control <?php $__errorArgs = ['employee_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" placeholder="Search employee...">
                                    <input type="hidden" name="employee_id" id="employee_id" value="<?php echo e(old('employee_id')); ?>">
                                    <div id="employee_list" class="list-group mt-2" style="max-height: 200px; overflow-y: auto; display: none;">
                                        <?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <a href="#" class="list-group-item list-group-item-action employee-item"
                                               data-id="<?php echo e($employee->employee_id); ?>"
                                               data-name="<?php echo e($employee->first_name); ?> <?php echo e($employee->surname); ?> (<?php echo e($employee->staff_no); ?>)">
                                                <?php echo e($employee->first_name); ?> <?php echo e($employee->surname); ?> (<?php echo e($employee->staff_no); ?>)
                                            </a>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                    <?php $__errorArgs = ['employee_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="leave_type" class="form-label">Leave Type</label>
                                    <select name="leave_type" id="leave_type" class="form-select <?php $__errorArgs = ['leave_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required>
                                        <option value="">Select Leave Type</option>
                                        <option value="Annual" <?php echo e(old('leave_type') == 'Annual' ? 'selected' : ''); ?>>Annual Leave</option>
                                        <option value="Sick" <?php echo e(old('leave_type') == 'Sick' ? 'selected' : ''); ?>>Sick Leave</option>
                                        <option value="Maternity" <?php echo e(old('leave_type') == 'Maternity' ? 'selected' : ''); ?>>Maternity Leave</option>
                                        <option value="Paternity" <?php echo e(old('leave_type') == 'Paternity' ? 'selected' : ''); ?>>Paternity Leave</option>
                                        <option value="Emergency" <?php echo e(old('leave_type') == 'Emergency' ? 'selected' : ''); ?>>Emergency Leave</option>
                                        <option value="Study" <?php echo e(old('leave_type') == 'Study' ? 'selected' : ''); ?>>Study Leave</option>
                                        <option value="Other" <?php echo e(old('leave_type') == 'Other' ? 'selected' : ''); ?>>Other</option>
                                    </select>
                                    <?php $__errorArgs = ['leave_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="start_date" class="form-label">Start Date</label>
                                    <input type="date" name="start_date" id="start_date" class="form-control <?php $__errorArgs = ['start_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" value="<?php echo e(old('start_date')); ?>" required>
                                    <?php $__errorArgs = ['start_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="end_date" class="form-label">End Date</label>
                                    <input type="date" name="end_date" id="end_date" class="form-control <?php $__errorArgs = ['end_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" value="<?php echo e(old('end_date')); ?>" required>
                                    <?php $__errorArgs = ['end_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="reason" class="form-label">Reason</label>
                                    <textarea name="reason" id="reason" class="form-control <?php $__errorArgs = ['reason'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" rows="4" placeholder="Enter reason for leave request"><?php echo e(old('reason')); ?></textarea>
                                    <?php $__errorArgs = ['reason'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">Submit Leave Request</button>
                            <a href="<?php echo e(route('leaves.index')); ?>" class="btn btn-secondary ms-2">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    const employeeSearch = document.getElementById('employee_search');
    const employeeList = document.getElementById('employee_list');
    const employeeSelect = document.getElementById('employee_id');
    const employeeItems = document.querySelectorAll('.employee-item');
    const form = document.querySelector('form');

    startDateInput.addEventListener('change', function() {
        endDateInput.min = this.value;
    });

    // Set min date for start date to today
    startDateInput.min = new Date().toISOString().split('T')[0];

    // Employee search functionality
    employeeSearch.addEventListener('keyup', function() {
        const searchTerm = this.value.toLowerCase();
        let hasResults = false;

        employeeItems.forEach(item => {
            const text = item.textContent.toLowerCase();
            if (text.includes(searchTerm)) {
                item.style.display = 'block';
                hasResults = true;
            } else {
                item.style.display = 'none';
            }
        });

        if (searchTerm.length > 0 && hasResults) {
            employeeList.style.display = 'block';
        } else {
            employeeList.style.display = 'none';
        }
    });

    // Select employee from search results
    employeeItems.forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const employeeId = this.getAttribute('data-id');
            const employeeName = this.getAttribute('data-name');

            employeeSearch.value = employeeName;
            employeeSelect.value = employeeId;
            employeeList.style.display = 'none';
        });
    });

    // Hide list when clicking outside
    document.addEventListener('click', function(e) {
        if (!employeeSearch.contains(e.target) && !employeeList.contains(e.target)) {
            employeeList.style.display = 'none';
        }
    });

    // Form validation to ensure employee is selected
    form.addEventListener('submit', function(e) {
        if (!employeeSelect.value) {
            e.preventDefault();
            employeeSearch.classList.add('is-invalid');
            alert('Please select an employee from the search results.');
        } else {
            employeeSearch.classList.remove('is-invalid');
        }
    });
});
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Rowwww\Herd\hrm_payroll_system_design\resources\views/leaves/create.blade.php ENDPATH**/ ?>