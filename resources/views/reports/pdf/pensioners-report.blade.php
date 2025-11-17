<!DOCTYPE html>
<html>
<head>
    <title>{{ $data['report_title'] ?? 'Pensioners Report' }}</title>
    <meta charset="utf-8">
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
            background-color: #f9f9f9;
        }
        
        .badge {
            display: inline-block;
            padding: 0.25em 0.4em;
            font-size: 75%;
            font-weight: 700;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: 0.25rem;
        }
        
        .badge-success {
            color: #fff;
            background-color: #28a745;
        }
        
        .badge-danger {
            color: #fff;
            background-color: #dc3545;
        }
        
        .badge-secondary {
            color: #fff;
            background-color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $data['report_title'] ?? 'Pensioners Report' }}</h1>
        <p>Generated on: {{ $data['generated_date'] ?? 'N/A' }}</p>
        <p>Total Pensioners: {{ $data['total_pensioners'] ?? 0 }}</p>
    </div>
    
    <div class="info-section">
        <h3>Pensioners List</h3>
        
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Employee ID</th>
                        <th>Full Name</th>
                        <th>Department</th>
                        <th>Cadre</th>
                        <th>Grade Level</th>
                        <th>Retirement Date</th>
                        <th>Pension Start Date</th>
                        <th>Pension Type</th>
                        <th>Pension Amount</th>
                        <th>RSA Balance at Retirement</th>
                        <th>Lump Sum Amount</th>
                        <th>Expected Lifespan (Months)</th>
                        <th>Status</th>
                        <th>Bank Details</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data['pensioners'] ?? [] as $index => $pensioner)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $pensioner['employee_id'] ?? 'N/A' }}</td>
                            <td>{{ $pensioner['full_name'] ?? 'N/A' }}</td>
                            <td>{{ $pensioner['department'] ?? 'N/A' }}</td>
                            <td>{{ $pensioner['cadre'] ?? 'N/A' }}</td>
                            <td>{{ $pensioner['grade_level'] ?? 'N/A' }}</td>
                            <td>{{ $pensioner['retirement_date'] ?? 'N/A' }}</td>
                            <td>{{ $pensioner['pension_start_date'] ?? 'N/A' }}</td>
                            <td>{{ $pensioner['pension_type'] ?? 'N/A' }}</td>
                            <td>{{ $pensioner['pension_amount'] ?? '₦0.00' }}</td>
                            <td>{{ $pensioner['rsa_balance_at_retirement'] ?? '₦0.00' }}</td>
                            <td>{{ $pensioner['lump_sum_amount'] ?? '₦0.00' }}</td>
                            <td>{{ $pensioner['expected_lifespan_months'] ?? 'N/A' }}</td>
                            <td>
                                @if(isset($pensioner['status']))
                                    <span class="badge 
                                        @if($pensioner['status'] === 'Active')
                                            badge-success
                                        @elseif($pensioner['status'] === 'Deceased')
                                            badge-danger
                                        @else
                                            badge-secondary
                                        @endif">
                                        {{ $pensioner['status'] }}
                                    </span>
                                @else
                                    <span class="badge badge-secondary">N/A</span>
                                @endif
                            </td>
                            <td>{{ $pensioner['bank_details'] ?? 'N/A' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="15" class="text-center">No pensioners found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="summary">
        <p><strong>Report Summary:</strong></p>
        <ul>
            <li>Total Pensioners: {{ $data['total_pensioners'] ?? 0 }}</li>
            <li>Report Generated On: {{ $data['generated_date'] ?? 'N/A' }}</li>
        </ul>
    </div>
    
    <div class="footer">
        <p>This is an official report generated by the HR Management System</p>
        <p>Page 1 of 1</p>
    </div>
</body>
</html>