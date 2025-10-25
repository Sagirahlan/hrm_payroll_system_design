<!DOCTYPE html>
<html>
<head>
    <title>Retired Employees Report</title>
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
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
        }
        .header p {
            margin: 5px 0 0 0;
            font-size: 14px;
        }
        .summary-section {
            background-color: #f0f0f0;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .section {
            margin-bottom: 20px;
        }
        .section-title {
            background-color: #f0f0f0;
            padding: 8px;
            font-weight: bold;
            border: 1px solid #ddd;
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
        }
        th {
            background-color: #f8f8f8;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
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