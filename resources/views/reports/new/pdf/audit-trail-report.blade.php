<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Audit Trail Report</title>
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
        <div class="report-title">Audit Trail Report</div>
        <div class="generated-date">Generated on: {{ now()->format('F j, Y g:i A') }}</div>
    </div>

    <div class="summary">
        <p><strong>Total Activities:</strong> {{ $data['total_activities'] }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>User Name</th>
                <th>Action</th>
                <th>Description</th>
                <th>Timestamp</th>
                <th>Entity Type</th>
                <th>Entity ID</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['activities'] as $activity)
            <tr>
                <td>{{ $activity['user_name'] }}</td>
                <td>{{ $activity['action'] }}</td>
                <td>{{ $activity['description'] }}</td>
                <td>{{ $activity['timestamp'] }}</td>
                <td>{{ $activity['entity_type'] }}</td>
                <td>{{ $activity['entity_id'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>