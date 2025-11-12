<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payslip PDF</title>
    <style>
        body { 
            font-family: DejaVu Sans, sans-serif; 
            font-size: 12px; 
            margin: 20px;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 20px; 
            page-break-inside: auto;
        }
        th, td { 
            border: 1px solid #333; 
            padding: 6px; 
            text-align: left; 
        }
        th { 
            background: #eee; 
        }
        .header-table td { 
            border: none; 
        }
        .payslip-header {
            text-align: center;
            margin-bottom: 20px;
        }
        .company-info {
            text-align: center;
            margin-bottom: 20px;
            font-weight: bold;
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
            margin-bottom: 15px;
        }
        .employee-details table {
            margin-bottom: 10px;
        }
        .employee-details th, .employee-details td {
            border: none;
            padding: 4px;
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
            <td><strong>Payroll ID:</strong></td>
            <td>{{ $payroll->payroll_id }}</td>
            <td><strong>Pay Period:</strong></td>
            <td>{{ $payroll->payroll_month ? \Carbon\Carbon::parse($payroll->payroll_month)->format('F Y') : 'N/A' }}</td>
        </tr>
        <tr>
            <td><strong>Payroll Date:</strong></td>
            <td>{{ $payroll->payment_date ? $payroll->payment_date->format('M d, Y') : 'Pending' }}</td>
            <td><strong>Generated:</strong></td>
            <td>{{ $payroll->created_at->format('M d, Y H:i') }}</td>
        </tr>
        <tr>
            <td><strong>Status:</strong></td>
            <td>
                <span class="badge 
                    @if($payroll->status === 'Approved') bg-success
                    @elseif($payroll->status === 'Paid') bg-success
                    @elseif($payroll->status === 'Pending Final Approval') bg-info
                    @elseif($payroll->status === 'Processed') bg-primary
                    @elseif($payroll->status === 'Under Review') bg-warning text-dark
                    @elseif($payroll->status === 'Reviewed') bg-info
                    @elseif($payroll->status === 'Pending Review') bg-secondary
                    @elseif($payroll->status === 'Rejected') bg-danger
                    @else bg-secondary @endif">
                    {{ $payroll->status }}
                </span>
            </td>
            <td><strong>Remarks:</strong></td>
            <td>{{ $payroll->remarks ?? 'N/A' }}</td>
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
                <td><strong>Employee ID:</strong></td>
                <td>{{ $payroll->employee->employee_id }}</td>
            </tr>
            <tr>
                <td><strong>staff No:</strong></td>
                <td>{{ $payroll->employee->staff_no ?? 'N/A' }}</td>
                <td><strong>Department:</strong></td>
                <td>{{ $payroll->employee->department->department_name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td><strong>Grade Level:</strong></td>
                <td>{{ $payroll->employee->gradeLevel->name ?? 'N/A' }}</td>
                <td><strong>Step:</strong></td>
                <td>{{ $payroll->employee->step->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td><strong>Employment Status:</strong></td>
                <td>
                    {{ $payroll->employee->status }}
                    @if($payroll->employee->status === 'Suspended')
                        <span style="color: orange;">(Suspended - Special Calculation Applied)</span>
                    @endif
                </td>
                <td><strong>Bank:</strong></td>
                <td>{{ $payroll->employee->bank->bank_name ?? 'N/A' }}</td>
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
                <td><strong>Account No:</strong></td>
                <td>{{ $payroll->employee->bank->account_no ?? 'N/A' }}</td>
            </tr>
            @endif
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
                    <th>Category</th>
                    <th>Type</th>
                    <th>Amount (₦)</th>
                    <th>Period</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($deductions as $deduction)
                    <tr>
                        <td>{{ optional($deduction->deductionType)->name ?? $deduction->deduction_type }}</td>
                        <td>{{ $deduction->deduction_type }}</td>
                        <td>₦{{ number_format($deduction->amount, 2) }}</td>
                        <td>{{ $deduction->deduction_period }}</td>
                        <td>{{ $deduction->start_date ? \Carbon\Carbon::parse($deduction->start_date)->format('M d, Y') : 'N/A' }}</td>
                        <td>{{ $deduction->end_date ? \Carbon\Carbon::parse($deduction->end_date)->format('M d, Y') : 'Ongoing' }}</td>
                    </tr>
                @endforeach
                <tr class="total-row">
                    <td colspan="2"><strong>Total Deductions</strong></td>
                    <td><strong>₦{{ number_format($deductions->sum('amount'), 2) }}</strong></td>
                    <td colspan="3"></td>
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
                    <th>Category</th>
                    <th>Type</th>
                    <th>Amount (₦)</th>
                    <th>Period</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($additions as $addition)
                    <tr>
                        <td>{{ optional($addition->additionType)->name ?? 'N/A' }}</td>
                        <td>{{ $addition->addition_type }}</td>
                        <td>₦{{ number_format($addition->amount, 2) }}</td>
                        <td>{{ $addition->addition_period }}</td>
                        <td>{{ $addition->start_date ? \Carbon\Carbon::parse($addition->start_date)->format('M d, Y') : 'N/A' }}</td>
                        <td>{{ $addition->end_date ? \Carbon\Carbon::parse($addition->end_date)->format('M d, Y') : 'Ongoing' }}</td>
                    </tr>
                @endforeach
                <tr class="total-row">
                    <td colspan="2"><strong>Total Additions</strong></td>
                    <td><strong>₦{{ number_format($additions->sum('amount'), 2) }}</strong></td>
                    <td colspan="3"></td>
                </tr>
            </tbody>
        </table>
    @else
        <p>No additions for this period.</p>
    @endif

    <div style="margin-top: 30px; text-align: right;">
        <p><em>Generated on: {{ now('Africa/Lagos')->format('M d, Y H:i') }}</em></p>
        <p><em>Powered by HRM Payroll System</em></p>
    </div>
</body>
</html>