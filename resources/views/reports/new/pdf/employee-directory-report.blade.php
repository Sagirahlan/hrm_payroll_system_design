<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Employee Directory Report</title>
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
        <div class="report-title">Employee Directory Report</div>
        <div class="generated-date">Generated on: {{ now()->format('F j, Y g:i A') }}</div>
    </div>

    <div class="summary">
        <strong>Total Employees:</strong> {{ $data['total_employees'] }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Employee ID</th>
                <th>Full Name</th>
                <th>Department</th>
                <th>Grade Level</th>
                <th>Step</th>
                <th>Status</th>
                <th>Email</th>
                <th>Mobile No</th>
                <th>Extension</th>
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
                <td>{{ $employee['email'] }}</td>
                <td>{{ $employee['mobile_no'] }}</td>
                <td>{{ $employee['extension'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>