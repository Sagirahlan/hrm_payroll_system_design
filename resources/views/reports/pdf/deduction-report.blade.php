<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Deduction Report: {{ $deductionTypeName ?? 'Unknown' }}</title>
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
            width: 210mm;
            height: 297mm;
            margin: 10px auto;
            padding: 15mm;
            background: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .header {
            text-align: center;
            margin-bottom: 12px;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }

        .report-title {
            font-size: 14px;
            font-weight: bold;
            color: #333;
            margin-bottom: 4px;
        }

        .generated-date {
            font-size: 9px;
            color: #666;
            margin-bottom: 2px;
            line-height: 1.3;
        }

        .summary-info {
            font-size: 9px;
            color: #667eea;
            font-weight: 600;
            margin-top: 4px;
        }

        .content {
            flex: 1;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .table-wrapper {
            flex: 1;
            overflow: hidden;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8px;
            line-height: 1.2;
        }

        thead {
            background-color: #f0f0f0;
            position: sticky;
            top: 0;
        }

        th {
            border: 1px solid #ddd;
            padding: 4px 3px;
            text-align: left;
            font-weight: bold;
            color: #333;
            white-space: nowrap;
        }

        td {
            border: 1px solid #ddd;
            padding: 3px;
            text-align: left;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tbody tr:hover {
            background-color: #f0f7ff;
        }

        .employee-name {
            font-weight: 600;
            color: #333;
        }

        .amount {
            text-align: right;
            font-weight: 600;
            color: #667eea;
        }

        .status-badge {
            display: inline-block;
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 7px;
            font-weight: bold;
            text-transform: uppercase;
            background: #e8f4f8;
            color: #333;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #999;
            font-size: 11px;
        }

        .footer {
            border-top: 1px solid #ddd;
            padding-top: 6px;
            margin-top: 8px;
            font-size: 8px;
            color: #666;
            text-align: right;
        }

        @media (max-width: 800px) {
            .page {
                width: 100%;
                height: auto;
                margin: 5px 0;
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="header">
            <div class="report-title">{{ $deductionTypeName ?? 'Deduction Report' }}</div>
            <div class="generated-date">
                <strong>Generated:</strong> {{ now()->format('d M Y') }}
                @if(isset($data['start_date']) || isset($data['end_date']))
                    | 
                    @if(isset($data['start_date']))
                        <strong>From:</strong> {{ $data['start_date'] }}
                    @endif
                    @if(isset($data['end_date']))
                        <strong>To:</strong> {{ $data['end_date'] }}
                    @endif
                @endif
            </div>
            <div class="summary-info">Total Records: {{ count($data['deductions'] ?? []) }}</div>
        </div>

        <div class="content">
            @if(empty($data['deductions']))
                <div class="empty-state">
                    <p>No deductions found for the selected period.</p>
                </div>
            @else
                <div class="table-wrapper">
                    <table>
                        <thead>
                            @if($data['is_loan_related'] ?? false)
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Amount</th>
                                    <th>Start</th>
                                    <th>End</th>
                                    <th>Type</th>
                                    <th>Principal</th>
                                    <th>Repaid</th>
                                    <th>Balance</th>
                                    <th>Monthly</th>
                                    <th>Status</th>
                                </tr>
                            @else
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Amount</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                </tr>
                            @endif
                        </thead>
                        <tbody>
                            @foreach($data['deductions'] ?? [] as $deduction)
                                <tr>
                                    <td>{{ $deduction['employee']['employee_id'] ?? '-' }}</td>
                                    <td class="employee-name">{{ ($deduction['employee']['first_name'] ?? '') . ' ' . ($deduction['employee']['surname'] ?? '') }}</td>
                                    <td class="amount">₦{{ number_format($deduction['amount'] ?? 0, 0) }}</td>
                                    <td>{{ $deduction['start_date'] ?? '-' }}</td>
                                    <td>{{ $deduction['end_date'] ?? 'N/A' }}</td>
                                    
                                    @if($data['is_loan_related'] ?? false)
                                        @if(isset($deduction['loan_details']))
                                            <td>{{ $deduction['loan_details']['loan_type'] ?? 'N/A' }}</td>
                                            <td class="amount">₦{{ number_format($deduction['loan_details']['principal_amount'] ?? 0, 0) }}</td>
                                            <td class="amount">₦{{ number_format($deduction['loan_details']['total_repaid'] ?? 0, 0) }}</td>
                                            <td class="amount">₦{{ number_format($deduction['loan_details']['remaining_balance'] ?? 0, 0) }}</td>
                                            <td class="amount">₦{{ number_format($deduction['loan_details']['monthly_deduction'] ?? 0, 0) }}</td>
                                            <td><span class="status-badge">{{ $deduction['loan_details']['status'] ?? 'N/A' }}</span></td>
                                        @else
                                            <td>-</td>
                                            <td class="amount">₦0</td>
                                            <td class="amount">₦0</td>
                                            <td class="amount">₦0</td>
                                            <td class="amount">₦0</td>
                                            <td><span class="status-badge">N/A</span></td>
                                        @endif
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <div class="footer">
            <p>{{ config('app.name') }} | Report Generated on {{ now()->format('d M Y H:i') }}</p>
        </div>
    </div>
</body>
</html> 