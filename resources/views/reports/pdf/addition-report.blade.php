<!DOCTYPE html>
<html>
<head>
    <title>Addition Report</title>
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
    </style>
</head>
<body>
    <div class="header">
        <h1>Addition Report</h1>
        <p>Generated on {{ date('F j, Y') }}</p>
    </div>

    @if(is_string($data))
        @php
            $decodedData = json_decode($data, true);
        @endphp
    @else
        @php
            $decodedData = $data;
        @endphp
    @endif
    
    @if(isset($decodedData['addition_type']) && isset($decodedData['additions']))
        <div class="section">
            <div class="section-title">{{ $decodedData['addition_type'] }}</div>
            
            @if(count($decodedData['additions']) > 0)
            <table>
                <thead>
                    <tr>
                        <th>Employee ID</th>
                        <th>Employee Name</th>
                        <th>Amount</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($decodedData['additions'] as $addition)
                    <tr>
                        <td>{{ $addition['employee']['employee_id'] ?? 'N/A' }}</td>
                        <td>{{ ($addition['employee']['first_name'] ?? '') . ' ' . ($addition['employee']['surname'] ?? '') }}</td>
                        <td class="text-right">₦{{ number_format($addition['amount'] ?? 0, 2) }}</td>
                        <td class="text-center">{{ $addition['start_date'] ? date('Y-m-d', strtotime($addition['start_date'])) : 'N/A' }}</td>
                        <td class="text-center">{{ $addition['end_date'] ? date('Y-m-d', strtotime($addition['end_date'])) : 'N/A' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <p>No additions found for this type.</p>
            @endif
        </div>
    @else
        @foreach($decodedData as $additionGroup)
        <div class="section">
            <div class="section-title">{{ $additionGroup['addition_type'] }}</div>
            
            @if(count($additionGroup['additions']) > 0)
            <table>
                <thead>
                    <tr>
                        <th>Employee ID</th>
                        <th>Employee Name</th>
                        <th>Amount</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($additionGroup['additions'] as $addition)
                    <tr>
                        <td>{{ $addition['employee']['employee_id'] ?? 'N/A' }}</td>
                        <td>{{ ($addition['employee']['first_name'] ?? '') . ' ' . ($addition['employee']['surname'] ?? '') }}</td>
                        <td class="text-right">₦{{ number_format($addition['amount'] ?? 0, 2) }}</td>
                        <td class="text-center">{{ $addition['start_date'] ? date('Y-m-d', strtotime($addition['start_date'])) : 'N/A' }}</td>
                        <td class="text-center">{{ $addition['end_date'] ? date('Y-m-d', strtotime($addition['end_date'])) : 'N/A' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <p>No additions found for this type.</p>
            @endif
        </div>
        
        @if(!$loop->last)
        <div class="page-break"></div>
        @endif
        @endforeach
    @endif

    <div class="footer">
        <p>Report generated by {{ $report->generatedBy->name ?? 'System' }} on {{ date('F j, Y g:i A') }}</p>
    </div>
</body>
</html>