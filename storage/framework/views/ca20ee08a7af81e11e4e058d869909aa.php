<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payroll Journal Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .logo {
            width: 80px;
            height: auto;
            margin-bottom: 10px;
        }
        .org-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .report-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .period {
            font-size: 14px;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .grand-total {
            font-weight: bold;
            font-size: 14px;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <!-- Assuming logo path is standard, or we can omit if not available -->
        <!-- <img src="<?php echo e(public_path('images/logo.png')); ?>" class="logo" alt="Logo"> -->
        <div class="org-name">Katsina State Water Board</div>
        <div class="report-title">Payroll Journals</div>
        <div class="period">For the Month of <?php echo e($data['period']); ?></div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Code</th>
                <th>Description</th>
                <th class="text-center">Count</th>
                <th class="text-right">Amount</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $data['journal_items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><?php echo e($item['code']); ?></td>
                <td><?php echo e($item['description']); ?></td>
                <td class="text-center"><?php echo e($item['count']); ?></td>
                <td class="text-right">0.00</td> <!-- The image shows 0 in one column and amount in another, or vice versa depending on credit/debit. For simplicity, I'll put 0 in one and Total in another if it's a summary -->
                <!-- Actually, the image shows "304" (Count), "0" (Amount?), "2,358,726" (Total). 
                     It seems "Amount" column might be unit amount or something, but here we only have totals. 
                     I'll put 0 for now or remove the column if not needed, but to match image I'll keep it. -->
                <td class="text-right"><?php echo e(number_format($item['amount'], 2)); ?></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <tr class="grand-total">
                <td colspan="4" class="text-right">Grand Total:</td>
                <td class="text-right"><?php echo e(number_format($data['grand_total'], 2)); ?></td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        Generated on: <?php echo e($data['generated_date']); ?>

    </div>
</body>
</html>
<?php /**PATH C:\Users\Rowwww\Herd\hrm_payroll_system_design\resources\views/reports/new/pdf/payroll-journal-report.blade.php ENDPATH**/ ?>