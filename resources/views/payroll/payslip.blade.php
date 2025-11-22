<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payslip PDF</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            margin: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            page-break-inside: auto;
        }
        th, td {
            border: 1px solid #333;
            padding: 4px;
            text-align: left;
        }
        th {
            background: #eee;
            font-size: 10px;
        }
        .header-table td {
            border: none;
        }
        .payslip-header {
            text-align: center;
            margin-bottom: 10px;
        }
        .company-info {
            text-align: center;
            margin-bottom: 10px;
            font-weight: bold;
            font-size: 14px;
        }
        .section-header {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .total-row {
            font-weight: bold;
            background-color: #f8f9fa;
        }
        .employee-details {
            margin-bottom: 10px;
        }
        .employee-details table {
            margin-bottom: 5px;
        }
        .employee-details th, .employee-details td {
            border: none;
            padding: 3px;
        }
        h4 {
            margin: 8px 0 5px 0;
            font-size: 11px;
        }
        .text-right {
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="company-info">
        <h2>HRM PAYROLL SYSTEM</h2>
        <h3>Pay Slip</h3>
    </div>

    <!-- Basic Payroll Information -->
    <table class="header-table">
        <tr>
            <td><strong>Pay Period:</strong></td>
            <td>{{ $payroll->payroll_month ? \Carbon\Carbon::parse($payroll->payroll_month)->format('F Y') : 'N/A' }}</td>
            <td><strong>Payroll Date:</strong></td>
            <td>{{ $payroll->payment_date ? $payroll->payment_date->format('M d, Y') : 'Pending' }}</td>
        </tr>
        <tr>
            <td><strong>Generated:</strong></td>
            <td>{{ $payroll->created_at->format('M d, Y H:i') }}</td>
            <td></td>
            <td></td>
        </tr>
    </table>

    <!-- Employee Information -->
    <div class="employee-details">
        <table class="header-table">
            <tr class="section-header">
                <th colspan="4">Employee Information</th>
            </tr>
            <tr>
                <td><strong>Employee Name:</strong></td>
                <td>{{ $payroll->employee->first_name }} {{ $payroll->employee->middle_name ?? '' }} {{ $payroll->employee->surname }}</td>
                <td><strong>Staff No:</strong></td>
                <td>{{ $payroll->employee->staff_no ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td><strong>Department:</strong></td>
                <td>{{ $payroll->employee->department->department_name ?? 'N/A' }}</td>
                <td><strong>Grade Level/Step:</strong></td>
                <td>{{ $payroll->employee->gradeLevel->name ?? 'N/A' }} / {{ $payroll->employee->step->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td><strong>Bank:</strong></td>
                <td>{{ $payroll->employee->bank->bank_name ?? 'N/A' }}</td>
                <td><strong>Account No:</strong></td>
                <td>{{ $payroll->employee->bank->account_no ?? 'N/A' }}</td>
            </tr>
        </table>
    </div>

    <!-- Salary Breakdown -->
    <table>
        <thead>
            <tr class="section-header">
                <th>Salary Component</th>
                <th>Amount (₦)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Basic Salary</strong></td>
                <td class="text-right">₦{{ number_format($payroll->basic_salary, 2) }}</td>
            </tr>
            <tr>
                <td><strong>Total Additions</strong></td>
                <td class="text-right">₦{{ number_format($payroll->total_additions, 2) }}</td>
            </tr>
            <tr>
                <td><strong>Total Deductions</strong></td>
                <td class="text-right">₦{{ number_format($payroll->total_deductions, 2) }}</td>
            </tr>
            <tr class="total-row">
                <td><strong>Net Salary</strong></td>
                <td class="text-right"><strong>₦{{ number_format($payroll->net_salary, 2) }}</strong></td>
            </tr>
        </tbody>
    </table>

    <!-- Deductions Section -->
    <h4>Deductions</h4>
    @if($deductions->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Amount (₦)</th>
                    <th>Period</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($deductions as $deduction)
                    <tr>
                        <td>{{ optional($deduction->deductionType)->name ?? $deduction->deduction_type }}</td>
                        <td>₦{{ number_format($deduction->amount, 2) }}</td>
                        <td>{{ $deduction->deduction_period }}</td>
                    </tr>
                @endforeach
                <tr class="total-row">
                    <td><strong>Total Deductions</strong></td>
                    <td><strong>₦{{ number_format($deductions->sum('amount'), 2) }}</strong></td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    @else
        <p>No deductions for this period.</p>
    @endif

    <!-- Additions Section -->
    <h4>Additions</h4>
    @if($additions->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Amount (₦)</th>
                    <th>Period</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($additions as $addition)
                    <tr>
                        <td>{{ optional($addition->additionType)->name ?? 'N/A' }}</td>
                        <td>₦{{ number_format($addition->amount, 2) }}</td>
                        <td>{{ $addition->addition_period }}</td>
                    </tr>
                @endforeach
                <tr class="total-row">
                    <td><strong>Total Additions</strong></td>
                    <td><strong>₦{{ number_format($additions->sum('amount'), 2) }}</strong></td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    @else
        <p>No additions for this period.</p>
    @endif

    <div style="margin-top: 15px; text-align: right; font-size: 9px;">
        <p><em>Generated on: {{ now('Africa/Lagos')->format('M d, Y H:i') }}</em></p>
        <p><em>Powered by HRM Payroll System</em></p>
    </div>
</body>
</html>