re design this; <!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Retired Employees Summary Report</title>
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
            font-size: 14px;
            color: #666;
        }
        .summary-info {
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
            font-size: 12px;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="report-title">Retired Employees Summary Report</div>
        <div class="generated-date">Generated on: {{ $data['generated_date'] }}</div>
        <div class="summary-info">Total Retired Employees: {{ $data['total_retired_employees'] }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Staff ID</th>
                <th>Name</th>
                <th>Date of Birth</th>
                <th>Age</th>
                <th>Years of Service</th>
                <th>Rank</th>
                <th>Grade Level/Step</th>
                <th>Department</th>
                <th>Retirement Date</th>
                <th>Retire Reason</th>
                
            </tr>
        </thead>
        <tbody>
            @forelse($data['employees'] as $employee)
                <tr>
                    <td>{{ $employee['employee_id'] }}</td>
                    <td>{{ $employee['name'] }}</td>
                    <td>{{ $employee['date_of_birth'] }}</td>
                    <td>{{ $employee['age'] }}</td>
                    <td>{{ $employee['years_of_service'] }}</td>
                    <td>{{ $employee['rank'] }}</td>
                    <td>{{ $employee['grade_level_step'] }}</td>
                    <td>{{ $employee['department'] }}</td>
                    <td>{{ $employee['retirement_date'] }}</td>
                    <td>{{ $employee['retire_reason'] }}</td>
                    <td></td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" style="text-align: center;">No retired employees found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>