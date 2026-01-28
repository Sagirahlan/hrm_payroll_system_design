<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Promotion History Report</title>
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
        <div class="report-title">Promotion History Report</div>
        <div class="generated-date">Generated on: {{ now()->format('F j, Y g:i A') }}</div>
    </div>

    <div class="summary">
        <p><strong>Total Promotions:</strong> {{ $data['total_promotions'] }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Staff No</th>
                <th>Employee Name</th>
                <th>Department</th>
                <th>Previous Grade</th>
                <th>New Grade</th>
                <th>Promotion Date</th>
                <th>Promotion Type</th>
                <th>Reason</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['promotions'] as $promotion)
            <tr>
                <td>{{ $promotion['employee_id'] }}</td>
                <td>{{ $promotion['employee_name'] }}</td>
                <td>{{ $promotion['department'] }}</td>
                <td>{{ $promotion['previous_grade'] }}</td>
                <td>{{ $promotion['new_grade'] }}</td>
                <td>{{ $promotion['promotion_date'] }}</td>
                <td>{{ $promotion['promotion_type'] }}</td>
                <td>{{ $promotion['reason'] }}</td>
                <td>{{ $promotion['status'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>