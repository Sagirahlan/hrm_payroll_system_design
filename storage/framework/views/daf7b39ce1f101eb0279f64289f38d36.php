<!DOCTYPE html>
<html>
<head>
    <title><?php echo e($reportType); ?></title>
    <style>
        @page {
            margin: 10mm;
            size: landscape;
        }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 8px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8px;
        }
        th, td {
            border: 1px solid #000;
            padding: 3px;
            text-align: center;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h2, .header h3 {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Katsina State Water Board</h2>
        <h3><?php echo e($reportType); ?></h3>
        <p>Period: <?php echo e($data['period']); ?> | Generated: <?php echo e($data['generated_date']); ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="3%">S/N</th>
                <th width="5%">Staff No</th>
                <th width="12%">Name</th>
                <th width="8%">Rank</th>
                <th width="8%">Basic Salary</th>
                
                <?php $__currentLoopData = $data['addition_types']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <th><?php echo e($type); ?></th>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <th>Total Additions</th>
                <th>Gross Salary</th>

                <?php $__currentLoopData = $data['deduction_types']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <th><?php echo e($type); ?></th>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <th>Total Deductions</th>
                <th>Net Salary</th>
            </tr>
        </thead>
        <tbody>
            <?php $sn = 1; ?>
            <?php $__currentLoopData = $data['payroll_records']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $record): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($sn++); ?></td>
                    <td><?php echo e($record['staff_no']); ?></td>
                    <td class="text-left"><?php echo e($record['name']); ?></td>
                    <td class="text-left"><?php echo e($record['rank']); ?></td>
                    <td class="text-right"><?php echo e(number_format($record['basic_salary'], 2)); ?></td>

                    
                    <?php $__currentLoopData = $data['addition_types']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <td class="text-right"><?php echo e(number_format($record['additions'][$type] ?? 0, 2)); ?></td>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <td class="text-right"><?php echo e(number_format($record['total_additions'], 2)); ?></td>
                    <td class="text-right"><?php echo e(number_format($record['gross_salary'], 2)); ?></td>

                    
                    <?php $__currentLoopData = $data['deduction_types']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <td class="text-right"><?php echo e(number_format($record['deductions'][$type] ?? 0, 2)); ?></td>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <td class="text-right"><?php echo e(number_format($record['total_deductions'], 2)); ?></td>
                    <td class="text-right"><strong><?php echo e(number_format($record['net_salary'], 2)); ?></strong></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            
            
            <tr style="background-color: #f9f9f9; font-weight: bold;">
                <td colspan="4" class="text-right">Totals</td>
                <td class="text-right"><?php echo e(number_format(collect($data['payroll_records'])->sum('basic_salary'), 2)); ?></td>

                <?php $__currentLoopData = $data['addition_types']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <td class="text-right">
                        <?php echo e(number_format(collect($data['payroll_records'])->sum(function($rec) use ($type) { return $rec['additions'][$type] ?? 0; }), 2)); ?>

                    </td>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <td class="text-right"><?php echo e(number_format(collect($data['payroll_records'])->sum('total_additions'), 2)); ?></td>
                <td class="text-right"><?php echo e(number_format(collect($data['payroll_records'])->sum('gross_salary'), 2)); ?></td>

                <?php $__currentLoopData = $data['deduction_types']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <td class="text-right">
                        <?php echo e(number_format(collect($data['payroll_records'])->sum(function($rec) use ($type) { return $rec['deductions'][$type] ?? 0; }), 2)); ?>

                    </td>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <td class="text-right"><?php echo e(number_format(collect($data['payroll_records'])->sum('total_deductions'), 2)); ?></td>
                <td class="text-right"><?php echo e(number_format(collect($data['payroll_records'])->sum('net_salary'), 2)); ?></td>
            </tr>
        </tbody>
    </table>
</body>
</html>
<?php /**PATH C:\Users\Rowwww\Herd\hrm_payroll_system_design\resources\views/reports/new/pdf/full-payroll-report.blade.php ENDPATH**/ ?>