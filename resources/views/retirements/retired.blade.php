@extends('layouts.app')

@section('title', 'Retired Employees')

@section('content')
<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-success text-white">
            <h5>List of Retired Employees</h5>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Employee ID</th>
                        <th>Name</th>
                        <th>Expected Retirement Date</th>
                        <th>Salary scale</th>
                        <th>Gratuity (Estimate)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($retiredEmployees as $index => $employee)
                        @php
                            $payroll = $employee->payrollRecords->sortByDesc('created_at')->first();
                            $salary = $payroll?->gross_salary ?? 0;
                            $years = \Carbon\Carbon::now()->diffInYears($employee->date_of_first_appointment);
                            $gratuity = $salary * 0.1 * $years;
                        @endphp
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $employee->employee_id }}</td>
                            <td>{{ $employee->first_name }} {{ $employee->surname }}</td>
                            <td>{{ \Carbon\Carbon::parse($employee->expected_retirement_date)->format('Y-m-d') }}</td>
                            {{ $employee->salaryScale->scale_name ?? 'N/A' }}</td>
                            <td>₦{{ number_format($gratuity, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="d-flex justify-content-center">
                {{ $retiredEmployees->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>
@endsection
