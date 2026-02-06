<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payroll Report - {{ $month }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 10mm;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 8px;
            line-height: 1.2;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
        }

        .logo {
            width: 80px;
            height: 80px;
            margin: 0 auto 10px;
        }

        .org-name {
            font-size: 16px;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 3px;
        }

        .report-title {
            font-size: 12px;
            color: #dc2626;
            font-weight: bold;
            margin-bottom: 2px;
        }

        .report-subtitle {
            font-size: 11px;
            color: #dc2626;
            font-weight: bold;
        }

        .bank-section {
            margin-bottom: 20px;
            page-break-inside: avoid;
        }

        .bank-header {
            background-color: #f3f4f6;
            padding: 5px;
            font-weight: bold;
            font-size: 9px;
            margin-bottom: 5px;
            border: 1px solid #000;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        th, td {
            border: 1px solid #000;
            padding: 3px 2px;
            text-align: center;
        }

        th {
            background-color: #e5e7eb;
            font-weight: bold;
            font-size: 7px;
            white-space: nowrap;
        }

        td {
            font-size: 7px;
        }

        .text-left {
            text-align: left;
        }

        .text-right {
            text-align: right;
        }

        .total-row {
            font-weight: bold;
            background-color: #f9fafb;
        }

        .bank-total {
            font-weight: bold;
            font-style: italic;
            background-color: #e5e7eb;
        }
    </style>
</head>
<body>
    <div class="header">
        @if(file_exists(public_path('images/logo.png')))
            <img src="{{ public_path('images/logo.png') }}" alt="Logo" class="logo">
        @endif
        <div class="org-name">KATSINA STATE WATER BOARD</div>
        <div class="report-title">{{ strtoupper(date('F Y', strtotime($month))) }} SALARY.</div>
        <div class="report-subtitle">{{ strtoupper($category ?? 'ALL STAFF') }}</div>
    </div>

    @php
        $groupedByBank = $payrolls->groupBy(function($payroll) {
            return $payroll->employee->bank->bank_name ?? 'NO BANK';
        });
        $grandTotal = [
            'basic' => 0,
            'gross' => 0,
            'additions' => 0,
            'deductions' => 0,
            'net' => 0
        ];
    @endphp

    @foreach($groupedByBank as $bankName => $records)
    <div class="bank-section">
        <div class="bank-header">BANK: {{ strtoupper($bankName) }}</div>
        
        <table>
            <thead>
                <tr>
                    <th rowspan="2">S/N</th>
                    <th rowspan="2">STAFF<br>NO</th>
                    <th rowspan="2">STAFF<br>NAME</th>
                    <th rowspan="2">GRADE<br>LEVEL</th>
                    <th rowspan="2">BASIC<br>PAY</th>
                    <th rowspan="2">GROSS<br>PAY</th>
                    <th rowspan="2">DATE<br>APPT</th>
                    <th rowspan="2">NHF<br>LEVY</th>
                    <th rowspan="2">NHD<br>LEVY</th>
                    <th rowspan="2">UNIFORM<br>LEVY</th>
                    <th rowspan="2">RENT/DED<br>LEVY</th>
                    <th colspan="2">STAFF LOAN</th>
                    <th rowspan="2">RETIRE<br>LOAN</th>
                    <th rowspan="2">SPECIAL<br>LOAN</th>
                    <th rowspan="2">NHF REC<br>LOAN</th>
                    <th rowspan="2">SECOND<br>LOAN</th>
                    <th rowspan="2">TOTAL<br>DEDUCTION</th>
                    <th rowspan="2">NET<br>PAY</th>
                </tr>
                <tr>
                    <th>LOAN</th>
                    <th>INT</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $sn = 1;
                    $bankTotal = [
                        'basic' => 0,
                        'gross' => 0,
                        'additions' => 0,
                        'deductions' => 0,
                        'net' => 0
                    ];
                @endphp
                
                @foreach($records as $payroll)
                @php
                    $employee = $payroll->employee;
                    $basicSalary = $payroll->basic_salary;
                    $grossPay = $basicSalary + $payroll->total_additions;
                    
                    $bankTotal['basic'] += $basicSalary;
                    $bankTotal['gross'] += $grossPay;
                    $bankTotal['additions'] += $payroll->total_additions;
                    $bankTotal['deductions'] += $payroll->total_deductions;
                    $bankTotal['net'] += $payroll->net_salary;
                @endphp
                <tr>
                    <td>{{ $sn++ }}</td>
                    <td>{{ $employee->staff_no }}</td>
                    <td class="text-left">{{ $employee->full_name }}</td>
                    <td>{{ $employee->gradeLevel->name ?? 'N/A' }}</td>
                    <td class="text-right">{{ number_format($basicSalary, 2) }}</td>
                    <td class="text-right">{{ number_format($grossPay, 2) }}</td>
                    <td>{{ $employee->date_of_first_appointment ? date('d/m/Y', strtotime($employee->date_of_first_appointment)) : '' }}</td>
                    <td class="text-right">0.00</td>
                    <td class="text-right">0.00</td>
                    <td class="text-right">0.00</td>
                    <td class="text-right">0.00</td>
                    <td class="text-right">0.00</td>
                    <td class="text-right">0.00</td>
                    <td class="text-right">0.00</td>
                    <td class="text-right">0.00</td>
                    <td class="text-right">0.00</td>
                    <td class="text-right">0.00</td>
                    <td class="text-right">{{ number_format($payroll->total_deductions, 2) }}</td>
                    <td class="text-right">{{ number_format($payroll->net_salary, 2) }}</td>
                </tr>
                @endforeach
                
                <tr class="bank-total">
                    <td colspan="4" class="text-right">BANK TOTAL</td>
                    <td class="text-right">{{ number_format($bankTotal['basic'], 2) }}</td>
                    <td class="text-right">{{ number_format($bankTotal['gross'], 2) }}</td>
                    <td colspan="11"></td>
                    <td class="text-right">{{ number_format($bankTotal['deductions'], 2) }}</td>
                    <td class="text-right">{{ number_format($bankTotal['net'], 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>
    
    @php
        $grandTotal['basic'] += $bankTotal['basic'];
        $grandTotal['gross'] += $bankTotal['gross'];
        $grandTotal['additions'] += $bankTotal['additions'];
        $grandTotal['deductions'] += $bankTotal['deductions'];
        $grandTotal['net'] += $bankTotal['net'];
    @endphp
    @endforeach

    <table>
        <tr class="total-row">
            <td colspan="4" class="text-right"><strong>GRAND TOTAL</strong></td>
            <td class="text-right"><strong>{{ number_format($grandTotal['basic'], 2) }}</strong></td>
            <td class="text-right"><strong>{{ number_format($grandTotal['gross'], 2) }}</strong></td>
            <td colspan="11"></td>
            <td class="text-right"><strong>{{ number_format($grandTotal['deductions'], 2) }}</strong></td>
            <td class="text-right"><strong>{{ number_format($grandTotal['net'], 2) }}</strong></td>
        </tr>
    </table>
</body>
</html>
