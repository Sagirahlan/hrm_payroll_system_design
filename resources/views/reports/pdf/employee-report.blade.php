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
            <p>Report Type: {{ ucfirst($data['report_type'] ?? 'comprehensive') }}</p>
        </div>

        <!-- Personal Information -->
        <div class="flex-row">
            @if(isset($data['employee_info']))
            <div class="card" style="max-width: 370px;">
                <h2>Personal Information</h2>
                <table class="info-table">
                    <tr><th>Employee ID</th><td>{{ $data['employee_info']['employee_id'] ?? 'N/A' }}</td></tr>
                    <tr><th>Full Name</th><td>{{ $data['employee_info']['full_name'] ?? 'N/A' }}</td></tr>
                    <tr><th>First Name</th><td>{{ $data['employee_info']['first_name'] ?? 'N/A' }}</td></tr>
                    <tr><th>Middle Name</th><td>{{ $data['employee_info']['middle_name'] ?? 'N/A' }}</td></tr>
                    <tr><th>Surname</th><td>{{ $data['employee_info']['surname'] ?? 'N/A' }}</td></tr>
                    <tr><th>Gender</th><td>{{ $data['employee_info']['gender'] ?? 'N/A' }}</td></tr>
                    <tr><th>Date of Birth</th><td>{{ $data['employee_info']['date_of_birth'] ?? 'N/A' }}</td></tr>
                    <tr><th>State of Origin</th><td>{{ $data['employee_info']['state_of_origin'] ?? 'N/A' }}</td></tr>
                    <tr><th>LGA</th><td>{{ $data['employee_info']['lga'] ?? 'N/A' }}</td></tr>
                    <tr><th>Nationality</th><td>{{ $data['employee_info']['nationality'] ?? 'N/A' }}</td></tr>
                    <tr><th>NIN</th><td>{{ $data['employee_info']['nin'] ?? 'N/A' }}</td></tr>
                    <tr><th>Mobile No</th><td>{{ $data['employee_info']['mobile_no'] ?? 'N/A' }}</td></tr>
                    <tr><th>Email</th><td>{{ $data['employee_info']['email'] ?? 'N/A' }}</td></tr>
                    <tr><th>Address</th><td>{{ $data['employee_info']['address'] ?? 'N/A' }}</td></tr>
                </table>
            </div>
            
            <div class="card" style="max-width: 370px;">
                <h2>Employment Information</h2>
                <table class="info-table">
                    <tr><th>Date of First Appointment</th><td>{{ $data['employee_info']['date_of_first_appointment'] ?? 'N/A' }}</td></tr>
                    <tr><th>Cadre</th><td>{{ $data['employee_info']['cadre'] ?? 'N/A' }}</td></tr>
                    <tr><th>Registration No</th><td>{{ $data['employee_info']['reg_no'] ?? 'N/A' }}</td></tr>
                    <tr><th>Grade Level</th><td>{{ $data['employee_info']['grade_level'] ?? 'N/A' }}</td></tr>
                    <tr><th>Department</th><td>{{ $data['employee_info']['department'] ?? 'N/A' }}</td></tr>
                    <tr><th>Expected Next Promotion</th><td>{{ $data['employee_info']['expected_next_promotion'] ?? 'N/A' }}</td></tr>
                    <tr><th>Expected Retirement Date</th><td>{{ $data['employee_info']['expected_retirement_date'] ?? 'N/A' }}</td></tr>
                    <tr><th>Status</th><td>{{ ucfirst($data['employee_info']['status'] ?? 'N/A') }}</td></tr>
                    <tr><th>Highest Certificate</th><td>{{ $data['employee_info']['highest_certificate'] ?? 'N/A' }}</td></tr>
                    <tr><th>Grade Level Limit</th><td>{{ $data['employee_info']['grade_level_limit'] ?? 'N/A' }}</td></tr>
                    <tr><th>Appointment Type</th><td>{{ $data['employee_info']['appointment_type'] ?? 'N/A' }}</td></tr>
                    <tr><th>Service Years</th><td>{{ $data['employee_info']['service_years'] ?? 'N/A' }}</td></tr>
                </table>
            </div>
            
            @if(isset($data['payroll_info']))
            <div class="card" style="max-width: 320px;">
                <h2>Payroll Information</h2>
                <table class="info-table">
                    @if(isset($data['employee_info']['appointment_type']) && $data['employee_info']['appointment_type'] === 'Contract')
                        <tr><th>Amount</th><td>₦{{ number_format($data['payroll_info']['basic_salary'] ?? 0, 2) }}</td></tr>
                    @else
                        <tr><th>Basic Salary</th><td>₦{{ number_format($data['payroll_info']['basic_salary'] ?? 0, 2) }}</td></tr>
                    @endif
                    <tr><th>Net Salary</th><td>₦{{ number_format($data['payroll_info']['net_salary'] ?? 0, 2) }}</td></tr>
                    <tr><th>Bank Name</th><td>{{ $data['payroll_info']['bank_name'] ?? 'N/A' }}</td></tr>
                    <tr><th>Account Number</th><td>{{ $data['payroll_info']['account_number'] ?? 'N/A' }}</td></tr>
                </table>
            </div>
            @endif
        </div>
        @endif

        <!-- Retirement Information -->
        @if(isset($data['retirement_info']))
        <div class="flex-row">
            <div class="card" style="max-width: 320px;">
                <h2>Retirement Details</h2>
                <table class="info-table">
                    <tr><th>Expected Retirement Date</th><td>{{ $data['retirement_info']['expected_retirement_date'] ?? 'N/A' }}</td></tr>
                    <tr><th>Years to Retirement</th><td>{{ $data['retirement_info']['years_to_retirement'] ?? 'N/A' }}</td></tr>
                    <tr><th>Service Years</th><td>{{ $data['retirement_info']['service_years'] ?? 'N/A' }}</td></tr>
                    <tr>
                        <th>Retirement Status</th>
                        <td>
                            @if(($data['retirement_info']['retirement_status'] ?? '') === 'retired')
                                <span class="badge badge-warning">Pre-retirement</span>
                            @else
                                <span class="badge badge-success">Active Service</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        @endif

        <!-- Statistics -->
        @if(isset($data['statistics']) && !empty($data['statistics']))
        <div class="flex-row">
            <div class="card">
                <h2>Statistics</h2>
                <table class="info-table">
                    <tr><th>Total Service Years</th><td>{{ $data['statistics']['total_service_years'] ?? 'N/A' }}</td></tr>
                    <tr><th>Current Grade Level</th><td>{{ $data['statistics']['current_grade_level'] ?? 'N/A' }}</td></tr>
                    <tr><th>Total Lifetime Earnings</th><td>₦{{ number_format($data['statistics']['total_lifetime_earnings'] ?? 0, 2) }}</td></tr>
                    <tr><th>Total Monthly Deductions</th><td>₦{{ number_format($data['statistics']['total_monthly_deductions'] ?? 0, 2) }}</td></tr>
                    <tr><th>Total Monthly Allowances</th><td>₦{{ number_format($data['statistics']['total_monthly_allowances'] ?? 0, 2) }}</td></tr>
                    <tr><th>Active Disciplinary Cases</th><td>{{ $data['statistics']['active_disciplinary_cases'] ?? 0 }}</td></tr>
                    <tr><th>Last Promotion Date</th><td>{{ $data['statistics']['last_promotion_date'] ?? 'N/A' }}</td></tr>
                </table>
            </div>
        </div>
        @endif

        <!-- Deductions -->
        @if(isset($data['deductions']) && is_array($data['deductions']) && count($data['deductions']) > 0)
        <div class="flex-row">
            <div class="card">
                <h2>Deductions</h2>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Frequency</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data['deductions'] as $deduction)
                        <tr>
                            <td>{{ $deduction['deduction_type'] ?? 'N/A' }}</td>
                            <td>₦{{ number_format($deduction['amount'] ?? 0, 2) }}</td>
                            <td>{{ ucfirst($deduction['frequency'] ?? 'N/A') }}</td>
                            <td>{{ $deduction['start_date'] ?? 'N/A' }}</td>
                            <td>{{ $deduction['end_date'] ?? 'N/A' }}</td>
                            <td>{{ $deduction['description'] ?? 'N/A' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <!-- Additions / Allowances -->
        @if(isset($data['additions']) && is_array($data['additions']) && count($data['additions']) > 0)
        <div class="flex-row">
            <div class="card">
                <h2>Additions / Allowances</h2>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Frequency</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data['additions'] as $addition)
                        <tr>
                            <td>{{ $addition['addition_type'] ?? 'N/A' }}</td>
                            <td>₦{{ number_format($addition['amount'] ?? 0, 2) }}</td>
                            <td>{{ ucfirst($addition['frequency'] ?? 'N/A') }}</td>
                            <td>{{ $addition['start_date'] ?? 'N/A' }}</td>
                            <td>{{ $addition['end_date'] ?? 'N/A' }}</td>
                            <td>{{ $addition['description'] ?? 'N/A' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <!-- Payroll Records -->
        @if(isset($data['payroll_records']) && is_array($data['payroll_records']) && count($data['payroll_records']) > 0)
        <div class="flex-row">
            <div class="card" style="flex: 2 1 100%;">
                <h2>Recent Payroll Records</h2>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Payroll ID</th>
                            <th>Basic Salary</th>
                            <th>Total Deductions</th>
                            <th>Total Additions</th>
                            <th>Net Salary</th>
                            <th>Payment Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data['payroll_records'] as $record)
                        <tr>
                            <td>{{ $record['payroll_id'] ?? 'N/A' }}</td>
                            <td>₦{{ number_format($record['basic_salary'] ?? 0, 2) }}</td>
                            <td>₦{{ number_format($record['total_deductions'] ?? 0, 2) }}</td>
                            <td>₦{{ number_format($record['total_additions'] ?? 0, 2) }}</td>
                            <td>₦{{ number_format($record['net_salary'] ?? 0, 2) }}</td>
                            <td>{{ $record['payment_date'] ?? 'N/A' }}</td>
                            <td>{{ ucfirst($record['status'] ?? 'N/A') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <!-- Promotions/Demotions -->
        @if(isset($data['promotion_data']) && is_array($data['promotion_data']) && count($data['promotion_data']) > 0)
        <div class="flex-row">
            <div class="card" style="flex: 2 1 100%;">
                <h2>Promotions/Demotions</h2>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Promotion Date</th>
                            <th>Type</th>
                            <th>From Grade</th>
                            <th>To Grade</th>
                            <th>Effective Date</th>
                            <th>Approving Authority</th>
                            <th>Status</th>
                            <th>Reason</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data['promotion_data'] as $promotion)
                        <tr>
                            <td>{{ $promotion['promotion_date'] ?? 'N/A' }}</td>
                            <td>{{ ucfirst($promotion['promotion_type'] ?? 'promotion') }}</td>
                            <td>{{ $promotion['from_grade'] ?? 'N/A' }}</td>
                            <td>{{ $promotion['to_grade'] ?? 'N/A' }}</td>
                            <td>{{ $promotion['effective_date'] ?? 'N/A' }}</td>
                            <td>{{ $promotion['approving_authority'] ?? 'N/A' }}</td>
                            <td>{{ ucfirst($promotion['status'] ?? 'N/A') }}</td>
                            <td>{{ $promotion['reason'] ?? 'N/A' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <!-- Disciplinary Records -->
        @if(isset($data['disciplinary_records']) && is_array($data['disciplinary_records']) && count($data['disciplinary_records']) > 0)
        <div class="flex-row">
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
                            <td>{{ $record['action_type'] ?? 'N/A' }}</td>
                            <td>{{ $record['description'] ?? 'N/A' }}</td>
                            <td>{{ $record['action_date'] ?? 'N/A' }}</td>
                            <td>{{ ucfirst($record['status'] ?? 'N/A') }}</td>
                            <td>{{ $record['resolution'] ?? 'N/A' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
        <div class="footer">
            HR & Payroll System<br>
            Report generated automatically on {{ date('Y-m-d H:i:s') }}.
        </div>
    </div>
</body>
</html>
