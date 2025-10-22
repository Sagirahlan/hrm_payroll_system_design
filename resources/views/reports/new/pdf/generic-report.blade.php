<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $reportType }}</title>
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
        <div class="report-title">{{ $reportType }}</div>
        <div class="generated-date">Generated on: {{ now()->format('F j, Y g:i A') }}</div>
    </div>

    @if(isset($data['total_employees']))
    <div class="summary">
        <strong>Total Employees:</strong> {{ $data['total_employees'] }}
    </div>
    @endif

    @if(isset($data['employees']))
    <table>
        <thead>
            <tr>
                <th>Employee ID</th>
                <th>Full Name</th>
                <th>Department</th>
                <th>Grade Level</th>
                <th>Step</th>
                <th>Status</th>
                <th>Basic Salary</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['employees'] as $employee)
            <tr>
                <td>{{ $employee['employee_id'] }}</td>
                <td>{{ $employee['full_name'] }}</td>
                <td>{{ $employee['department'] }}</td>
                <td>{{ $employee['grade_level'] }}</td>
                <td>{{ $employee['step'] }}</td>
                <td>{{ $employee['status'] }}</td>
                <td>₦{{ number_format($employee['basic_salary'], 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    @if(isset($data['payroll_records']))
    <table>
        <thead>
            <tr>
                <th>Employee ID</th>
                <th>Full Name</th>
                <th>Department</th>
                <th>Basic Salary</th>
                <th>Net Salary</th>
                <th>Payment Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['payroll_records'] as $record)
            <tr>
                <td>{{ $record['employee_id'] }}</td>
                <td>{{ $record['full_name'] }}</td>
                <td>{{ $record['department'] }}</td>
                <td>₦{{ number_format($record['basic_salary'], 2) }}</td>
                <td>₦{{ number_format($record['net_salary'], 2) }}</td>
                <td>{{ $record['payment_date'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    @if(isset($data['actions']))
    <table>
        <thead>
            <tr>
                <th>Employee ID</th>
                <th>Employee Name</th>
                <th>Department</th>
                <th>Action Type</th>
                <th>Action Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['actions'] as $action)
            <tr>
                <td>{{ $action['employee_id'] }}</td>
                <td>{{ $action['employee_name'] }}</td>
                <td>{{ $action['department'] }}</td>
                <td>{{ $action['action_type'] }}</td>
                <td>{{ $action['action_date'] }}</td>
                <td>{{ $action['status'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</body>
</html>