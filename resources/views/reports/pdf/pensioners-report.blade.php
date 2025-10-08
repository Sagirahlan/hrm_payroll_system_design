<!DOCTYPE html>
<html>
<head>
    <title>{{ $data['report_title'] ?? 'Pensioners Report' }}</title>
    <meta charset="utf-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        
        .header h1 {
            margin: 0;
            font-size: 18px;
            color: #000;
        }
        
        .header p {
            margin: 5px 0;
            font-size: 12px;
        }
        
        .info-section {
            margin-bottom: 20px;
        }
        
        .info-section table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .info-section th, .info-section td {
            padding: 8px;
            text-align: left;
            border: 1px solid #ddd;
        }
        
        .info-section th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        
        .summary {
            margin: 20px 0;
            padding: 10px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
        
        .page-break {
            page-break-after: always;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .table-responsive {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        th, td {
            padding: 6px;
            text-align: left;
            vertical-align: top;
            border: 1px solid #ddd;
        }
        
        th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        
        tr:nth-child(even) {
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