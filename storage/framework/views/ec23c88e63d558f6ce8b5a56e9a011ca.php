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
                width: 80px;
                height: auto;
                margin-bottom: 10px;
            }

            .org-name {
                font-size: 16px;
                font-weight: bold;
                color: #333;
                margin-bottom: 5px;
                text-transform: uppercase;
            }

            .report-title {
                font-size: 14px;
                font-weight: bold;
                color: #667eea;
                margin-bottom: 4px;
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
            <img src="data:image/png;base64,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ByAr7UC8v3LuEgcKBskbD/nxprp2lt2A4iSpxW6juf4FFFFFQaaUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUVhlw4k+OuJOjNSGXBsptxIUk+Rpb5BoZZZEg3PFJz9kmg8QDaiWifh1T5UzqKlW969aH7pRH6HzGxqDeadbX4h9APQ8x5EZFJY3bXDB1f9pwBfYLewLjSe0Vw+PLZX4VJWf0hsYkverXuDKtzoPCeJO4B946imvUPesOxbIgRerFDlE/fW0OL/MOdTxf2tx/5LIB6pwfbalR0u/tP/CuCR+FYke+4rWtuoWF3YAwsihqJ6BS+A/jtU61JjPgKZkNuA96VA0trl6PuDy1FdvXNt6uoDbvEkH4KqvSfR/yKG4HbDnTqOE7hLqVJ/5TWfltOe/u3inzH7isfO6yx/fW6V+KVR+RzTuo2G++3WkijS3WGIeKLmzK9h0U+5t+VC8F12G/BlEY7dP1pf8AKtfs1g7XCfzrP21dj6rRf5H96d1eXHWmhxOuoQPFSgKSatN9cJYAfzmMyN99g84fyFCNBcwuKuK/6iPOAncpaStXy4jR9n2qcruB6AmtvtW+Xhu1VPiQKZ9zz3D7QSmfkERCk9UhfER5CqZfPSBxeCS1Zoki4u/dIHAgnz5/hXi3+jrhzC0u3Sfcbgsbb8bgQD8hv+NXiyYLiWOpSLTYorKk/wBoUcSz/iO5rM6YxnvLPsP5rSNcusEpaHqo/wAUpXcm1o1BUWrJaHLXBc5BwDshsf31cz5VNY5oDCDgn5rdHbnIJ4iyhZDf+JR9pX4U3enIUVq5q7gTwW6QhPhv6net2vh5lSg7eLU6odTgeQGK1bba7dZ4qYVrhMxWEDYIaQEitqiilKllZk70/QhLY4UCBRRRRWK2oooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooor/2Q==" class="logo" alt="KSWB Logo" />
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