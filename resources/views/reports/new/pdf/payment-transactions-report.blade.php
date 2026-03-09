<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment Transactions Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            font-size: 10px;
        }
        .header {
            text-align: center;
            margin-bottom: 12px;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }
        .logo {
            width: 55px;
            height: 55px;
            margin: 0 auto 6px;
            display: block;
        }
        .org-name {
            font-size: 14px;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 2px;
        }
        .report-title {
            font-size: 12px;
            font-weight: bold;
            color: #dc2626;
            margin-bottom: 1px;
        }
        .report-subtitle {
            font-size: 11px;
            font-weight: bold;
            color: #333;
            margin-bottom: 2px;
        }
        .generated-date {
            font-size: 9px;
            color: #666;
            margin-bottom: 2px;
            line-height: 1.3;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 5px 6px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
            font-size: 9px;
        }
        td {
            font-size: 9px;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
            color: #fff;
        }
        .badge-success { background-color: #28a745; }
        .badge-info { background-color: #17a2b8; }
        .badge-warning { background-color: #ffc107; color: #333; }
        .badge-secondary { background-color: #6c757d; }
        .badge-danger { background-color: #dc3545; }
        .summary-row {
            font-weight: bold;
            background-color: #f1f5f9;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 9px;
            color: #666;
        }
        .sn-col { width: 30px; }
    </style>
</head>
<body>
    <div class="header">
        @if(file_exists(public_path('images/WhatsApp Image 2026-01-22 at 10.28.01 AM.jpeg')))
            <img src="{{ public_path('images/WhatsApp Image 2026-01-22 at 10.28.01 AM.jpeg') }}" alt="Logo" class="logo">
        @endif
        <div class="org-name">KATSINA STATE WATER BOARD</div>
        <div class="report-title">{{ strtoupper($monthStr) }} SALARY</div>
        <div class="report-subtitle">{{ strtoupper($appointmentTypeName) }} PAYMENT SCHEDULE</div>
        <div class="generated-date">Generated on: {{ now()->format('F j, Y g:i A') }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th class="sn-col">S/N</th>
                <th>Date</th>
                <th>Employee</th>
                <th>Staff ID</th>
                <th>Payroll Month</th>
                <th class="text-right">Amount (₦)</th>
                <th>Bank</th>
                <th>Account Number</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @php $sn = 1; $totalAmount = 0; @endphp
            @foreach($transactions as $transaction)
                @php $totalAmount += $transaction->amount; @endphp
                <tr>
                    <td class="text-center">{{ $sn++ }}</td>
                    <td>{{ $transaction->payment_date ? \Carbon\Carbon::parse($transaction->payment_date)->format('Y-m-d') : 'N/A' }}</td>
                    <td>
                        @if($transaction->employee)
                            {{ $transaction->employee->first_name }} {{ $transaction->employee->surname }}
                        @else
                            Unknown
                        @endif
                    </td>
                    <td>{{ $transaction->employee->staff_no ?? $transaction->employee_id }}</td>
                    <td>
                        @if($transaction->payroll && $transaction->payroll->payroll_month)
                            {{ $transaction->payroll->payroll_month->format('M Y') }}
                        @else
                            N/A
                        @endif
                    </td>
                    <td class="text-right">{{ number_format($transaction->amount, 2) }}</td>
                    <td>{{ $transaction->bank_code }}</td>
                    <td>{{ $transaction->account_number }}</td>
                    <td>
                        <span class="badge
                            @if($transaction->status == 'Approved') badge-success
                            @elseif($transaction->status == 'Pending Final Approval') badge-info
                            @elseif($transaction->status == 'Under Review') badge-warning
                            @elseif($transaction->status == 'Reviewed') badge-info
                            @elseif($transaction->status == 'Rejected') badge-danger
                            @else badge-secondary
                            @endif">
                            {{ $transaction->status ?? 'Pending Review' }}
                        </span>
                    </td>
                </tr>
            @endforeach
            <tr class="summary-row">
                <td colspan="5" class="text-right">Total ({{ count($transactions) }} records):</td>
                <td class="text-right">₦ {{ number_format($totalAmount, 2) }}</td>
                <td colspan="3"></td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        Generated on: {{ now()->format('F j, Y g:i A') }}
    </div>
</body>
</html>
