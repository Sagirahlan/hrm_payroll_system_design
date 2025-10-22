<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Grade Level Summary Report</title>
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
        <div class="report-title">Grade Level Summary Report</div>
        <div class="generated-date">Generated on: {{ now()->format('F j, Y g:i A') }}</div>
    </div>

    <div class="summary">
        <p><strong>Total Grade Levels:</strong> {{ $data['total_grade_levels'] }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Grade Level</th>
                <th>Salary Scale</th>
                <th>Total Employees</th>
                <th>Active Employees</th>
                <th>Total Basic Salary</th>
                <th>Avg Basic Salary</th>
                <th>Min Basic Salary</th>
                <th>Max Basic Salary</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['grade_levels'] as $level)
            <tr>
                <td>{{ $level['grade_level_name'] }}</td>
                <td>{{ $level['salary_scale'] }}</td>
                <td>{{ $level['total_employees'] }}</td>
                <td>{{ $level['active_employees'] }}</td>
                <td>₦{{ number_format($level['total_basic_salary'], 2) }}</td>
                <td>₦{{ number_format($level['average_basic_salary'], 2) }}</td>
                <td>₦{{ number_format($level['min_basic_salary'], 2) }}</td>
                <td>₦{{ number_format($level['max_basic_salary'], 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>