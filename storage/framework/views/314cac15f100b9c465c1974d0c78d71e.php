<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payslip PDF</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            margin: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            page-break-inside: auto;
        }
        th, td {
            border: 1px solid #333;
            padding: 4px;
            text-align: left;
        }
        th {
            background: #eee;
            font-size: 10px;
        }
        .header-table td {
            border: none;
        }
        .payslip-header {
            text-align: center;
            margin-bottom: 10px;
        }
        .company-info {
            text-align: center;
            margin-bottom: 10px;
            font-weight: bold;
            font-size: 14px;
        }
        .section-header {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .total-row {
            font-weight: bold;
            background-color: #f8f9fa;
        }
        .employee-details {
            margin-bottom: 10px;
        }
        .employee-details table {
            margin-bottom: 5px;
        }
        .employee-details th, .employee-details td {
            border: none;
            padding: 3px;
        }
        h4 {
            margin: 8px 0 5px 0;
            font-size: 11px;
        }
        .text-right {
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="company-info">
        <h2>HRM PAYROLL SYSTEM</h2>
        <h3>Pay Slip</h3>
    </div>

    <!-- Basic Payroll Information -->
    <table class="header-table">
        <tr>
            <td><strong>Pay Period:</strong></td>
            <td><?php echo e($payroll->payroll_month ? \Carbon\Carbon::parse($payroll->payroll_month)->format('F Y') : 'N/A'); ?></td>
            <td><strong>Payroll Date:</strong></td>
            <td><?php echo e($payroll->payment_date ? $payroll->payment_date->format('M d, Y') : 'Pending'); ?></td>
        </tr>
        <tr>
            <td><strong>Generated:</strong></td>
            <td><?php echo e($payroll->created_at->format('M d, Y H:i')); ?></td>
            <td></td>
            <td></td>
        </tr>
    </table>

    <!-- Employee Information -->
    <div class="employee-details">
        <table class="header-table">
            <tr class="section-header">
                <th colspan="4">Employee Information</th>
            </tr>
            <tr>
                <td><strong>Employee Name:</strong></td>
                <td><?php echo e($payroll->employee->first_name); ?> <?php echo e($payroll->employee->middle_name ?? ''); ?> <?php echo e($payroll->employee->surname); ?></td>
                <td><strong>Staff No:</strong></td>
                <td><?php echo e($payroll->employee->staff_no ?? 'N/A'); ?></td>
            </tr>
            <tr>
                <td><strong>Department:</strong></td>
                <td><?php echo e($payroll->employee->department->department_name ?? 'N/A'); ?></td>
                <td><strong>Appointment Type:</strong></td>
                <td><?php echo e($payroll->employee->appointmentType->name ?? 'N/A'); ?></td>
            </tr>
            <tr>
                <td><strong>Grade Level/Step:</strong></td>
                <td><?php echo e($payroll->employee->gradeLevel->name ?? 'N/A'); ?> / <?php echo e($payroll->employee->step->name ?? 'N/A'); ?></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td><strong>Bank:</strong></td>
                <td><?php echo e($payroll->employee->bank->bank_name ?? 'N/A'); ?></td>
                <td><strong>Account No:</strong></td>
                <td><?php echo e($payroll->employee->bank->account_no ?? 'N/A'); ?></td>
            </tr>
        </table>
    </div>

    <!-- Salary Breakdown -->
    <table>
        <thead>
            <tr class="section-header">
                <th>Salary Component</th>
                <th>Amount (₦)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Basic Salary</strong></td>
                <td class="text-right">₦<?php echo e(number_format($payroll->basic_salary, 2)); ?></td>
            </tr>
            <tr>
                <td><strong>Total Additions</strong></td>
                <td class="text-right">₦<?php echo e(number_format($payroll->total_additions, 2)); ?></td>
            </tr>
            <tr>
                <td><strong>Total Deductions</strong></td>
                <td class="text-right">₦<?php echo e(number_format($payroll->total_deductions, 2)); ?></td>
            </tr>
            <tr class="total-row">
                <td><strong>Net Salary</strong></td>
                <td class="text-right"><strong>₦<?php echo e(number_format($payroll->net_salary, 2)); ?></strong></td>
            </tr>
        </tbody>
    </table>

    <!-- Deductions Section -->
    <h4>Deductions</h4>
    <?php if($deductions->count() > 0 || ($payroll->payment_type == 'Gratuity' && $payroll->total_deductions > 0)): ?>
        <table>
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Amount (₦)</th>
                    <th>Period</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $deductions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $deduction): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e(optional($deduction->deductionType)->name ?? $deduction->deduction_type); ?></td>
                        <td>₦<?php echo e(number_format($deduction->amount, 2)); ?></td>
                        <td><?php echo e($deduction->deduction_period); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                
                <?php if($payroll->payment_type == 'Gratuity' && $payroll->total_deductions > 0): ?>
                    <tr>
                        <td>Overstay Deduction</td>
                        <td>₦<?php echo e(number_format($payroll->total_deductions, 2)); ?></td>
                        <td>ONE-OFF</td>
                    </tr>
                <?php endif; ?>

                <tr class="total-row">
                    <td><strong>Total Deductions</strong></td>
                    
                    <td><strong>₦<?php echo e(number_format($payroll->total_deductions, 2)); ?></strong></td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    <?php else: ?>
        <p>No deductions for this period.</p>
    <?php endif; ?>

    <!-- Additions Section -->
    <h4>Additions</h4>
    <?php if($additions->count() > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Amount (₦)</th>
                    <th>Period</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $additions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $addition): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e(optional($addition->additionType)->name ?? 'N/A'); ?></td>
                        <td>₦<?php echo e(number_format($addition->amount, 2)); ?></td>
                        <td><?php echo e($addition->addition_period); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <tr class="total-row">
                    <td><strong>Total Additions</strong></td>
                    <td><strong>₦<?php echo e(number_format($additions->sum('amount'), 2)); ?></strong></td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    <?php else: ?>
        <p>No additions for this period.</p>
    <?php endif; ?>

    <div style="margin-top: 15px; text-align: right; font-size: 9px;">
        <p><em>Generated on: <?php echo e(now('Africa/Lagos')->format('M d, Y H:i')); ?></em></p>
        <p><em>Powered by HRM Payroll System</em></p>
    </div>
</body>
</html><?php /**PATH C:\Users\Rowwww\Herd\hrm_payroll_system_design\resources\views/payroll/payslip.blade.php ENDPATH**/ ?>