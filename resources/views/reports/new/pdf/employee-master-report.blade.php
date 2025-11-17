<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Employee Master Report</title>
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
            overflow-wrap: break-word;
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
    <div class="page-container">
        <div class="header">
            <div class="report-title">Employee Master Report</div>
            <div class="generated-date">Generated on: {{ now()->format('F j, Y g:i A') }}</div>
        </div>

        <div class="summary">
            <strong>Total Employees:</strong> {{ $data['total_employees'] }}
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 4%;">Emp. ID</th>
                    <th style="width: 14%;">Full Name</th>
                    <th style="width: 10%;">Department</th>
                    <th style="width: 8%;">Cadre</th>
                    <th class="center" style="width: 5%;">Grade</th>
                    <th class="center" style="width: 4%;">Step</th>
                    <th class="center" style="width: 6%;">Status</th>
                    <th class="center" style="width: 8%;">App. Type</th>
                    <th class="center" style="width: 5%;">YOS</th>
                    <th style="width: 8%;">Basic Salary</th>
                    <th style="width: 18%;">Email</th>
                    <th style="width: 18%;">Mobile</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['employees'] as $employee)
                <tr>
                    <td>{{ $employee['employee_id'] }}</td>
                    <td>{{ $employee['full_name'] }}</td>
                    <td>{{ $employee['department'] }}</td>
                    <td>{{ $employee['cadre'] }}</td>
                    <td class="center">{{ $employee['grade_level'] }}</td>
                    <td class="center">{{ $employee['step'] }}</td>
                    <td class="center">{{ $employee['status'] }}</td>
                    <td class="center">{{ $employee['appointment_type'] }}</td>
                    <td class="center">{{ $employee['years_of_service'] }}</td>
                    <td class="currency">₦{{ number_format($employee['basic_salary'], 2) }}</td>
                    <td style="font-size: 8pt;">{{ $employee['email'] }}</td>
                    <td style="font-size: 8pt;">{{ $employee['mobile_no'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        
        <div class="footer">
            Employee Master Report | Confidential Document | Page 1
        </div>
    </div>
</body>
</html>