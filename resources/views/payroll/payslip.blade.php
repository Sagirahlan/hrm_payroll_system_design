<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payslip PDF</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #333; padding: 6px; text-align: left; }
        th { background: #eee; }
        .header-table td { border: none; }
    </style>
</head>
<body>
    <h2 style="text-align:center;">Pay Slip</h2>
    <table class="header-table">
        <tr>
            <td><strong>Payroll ID:</strong></td>
            <td>{{ $payroll->payroll_id }}</td>
            <td><strong>Payroll Date:</strong></td>
            <td>{{ $payroll->payment_date ?? 'Pending' }}</td>
        </tr>
        <tr>
            <td><strong>Employee:</strong></td>
            <td>{{ $payroll->employee->first_name }} {{ $payroll->employee->surname }}</td>
            <td><strong>Remarks:</strong></td>
            <td>{{ $payroll->remarks ?? 'N/A' }}</td>
        </tr>
        @if(isset($payroll->employee->expected_retirement_date))
        <tr>
            <td><strong>Expected Retirement Date:</strong></td>
            <td>
                @if(is_string($payroll->employee->expected_retirement_date))
                    {{ \Carbon\Carbon::parse($payroll->employee->expected_retirement_date)->format('M d, Y') }}
                @else
                    {{ $payroll->employee->expected_retirement_date->format('M d, Y') }}
                @endif
            </td>
            <td></td>
            <td></td>
        </tr>
        @endif
    </table>

    <table>
        <tr>
            <th>Gross Salary</th>
            <th>Total Additions</th>
            <th>Total Deductions</th>
            <th>Net Salary</th>
        </tr>
        <tr>
            <td>₦{{ number_format(optional($payroll->employee->salaryScale)->basic_salary, 2) }}</td>
            <td>₦{{ number_format($payroll->total_additions, 2) }}</td>
            <td>₦{{ number_format($payroll->total_deductions, 2) }}</td>
            <td>₦{{ number_format($payroll->net_salary, 2) }}</td>
        </tr>
    </table>

    <h4>Deductions</h4>
    <table>
        <thead>
            <tr>
                <th>Type</th>
                <th>Amount</th>
                <th>Period</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($deductions as $deduction)
                <tr>
                    <td>{{ $deduction->name_type }}</td>
                    <td>₦{{ number_format($deduction->amount, 2) }}</td>
                    <td>{{ $deduction->period }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h4>Additions</h4>
    <table>
        <thead>
            <tr>
                <th>Type</th>
                <th>Amount</th>
                <th>Period</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($additions as $addition)
                <tr>
                    <td>{{ $addition->name_type }}</td>
                    <td>₦{{ number_format($addition->amount, 2) }}</td>
                    <td>{{ $addition->period }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>