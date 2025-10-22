<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Employee Master Report</title>
    <style>
        @page {
            size: A4;
            margin: 10mm;
        }
        
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            font-size: 6pt;
            line-height: 1;
        }
        
        .page-container {
            width: 210mm;
            min-height: 297mm;
            padding: 1mm;
            margin: 0 auto;
            background: white;
            box-sizing: border-box;
        }
        
        .header {
            text-align: center;
            margin-bottom: 3px;
            border-bottom: 1.5px solid #333;
            padding-bottom: 2px;
        }
        
        .report-title {
            font-size: 10pt;
            font-weight: bold;
            margin-bottom: 1px;
            color: #1a1a1a;
        }
        
        .generated-date {
            font-size: 5.5pt;
            color: #666;
        }
        
        .summary {
            background-color: #f5f5f5;
            padding: 1px 4px;
            margin-bottom: 3px;
            border-left: 2px solid #007bff;
            font-size: 5.5pt;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 4.8pt;
        }
        
        th {
            background-color: #2c3e50;
            color: white;
            padding: 0.4px 0.2px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #1a252f;
            font-size: 4.8pt;
        }
        
        td {
            border: 1px solid #ddd;
            padding: 0.4px 0.2px;
            text-align: left;
            overflow-wrap: break-word;
            word-wrap: break-word;
        }
        
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        tr:hover {
            background-color: #f0f0f0;
        }
        
        .currency {
            text-align: right;
            font-family: 'Courier New', monospace;
        }
        
        .center {
            text-align: center;
        }
        
        .footer {
            margin-top: 6px;
            padding-top: 3px;
            border-top: 1px solid #ddd;
            font-size: 5pt;
            color: #666;
            text-align: center;
        }
        
        @media print {
            .page-container {
                width: 100%;
                padding: 0;
            }
            
            body {
                margin: 0;
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
                    <td class="center">{{ $employee['years_of_service'] }}</td>
                    <td class="currency">₦{{ number_format($employee['basic_salary'], 2) }}</td>
                    <td style="font-size: 4pt;">{{ $employee['email'] }}</td>
                    <td style="font-size: 4pt;">{{ $employee['mobile_no'] }}</td>
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