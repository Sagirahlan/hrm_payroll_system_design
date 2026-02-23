<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?php echo e($reportType); ?></title>
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
                width: 210mm;
                height: 297mm;
                margin: 10px auto;
                padding: 15mm;
                background: white;
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                overflow: hidden;
                display: flex;
                flex-direction: column;
            }

            .header {
                text-align: center;
                margin-bottom: 20px;
                border-bottom: 2px solid #667eea;
                padding-bottom: 10px;
            }

            .logo {
                width: 55px;
                height: 55px;
                margin: 0 auto 6px;
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
                font-weight: bold;
                color: #dc2626;
                margin-bottom: 1px;
            }

            .generated-date {
                font-size: 9px;
                color: #666;
                margin-bottom: 2px;
                line-height: 1.3;
            }

            .section-title {
                font-size: 12px;
                font-weight: bold;
                color: #333;
                margin: 15px 0 5px 0;
                padding-bottom: 5px;
                border-bottom: 1px solid #ddd;
            }

            .table-container {
                margin-bottom: 20px;
            }

            table {
                width: 100%;
                border-collapse: collapse;
                font-size: 8px;
                line-height: 1.2;
            }

            thead {
                background-color: #f0f0f0;
            }

            th {
                border: 1px solid #ddd;
                padding: 6px 4px;
                text-align: left;
                font-weight: bold;
                color: #333;
            }

            td {
                border: 1px solid #ddd;
                padding: 4px;
                text-align: left;
                vertical-align: top;
            }

            tr:nth-child(even) {
                background-color: #f9f9f9;
            }
            
            .group-spacer {
                border-top: 2px solid #667eea;
            }

            .empty-state {
                text-align: center;
                padding: 20px;
                color: #777;
                font-style: italic;
                font-size: 10px;
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
            <div class="report-title"><?php echo e($reportType); ?></div>
            <div class="generated-date">Generated on: <?php echo e(now()->format('F j, Y g:i A')); ?></div>
        </div>

    <!-- Duplicate Accounts Section -->
    <div class="section-title">DUPLICATE BANK ACCOUNTS</div>
    <div class="summary-info">Total Groups Found: <?php echo e($data['total_duplicate_account_groups']); ?></div>
    
    <?php if(empty($data['duplicate_accounts'])): ?>
        <div class="empty-state">No duplicate bank accounts found.</div>
    <?php else: ?>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Account Number</th>
                        <th>Bank</th>
                        <th>Beneficiary Name</th>
                        <th>Type</th>
                        <th>ID/Staff No</th>
                        <th>Department</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $data['duplicate_accounts']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php $__currentLoopData = $group; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $beneficiary): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr class="<?php echo e($index === 0 ? 'group-spacer' : ''); ?>">
                            <td><?php echo e($beneficiary['account_number']); ?></td>
                            <td><?php echo e($beneficiary['bank_name']); ?></td>
                            <td class="employee-name"><?php echo e($beneficiary['name']); ?></td>
                            <td><?php echo e($beneficiary['type']); ?></td>
                            <td><?php echo e($beneficiary['id']); ?></td>
                            <td><?php echo e($beneficiary['department']); ?></td>
                            <td><span class="status-badge"><?php echo e($beneficiary['status']); ?></span></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <!-- Duplicate NINs Section -->
    <div class="section-title" style="margin-top: 30px;">DUPLICATE NINs</div>
    <div class="summary-info">Total Groups Found: <?php echo e($data['total_duplicate_nin_groups']); ?></div>

    <?php if(empty($data['duplicate_nins'])): ?>
        <div class="empty-state">No duplicate NINs found.</div>
    <?php else: ?>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>NIN</th>
                        <th>Beneficiary Name</th>
                        <th>Type</th>
                        <th>ID/Staff No</th>
                        <th>Department</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $data['duplicate_nins']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php $__currentLoopData = $group; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $beneficiary): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr class="<?php echo e($index === 0 ? 'group-spacer' : ''); ?>">
                            <td><?php echo e($beneficiary['nin']); ?></td>
                            <td class="employee-name"><?php echo e($beneficiary['name']); ?></td>
                            <td><?php echo e($beneficiary['type']); ?></td>
                            <td><?php echo e($beneficiary['id']); ?></td>
                            <td><?php echo e($beneficiary['department']); ?></td>
                            <td><span class="status-badge"><?php echo e($beneficiary['status']); ?></span></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <div class="footer">
        Page 1
    </div>
</body>
</html>
<?php /**PATH C:\Users\Rowwww\Herd\hrm_payroll_system_design\resources\views/reports/new/pdf/duplicate-beneficiary-report.blade.php ENDPATH**/ ?>