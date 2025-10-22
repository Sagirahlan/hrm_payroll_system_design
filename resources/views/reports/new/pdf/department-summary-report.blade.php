<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Department Summary Report</title>
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
        <div class="report-title">Department Summary Report</div>
        <div class="generated-date">Generated on: {{ now()->format('F j, Y g:i A') }}</div>
    </div>

    <div class="summary">
        <p><strong>Total Departments:</strong> {{ $data['total_departments'] }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Department Name</th>
                <th>Total Employees</th>
                <th>Active</th>
                <th>Suspended</th>
                <th>Retired</th>
                <th>Deceased</th>
                <th>Total Basic Salary</th>
                <th>Total Contract Amount</th>
                <th>Avg Years of Service</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['departments'] as $dept)
            <tr>
                <td>{{ $dept['department_name'] }}</td>
                <td>{{ $dept['total_employees'] }}</td>
                <td>{{ $dept['active_employees'] }}</td>
                <td>{{ $dept['suspended_employees'] }}</td>
                <td>{{ $dept['retired_employees'] }}</td>
                <td>{{ $dept['deceased_employees'] }}</td>
                <td>₦{{ number_format($dept['total_basic_salary'], 2) }}</td>
                <td>₦{{ number_format($dept['total_contract_amount'], 2) }}</td>
                <td>{{ number_format($dept['average_years_of_service'], 1) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>