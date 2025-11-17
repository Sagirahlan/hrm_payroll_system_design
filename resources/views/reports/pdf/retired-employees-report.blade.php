<!DOCTYPE html>
<html>
<head>
    <title>Retired Employees Report</title>
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
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #ccc;
            font-size: 10px;
            color: #666;
        }
        .page-break {
            page-break-after: always;
        }
        .subsection {
            margin-left: 15px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Retired Employees Report</h1>
        <p>Generated on {{ date('F j, Y') }}</p>
    </div>

    @if(isset($data['report_title']))
    <div class="summary-section">
        <p><strong>{{ $data['report_title'] }}</strong></p>
        <p><strong>Total Retired Employees:</strong> {{ $data['total_retired_employees'] ?? 0 }}</p>
        <p><strong>Report Generated:</strong> {{ $data['generated_date'] ?? date('F j, Y') }}</p>
    </div>
    @endif

    @if(isset($data['employees']) && count($data['employees']) > 0)
        @foreach($data['employees'] as $employee)
        <div class="section">
            <div class="section-title">
                {!! ($employee['full_name'] ?? 'N/A') . ' (ID: ' . ($employee['employee_id'] ?? 'N/A') . ')' !!}
            </div>
            
            <table>
                <tr>
                    <th>Department</th>
                    <td>{{ $employee['department'] ?? 'N/A' }}</td>
                    <th>Cadre</th>
                    <td>{{ $employee['cadre'] ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Grade Level</th>
                    <td>{{ $employee['grade_level'] ?? 'N/A' }}</td>
                    <th>Date of First Appointment</th>
                    <td>{{ $employee['date_of_first_appointment'] ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Date of Retirement</th>
                    <td>{{ $employee['date_of_retirement'] ?? 'N/A' }}</td>
                    <th>Years of Service</th>
                    <td>{{ $employee['years_of_service'] ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Basic Salary</th>
                    <td>{{ $employee['basic_salary'] ?? 'N/A' }}</td>
                    <th>Bank Details</th>
                    <td>{{ $employee['bank_details'] ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Disciplinary Records</th>
                    <td>{{ $employee['disciplinary_records'] ?? 'N/A' }}</td>
                    <th>Last Payroll Date</th>
                    <td>{{ $employee['last_payroll_date'] ?? 'N/A' }}</td>
                </tr>
            </table>

            @if(isset($employee['deductions']) && count($employee['deductions']) > 0)
            <div class="subsection">
                <div class="section-title">Deductions</div>
                <table>
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($employee['deductions'] as $deduction)
                        <tr>
                            <td>{{ $deduction['type'] ?? 'N/A' }}</td>
                            <td class="text-right">{{ $deduction['amount'] ?? 'N/A' }}</td>
                            <td class="text-center">{{ $deduction['start_date'] ?? 'N/A' }}</td>
                            <td class="text-center">{{ $deduction['end_date'] ?? 'N/A' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

            @if(isset($employee['additions']) && count($employee['additions']) > 0)
            <div class="subsection">
                <div class="section-title">Additions</div>
                <table>
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($employee['additions'] as $addition)
                        <tr>
                            <td>{{ $addition['type'] ?? 'N/A' }}</td>
                            <td class="text-right">{{ $addition['amount'] ?? 'N/A' }}</td>
                            <td class="text-center">{{ $addition['start_date'] ?? 'N/A' }}</td>
                            <td class="text-center">{{ $addition['end_date'] ?? 'N/A' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
        
        @if(!$loop->last)
        <div class="page-break"></div>
        @endif
        @endforeach
    @else
        <div class="section">
            <p>No retired employees found.</p>
        </div>
    @endif

    <div class="footer">
        <p>Report generated by {{ $report->generatedBy->name ?? 'System' }} on {{ date('F j, Y g:i A') }}</p>
    </div>
</body>
</html>