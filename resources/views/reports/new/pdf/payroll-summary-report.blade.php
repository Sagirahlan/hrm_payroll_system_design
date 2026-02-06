<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payroll Summary Report</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 8mm;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 7px;
            line-height: 1.2;
            background: white;
        }

        .header {
            text-align: center;
            margin-bottom: 10px;
        }

        .logo {
            width: 60px;
            height: 60px;
            margin: 0 auto 8px;
            display: block;
        }

        .org-name {
            font-size: 14px;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 2px;
        }

        .report-title {
            font-size: 11px;
            color: #dc2626;
            font-weight: bold;
            margin-bottom: 1px;
        }

        .report-subtitle {
            font-size: 10px;
            color: #dc2626;
            font-weight: bold;
        }

        .bank-section {
            margin-bottom: 12px;
            page-break-inside: avoid;
        }

        .bank-header {
            background-color: #f3f4f6;
            padding: 4px 8px;
            font-weight: bold;
            font-size: 8px;
            margin-bottom: 3px;
            border: 1px solid #000;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        th, td {
            border: 1px solid #000;
            padding: 2px 1px;
            text-align: center;
            font-size: 6px;
        }

        th {
            background-color: #e5e7eb;
            font-weight: bold;
            white-space: nowrap;
        }

        .text-left {
            text-align: left;
        }

        .text-right {
            text-align: right;
        }

        .bank-total {
            font-weight: bold;
            font-style: italic;
            background-color: #e5e7eb;
        }

        .grand-total {
            font-weight: bold;
            background-color: #d1d5db;
            font-size: 7px;
        }

        .summary-box {
            margin: 8px 0;
            padding: 8px;
            background: #f9fafb;
            border: 1px solid #ddd;
            font-size: 8px;
        }

        .summary-box p {
            margin: 2px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        @if(file_exists(public_path('images/WhatsApp Image 2026-01-22 at 10.28.01 AM.jpeg')))
            <img src="{{ public_path('images/WhatsApp Image 2026-01-22 at 10.28.01 AM.jpeg') }}" alt="Logo" class="logo">
        @endif
        <div class="org-name">KATSINA STATE WATER BOARD</div>
        <div class="report-title">{{ strtoupper($data['period'] ?? now()->format('F Y')) }} SALARY.</div>
        @php
            $paymentTypeLabel = 'ALL STAFF';
            if (!empty($data['payment_type'])) {
                $paymentTypeLabel = match($data['payment_type']) {
                    'Regular' => 'REGULAR SALARY STAFF',
                    'Permanent' => 'PERMANENT STAFF',
                    'Casual' => 'CASUAL STAFF',
                    'Pension' => 'PENSIONERS',
                    'Gratuity' => 'GRATUITY',
                    default => strtoupper($data['payment_type']) . ' STAFF'
                };
            }
        @endphp
        <div class="report-subtitle">{{ $paymentTypeLabel }}</div>
    </div>

    @php
        // Group payroll records by bank
        $groupedByBank = collect($data['payroll_records'])->groupBy(function($record) {
            return $record['bank_name'] ?? 'NO BANK';
        });
        
        $grandTotal = [
            'basic' => 0,
            'gross' => 0,
            'deductions' => 0,
            'net' => 0,
            'count' => 0
        ];
    @endphp

    @foreach($groupedByBank as $bankName => $records)
    <div class="bank-section">
        <div class="bank-header">BANK: {{ strtoupper($bankName) }}</div>
        
        <table>
            <thead>
                <tr>
                    <th>S/N</th>
                    <th>STAFF NO</th>
                    <th>STAFF NAME</th>
                    <th>GRADE LEVEL</th>
                    <th>BASIC PAY</th>
                    <th>GROSS PAY</th>
                    <th>PAYE</th>
                    <th>NHF DEDT</th>
                    <th>NHIS DEDT</th>
                    <th>UNIONDL DEDT</th>
                    <th>RENTDED DEDT</th>
                    <th>STAFF P LOAN</th>
                    <th>REFURBI LOAN</th>
                    <th>SPECIAL LOAN</th>
                    <th>NHF REN LOAN</th>
                    <th>SECOND LOAN</th>
                    <th>TOTAL DEDUCTION</th>
                    <th>NET PAY</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $sn = 1;
                    $bankTotal = [
                        'basic' => 0,
                        'gross' => 0,
                        'deductions' => 0,
                        'net' => 0
                    ];
                @endphp
                
                @foreach($records as $record)
                @php
                    $basicSalary = $record['basic_salary'] ?? 0;
                    $grossPay = $basicSalary + ($record['total_additions'] ?? 0);
                    
                    $bankTotal['basic'] += $basicSalary;
                    $bankTotal['gross'] += $grossPay;
                    $bankTotal['deductions'] += $record['total_deductions'] ?? 0;
                    $bankTotal['net'] += $record['net_salary'] ?? 0;
                @endphp
                @php
                    $deductions = $record['deduction_breakdown'] ?? [];
                    // Helper function to get deduction amount by partial name match
                    $getDeduction = function($keywords) use ($deductions) {
                        foreach ($deductions as $name => $amount) {
                            foreach ((array)$keywords as $keyword) {
                                if (stripos($name, $keyword) !== false) {
                                    return $amount;
                                }
                            }
                        }
                        return 0;
                    };
                @endphp
                <tr>
                    <td>{{ $sn++ }}</td>
                    <td>{{ $record['employee_id'] }}</td>
                    <td class="text-left">{{ $record['full_name'] }}</td>
                    <td>{{ $record['grade_level'] ?? 'N/A' }}</td>
                    <td class="text-right">{{ number_format($basicSalary, 2) }}</td>
                    <td class="text-right">{{ number_format($grossPay, 2) }}</td>
                    <td class="text-right">{{ number_format($getDeduction(['PAYE', 'Tax']), 2) }}</td>
                    <td class="text-right">{{ number_format($getDeduction(['NHF']), 2) }}</td>
                    <td class="text-right">{{ number_format($getDeduction(['NHIS', 'Health']), 2) }}</td>
                    <td class="text-right">{{ number_format($getDeduction(['Union', 'Dues']), 2) }}</td>
                    <td class="text-right">{{ number_format($getDeduction(['Rent']), 2) }}</td>
                    <td class="text-right">{{ number_format($getDeduction(['Staff', 'Personal Loan']), 2) }}</td>
                    <td class="text-right">{{ number_format($getDeduction(['Refurb']), 2) }}</td>
                    <td class="text-right">{{ number_format($getDeduction(['Special']), 2) }}</td>
                    <td class="text-right">{{ number_format($getDeduction(['NHF Ren', 'Housing Loan']), 2) }}</td>
                    <td class="text-right">{{ number_format($getDeduction(['Second']), 2) }}</td>
                    <td class="text-right">{{ number_format($record['total_deductions'] ?? 0, 2) }}</td>
                    <td class="text-right">{{ number_format($record['net_salary'] ?? 0, 2) }}</td>
                </tr>
                @endforeach
                
                <tr class="bank-total">
                    <td colspan="4" class="text-right">BANK TOTAL</td>
                    <td class="text-right">{{ number_format($bankTotal['basic'], 2) }}</td>
                    <td class="text-right">{{ number_format($bankTotal['gross'], 2) }}</td>
                    <td colspan="10"></td>
                    <td class="text-right">{{ number_format($bankTotal['deductions'], 2) }}</td>
                    <td class="text-right">{{ number_format($bankTotal['net'], 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>
    
    @php
        $grandTotal['basic'] += $bankTotal['basic'];
        $grandTotal['gross'] += $bankTotal['gross'];
        $grandTotal['deductions'] += $bankTotal['deductions'];
        $grandTotal['net'] += $bankTotal['net'];
        $grandTotal['count'] += count($records);
    @endphp
    @endforeach

    <table>
        <tr class="grand-total">
            <td colspan="4" class="text-right"><strong>GRAND TOTAL</strong></td>
            <td class="text-right"><strong>{{ number_format($grandTotal['basic'], 2) }}</strong></td>
            <td class="text-right"><strong>{{ number_format($grandTotal['gross'], 2) }}</strong></td>
            <td colspan="10"></td>
            <td class="text-right"><strong>{{ number_format($grandTotal['deductions'], 2) }}</strong></td>
            <td class="text-right"><strong>{{ number_format($grandTotal['net'], 2) }}</strong></td>
        </tr>
    </table>

    <div class="summary-box">
        <p><strong>Period:</strong> {{ $data['period'] }}</p>
        <p><strong>Total Records:</strong> {{ $grandTotal['count'] }}</p>
        <p><strong>Total Basic Salary:</strong> ₦{{ number_format($grandTotal['basic'], 2) }}</p>
        <p><strong>Total Net Salary:</strong> ₦{{ number_format($grandTotal['net'], 2) }}</p>
        <p><strong>Generated on:</strong> {{ now()->format('F j, Y g:i A') }}</p>
    </div>
</body>
</html>