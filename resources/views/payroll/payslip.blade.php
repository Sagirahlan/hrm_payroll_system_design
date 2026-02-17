<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payslip PDF</title>
    <style>
        @page {
            margin: 0; /* Use body margin instead for border control */
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif; /* More professional font stack */
            font-size: 9px;
            margin: 5mm;
            padding: 0;
            position: relative;
        }
        .payslip-container {
            border: 2px solid #333; /* Professional border */
            padding: 10px;
            position: relative;
            height: 95mm; /* Fixed height to ensure 3 fit per page */
            box-sizing: border-box;
            overflow: hidden;
        }
        .right-logo {
            position: absolute;
            top: 15px;
            right: 15px;
            width: 50px; /* Reduced size */
            height: auto;
            opacity: 1; /* Fully visible */
            z-index: 10;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 3px;
        }
        th, td {
            border: 1px solid #ccc; /* Softer borders */
            padding: 2px 4px;
            text-align: left;
        }
        th {
            background: #f0f0f0;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase; /* Professional header style */
        }
        .header-table td {
            border: none;
            padding: 1px 3px;
        }
        .company-info {
            text-align: center;
            margin-bottom: 5px; /* Reduced */
        }
        .company-info h2 {
            margin: 0 0 2px 0;
            font-size: 14px; /* Compact header */
            text-transform: uppercase;
        }
        .company-info h3 {
            margin: 0;
            font-size: 10px;
            font-weight: normal;
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
            margin-bottom: 4px;
        }
        h4 {
            margin: 4px 0 2px 0; /* Reduced margins */
            font-size: 10px;
            text-decoration: underline;
        }
        .text-right {
            text-align: right;
        }
        /* Two column layout for deductions/additions if needed, but simple table is safer for PDF */
        .columns-container {
            width: 100%;
            overflow: hidden;
        }
        .column {
            float: left;
            width: 48%;
        }
        .column:last-child {
            float: right;
        }
    </style>
</head>
<body>
    <div class="payslip-container">
        @if(file_exists(public_path('images/WhatsApp Image 2026-01-22 at 10.28.01 AM.jpeg')))
            <img src="{{ public_path('images/WhatsApp Image 2026-01-22 at 10.28.01 AM.jpeg') }}" class="right-logo" alt="Logo">
        @endif

        <div class="company-info">
            <h2>Katsina State Water Board</h2>
            <h3>Individual Payment Slip for the Month of {{ $payroll->payroll_month ? \Carbon\Carbon::parse($payroll->payroll_month)->format('F Y') : 'N/A' }}</h3>
        </div>

        <!-- Combined Header Info (Employee + Payroll) -->
        <table class="header-table" style="margin-bottom: 5px; border: none;">
            <tr>
                <td width="15%" style="border: none;"><strong>Name:</strong></td>
                <td width="35%" style="border: none;">{{ $payroll->employee->first_name }} {{ $payroll->employee->middle_name ?? '' }} {{ $payroll->employee->surname }}</td>
                <td width="15%" style="border: none;"><strong>Staff No:</strong></td>
                <td width="35%" style="border: none;">{{ $payroll->employee->staff_no ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td style="border: none;"><strong>Dept:</strong></td>
                <td style="border: none;">{{ ($payroll->department ?? ($payroll->employee->department ?? null))->department_name ?? 'N/A' }}</td>
                <td style="border: none;"><strong>Rank/GL:</strong></td>
                <td style="border: none;">
                    {{ ($payroll->gradeLevel ?? ($payroll->employee->gradeLevel ?? null))->name ?? 'N/A' }} / 
                    {{ ($payroll->step ?? ($payroll->employee->step ?? null))->name ?? 'N/A' }}
                </td>
            </tr>
            <tr>
                <td style="border: none;"><strong>Bank:</strong></td>
                <td style="border: none;">{{ $payroll->employee->bank->bank_name ?? 'N/A' }} - {{ $payroll->employee->bank->account_no ?? 'N/A' }}</td>
                <td style="border: none;"><strong>Date:</strong></td>
                <td style="border: none;">{{ $payroll->payment_date ? $payroll->payment_date->format('M d, Y') : 'Pending' }}</td>
            </tr>
        </table>

        <!-- Salary Breakdown -->
        <table style="margin-top: 0;">
            <thead>
                <tr class="section-header">
                    <th width="60%">Description</th>
                    <th width="40%" class="text-right">Amount (₦)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Basic Salary</td>
                    <td class="text-right">{{ number_format($payroll->basic_salary, 2) }}</td>
                </tr>
                @if($additions->count() > 0)
                    @foreach ($additions as $addition)
                    <tr>
                        <td>{{ optional($addition->additionType)->name ?? 'Add' }}</td>
                        <td class="text-right">{{ number_format($addition->amount, 2) }}</td>
                    </tr>
                    @endforeach
                @endif
                <tr class="total-row">
                    <td><strong>Gross Salary</strong></td>
                    <td class="text-right"><strong>{{ number_format($payroll->basic_salary + $payroll->total_additions, 2) }}</strong></td>
                </tr>
            </tbody>
        </table>

        <!-- Deductions -->
        @if($deductions->count() > 0 || ($payroll->payment_type == 'Gratuity' && $payroll->total_deductions > 0))
        <h4 style="border-bottom: 1px solid #ccc; padding-bottom: 2px;">Deductions</h4>
        <table>
            <tbody>
                @foreach ($deductions as $deduction)
                    <tr>
                        <td width="60%">{{ optional($deduction->deductionType)->name ?? $deduction->deduction_type }}</td>
                        <td width="40%" class="text-right">{{ number_format($deduction->amount, 2) }}</td>
                    </tr>
                @endforeach
                
                @if($payroll->payment_type == 'Gratuity' && $payroll->total_deductions > 0)
                    <tr>
                        <td>Overstay Deduction</td>
                        <td class="text-right">{{ number_format($payroll->total_deductions, 2) }}</td>
                    </tr>
                @endif

                <tr class="total-row">
                    <td><strong>Total Deductions</strong></td>
                    <td class="text-right"><strong>{{ number_format($payroll->total_deductions, 2) }}</strong></td>
                </tr>
            </tbody>
        </table>
        @endif

        <!-- Net Pay -->
        <table style="margin-top: 5px; border: 2px solid #000;">
            <tr style="background-color: #e2e8f0;">
                <td width="60%" style="font-size: 11px; font-weight: bold; border: none;">NET PAY</td>
                <td width="40%" class="text-right" style="font-size: 11px; font-weight: bold; border: none;">₦{{ number_format($payroll->net_salary, 2) }}</td>
            </tr>
        </table>

        <div style="margin-top: 5px; text-align: right; font-size: 7px; color: #666;">
            Generated: {{ now('Africa/Lagos')->format('d/m/Y H:i') }} | Katsina State Water Board
        </div>
    </div>
</body>
</html>