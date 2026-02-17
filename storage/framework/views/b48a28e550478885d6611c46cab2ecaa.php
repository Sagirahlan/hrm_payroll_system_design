<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payroll Summary Report</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 8mm;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 7px;
            line-height: 1.2;
            background: white;
        }

        .header {
            text-align: center;
            margin-bottom: 10px;
        }

        .logo {
            width: 60px;
            height: 60px;
            margin: 0 auto 8px;
            display: block;
        }

        .org-name {
            font-size: 14px;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 2px;
        }

        .report-title {
            font-size: 11px;
            color: #dc2626;
            font-weight: bold;
            margin-bottom: 1px;
        }

        .report-subtitle {
            font-size: 10px;
            color: #dc2626;
            font-weight: bold;
        }

        .bank-section {
            margin-bottom: 12px;
            page-break-inside: avoid;
        }

        .bank-header {
            background-color: #f3f4f6;
            padding: 4px 8px;
            font-weight: bold;
            font-size: 8px;
            margin-bottom: 3px;
            border: 1px solid #000;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        th, td {
            border: 1px solid #000;
            padding: 2px 1px;
            text-align: center;
            font-size: 6px;
        }

        th {
            background-color: #e5e7eb;
            font-weight: bold;
            white-space: nowrap;
        }

        .text-left {
            text-align: left;
        }

        .text-right {
            text-align: right;
        }

        .bank-total {
            font-weight: bold;
            font-style: italic;
            background-color: #e5e7eb;
        }

        .grand-total {
            font-weight: bold;
            background-color: #d1d5db;
            font-size: 7px;
        }

        .summary-box {
            margin: 8px 0;
            padding: 8px;
            background: #f9fafb;
            border: 1px solid #ddd;
            font-size: 8px;
        }

        .summary-box p {
            margin: 2px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <?php if(file_exists(public_path('images/WhatsApp Image 2026-01-22 at 10.28.01 AM.jpeg'))): ?>
            <img src="<?php echo e(public_path('images/WhatsApp Image 2026-01-22 at 10.28.01 AM.jpeg')); ?>" alt="Logo" class="logo">
        <?php endif; ?>
        <div class="org-name">KATSINA STATE WATER BOARD</div>
        <div class="report-title"><?php echo e(strtoupper($data['period'] ?? now()->format('F Y'))); ?> SALARY.</div>
        <?php
            $paymentTypeLabel = 'ALL STAFF';
            if (!empty($data['payment_type'])) {
                $paymentTypeLabel = match($data['payment_type']) {
                    'Regular' => 'REGULAR SALARY STAFF',
                    'Permanent' => 'PERMANENT STAFF',
                    'Casual' => 'CASUAL STAFF',
                    'Pension' => 'PENSIONERS',
                    'Gratuity' => 'GRATUITY',
                    default => strtoupper($data['payment_type']) . ' STAFF'
                };
            }
        ?>
        <div class="report-subtitle"><?php echo e($paymentTypeLabel); ?></div>
    </div>

    <?php
        // Group payroll records by bank
        $groupedByBank = collect($data['payroll_records'])->groupBy(function($record) {
            return $record['bank_name'] ?? 'NO BANK';
        });
        
        // 1. Collect all unique deduction types from ALL records to ensure consistent columns across banks
        $allDeductionTypes = [];
        foreach ($data['payroll_records'] as $record) {
            if (!empty($record['deduction_breakdown'])) {
                foreach (array_keys($record['deduction_breakdown']) as $type) {
                    $allDeductionTypes[$type] = true;
                }
            }
        }
        $sortedDeductionTypes = array_keys($allDeductionTypes);
        sort($sortedDeductionTypes); // Sort alphabetically or define a specific order if needed

        // Initialize Grand Total with dynamic deduction keys
        $grandTotal = [
            'basic' => 0,
            'gross' => 0,
            'net' => 0,
            'count' => 0,
            'total_deductions_sum' => 0 // Sum of "Total Deduction" column
        ];
        // Initialize deduction-specific grand totals
        foreach ($sortedDeductionTypes as $type) {
            $grandTotal['deductions'][$type] = 0;
        }
    ?>

    <?php $__currentLoopData = $groupedByBank; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bankName => $records): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="bank-section">
        <div class="bank-header">BANK: <?php echo e(strtoupper($bankName)); ?></div>
        
        <table>
            <thead>
                <tr>
                    <th>S/N</th>
                    <th>STAFF NO</th>
                    <th>STAFF NAME</th>
                    <th>GRADE LEVEL</th>
                    <th>BASIC PAY</th>
                    <th>GROSS PAY</th>
                    
                    
                    <?php $__currentLoopData = $sortedDeductionTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <th><?php echo e(strtoupper($type)); ?></th>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                    <th>TOTAL DEDUCTION</th>
                    <th>NET PAY</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    $sn = 1;
                    $bankTotal = [
                        'basic' => 0,
                        'gross' => 0,
                        'net' => 0,
                        'total_deductions_sum' => 0
                    ];
                    // Initialize deduction-specific bank totals
                    foreach ($sortedDeductionTypes as $type) {
                        $bankTotal['deductions'][$type] = 0;
                    }
                ?>
                
                <?php $__currentLoopData = $records; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $record): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $basicSalary = $record['basic_salary'] ?? 0;
                    $grossPay = $basicSalary + ($record['total_additions'] ?? 0);
                    $totalDeductionsForRecord = $record['total_deductions'] ?? 0;
                    
                    $bankTotal['basic'] += $basicSalary;
                    $bankTotal['gross'] += $grossPay;
                    $bankTotal['total_deductions_sum'] += $totalDeductionsForRecord;
                    $bankTotal['net'] += $record['net_salary'] ?? 0;
                ?>
                <tr>
                    <td><?php echo e($sn++); ?></td>
                    <td><?php echo e($record['employee_id']); ?></td>
                    <td class="text-left"><?php echo e($record['full_name']); ?></td>
                    <td><?php echo e($record['grade_level'] ?? 'N/A'); ?></td>
                    <td class="text-right"><?php echo e(number_format($basicSalary, 2)); ?></td>
                    <td class="text-right"><?php echo e(number_format($grossPay, 2)); ?></td>
                    
                    
                    <?php $__currentLoopData = $sortedDeductionTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $amount = $record['deduction_breakdown'][$type] ?? 0;
                            $bankTotal['deductions'][$type] += $amount;
                        ?>
                        <td class="text-right"><?php echo e(number_format($amount, 2)); ?></td>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                    <td class="text-right"><?php echo e(number_format($totalDeductionsForRecord, 2)); ?></td>
                    <td class="text-right"><?php echo e(number_format($record['net_salary'] ?? 0, 2)); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                
                
                <tr class="bank-total">
                    <td colspan="4" class="text-right">BANK TOTAL</td>
                    <td class="text-right"><?php echo e(number_format($bankTotal['basic'], 2)); ?></td>
                    <td class="text-right"><?php echo e(number_format($bankTotal['gross'], 2)); ?></td>
                    
                    <?php $__currentLoopData = $sortedDeductionTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <td class="text-right"><?php echo e(number_format($bankTotal['deductions'][$type], 2)); ?></td>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                    <td class="text-right"><?php echo e(number_format($bankTotal['total_deductions_sum'], 2)); ?></td>
                    <td class="text-right"><?php echo e(number_format($bankTotal['net'], 2)); ?></td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <?php
        $grandTotal['basic'] += $bankTotal['basic'];
        $grandTotal['gross'] += $bankTotal['gross'];
        $grandTotal['net'] += $bankTotal['net'];
        $grandTotal['count'] += count($records);
        $grandTotal['total_deductions_sum'] += $bankTotal['total_deductions_sum'];
        
        foreach ($sortedDeductionTypes as $type) {
            $grandTotal['deductions'][$type] += $bankTotal['deductions'][$type];
        }
    ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    
    <table>
        <tr class="grand-total">
            <td colspan="4" class="text-right"><strong>GRAND TOTAL</strong></td>
            <td class="text-right"><strong><?php echo e(number_format($grandTotal['basic'], 2)); ?></strong></td>
            <td class="text-right"><strong><?php echo e(number_format($grandTotal['gross'], 2)); ?></strong></td>
            
            <?php $__currentLoopData = $sortedDeductionTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <td class="text-right"><strong><?php echo e(number_format($grandTotal['deductions'][$type], 2)); ?></strong></td>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            <td class="text-right"><strong><?php echo e(number_format($grandTotal['total_deductions_sum'], 2)); ?></strong></td>
            <td class="text-right"><strong><?php echo e(number_format($grandTotal['net'], 2)); ?></strong></td>
        </tr>
    </table>

    <div class="summary-box">
        <p><strong>Period:</strong> <?php echo e($data['period']); ?></p>
        <p><strong>Total Records:</strong> <?php echo e($grandTotal['count']); ?></p>
        <p><strong>Total Basic Salary:</strong> ₦<?php echo e(number_format($grandTotal['basic'], 2)); ?></p>
        <p><strong>Total Net Salary:</strong> ₦<?php echo e(number_format($grandTotal['net'], 2)); ?></p>
        <p><strong>Generated on:</strong> <?php echo e(now()->format('F j, Y g:i A')); ?></p>
    </div>
</body>
</html><?php /**PATH C:\Users\Rowwww\Herd\hrm_payroll_system_design\resources\views/reports/new/pdf/payroll-summary-report.blade.php ENDPATH**/ ?>