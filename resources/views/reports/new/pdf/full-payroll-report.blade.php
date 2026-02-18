<!DOCTYPE html>
<html>
<head>
    <title>{{ $reportType }}</title>
    <style>
        @page {
            margin: 10mm;
            size: landscape;
        }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 8px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8px;
        }
        th, td {
            border: 1px solid #000;
            padding: 3px;
            text-align: center;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h2, .header h3 {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Katsina State Water Board</h2>
        <h3>{{ $reportType }}</h3>
        <p>Period: {{ $data['period'] }} | Generated: {{ $data['generated_date'] }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="3%">S/N</th>
                <th width="5%">Staff No</th>
                <th width="12%">Name</th>
                <th width="8%">Rank</th>
                <th width="8%">Basic Salary</th>
                
                @foreach($data['addition_types'] as $type)
                    <th>{{ $type }}</th>
                @endforeach
                <th>Total Additions</th>
                <th>Gross Salary</th>

                @foreach($data['deduction_types'] as $type)
                    <th>{{ $type }}</th>
                @endforeach
                <th>Total Deductions</th>
                <th>Net Salary</th>
            </tr>
        </thead>
        <tbody>
            @php $sn = 1; @endphp
            @foreach($data['payroll_records'] as $record)
                <tr>
                    <td>{{ $sn++ }}</td>
                    <td>{{ $record['staff_no'] }}</td>
                    <td class="text-left">{{ $record['name'] }}</td>
                    <td class="text-left">{{ $record['rank'] }}</td>
                    <td class="text-right">{{ number_format($record['basic_salary'], 2) }}</td>

                    {{-- Additions --}}
                    @foreach($data['addition_types'] as $type)
                        <td class="text-right">{{ number_format($record['additions'][$type] ?? 0, 2) }}</td>
                    @endforeach
                    <td class="text-right">{{ number_format($record['total_additions'], 2) }}</td>
                    <td class="text-right">{{ number_format($record['gross_salary'], 2) }}</td>

                    {{-- Deductions --}}
                    @foreach($data['deduction_types'] as $type)
                        <td class="text-right">{{ number_format($record['deductions'][$type] ?? 0, 2) }}</td>
                    @endforeach
                    <td class="text-right">{{ number_format($record['total_deductions'], 2) }}</td>
                    <td class="text-right"><strong>{{ number_format($record['net_salary'], 2) }}</strong></td>
                </tr>
            @endforeach
            
            {{-- Totals Row --}}
            <tr style="background-color: #f9f9f9; font-weight: bold;">
                <td colspan="4" class="text-right">Totals</td>
                <td class="text-right">{{ number_format(collect($data['payroll_records'])->sum('basic_salary'), 2) }}</td>

                @foreach($data['addition_types'] as $type)
                    <td class="text-right">
                        {{ number_format(collect($data['payroll_records'])->sum(function($rec) use ($type) { return $rec['additions'][$type] ?? 0; }), 2) }}
                    </td>
                @endforeach
                <td class="text-right">{{ number_format(collect($data['payroll_records'])->sum('total_additions'), 2) }}</td>
                <td class="text-right">{{ number_format(collect($data['payroll_records'])->sum('gross_salary'), 2) }}</td>

                @foreach($data['deduction_types'] as $type)
                    <td class="text-right">
                        {{ number_format(collect($data['payroll_records'])->sum(function($rec) use ($type) { return $rec['deductions'][$type] ?? 0; }), 2) }}
                    </td>
                @endforeach
                <td class="text-right">{{ number_format(collect($data['payroll_records'])->sum('total_deductions'), 2) }}</td>
                <td class="text-right">{{ number_format(collect($data['payroll_records'])->sum('net_salary'), 2) }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html>
