<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Loan Status Report</title>
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
        <div class="report-title">Loan Status Report</div>
        <div class="generated-date">Generated on: {{ now()->format('F j, Y g:i A') }}</div>
    </div>

    <div class="summary">
        <p><strong>Total Loans:</strong> {{ $data['total_loans'] }}</p>
        <p><strong>Total Principal:</strong> ₦{{ number_format($data['total_principal'], 2) }}</p>
        <p><strong>Total Repaid:</strong> ₦{{ number_format($data['total_repaid'], 2) }}</p>
        <p><strong>Total Remaining:</strong> ₦{{ number_format($data['total_remaining'], 2) }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Employee ID</th>
                <th>Employee Name</th>
                <th>Department</th>
                <th>Loan Type</th>
                <th>Principal Amount</th>
                <th>Monthly Deduction</th>
                <th>Total Months</th>
                <th>Total Repaid</th>
                <th>Remaining Balance</th>
                <th>Status</th>
                <th>Application Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['loans'] as $loan)
            <tr>
                <td>{{ $loan['employee_id'] }}</td>
                <td>{{ $loan['employee_name'] }}</td>
                <td>{{ $loan['department'] }}</td>
                <td>{{ $loan['loan_type'] }}</td>
                <td>₦{{ number_format($loan['principal_amount'], 2) }}</td>
                <td>₦{{ number_format($loan['monthly_deduction'], 2) }}</td>
                <td>{{ $loan['total_months'] }}</td>
                <td>₦{{ number_format($loan['total_repaid'], 2) }}</td>
                <td>₦{{ number_format($loan['remaining_balance'], 2) }}</td>
                <td>{{ $loan['status'] }}</td>
                <td>{{ $loan['application_date'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>