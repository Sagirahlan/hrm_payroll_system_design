<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payroll Analysis Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .report-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .generated-date {
            font-size: 12px;
            color: #666;
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
        <div class="report-title">Payroll Analysis Report</div>
        <div class="generated-date">Generated on: {{ now()->format('F j, Y g:i A') }}</div>
    </div>

    <div class="summary">
        <p><strong>Period:</strong> {{ $data['period'] }}</p>
        <p><strong>Total Records:</strong> {{ $data['total_records'] }}</p>
        <p><strong>Total Basic Salary:</strong> ₦{{ number_format($data['total_basic_salary'], 2) }}</p>
        <p><strong>Total Deductions:</strong> ₦{{ number_format($data['total_deductions'], 2) }}</p>
        <p><strong>Total Additions:</strong> ₦{{ number_format($data['total_additions'], 2) }}</p>
        <p><strong>Total Net Salary:</strong> ₦{{ number_format($data['total_net_salary'], 2) }}</p>
        <p><strong>Average Basic Salary:</strong> ₦{{ number_format($data['average_basic_salary'], 2) }}</p>
        <p><strong>Average Net Salary:</strong> ₦{{ number_format($data['average_net_salary'], 2) }}</p>
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