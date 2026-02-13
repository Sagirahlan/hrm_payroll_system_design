<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Pensioner Report with Bank Details</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        @media print {
            body {
                margin: 0;
                padding: 0;
            }
            .page {
                margin: 0;
                padding: 0;
                page-break-after: always;
            }
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            padding: 10px;
        }

        .page {
            width: 297mm;
            min-height: 210mm;
            margin: 10px auto;
            padding: 10mm;
            background: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            position: relative;
        }

        .page::before {
            content: "KATSINA STATE WATER BOARD";
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 60px;
            color: rgba(30, 64, 175, 0.03);
            font-weight: bold;
            white-space: nowrap;
            z-index: 0;
            pointer-events: none;
        }

        .logo {
            width: 80px;
            height: auto;
            margin-bottom: 8px;
            pointer-events: none;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #1e40af;
            padding-bottom: 8px;
            position: relative;
            z-index: 1;
        }

        .org-name {
            font-size: 16px;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 4px;
            letter-spacing: 1px;
        }

        .report-title {
            font-size: 18px;
            font-weight: bold;
            color: #333;
            margin-bottom: 4px;
        }

        .generated-date {
            font-size: 10px;
            color: #666;
            margin-bottom: 5px;
        }

        .summary {
            background-color: #e8f4f8;
            padding: 10px;
            margin-bottom: 15px;
            border-left: 4px solid #1e40af;
            position: relative;
            z-index: 1;
            font-size: 10px;
        }

        .summary p {
            margin-bottom: 4px;
        }

        .content {
            flex: 1;
            overflow: hidden;
            position: relative;
            z-index: 1;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            font-size: 7.5px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 4px 3px;
            text-align: left;
        }

        th {
            background-color: #1e40af;
            color: white;
            font-weight: bold;
            font-size: 7px;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .text-right {
            text-align: right;
        }

        .footer {
            border-top: 1px solid #ddd;
            padding-top: 8px;
            margin-top: auto;
            font-size: 8px;
            color: #888;
            text-align: center;
            position: relative;
            z-index: 1;
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="header">
            <img src="data:image/jpeg;base64,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\">
            <div class="org-name">KATSINA STATE WATER BOARD</div>
            <div class="report-title">{{ $data['report_title'] ?? 'Pensioner Report with Bank Details' }}</div>
            <div class="generated-date">Generated on: {{ now()->format('F j, Y g:i A') }}</div>
        </div>

        <div class="summary">
            <p><strong>Total Pensioners:</strong> {{ $data['total_pensioners'] }}</p>
            <p><strong>Total Pension Amount:</strong> ₦{{ number_format($data['total_pension_amount'], 2) }}</p>
            <p><strong>Total Gratuity Amount:</strong> ₦{{ number_format($data['total_gratuity_amount'], 2) }}</p>
        </div>

        <div class="content">
            <table>
                <thead>
                    <tr>
                        <th>S/N</th>
                        <th>Full Name</th>
                        <th>Department</th>
                        <th>Rank</th>
                        <th>GL/Step</th>
                        <th>Retirement Date</th>
                        <th>Pension Amt</th>
                        <th>Gratuity Amt</th>
                        <th>Bank Name</th>
                        <th>Account No</th>
                        <th>Account Name</th>
                        <th>Phone</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['pensioners'] as $index => $pensioner)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $pensioner['full_name'] }}</td>
                        <td>{{ $pensioner['department'] }}</td>
                        <td>{{ $pensioner['rank'] }}</td>
                        <td>{{ $pensioner['grade_level'] }}/{{ $pensioner['step'] }}</td>
                        <td>{{ $pensioner['date_of_retirement'] }}</td>
                        <td class="text-right">₦{{ number_format($pensioner['pension_amount'], 2) }}</td>
                        <td class="text-right">₦{{ number_format($pensioner['gratuity_amount'], 2) }}</td>
                        <td>{{ $pensioner['bank_name'] }}</td>
                        <td>{{ $pensioner['account_number'] }}</td>
                        <td>{{ $pensioner['account_name'] }}</td>
                        <td>{{ $pensioner['phone_number'] }}</td>
                        <td>{{ $pensioner['status'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="footer">
            Katsina State Water Board - HR & Payroll Management System<br>
            Pensioner Report with Bank Details | Generated on {{ now()->format('F j, Y g:i A') }}.
        </div>
    </div>
</body>
</html>
