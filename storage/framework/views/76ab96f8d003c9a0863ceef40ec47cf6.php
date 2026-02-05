<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Loan Details</h4>
                    <a href="<?php echo e(route('loans.index')); ?>" class="btn btn-secondary">Back to Loans</a>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Loan Information</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Loan ID:</strong></td>
                                    <td><?php echo e($loan->loan_id); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Staff No:</strong></td>
                                    <td><?php echo e($loan->employee->staff_no); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Employee:</strong></td>
                                    <td><?php echo e($loan->employee->first_name); ?> <?php echo e($loan->employee->last_name); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Loan Type:</strong></td>
                                    <td><?php echo e($loan->deductionType->name ?? $loan->loan_type); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Description:</strong></td>
                                    <td><?php echo e($loan->description ?? 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        <span class="badge bg-<?php echo e($loan->status === 'active' ? 'primary' : ($loan->status === 'completed' ? 'success' : 'warning')); ?>">
                                            <?php echo e(ucfirst($loan->status)); ?>

                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5>Financial Information</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Principal Amount:</strong></td>
                                    <td><?php echo e(number_format($loan->principal_amount, 2)); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Interest Rate:</strong></td>
                                    <td><?php echo e($loan->interest_rate ? $loan->interest_rate . '%' : '0.00%'); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Total Interest:</strong></td>
                                    <td><?php echo e(number_format($loan->total_interest, 2)); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Total Repayment:</strong></td>
                                    <td><?php echo e(number_format($loan->total_repayment, 2)); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Monthly Deduction:</strong></td>
                                    <td><?php echo e(number_format($loan->monthly_deduction, 2)); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Monthly Percentage:</strong></td>
                                    <td><?php echo e($loan->monthly_percentage ? $loan->monthly_percentage . '%' : 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Total Months:</strong></td>
                                    <td><?php echo e($loan->total_months); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Remaining Months:</strong></td>
                                    <td><?php echo e($loan->remaining_months); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Months Completed:</strong></td>
                                    <td><?php echo e($loan->months_completed); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Total Repaid:</strong></td>
                                    <td><?php echo e(number_format($loan->total_repaid, 2)); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Remaining Balance:</strong></td>
                                    <td><?php echo e(number_format($loan->remaining_balance, 2)); ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <h5>Dates</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Deduction Start Month:</strong></td>
                                    <td><?php echo e($loan->deduction_start_month ? \Carbon\Carbon::parse($loan->deduction_start_month . '-01')->format('F Y') : 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>End Date:</strong></td>
                                    <td><?php echo e($loan->end_date ? $loan->end_date->format('Y-m-d') : 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Calculated End Date:</strong></td>
                                    <td><?php echo e($loan->calculateEndDate()->format('Y-m-d')); ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="mt-3">
                        <a href="<?php echo e(route('loans.index')); ?>" class="btn btn-secondary">Back to List</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Rowwww\Herd\hrm_payroll_system_design\resources\views/loans/show.blade.php ENDPATH**/ ?>