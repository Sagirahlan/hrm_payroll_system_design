<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">
    <div class="mb-3">
        <a href="<?php echo e(route('payroll.index')); ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Payroll
        </a>
    </div>
    
    <div class="row">
        <div class="col-12">
            <div class="card border-primary shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Manage Deductions for <?php echo e($employee->first_name); ?> <?php echo e($employee->surname); ?></h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12 mb-3">
                            <a href="<?php echo e(url()->previous()); ?>" class="btn btn-outline-primary">
                                &larr; Back
                            </a>
                        </div>
                        <!-- Deduction Form -->
                        <div class="col-lg-6 mb-4">
                            <div class="card border-danger shadow">
                                <div class="card-header bg-danger text-white">
                                    <strong>Add Deduction</strong>
                                </div>
                                <div class="card-body">
                                    <form action="<?php echo e(route('payroll.deductions.store', $employee->employee_id)); ?>" method="POST">
                                        <?php echo csrf_field(); ?>
                                        <div class="mb-3">
                                            <label for="deduction_type_id" class="form-label">Deduction Type</label>
                                            <select name="deduction_type_id" id="deduction_type_id" class="form-select" required>
                                                <option value="">-- Select Non-Statutory Deduction --</option>
                                                <?php $__currentLoopData = $deductionTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <?php if(!$type->is_statutory): ?>
                                                        <option value="<?php echo e($type->id); ?>"
                                                                data-calculation-type="<?php echo e($type->calculation_type); ?>"
                                                                data-rate-or-amount="<?php echo e($type->rate_or_amount); ?>">
                                                            <?php echo e($type->name); ?>

                                                        </option>
                                                    <?php endif; ?>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </select>
                                        </div>
                                        <div class="mb-3" id="amount_type_section" style="display: none;">
                                            <label for="amount_type" class="form-label">Amount Type</label>
                                            <select name="amount_type" id="amount_type" class="form-select">
                                                <option value="fixed">Fixed</option>
                                                <option value="percentage">Percentage</option>
                                            </select>
                                        </div>
                                        <div class="mb-3" id="amount_section" style="display: none;">
                                            <label for="amount" class="form-label">Amount/Percentage</label>
                                            <input type="number" name="amount" id="amount" step="0.01" class="form-control">
                                        </div>
                                        <div class="mb-3">
                                            <label for="period" class="form-label">Period</label>
                                            <select name="period" id="period" class="form-select" required>
                                                <option value="OneTime">One-Time</option>
                                                <option value="Monthly">Monthly</option>
                                                <option value="Perpetual">Perpetual</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="start_date" class="form-label">Start Date</label>
                                            <input type="date" name="start_date" id="start_date" class="form-control" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="end_date" class="form-label">End Date</label>
                                            <input type="date" name="end_date" id="end_date" class="form-control" required>
                                        </div>
                                        <button type="submit" class="btn btn-danger">Add Deduction</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <!-- Deductions History -->
                        <div class="col-lg-6 mb-4">
                            <div class="card border-info shadow">
                                <div class="card-header bg-info text-white">
                                    <strong>Deductions History</strong>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Type</th>
                                                    <th>Amount</th>
                                                    <th>Period</th>
                                                    <th>Start Date</th>
                                                    <th>Start Date</th>
                                                    <th>End Date</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $__empty_1 = true; $__currentLoopData = $deductions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $deduction): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                    <tr>
                                                        <td><?php echo e($deduction->deduction_type); ?></td>
                                                        <td>₦<?php echo e(number_format($deduction->amount, 2)); ?></td>
                                                        <td><?php echo e($deduction->deduction_period); ?></td>
                                                        <td><?php echo e(\Carbon\Carbon::parse($deduction->start_date)->format('M d, Y')); ?></td>
                                                        <td><?php echo e($deduction->end_date ? \Carbon\Carbon::parse($deduction->end_date)->format('M d, Y') : 'N/A'); ?></td>
                                                        <td>
                                                            <form action="<?php echo e(route('payroll.deductions.destroy', ['employeeId' => $employee->employee_id, 'deductionId' => $deduction->deduction_id])); ?>" method="POST" onsubmit="return confirm('Are you sure you want to delete this deduction?');">
                                                                <?php echo csrf_field(); ?>
                                                                <?php echo method_field('DELETE'); ?>
                                                                <button type="submit" class="btn btn-sm btn-danger">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                                    <tr>
                                                        <td colspan="5" class="text-center text-muted">No deductions found.</td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <!-- Pagination -->
                                    <div class="card-footer bg-white">
                                        <?php echo e($deductions->withQueryString()->links()); ?>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const deductionTypeSelect = document.getElementById('deduction_type_id');
    const amountTypeSection = document.getElementById('amount_type_section');
    const amountSection = document.getElementById('amount_section');
    const amountTypeSelect = document.getElementById('amount_type');
    const amountInput = document.getElementById('amount');
    const periodSelect = document.getElementById('period');
    const endDateInput = document.getElementById('end_date');
    const endDateContainer = endDateInput.closest('.mb-3');

    // Set default dates (start of month and end of month)
    function setDefaultDates() {
        const today = new Date();
        const year = today.getFullYear();
        const month = String(today.getMonth() + 1).padStart(2, '0'); // Month is 0-indexed

        // Set start date to first day of current month
        const startDate = `${year}-${month}-01`;
        document.getElementById('start_date').value = startDate;

        // Calculate last day of current month
        const lastDay = new Date(year, today.getMonth() + 1, 0).getDate();
        const endDate = `${year}-${month}-${String(lastDay).padStart(2, '0')}`;
        endDateInput.value = endDate;
    }

    function toggleEndDate() {
        // End date is always required and visible now
        endDateContainer.style.display = 'block';
        endDateInput.required = true;
    }

    periodSelect.addEventListener('change', function () {
        toggleEndDate();

        if (this.value === 'OneTime' && document.getElementById('start_date').value) {
            // For OneTime, use current date as start date
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('start_date').value = today;
        }
    });

    deductionTypeSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const isStatutory = selectedOption.getAttribute('data-is-statutory') === '1';
        const calculationType = selectedOption.getAttribute('data-calculation-type');
        const rateOrAmount = selectedOption.getAttribute('data-rate-or-amount');

        if (isStatutory) {
            // Hide amount fields for statutory deductions
            amountTypeSection.style.display = 'none';
            amountSection.style.display = 'none';
        } else {
            // Show amount fields for non-statutory deductions
            amountTypeSection.style.display = 'block';
            amountSection.style.display = 'block';

            // Set default values based on deduction type
            if (calculationType === 'percentage') {
                amountTypeSelect.value = 'percentage';
                amountInput.placeholder = 'Enter percentage (e.g., 2.5 for 2.5%)';
            } else {
                amountTypeSelect.value = 'fixed';
                amountInput.placeholder = 'Enter fixed amount';
            }

            // Pre-fill with rate_or_amount if available
            if (rateOrAmount) {
                amountInput.value = rateOrAmount;
            }
        }
    });

    // Initialize default dates and visibility
    setDefaultDates();
    toggleEndDate();
});
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Rowwww\Herd\hrm_payroll_system_design\resources\views/payroll/show_deductions.blade.php ENDPATH**/ ?>