<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Employee Report</title>
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
                box-shadow: none;
            }
            .no-print {
                display: none;
            }
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            padding: 10px;
            font-size: 11px;
            line-height: 1.4;
        }

        .page {
            width: 210mm;
            min-height: 297mm;
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
            margin-bottom: 20px;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }

        .report-title {
            font-size: 18px;
            font-weight: bold;
            color: #333;
            margin-bottom: 4px;
            letter-spacing: 0.5px;
        }

        .generated-date, .report-type {
            font-size: 10px;
            color: #666;
            margin-bottom: 2px;
            line-height: 1.3;
        }

        .report-type {
            color: #667eea;
            font-weight: 600;
        }

        .content {
            flex: 1;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .flex-row {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 20px;
        }

        .card {
            flex: 1;
            min-width: 300px;
            background: #fff;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            padding: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .card h2 {
            font-size: 13px;
            color: #333;
            margin-bottom: 10px;
            border-bottom: 1px solid #f0f0f0;
            padding-bottom: 5px;
        }

        .info-table, .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
        }

        .info-table th, .info-table td,
        .data-table th, .data-table td {
            border: 1px solid #ddd;
            padding: 4px 6px;
            text-align: left;
            vertical-align: top;
        }

        .info-table th {
            background: #f8f9fa;
            font-weight: bold;
            width: 35%;
            color: #555;
        }

        .data-table thead th {
            background: #667eea;
            color: white;
            font-weight: bold;
            text-align: center;
            white-space: nowrap;
        }

        .data-table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .data-table tbody tr:hover {
            background-color: #f0f7ff;
        }

        .amount {
            text-align: right;
            font-weight: 600;
            color: #667eea;
        }

        .status-badge {
            display: inline-block;
            padding: 3px 6px;
            border-radius: 4px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .badge-success {
            background: #d4edda;
            color: #155724;
        }

        .badge-warning {
            background: #fff3cd;
            color: #856404;
        }

        .badge-danger {
            background: #f8d7da;
            color: #721c24;
        }

        .full-width {
            flex: 2 1 100%;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #999;
            font-style: italic;
            font-size: 11px;
        }

        .footer {
            border-top: 1px solid #ddd;
            padding-top: 10px;
            margin-top: auto;
            font-size: 9px;
            color: #888;
            text-align: center;
        }

        @media (max-width: 800px) {
            .page {
                width: 100%;
                height: auto;
                margin: 5px 0;
                padding: 10px;
            }
            .flex-row {
                flex-direction: column;
            }
            .card {
                min-width: auto;
            }
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="header">
            <div class="report-title">COMPREHENSIVE EMPLOYEE REPORT</div>
            <div class="generated-date">Generated on: {{ date('Y-m-d H:i:s') }}</div>
            <div class="report-type">Report Type: {{ ucfirst($data['report_type'] ?? 'comprehensive') }}</div>
        </div>

        <div class="content">
            @if(isset($data['employee_info']))
            <!-- Personal & Employment Information Row -->
            <div class="flex-row">
                <div class="card">
                    <h2>Personal Information</h2>
                    <table class="info-table">
                        <tr><th>Employee ID</th><td>{{ $data['employee_info']['employee_id'] ?? 'N/A' }}</td></tr>
                        <tr><th>Full Name</th><td>{{ $data['employee_info']['full_name'] ?? 'N/A' }}</td></tr>
                        <tr><th>First Name</th><td>{{ $data['employee_info']['first_name'] ?? 'N/A' }}</td></tr>
                        <tr><th>Middle Name</th><td>{{ $data['employee_info']['middle_name'] ?? 'N/A' }}</td></tr>
                        <tr><th>Surname</th><td>{{ $data['employee_info']['surname'] ?? 'N/A' }}</td></tr>
                        <tr><th>Gender</th><td>{{ $data['employee_info']['gender'] ?? 'N/A' }}</td></tr>
                        <tr><th>Date of Birth</th><td>{{ $data['employee_info']['date_of_birth'] ?? 'N/A' }}</td></tr>
                        <tr><th>State </th><td>{{ $data['employee_info']['state_of_origin'] ?? 'N/A' }}</td></tr>
                        <tr><th>LGA</th><td>{{ $data['employee_info']['lga'] ?? 'N/A' }}</td></tr>
                        <tr><th>Ward</th><td>{{ $data['employee_info']['ward'] ?? 'N/A' }}</td></tr>
                        <tr><th>Nationality</th><td>{{ $data['employee_info']['nationality'] ?? 'N/A' }}</td></tr>
                        <tr><th>NIN</th><td>{{ $data['employee_info']['nin'] ?? 'N/A' }}</td></tr>
                        <tr><th>Mobile No</th><td>{{ $data['employee_info']['mobile_no'] ?? 'N/A' }}</td></tr>
                        <tr><th>Email</th><td>{{ $data['employee_info']['email'] ?? 'N/A' }}</td></tr>
                        <tr><th>Address</th><td>{{ $data['employee_info']['address'] ?? 'N/A' }}</td></tr>
                    </table>
                </div>

                <div class="card">
                    <h2>Employment Information</h2>
                    <table class="info-table">
                        <tr><th>Date of First Appointment</th><td>{{ $data['employee_info']['date_of_first_appointment'] ?? 'N/A' }}</td></tr>
                        <tr><th>Cadre</th><td>{{ $data['employee_info']['cadre'] ?? 'N/A' }}</td></tr>
                        <tr><th>staff No</th><td>{{ $data['employee_info']['staff_no'] ?? 'N/A' }}</td></tr>
                        <tr><th>Grade Level</th><td>{{ $data['employee_info']['grade_level'] ?? 'N/A' }}</td></tr>
                        <tr><th>Department</th><td>{{ $data['employee_info']['department'] ?? 'N/A' }}</td></tr>
                        <tr><th>Expected Next Promotion</th><td>{{ $data['employee_info']['expected_next_promotion'] ?? 'N/A' }}</td></tr>
                        <tr><th>Expected Retirement Date</th><td>{{ $data['employee_info']['expected_retirement_date'] ?? 'N/A' }}</td></tr>
                        <tr><th>Status</th><td>{{ ucfirst($data['employee_info']['status'] ?? 'N/A') }}</td></tr>
                        <tr><th>Highest Certificate</th><td>{{ $data['employee_info']['highest_certificate'] ?? 'N/A' }}</td></tr>
                        <tr><th>Appointment Type</th><td>{{ $data['employee_info']['appointment_type'] ?? 'N/A' }}</td></tr>
                        <tr><th>Pay Point</th><td>{{ $data['employee_info']['pay_point'] ?? 'N/A' }}</td></tr>
                        <tr><th>Service Years</th><td>{{ $data['employee_info']['service_years'] ?? 'N/A' }}</td></tr>
                    </table>
                </div>

                @if(isset($data['payroll_info']))
                <div class="card">
                    <h2>Payroll Information</h2>
                    <table class="info-table">
                        @if(isset($data['employee_info']['appointment_type']) && $data['employee_info']['appointment_type'] === 'Contract')
                            <tr><th>Amount</th><td class="amount">₦{{ number_format($data['payroll_info']['basic_salary'] ?? 0, 2) }}</td></tr>
                        @else
                            <tr><th>Basic Salary</th><td class="amount">₦{{ number_format($data['payroll_info']['basic_salary'] ?? 0, 2) }}</td></tr>
                        @endif
                        <tr><th>Net Salary</th><td class="amount">₦{{ number_format($data['payroll_info']['net_salary'] ?? 0, 2) }}</td></tr>
                        <tr><th>Bank Name</th><td>{{ $data['payroll_info']['bank_name'] ?? 'N/A' }}</td></tr>
                        <tr><th>Account Number</th><td>{{ $data['payroll_info']['account_number'] ?? 'N/A' }}</td></tr>
                    </table>
                </div>
                @endif
            </div>

            <!-- Retirement Information Row -->
            @if(isset($data['retirement_info']))
            <div class="flex-row">
                <div class="card">
                    <h2>Retirement Details</h2>
                    <table class="info-table">
                        <tr><th>Expected Retirement Date</th><td>{{ $data['retirement_info']['expected_retirement_date'] ?? 'N/A' }}</td></tr>
                        <tr><th>Years to Retirement</th><td>{{ $data['retirement_info']['years_to_retirement'] ?? 'N/A' }}</td></tr>
                        <tr><th>Service Years</th><td>{{ $data['retirement_info']['service_years'] ?? 'N/A' }}</td></tr>
                        <tr>
                            <th>Retirement Status</th>
                            <td>
                                @if(($data['retirement_info']['retirement_status'] ?? '') === 'retired')
                                    <span class="status-badge badge-warning">Pre-retirement</span>
                                @else
                                    <span class="status-badge badge-success">Active Service</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            @endif

            <!-- Statistics Row -->
            @if(isset($data['statistics']) && !empty($data['statistics']))
            <div class="flex-row">
                <div class="card full-width">
                    <h2>Statistics</h2>
                    <table class="info-table">         
                        <tr><th>Total Lifetime Earnings</th><td class="amount">₦{{ number_format($data['statistics']['total_lifetime_earnings'] ?? 0, 2) }}</td></tr>
                        <tr><th>Total Monthly Deductions</th><td class="amount">₦{{ number_format($data['statistics']['total_monthly_deductions'] ?? 0, 2) }}</td></tr>
                        <tr><th>Total Monthly Allowances</th><td class="amount">₦{{ number_format($data['statistics']['total_monthly_allowances'] ?? 0, 2) }}</td></tr>
                        <tr><th>Active Disciplinary Cases</th><td>{{ $data['statistics']['active_disciplinary_cases'] ?? 0 }}</td></tr>
                        <tr><th>Last Promotion Date</th><td>{{ $data['statistics']['last_promotion_date'] ?? 'N/A' }}</td></tr>
                    </table>
                </div>
            </div>
            @endif

            @endif

            <!-- Deductions Table -->
            @if(isset($data['deductions']) && is_array($data['deductions']) && count($data['deductions']) > 0)
            <div class="flex-row">
                <div class="card full-width">
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
                                <td class="amount">₦{{ number_format($deduction['amount'] ?? 0, 2) }}</td>
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

            <!-- Additions / Allowances Table -->
            @if(isset($data['additions']) && is_array($data['additions']) && count($data['additions']) > 0)
            <div class="flex-row">
                <div class="card full-width">
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
                                <td class="amount">₦{{ number_format($addition['amount'] ?? 0, 2) }}</td>
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

            <!-- Payroll Records Table -->
            @if(isset($data['payroll_records']) && is_array($data['payroll_records']) && count($data['payroll_records']) > 0)
            <div class="flex-row">
                <div class="card full-width">
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
                                <td class="amount">₦{{ number_format($record['basic_salary'] ?? 0, 2) }}</td>
                                <td class="amount">₦{{ number_format($record['total_deductions'] ?? 0, 2) }}</td>
                                <td class="amount">₦{{ number_format($record['total_additions'] ?? 0, 2) }}</td>
                                <td class="amount">₦{{ number_format($record['net_salary'] ?? 0, 2) }}</td>
                                <td>{{ $record['payment_date'] ?? 'N/A' }}</td>
                                <td>{{ ucfirst($record['status'] ?? 'N/A') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            <!-- Promotions/Demotions Table -->
            @if(isset($data['promotion_data']) && is_array($data['promotion_data']) && count($data['promotion_data']) > 0)
            <div class="flex-row">
                <div class="card full-width">
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

            <!-- Disciplinary Records Table -->
            @if(isset($data['disciplinary_records']) && is_array($data['disciplinary_records']) && count($data['disciplinary_records']) > 0)
            <div class="flex-row">
                <div class="card full-width">
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

            @if(!isset($data['employee_info']) && !isset($data['deductions']) && !isset($data['additions']) && !isset($data['payroll_records']) && !isset($data['promotion_data']) && !isset($data['disciplinary_records']))
            <div class="empty-state">
                No data available for this report.
            </div>
            @endif
        </div>

        <div class="footer">
            Kundi HR & Payroll System<br>
            Report generated automatically on {{ date('Y-m-d H:i:s') }}.
        </div>
    </div>
</body>
</html>