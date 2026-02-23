<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Promotion History Report</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 10mm;
        }
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            font-size: 10px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .report-title {
            font-size: 11px;
            color: #dc2626;
            font-weight: bold;
            margin-bottom: 1px;
        }
        .generated-date {
            font-size: 10px;
            color: #666;
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
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .summary {
            background-color: #f9f9f9;
            padding: 10px;
            margin-bottom: 20px;
            border-left: 4px solid #007bff;
        }
    </style>
</head>
<body>
    <div class="header">
        <?php if(file_exists(public_path('images/WhatsApp Image 2026-01-22 at 10.28.01 AM.jpeg'))): ?>
            <img src="<?php echo e(public_path('images/WhatsApp Image 2026-01-22 at 10.28.01 AM.jpeg')); ?>" alt="Logo" class="logo">
        <?php endif; ?>
        <div class="org-name">KATSINA STATE WATER BOARD</div>
        <div class="report-title">PROMOTION HISTORY REPORT</div>
        <div class="generated-date">Generated on: <?php echo e(now()->format('F j, Y g:i A')); ?></div>
    </div>

    <div class="summary">
        <p><strong>Total Promotions:</strong> <?php echo e($data['total_promotions']); ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Staff No</th>
                <th>Employee Name</th>
                <th>Department</th>
                <th>Previous Grade</th>
                <th>Previous Step</th>
                <th>New Grade</th>
                <th>New Step</th>
                <th>Promotion Date</th>
                <th>Promotion Type</th>
                <th>Reason</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $data['promotions']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $promotion): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><?php echo e($promotion['employee_id']); ?></td>
                <td><?php echo e($promotion['employee_name']); ?></td>
                <td><?php echo e($promotion['department']); ?></td>
                <td><?php echo e($promotion['previous_grade']); ?></td>
                <td><?php echo e($promotion['previous_step']); ?></td>
                <td><?php echo e($promotion['new_grade']); ?></td>
                <td><?php echo e($promotion['new_step']); ?></td>
                <td><?php echo e($promotion['promotion_date']); ?></td>
                <td><?php echo e($promotion['promotion_type']); ?></td>
                <td><?php echo e($promotion['reason']); ?></td>
                <td><?php echo e($promotion['status']); ?></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
</body>
</html><?php /**PATH C:\Users\Rowwww\Herd\hrm_payroll_system_design\resources\views/reports/new/pdf/promotion-history-report.blade.php ENDPATH**/ ?>