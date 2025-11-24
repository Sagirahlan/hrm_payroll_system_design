<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Retirement Planning Report</title>
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
        <div class="report-title">{{ $data['report_title'] ?? 'Retirement Planning Report' }}</div>
        <div class="generated-date">Generated on: {{ now()->format('F j, Y g:i A') }}</div>
    </div>

    <div class="summary">
        <p><strong>Total Approaching Retirement:</strong> {{ $data['total_approaching_retirement'] }}</p>
        <p><strong>Period:</strong> {{ $data['retirement_period_label'] ?? 'N/A' }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Employee ID</th>
                <th>Full Name</th>
                <th>Calculated Retirement Date</th>
                <th>Expected Date of Retirement</th>
                <th>Years of Service</th>
                <th>Age</th>
                <th>Retirement Reason</th>
                <th>Months to Retirement</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['employees_approaching_retirement'] as $employee)
            <tr>
                <td>{{ $employee['employee_id'] }}</td>
                <td>{{ $employee['full_name'] }}</td>
                <td>{{ $employee['calculated_retirement_date'] ?? $employee['expected_retirement_date'] }}</td>
                <td>{{ $employee['expected_retirement_date'] }}</td>
                <td>{{ number_format($employee['years_of_service'], 1) ?? '0' }} years</td>
                <td>{{ $employee['age'] }}</td>
                <td>{{ $employee['retirement_reason'] ?? 'N/A' }}</td>
                <td>{{ $employee['months_to_retirement'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>