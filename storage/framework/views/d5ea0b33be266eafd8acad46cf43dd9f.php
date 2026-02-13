

<?php $__env->startSection('title', 'Payment Transactions Report'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Payment Transactions Report</h3>
                    <div class="card-tools d-flex align-items-center">
                        <a href="<?php echo e(route('reports.index')); ?>" class="btn btn-sm btn-secondary mr-2">
                            <i class="fas fa-arrow-left"></i> Back to Reports
                        </a>
                        <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <form action="<?php echo e(route('reports.payment-transactions')); ?>" method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="payment_month">Select Month</label>
                                    <input type="month" name="payment_month" id="payment_month" class="form-control" value="<?php echo e(request('payment_month', now()->format('Y-m'))); ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="status">Status</label>
                                    <select name="status" id="status" class="form-control">
                                        <option value="">All Statuses</option>
                                        <option value="pending" <?php echo e(request('status') == 'pending' ? 'selected' : ''); ?>>Pending</option>
                                        <option value="successful" <?php echo e(request('status') == 'successful' ? 'selected' : ''); ?>>Successful</option>
                                        <option value="failed" <?php echo e(request('status') == 'failed' ? 'selected' : ''); ?>>Failed</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="bank_code">Bank</label>
                                    <select name="bank_code" id="bank_code" class="form-control">
                                        <option value="">All Banks</option>
                                        <?php $__currentLoopData = $banks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bank): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($bank->bank_code); ?>" <?php echo e(request('bank_code') == $bank->bank_code ? 'selected' : ''); ?>>
                                                <?php echo e($bank->bank_name); ?>

                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="department_id">Department</label>
                                    <select name="department_id" id="department_id" class="form-control">
                                        <option value="">All Departments</option>
                                        <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($dept->department_id); ?>" <?php echo e(request('department_id') == $dept->department_id ? 'selected' : ''); ?>>
                                                <?php echo e($dept->department_name); ?>

                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="appointment_type_id">Appointment Type</label>
                                    <select name="appointment_type_id" id="appointment_type_id" class="form-control">
                                        <option value="">All Types</option>
                                        <?php $__currentLoopData = $appointmentTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($type->id); ?>" <?php echo e(request('appointment_type_id') == $type->id ? 'selected' : ''); ?>>
                                                <?php echo e($type->name); ?>

                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="search">Search Employee</label>
                                    <input type="text" name="search" id="search" class="form-control" placeholder="Name or Staff ID" value="<?php echo e(request('search')); ?>">
                                </div>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-12 text-right">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter"></i> Filter
                                </button>
                                <a href="<?php echo e(route('reports.payment-transactions')); ?>" class="btn btn-secondary">
                                    <i class="fas fa-undo"></i> Reset
                                </a>
                                <a href="<?php echo e(route('reports.payment-transactions.export', request()->all())); ?>" class="btn btn-success">
                                    <i class="fas fa-file-csv"></i> Export CSV
                                </a>
                                <button type="button" class="btn btn-info" onclick="window.print()">
                                    <i class="fas fa-print"></i> Print
                                </button>
                            </div>
                        </div>
                    </form>

                    <div class="text-center mb-4">
                        <img src="<?php echo e(asset('images/WhatsApp Image 2026-01-22 at 10.28.01 AM.jpeg')); ?>" alt="Logo" style="width: 80px; height: 80px;" class="mb-2">
                        <h3 class="font-weight-bold text-uppercase mb-1" style="color: #000;">KATSINA STATE WATER BOARD</h3>
                        <h5 class="font-weight-bold text-uppercase mb-1" style="color: #000;">
                            <?php echo e(\Carbon\Carbon::parse(request('payment_month', now()))->format('F Y')); ?> SALARY
                        </h5>
                        <h5 class="font-weight-bold text-uppercase" style="color: #000;">
                            <?php
                                $typeId = request('appointment_type_id');
                                $typeName = $typeId ? $appointmentTypes->firstWhere('id', $typeId)->name : 'ALL STAFF';
                            ?>
                            <?php echo e($typeName); ?> PAYMENT SCHEDULE
                        </h5>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Employee</th>
                                    <th>Staff ID</th>
                                    <th>Payroll Month</th>
                                    <th>Amount</th>
                                    <th>Bank</th>
                                    <th>Account Name</th>
                                    <th>Account Number</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $transactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $transaction): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><?php echo e($transaction->payment_date ? \Carbon\Carbon::parse($transaction->payment_date)->format('Y-m-d') : 'N/A'); ?></td>
                                        <td>
                                            <?php if($transaction->employee): ?>
                                                <?php echo e($transaction->employee->first_name); ?> <?php echo e($transaction->employee->surname); ?>

                                            <?php else: ?>
                                                <span class="text-muted">Unknown Employee</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo e($transaction->employee->staff_no ?? $transaction->employee_id); ?></td>
                                        <td>
                                            <?php if($transaction->payroll && $transaction->payroll->payroll_month): ?>
                                                <?php echo e($transaction->payroll->payroll_month->format('M Y')); ?>

                                            <?php else: ?>
                                                N/A
                                            <?php endif; ?>
                                        </td>
                                        <td>₦<?php echo e(number_format($transaction->amount, 2)); ?></td>
                                        <td><?php echo e($transaction->bank_code); ?></td>
                                        <td><?php echo e($transaction->account_name); ?></td>
                                        <td><?php echo e($transaction->account_number); ?></td>
                                        <td>
                                            <?php if($transaction->status == 'successful'): ?>
                                                <span class="badge badge-success">Successful</span>
                                            <?php elseif($transaction->status == 'pending'): ?>
                                                <span class="badge badge-warning">Pending</span>
                                            <?php elseif($transaction->status == 'failed'): ?>
                                                <span class="badge badge-danger">Failed</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary"><?php echo e(ucfirst($transaction->status)); ?></span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="8" class="text-center">No payment transactions found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        <?php echo e($transactions->links()); ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Rowwww\Herd\hrm_payroll_system_design\resources\views/reports/new/payment_transactions.blade.php ENDPATH**/ ?>