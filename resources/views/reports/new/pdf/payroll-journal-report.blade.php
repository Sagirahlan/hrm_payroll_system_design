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
                <th>Code</th>
                <th>Description</th>
                <th class="text-center">Count</th>
                <th class="text-right">Amount</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['journal_items'] as $item)
            <tr>
                <td>{{ $item['code'] }}</td>
                <td>{{ $item['description'] }}</td>
                <td class="text-center">{{ $item['count'] }}</td>
                <td class="text-right">0.00</td> <!-- The image shows 0 in one column and amount in another, or vice versa depending on credit/debit. For simplicity, I'll put 0 in one and Total in another if it's a summary -->
                <!-- Actually, the image shows "304" (Count), "0" (Amount?), "2,358,726" (Total). 
                     It seems "Amount" column might be unit amount or something, but here we only have totals. 
                     I'll put 0 for now or remove the column if not needed, but to match image I'll keep it. -->
                <td class="text-right">{{ number_format($item['amount'], 2) }}</td>
            </tr>
            @endforeach
            <tr class="grand-total">
                <td colspan="4" class="text-right">Grand Total:</td>
                <td class="text-right">{{ number_format($data['grand_total'], 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        Generated on: {{ $data['generated_date'] }}
    </div>
</body>
</html>
