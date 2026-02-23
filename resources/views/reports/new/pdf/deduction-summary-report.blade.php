<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Deduction Summary Report</title>
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
            font-size: 9px;
            line-height: 1.2;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
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

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th, td {
            border: 1px solid #000;
            padding: 4px;
            text-align: center;
        }

        th {
            background-color: #f0f0f0;
            font-weight: bold;
            white-space: nowrap;
        }

        .text-left { text-align: left; }
        .text-right { text-align: right; }
        
        .totals-row {
            font-weight: bold;
            background-color: #e0e0e0;
        }
    </style>
</head>
<body>
    <div class="header">
        @if(file_exists(public_path('images/WhatsApp Image 2026-01-22 at 10.28.01 AM.jpeg')))
            <img src="{{ public_path('images/WhatsApp Image 2026-01-22 at 10.28.01 AM.jpeg') }}" alt="Logo" class="logo">
        @endif
        <div class="org-name">KATSINA STATE WATER BOARD</div>
        <div class="report-title">DEDUCTION SUMMARY REPORT</div>
        <div class="report-subtitle">Generated on: {{ now()->format('F j, Y g:i A') }}</div>
        <div>Total Deductions: ₦{{ number_format($data['total_amount'], 2) }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>S/N</th>
                <th>Staff No</th>
                <th>Employee Name</th>
                <th>Department</th>
                
                @foreach($data['deduction_types'] as $type)
                    <th>{{ $type }}</th>
                @endforeach

                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @php
                $sn = 1;
                $columnTotals = array_fill_keys($data['deduction_types'], 0);
                $grandTotal = 0;
            @endphp
            
            @foreach($data['employees'] as $employee)
            <tr>
                <td>{{ $sn++ }}</td>
                <td>{{ $employee['employee_id'] }}</td>
                <td class="text-left">{{ $employee['employee_name'] }}</td>
                <td>{{ $employee['department'] }}</td>
                
                @foreach($data['deduction_types'] as $type)
                    @php
                        $amount = $employee['deductions'][$type] ?? 0;
                        $columnTotals[$type] += $amount;
                    @endphp
                    <td class="text-right">{{ $amount > 0 ? number_format($amount, 2) : '-' }}</td>
                @endforeach

                <td class="text-right"><strong>{{ number_format($employee['total_deductions'], 2) }}</strong></td>
                @php $grandTotal += $employee['total_deductions']; @endphp
            </tr>
            @endforeach

            <tr class="totals-row">
                <td colspan="4" class="text-right"><strong>TOTALS</strong></td>
                
                @foreach($data['deduction_types'] as $type)
                    <td class="text-right">{{ number_format($columnTotals[$type], 2) }}</td>
                @endforeach

                <td class="text-right">{{ number_format($grandTotal, 2) }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html>