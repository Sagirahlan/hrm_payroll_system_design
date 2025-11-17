<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Deduction Summary Report</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        @media print {
            body {
                margin: 0;
                padding: 0;
            }
            .page {
                margin: 0;
                padding: 0;
                page-break-after: always;
            }
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            padding: 10px;
        }

        .page {
            width: 210mm;
            height: 297mm;
            margin: 10px auto;
            padding: 15mm;
            background: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .header {
            text-align: center;
            margin-bottom: 12px;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }

        .report-title {
            font-size: 14px;
            font-weight: bold;
            color: #333;
            margin-bottom: 4px;
        }

        .generated-date {
            font-size: 9px;
            color: #666;
            margin-bottom: 2px;
            line-height: 1.3;
        }

        .summary-info {
            font-size: 9px;
            color: #667eea;
            font-weight: 600;
            margin-top: 4px;
        }

        .content {
            flex: 1;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .table-wrapper {
            flex: 1;
            overflow: hidden;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8px;
            line-height: 1.2;
        }

        thead {
            background-color: #f0f0f0;
            position: sticky;
            top: 0;
        }

        th {
            border: 1px solid #ddd;
            padding: 4px 3px;
            text-align: left;
            font-weight: bold;
            color: #333;
            white-space: nowrap;
        }

        td {
            border: 1px solid #ddd;
            padding: 3px;
            text-align: left;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tbody tr:hover {
            background-color: #f0f7ff;
        }

        .employee-name {
            font-weight: 600;
            color: #333;
        }

        .amount {
            text-align: right;
            font-weight: 600;
            color: #667eea;
        }

        .status-badge {
            display: inline-block;
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 7px;
            font-weight: bold;
            text-transform: uppercase;
            background: #e8f4f8;
            color: #333;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #999;
            font-size: 11px;
        }

        .footer {
            border-top: 1px solid #ddd;
            padding-top: 6px;
            margin-top: 8px;
            font-size: 8px;
            color: #666;
            text-align: right;
        }

        @media (max-width: 800px) {
            .page {
                width: 100%;
                height: auto;
                margin: 5px 0;
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="report-title">Deduction Summary Report</div>
        <div class="generated-date">Generated on: {{ now()->format('F j, Y g:i A') }}</div>
    </div>

    <div class="summary">
        <p><strong>Total Deductions:</strong> {{ $data['total_deductions'] }}</p>
        <p><strong>Total Amount:</strong> ₦{{ number_format($data['total_amount'], 2) }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Employee ID</th>
                <th>Employee Name</th>
                <th>Department</th>
                <th>Deduction Type</th>
                <th>Amount</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Frequency</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['deductions'] as $deduction)
            <tr>
                <td>{{ $deduction['employee_id'] }}</td>
                <td>{{ $deduction['employee_name'] }}</td>
                <td>{{ $deduction['department'] }}</td>
                <td>{{ $deduction['deduction_type'] }}</td>
                <td>₦{{ number_format($deduction['amount'], 2) }}</td>
                <td>{{ $deduction['start_date'] }}</td>
                <td>{{ $deduction['end_date'] }}</td>
                <td>{{ $deduction['frequency'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>