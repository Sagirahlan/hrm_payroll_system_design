<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Employee Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            margin: 0;
            padding: 0;
            background: #f7f7f7;
        }
        .container {
            width: 98%;
            margin: 10px auto;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px #e0e0e0;
            padding: 18px 18px 10px 18px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #007bff;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .header h1 {
            margin: 0;
            color: #007bff;
            font-size: 22px;
            letter-spacing: 1px;
        }
        .header p {
            margin: 2px 0;
            color: #555;
            font-size: 12px;
        }
        .flex-row {
            display: flex;
            flex-wrap: wrap;
            gap: 18px;
            margin-bottom: 10px;
        }
        .card {
            background: #fafbfc;
            border: 1px solid #e3e3e3;
            border-radius: 6px;
            flex: 1 1 320px;
            min-width: 300px;
            margin-bottom: 0;
            box-sizing: border-box;
            padding: 10px 14px 8px 14px;
        }
        .card h2 {
            font-size: 14px;
            margin: 0 0 8px 0;
            color: #333;
            border-left: 4px solid #007bff;
            background: #f5f7fa;
            padding: 5px 0 5px 8px;
        }
        .info-table, .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
        }
        .info-table th, .info-table td,
        .data-table th, .data-table td {
            border: 1px solid #e0e0e0;
            padding: 5px 7px;
            text-align: left;
        }
        .info-table th {
            background: #f3f6fa;
            width: 38%;
            font-weight: 600;
        }
        .info-table td {
            background: #fff;
        }
        .data-table th {
            background: #f3f6fa;
            font-weight: 600;
            font-size: 11px;
        }
        .data-table td {
            background: #fff;
            font-size: 10px;
        }
        .badge {
            padding: 2px 7px;
            border-radius: 3px;
            font-size: 10px;
            color: #fff;
            display: inline-block;
        }
        .badge-success { background: #28a745; }
        .badge-warning { background: #ffc107; color: #212529; }
        .badge-danger { background: #dc3545; }
        .footer {
            margin-top: 18px;
            text-align: center;
            font-size: 10px;
            color: #888;
            border-top: 1px solid #e0e0e0;
            padding-top: 7px;
        }
        @media print {
            .container { box-shadow: none; border-radius: 0; }
            .card { page-break-inside: avoid; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>COMPREHENSIVE EMPLOYEE REPORT</h1>
            <p>Generated on: {{ date('Y-m-d H:i:s') }}</p>
            <p>Report Type: {{ ucfirst($data['report_type']) }}</p>
        </div>

        <div class="flex-row">
            @if(isset($data['employee_info']))
            <div class="card" style="max-width: 370px;">
                <h2>Personal Information</h2>
                <table class="info-table">
                    <tr><th>Employee ID</th><td>{{ $data['employee_info']['employee_id'] }}</td></tr>
                    <tr><th>Full Name</th><td>{{ $data['employee_info']['full_name'] }}</td></tr>
                    <tr><th>Date of Birth</th><td>{{ $data['employee_info']['date_of_birth'] ?? 'N/A' }}</td></tr>
                    <tr><th>Gender</th><td>{{ $data['employee_info']['gender'] }}</td></tr>
                    <tr><th>State of Origin</th><td>{{ $data['employee_info']['state_of_origin'] }}</td></tr>
                    <tr><th>LGA</th><td>{{ $data['employee_info']['lga'] }}</td></tr>
                    <tr><th>Nationality</th><td>{{ $data['employee_info']['nationality'] }}</td></tr>
                    <tr><th>Date of First Appointment</th><td>{{ $data['employee_info']['date_of_first_appointment'] ?? 'N/A' }}</td></tr>
                    <tr><th>Years of Service</th><td>{{ $data['employee_info']['service_years'] ?? 'N/A' }}</td></tr>
                    <tr><th>Grade Level Limit</th><td>{{ $data['employee_info']['grade_level_limit'] ?? 'N/A' }}</td></tr>
                    <tr><th>Department</th><td>{{ $data['employee_info']['department'] }}</td></tr>
                    <tr><th>Status</th><td>{{ ucfirst($data['employee_info']['status']) }}</td></tr>
                </table>
            </div>
            @endif

            @if(isset($data['payroll_info']))
            <div class="card" style="max-width: 320px;">
                <h2>Payroll Information</h2>
                <table class="info-table">
                    <tr><th>Basic Salary</th><td>₦{{ number_format($data['payroll_info']['basic_salary'], 2) }}</td></tr>
                    <tr><th>Gross Salary</th><td>₦{{ number_format($data['payroll_info']['gross_salary'], 2) }}</td></tr>
                    <tr><th>Net Salary</th><td>₦{{ number_format($data['payroll_info']['net_salary'], 2) }}</td></tr>
                    <tr><th>Bank Name</th><td>{{ $data['payroll_info']['bank_name'] }}</td></tr>
                    <tr><th>Account Number</th><td>{{ $data['payroll_info']['account_number'] }}</td></tr>
                </table>
            </div>
            @endif

            @if(isset($data['retirement_info']))
            <div class="card" style="max-width: 320px;">
                <h2>Retirement Details</h2>
                <table class="info-table">
                    <tr><th>Expected Retirement Date</th><td>{{ $data['retirement_info']['expected_retirement_date'] }}</td></tr>
                    <tr><th>Years to Retirement</th><td>{{ $data['retirement_info']['years_to_retirement'] }}</td></tr>
                    <tr><th>Service Years</th><td>{{ $data['retirement_info']['service_years'] }}</td></tr>
                    <tr>
                        <th>Retirement Status</th>
                        <td>
                            @if($data['retirement_info']['retirement_status'] === 'Pre-retirement')
                                <span class="badge badge-warning">Pre-retirement</span>
                            @else
                                <span class="badge badge-success">Active Service</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
            @endif
        </div>

        <div class="flex-row">
            @if(isset($data['deductions']) && count($data['deductions']) > 0)
            <div class="card">
                <h2>Deductions</h2>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Frequency</th>
                            <th>Start Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data['deductions'] as $deduction)
                        <tr>
                            <td>{{ $deduction['deduction_type'] ?? 'N/A' }}</td>
                            <td>₦{{ number_format($deduction['amount'], 2) }}</td>
                            <td>{{ ucfirst($deduction['frequency']) }}</td>
                            <td>{{ $deduction['start_date'] }}</td>
                            <td>{{ ucfirst($employee['status']) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

            @if(isset($data['additions']) && count($data['additions']) > 0)
            <div class="card">
                <h2>Additions / Allowances</h2>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Frequency</th>
                            <th>Start Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data['additions'] as $addition)
                        <tr>
                            <td>{{ $addition['addition_type'] ?? 'N/A' }}</td>
                            <td>₦{{ number_format($addition['amount'], 2) }}</td>
                            <td>{{ ucfirst($addition['frequency']) }}</td>
                            <td>{{ $addition['start_date'] }}</td>
                            <td>{{ ucfirst($employee['status']) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>

        <div class="flex-row">
            @if(isset($data['disciplinary_records']) && count($data['disciplinary_records']) > 0)
            <div class="card" style="flex: 2 1 100%;">
                <h2>Disciplinary Records</h2>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Offense</th>
                            <th>Action Taken</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data['disciplinary_records'] as $record)
                        <tr>
                            <td>{{ $record['action_type'] }}</td>
                            <td>{{ $record['description'] }}</td>
                            <td>{{ $record['action_date'] }}</td>
                            <td>{{ ucfirst($record['status']) }}</td>
                            <td>{{ $record['resolution'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>

        <div class="footer">
            HR & Payroll System<br>
            Report generated automatically on {{ date('Y-m-d H:i:s') }}.
        </div>
    </div>
</body>
</html>
