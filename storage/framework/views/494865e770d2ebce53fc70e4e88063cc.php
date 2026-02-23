<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Pensioner Report with Bank Details</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        @media print {
            body {
                margin: 0;
                padding: 0;
            }
            .page {
                margin: 0;
                padding: 0;
                page-break-after: always;
            }
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            padding: 10px;
        }

        .page {
            width: 297mm;
            min-height: 210mm;
            margin: 10px auto;
            padding: 10mm;
            background: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            position: relative;
        }

        .page::before {
            content: "KATSINA STATE WATER BOARD";
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 60px;
            color: rgba(30, 64, 175, 0.03);
            font-weight: bold;
            white-space: nowrap;
            z-index: 0;
            pointer-events: none;
        }

        .logo {
            width: 80px;
            height: auto;
            margin-bottom: 8px;
            pointer-events: none;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #1e40af;
            padding-bottom: 8px;
            position: relative;
            z-index: 1;
        }

        .org-name {
            font-size: 16px;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 4px;
            letter-spacing: 1px;
        }

        .report-title {
            font-size: 18px;
            font-weight: bold;
            color: #333;
            margin-bottom: 4px;
        }

        .generated-date {
            font-size: 10px;
            color: #666;
            margin-bottom: 5px;
        }

        .summary {
            background-color: #e8f4f8;
            padding: 10px;
            margin-bottom: 15px;
            border-left: 4px solid #1e40af;
            position: relative;
            z-index: 1;
            font-size: 10px;
        }

        .summary p {
            margin-bottom: 4px;
        }

        .content {
            flex: 1;
            overflow: hidden;
            position: relative;
            z-index: 1;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            font-size: 7.5px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 4px 3px;
            text-align: left;
        }

        th {
            background-color: #1e40af;
            color: white;
            font-weight: bold;
            font-size: 7px;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .text-right {
            text-align: right;
        }

        .footer {
            border-top: 1px solid #ddd;
            padding-top: 8px;
            margin-top: auto;
            font-size: 8px;
            color: #888;
            text-align: center;
            position: relative;
            z-index: 1;
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="header">
            <?php if(file_exists(public_path('images/WhatsApp Image 2026-01-22 at 10.28.01 AM.jpeg'))): ?>
                <img src="<?php echo e(public_path('images/WhatsApp Image 2026-01-22 at 10.28.01 AM.jpeg')); ?>" alt="Logo" class="logo">
            <?php endif; ?>
            <div class="org-name">KATSINA STATE WATER BOARD</div>
            <div class="report-title"><?php echo e($data['report_title'] ?? 'Pensioner Report with Bank Details'); ?></div>
            <div class="generated-date">Generated on: <?php echo e(now()->format('F j, Y g:i A')); ?></div>
        </div>

        <div class="summary">
            <p><strong>Total Pensioners:</strong> <?php echo e($data['total_pensioners']); ?></p>
            <p><strong>Total Pension Amount:</strong> ₦<?php echo e(number_format($data['total_pension_amount'], 2)); ?></p>
            <p><strong>Total Gratuity Amount:</strong> ₦<?php echo e(number_format($data['total_gratuity_amount'], 2)); ?></p>
        </div>

        <div class="content">
            <table>
                <thead>
                    <tr>
                        <th>S/N</th>
                        <th>Full Name</th>
                        <th>Department</th>
                        <th>Rank</th>
                        <th>GL/Step</th>
                        <th>Retirement Date</th>
                        <th>Pension Amt</th>
                        <th>Gratuity Amt</th>
                        <th>Bank Name</th>
                        <th>Account No</th>
                        <th>Account Name</th>
                        <th>Phone</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $data['pensioners']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $pensioner): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($index + 1); ?></td>
                        <td><?php echo e($pensioner['full_name']); ?></td>
                        <td><?php echo e($pensioner['department']); ?></td>
                        <td><?php echo e($pensioner['rank']); ?></td>
                        <td><?php echo e($pensioner['grade_level']); ?>/<?php echo e($pensioner['step']); ?></td>
                        <td><?php echo e($pensioner['date_of_retirement']); ?></td>
                        <td class="text-right">₦<?php echo e(number_format($pensioner['pension_amount'], 2)); ?></td>
                        <td class="text-right">₦<?php echo e(number_format($pensioner['gratuity_amount'], 2)); ?></td>
                        <td><?php echo e($pensioner['bank_name']); ?></td>
                        <td><?php echo e($pensioner['account_number']); ?></td>
                        <td><?php echo e($pensioner['account_name']); ?></td>
                        <td><?php echo e($pensioner['phone_number']); ?></td>
                        <td><?php echo e($pensioner['status']); ?></td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>

        <div class="footer">
            Katsina State Water Board - HR & Payroll Management System<br>
            Pensioner Report with Bank Details | Generated on <?php echo e(now()->format('F j, Y g:i A')); ?>.
        </div>
    </div>
</body>
</html>
<?php /**PATH C:\Users\Rowwww\Herd\hrm_payroll_system_design\resources\views/reports/new/pdf/pensioner-report.blade.php ENDPATH**/ ?>