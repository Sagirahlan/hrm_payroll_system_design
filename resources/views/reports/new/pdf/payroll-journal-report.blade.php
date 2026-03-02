<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payroll Journal Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            font-size: 12px;
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
            font-size: 11px;
            font-weight: bold;
            color: #dc2626;
            margin-bottom: 1px;
        }
        .generated-date {
            font-size: 9px;
            color: #666;
            margin-bottom: 2px;
            line-height: 1.3;
        }
        .period {
            font-size: 10px;
            color: #667eea;
            font-weight: 600;
            margin-bottom: 10px;
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
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .grand-total {
            font-weight: bold;
            font-size: 14px;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        @if(file_exists(public_path('images/WhatsApp Image 2026-01-22 at 10.28.01 AM.jpeg')))
            <img src="{{ public_path('images/WhatsApp Image 2026-01-22 at 10.28.01 AM.jpeg') }}" alt="Logo" class="logo">
        @endif
        <div class="org-name">KATSINA STATE WATER BOARD</div>
        <div class="report-title">PAYROLL JOURNALS REPORT</div>
        <div class="period">For the Month of {{ $data['period'] }}</div>
        <div class="generated-date">Generated on: {{ now()->format('F j, Y g:i A') }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th colspan="4" style="text-align: left; background-color: #e0e7ff;">ADDITIONS</th>
            </tr>
            <tr>
                <th>Code</th>
                <th>Description</th>
                <th class="text-center">Count</th>
                <th class="text-right">Total Amount (₦)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['additions'] ?? [] as $item)
            <tr>
                <td>{{ $item['code'] }}</td>
                <td>{{ $item['description'] }}</td>
                <td class="text-center">{{ $item['count'] }}</td>
                <td class="text-right">{{ number_format($item['amount'], 2) }}</td>
            </tr>
            @endforeach
            <tr style="font-weight: bold; background-color: #f8fafc;">
                <td colspan="3" class="text-right">Total Additions:</td>
                <td class="text-right">{{ number_format($data['total_additions'] ?? 0, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <table>
        <thead>
            <tr>
                <th colspan="4" style="text-align: left; background-color: #fee2e2;">DEDUCTIONS</th>
            </tr>
            <tr>
                <th>Code</th>
                <th>Description</th>
                <th class="text-center">Count</th>
                <th class="text-right">Total Amount (₦)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['deductions'] ?? [] as $item)
            <tr>
                <td>{{ $item['code'] }}</td>
                <td>{{ $item['description'] }}</td>
                <td class="text-center">{{ $item['count'] }}</td>
                <td class="text-right">{{ number_format($item['amount'], 2) }}</td>
            </tr>
            @endforeach
            <tr style="font-weight: bold; background-color: #fef2f2;">
                <td colspan="3" class="text-right">Total Deductions:</td>
                <td class="text-right">{{ number_format($data['total_deductions'] ?? 0, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <table style="width: 50%; margin-left: auto; border: 2px solid #333;">
        <tbody>
            <tr class="grand-total" style="background-color: #f1f5f9;">
                <td class="text-right" style="padding: 12px;">NET PAY FOR PERIOD:</td>
                <td class="text-right" style="padding: 12px; color: #166534;">₦ {{ number_format($data['total_net_pay'] ?? $data['grand_total'], 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        Generated on: {{ $data['generated_date'] }}
    </div>
</body>
</html>
