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
                    <h5 class="mb-0">Manage Additions for <?php echo e($employee->first_name); ?> <?php echo e($employee->surname); ?></h5>
                </div>
                <div class="card-body">
                    <div class="row">
                         <div class="col-12 mb-3">
                            <a href="<?php echo e(url()->previous()); ?>" class="btn btn-outline-primary">
                                &larr; Back
                            </a>
                        </div>
                        <?php if(count($approvedPayrollMonths) > 0): ?>
                        <div class="col-12 mb-3">
                            <div class="alert alert-info d-flex align-items-center" role="alert">
                                <i class="fas fa-info-circle me-2"></i>
                                <div>
                                    <strong>Note:</strong> Some payroll months are approved. Additions for those months cannot be deleted. You can still add additions for unapproved months.
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        <!-- Addition Form -->
                        <div class="col-lg-6 mb-4">
                            <div class="card border-success shadow">
                                <div class="card-header bg-success text-white">
                                    <strong>Add Addition</strong>
                                </div>
                                <div class="card-body">
                                    <form action="<?php echo e(route('payroll.additions.store', $employee->employee_id)); ?>" method="POST">
                                        <?php echo csrf_field(); ?>
                                        <div class="mb-3">
                                            <label for="addition_type_id" class="form-label">Addition Type</label>
                                            <select name="addition_type_id" id="addition_type_id" class="form-select" required>
                                                <option value="">-- Select --</option>
                                                <?php $__currentLoopData = $additionTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <option value="<?php echo e($type->id); ?>"><?php echo e($type->name); ?></option>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="amount_type" class="form-label">Amount Type</label>
                                            <select name="amount_type" id="amount_type" class="form-select" required>
                                                <option value="fixed">Fixed</option>
                                                <option value="percentage">Percentage</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="amount" class="form-label">Amount</label>
                                            <input type="number" name="amount" id="amount" step="0.01" class="form-control" required>
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
                                        <button type="submit" class="btn btn-success">Add Addition</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <!-- Additions History -->
                        <div class="col-lg-6 mb-4">
                            <div class="card border-info shadow">
                                <div class="card-header bg-info text-white">
                                    <strong>Additions History</strong>
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
                                                <?php $__empty_1 = true; $__currentLoopData = $additions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $addition): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                    <tr>
                                                        <td><?php echo e($addition->addition_type); ?></td>
                                                        <td>₦<?php echo e(number_format($addition->amount, 2)); ?></td>
                                                        <td><?php echo e($addition->addition_period); ?></td>
                                                        <td><?php echo e(\Carbon\Carbon::parse($addition->start_date)->format('M d, Y')); ?></td>
                                                        <td><?php echo e($addition->end_date ? \Carbon\Carbon::parse($addition->end_date)->format('M d, Y') : 'N/A'); ?></td>
                                                        <td>
                                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete_additions')): ?>
                                                                <?php
                                                                    $additionMonth = $addition->start_date ? \Carbon\Carbon::parse($addition->start_date)->format('Y-m') : null;
                                                                    $isMonthLocked = $additionMonth && in_array($additionMonth, $approvedPayrollMonths);
                                                                ?>
                                                                <?php if($isMonthLocked): ?>
                                                                    <button type="button" class="btn btn-sm btn-secondary" disabled title="Cannot delete: Payroll for <?php echo e(\Carbon\Carbon::parse($addition->start_date)->format('F Y')); ?> has been approved">
                                                                        <i class="fas fa-lock"></i>
                                                                    </button>
                                                                <?php else: ?>
                                                                    <form action="<?php echo e(route('payroll.additions.destroy', ['employeeId' => $employee->employee_id, 'additionId' => $addition->addition_id])); ?>" method="POST" onsubmit="return confirm('Are you sure you want to delete this addition?');">
                                                                        <?php echo csrf_field(); ?>
                                                                        <?php echo method_field('DELETE'); ?>
                                                                        <button type="submit" class="btn btn-sm btn-danger">
                                                                            <i class="fas fa-trash"></i>
                                                                        </button>
                                                                    </form>
                                                                <?php endif; ?>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                                    <tr>
                                                        <td colspan="5" class="text-center text-muted">No additions found.</td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <!-- Pagination -->
                                    <div class="card-footer bg-white">
                                        <?php echo e($additions->withQueryString()->links()); ?>

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
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const periodSelect = document.getElementById('period');
        const startDateInput = document.getElementById('start_date');
        const endDateInput = document.getElementById('end_date');
        const endDateContainer = endDateInput.closest('.mb-3');

        // Set default dates (start of month and end of month)
        function setDefaultDates() {
            const today = new Date();
            const year = today.getFullYear();
            const month = String(today.getMonth() + 1).padStart(2, '0'); // Month is 0-indexed

            // Set start date to first day of current month
            const startDate = `${year}-${month}-01`;
            startDateInput.value = startDate;

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

            if (this.value === 'OneTime' && startDateInput.value) {
                // For OneTime, use current date as start date
                const today = new Date().toISOString().split('T')[0];
                startDateInput.value = today;
            }
        });

        // Initialize default dates and visibility
        setDefaultDates();
        toggleEndDate();
    });
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Rowwww\Herd\hrm_payroll_system_design\resources\views/payroll/show_additions.blade.php ENDPATH**/ ?>