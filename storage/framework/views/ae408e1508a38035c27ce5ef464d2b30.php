<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4>Loans Management</h4>
                        
                        <a href="<?php echo e(route('loans.create')); ?>" class="btn btn-primary">Add New Loan</a>
                        
                    </div>
                </div>

                <div class="card-body">
                    <?php if(session('success')): ?>
                        <div class="alert alert-success">
                            <?php echo e(session('success')); ?>

                        </div>
                    <?php endif; ?>

                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Staff No</th>
                                    <th>Full Name</th>
                                    <th>Loan Type</th>
                                    <th>Principal Amount</th>
                                    <th>Total Repayment</th>
                                    <th>Monthly Deduction</th>
                                    <th>Monthly Percentage</th>
                                    <th>Deduction Start Month</th>
                                    <th>End Date</th>
                                    <th>Remaining Balance</th>
                                    <th>Remaining Months</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $loans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $loan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><?php echo e($loan->loan_id); ?></td>
                                        <td><?php echo e($loan->employee->staff_no); ?></td>
                                        <td><?php echo e($loan->employee->first_name); ?> <?php echo e($loan->employee->surname); ?></td>
                                        <td><?php echo e($loan->deductionType->name ?? $loan->loan_type); ?></td>
                                        <td><?php echo e(number_format($loan->principal_amount, 2)); ?></td>
                                        <td><?php echo e(number_format($loan->total_repayment, 2)); ?></td>
                                        <td><?php echo e(number_format($loan->monthly_deduction, 2)); ?></td>
                                        <td><?php echo e($loan->monthly_percentage ? $loan->monthly_percentage . '%' : 'N/A'); ?></td>
                                        <td><?php echo e($loan->deduction_start_month ? \Carbon\Carbon::parse($loan->deduction_start_month . '-01')->format('F Y') : 'N/A'); ?></td>
                                        <td><?php echo e($loan->end_date ? $loan->end_date->format('Y-m-d') : 'N/A'); ?></td>
                                        <td><?php echo e(number_format($loan->remaining_balance, 2)); ?></td>
                                        <td><?php echo e($loan->remaining_months); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo e($loan->status === 'active' ? 'primary' : ($loan->status === 'completed' ? 'success' : 'warning')); ?>">
                                                <?php echo e(ucfirst($loan->status)); ?>

                                            </span>
                                        </td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                    Actions
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" href="<?php echo e(route('loans.show', $loan->loan_id)); ?>">View</a></li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <form action="<?php echo e(route('loans.destroy', $loan->loan_id)); ?>" method="POST" style="display: inline;">
                                                            <?php echo csrf_field(); ?>
                                                            <?php echo method_field('DELETE'); ?>
                                                            <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Are you sure you want to delete this loan?')">Delete</button>
                                                        </form>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="14" class="text-center">No loans found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center">
                        <?php echo e($loans->links()); ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Rowwww\Herd\hrm_payroll_system_design\resources\views/loans/index.blade.php ENDPATH**/ ?>