<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payroll Summary Report</title>
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
                margin-bottom: 12px;
                border-bottom: 2px solid #667eea;
                padding-bottom: 10px;
            }

            .report-title {
                font-size: 14px;
                font-weight: bold;
                color: #333;
                margin-bottom: 4px;
            }

            .generated-date {
                font-size: 9px;
                color: #666;
                margin-bottom: 2px;
                line-height: 1.3;
            }

            .summary-info {
                font-size: 9px;
                color: #667eea;
                font-weight: 600;
                margin-top: 4px;
            }

            .content {
                flex: 1;
                overflow: hidden;
                display: flex;
                flex-direction: column;
            }

            .table-wrapper {
                flex: 1;
                overflow: hidden;
                border: 1px solid #ddd;
                border-radius: 4px;
            }

            table {
                width: 100%;
                border-collapse: collapse;
                font-size: 8px;
                line-height: 1.2;
            }

            thead {
                background-color: #f0f0f0;
                position: sticky;
                top: 0;
            }

            th {
                border: 1px solid #ddd;
                padding: 4px 3px;
                text-align: left;
                font-weight: bold;
                color: #333;
                white-space: nowrap;
            }

            td {
                border: 1px solid #ddd;
                padding: 3px;
                text-align: left;
            }

            tr:nth-child(even) {
                background-color: #f9f9f9;
            }

            tbody tr:hover {
                background-color: #f0f7ff;
            }

            .employee-name {
                font-weight: 600;
                color: #333;
            }

            .amount {
                text-align: right;
                font-weight: 600;
                color: #667eea;
            }

            .status-badge {
                display: inline-block;
                padding: 2px 5px;
                border-radius: 3px;
                font-size: 7px;
                font-weight: bold;
                text-transform: uppercase;
                background: #e8f4f8;
                color: #333;
            }

            .empty-state {
                text-align: center;
                padding: 40px 20px;
                color: #999;
                font-size: 11px;
            }

            .footer {
                border-top: 1px solid #ddd;
                padding-top: 6px;
                margin-top: 8px;
                font-size: 8px;
                color: #666;
                text-align: right;
            }

            @media (max-width: 800px) {
                .page {
                    width: 100%;
                    height: auto;
                    margin: 5px 0;
                    padding: 10px;
                }
            }
    </style>
</head>
<body>
    <div class="header">
        <div class="report-title">Payroll Summary Report</div>
        <div class="generated-date">Generated on: {{ now()->format('F j, Y g:i A') }}</div>
    </div>

    <div class="summary">
        <p><strong>Period:</strong> {{ $data['period'] }}</p>
        <p><strong>Total Records:</strong> {{ $data['total_records'] }}</p>
        <p><strong>Total Basic Salary:</strong> ₦{{ number_format($data['total_basic_salary'], 2) }}</p>
        <p><strong>Total Deductions:</strong> ₦{{ number_format($data['total_deductions'], 2) }}</p>
        <p><strong>Total Additions:</strong> ₦{{ number_format($data['total_additions'], 2) }}</p>
        <p><strong>Total Net Salary:</strong> ₦{{ number_format($data['total_net_salary'], 2) }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Employee ID</th>
                <th>Full Name</th>
                <th>Department</th>
                <th>Grade Level</th>
                <th>Basic Salary</th>
                <th>Total Deductions</th>
                <th>Total Additions</th>
                <th>Net Salary</th>
                <th>Payment Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['payroll_records'] as $record)
            <tr>
                <td>{{ $record['employee_id'] }}</td>
                <td>{{ $record['full_name'] }}</td>
                <td>{{ $record['department'] }}</td>
                <td>{{ $record['grade_level'] }}</td>
                <td>₦{{ number_format($record['basic_salary'], 2) }}</td>
                <td>₦{{ number_format($record['total_deductions'], 2) }}</td>
                <td>₦{{ number_format($record['total_additions'], 2) }}</td>
                <td>₦{{ number_format($record['net_salary'], 2) }}</td>
                <td>{{ $record['payment_date'] }}</td>
                <td>{{ $record['status'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>