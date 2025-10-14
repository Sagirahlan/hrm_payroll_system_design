<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Addition Report: {{ $additionTypeName ?? 'Unknown' }}</title>
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
        <div class="report-title">Addition Report: {{ $additionTypeName ?? 'Unknown' }}</div>
        <div class="generated-date">Generated on: {{ now()->format('F j, Y') }}</div>
        @if(isset($data['start_date']) || isset($data['end_date']))
        <div class="generated-date">
            @if(isset($data['start_date']))
                From: {{ $data['start_date'] }}
            @endif
            @if(isset($data['end_date']))
                To: {{ $data['end_date'] }}
            @endif
        </div>
        @endif
        <div class="summary-info">Total Additions: {{ count($data['additions'] ?? []) }}</div>
    </div>

    @if(empty($data['additions']))
        <div style="text-align: center; padding: 20px;">
            <p>No additions found for the selected period.</p>
        </div>
    @else
        <table>
            <thead>
                <tr>
                    <th>Employee ID</th>
                    <th>Employee Name</th>
                    <th>Amount</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['additions'] ?? [] as $addition)
                    <tr>
                        <td>{{ $addition['employee']['employee_id'] ?? '' }}</td>
                        <td>{{ ($addition['employee']['first_name'] ?? '') . ' ' . ($addition['employee']['surname'] ?? '') }}</td>
                        <td>₦{{ number_format($addition['amount'] ?? 0, 2) }}</td>
                        <td>{{ $addition['start_date'] ?? '' }}</td>
                        <td>{{ $addition['end_date'] ?? 'N/A' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>