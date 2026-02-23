<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Addition Summary Report</title>
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
            font-size: 9px;
            line-height: 1.2;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
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

        .generated-date {
            color: #666;
            font-size: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th, td {
            border: 1px solid #000;
            padding: 4px;
            text-align: center;
        }

        th {
            background-color: #f0f0f0;
            font-weight: bold;
            white-space: nowrap;
        }

        .text-left { text-align: left; }
        .text-right { text-align: right; }
        
        .totals-row {
            font-weight: bold;
            background-color: #e0e0e0;
        }
    </style>
</head>
<body>
    <div class="header">
        <?php if(file_exists(public_path('images/WhatsApp Image 2026-01-22 at 10.28.01 AM.jpeg'))): ?>
            <img src="<?php echo e(public_path('images/WhatsApp Image 2026-01-22 at 10.28.01 AM.jpeg')); ?>" alt="Logo" class="logo">
        <?php endif; ?>
        <div class="org-name">KATSINA STATE WATER BOARD</div>
        <div class="report-title">ADDITION SUMMARY REPORT</div>
        <div class="report-subtitle">Generated on: <?php echo e(now()->format('F j, Y g:i A')); ?></div>
        <div>Total Additions: ₦<?php echo e(number_format($data['total_amount'], 2)); ?></div>
    </div>

    <table>
        <thead>
            <tr>
                <th>S/N</th>
                <th>Staff No</th>
                <th>Employee Name</th>
                <th>Department</th>
                
                <?php $__currentLoopData = $data['addition_types']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <th><?php echo e($type); ?></th>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php
                $sn = 1;
                $columnTotals = array_fill_keys($data['addition_types'], 0);
                $grandTotal = 0;
            ?>
            
            <?php $__currentLoopData = $data['employees']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><?php echo e($sn++); ?></td>
                <td><?php echo e($employee['employee_id']); ?></td>
                <td class="text-left"><?php echo e($employee['employee_name']); ?></td>
                <td><?php echo e($employee['department']); ?></td>
                
                <?php $__currentLoopData = $data['addition_types']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $amount = $employee['additions'][$type] ?? 0;
                        $columnTotals[$type] += $amount;
                    ?>
                    <td class="text-right"><?php echo e($amount > 0 ? number_format($amount, 2) : '-'); ?></td>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                <td class="text-right"><strong><?php echo e(number_format($employee['total_additions'], 2)); ?></strong></td>
                <?php $grandTotal += $employee['total_additions']; ?>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            <tr class="totals-row">
                <td colspan="4" class="text-right"><strong>TOTALS</strong></td>
                
                <?php $__currentLoopData = $data['addition_types']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <td class="text-right"><?php echo e(number_format($columnTotals[$type], 2)); ?></td>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                <td class="text-right"><?php echo e(number_format($grandTotal, 2)); ?></td>
            </tr>
        </tbody>
    </table>
</body>
</html><?php /**PATH C:\Users\Rowwww\Herd\hrm_payroll_system_design\resources\views/reports/new/pdf/addition-summary-report.blade.php ENDPATH**/ ?>