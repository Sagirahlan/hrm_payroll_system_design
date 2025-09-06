@extends('layouts.app')

@section('title', 'Eligible for Retirement')

@section('content')
<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0">Employees Eligible for Retirement</h5>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Employee ID</th>
                        <th>Name</th>
                        <th>Department</th>
                        <th>Age</th>
                        <th>Years of Service</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($eligibleEmployees as $employee)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $employee->employee_id }}</td>
                            <td>{{ $employee->first_name }} {{ $employee->surname }}</td>
                            <td>{{ $employee->department->department_name ?? 'N/A' }}</td>
                            <td>{{ \Carbon\Carbon::parse($employee->date_of_birth)->age }}</td>
                            <td>{{ \Carbon\Carbon::parse($employee->date_of_first_appointment)->diffInYears(\Carbon\Carbon::now()) }}</td>
                            <td>
                                <form action="{{ route('retirements.store') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="employee_id" value="{{ $employee->employee_id }}">
                                    <input type="hidden" name="retirement_date" value="{{ now()->toDateString() }}">
                                    <button type="submit" class="btn btn-success btn-sm">Retire</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">No employees are currently eligible for retirement.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection