<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Addition Summary Report</title>
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
        <div class="report-title">Addition Summary Report</div>
        <div class="generated-date">Generated on: {{ now()->format('F j, Y g:i A') }}</div>
    </div>

    <div class="summary">
        <p><strong>Total Additions:</strong> {{ $data['total_additions'] }}</p>
        <p><strong>Total Amount:</strong> ₦{{ number_format($data['total_amount'], 2) }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Employee ID</th>
                <th>Employee Name</th>
                <th>Department</th>
                <th>Addition Type</th>
                <th>Amount</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Frequency</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['additions'] as $addition)
            <tr>
                <td>{{ $addition['employee_id'] }}</td>
                <td>{{ $addition['employee_name'] }}</td>
                <td>{{ $addition['department'] }}</td>
                <td>{{ $addition['addition_type'] }}</td>
                <td>₦{{ number_format($addition['amount'], 2) }}</td>
                <td>{{ $addition['start_date'] }}</td>
                <td>{{ $addition['end_date'] }}</td>
                <td>{{ $addition['frequency'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>