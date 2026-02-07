<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Retired Employees Summary Report</title>
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
            min-height: 297mm;
            margin: 10px auto;
            padding: 15mm;
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

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #1e40af;
            padding-bottom: 10px;
            position: relative;
            z-index: 1;
        }

        .logo {
            width: 80px;
            height: auto;
            margin-bottom: 8px;
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

        .summary-info {
            font-size: 12px;
            color: #1e40af;
            font-weight: 600;
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
            margin-top: 10px;
            font-size: 9px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
        }

        th {
            background-color: #1e40af;
            color: white;
            font-weight: bold;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .footer {
            border-top: 1px solid #ddd;
            padding-top: 10px;
            margin-top: auto;
            font-size: 9px;
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
            <img src="data:image/png;base64,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oooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooor/2Q==" class="logo" alt="KSWB Logo" />
            <div class="org-name">KATSINA STATE WATER BOARD</div>
            <div class="report-title">Retired Employees Summary Report</div>
            <div class="generated-date">Generated on: {{ $data['generated_date'] }}</div>
            <div class="summary-info">Total Retired Employees: {{ $data['total_retired_employees'] }}</div>
        </div>

        <div class="content">
            <table>
                <thead>
                    <tr>
                        <th>Staff ID</th>
                        <th>Name</th>
                        <th>Date of Birth</th>
                        <th>Age</th>
                        <th>Years of Service</th>
                        <th>Rank</th>
                        <th>Grade Level/Step</th>
                        <th>Department</th>
                        <th>Retirement Date</th>
                        <th>Retire Reason</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data['employees'] as $employee)
                        <tr>
                            <td>{{ $employee['employee_id'] }}</td>
                            <td>{{ $employee['name'] }}</td>
                            <td>{{ $employee['date_of_birth'] }}</td>
                            <td>{{ $employee['age'] }}</td>
                            <td>{{ $employee['years_of_service'] }}</td>
                            <td>{{ $employee['rank'] }}</td>
                            <td>{{ $employee['grade_level_step'] }}</td>
                            <td>{{ $employee['department'] }}</td>
                            <td>{{ $employee['retirement_date'] }}</td>
                            <td>{{ $employee['retire_reason'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" style="text-align: center;">No retired employees found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="footer">
            Katsina State Water Board - HR & Payroll Management System<br>
            Report generated automatically on {{ $data['generated_date'] }}.
        </div>
    </div>
</body>
</html>