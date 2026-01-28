@extends('layouts.app')

@section('title', 'Retired Employees')

@section('content')
<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">List of Retired Employees</h5>
            <a href="{{ route('retirements.index') }}" class="btn btn-light btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Back to Approaching Retirement
            </a>
        </div>
        <div class="card-body">
            <!-- Search and Filter Form -->
            <form method="GET" class="mb-3">
                <div class="row g-2">
                    <div class="col-md-6">
                        <input type="text" name="search" class="form-control" placeholder="Search Staff No or name" value="{{ request('search') }}">
                    </div>
                    <div class="col-md-4">
                        <input type="date" name="retirement_date" class="form-control" value="{{ request('retirement_date') }}" placeholder="Retirement Date">
                    </div>
                    <div class="col-md-2">
                        <div class="btn-group w-100" role="group">
                            <button class="btn btn-outline-secondary" type="submit">Filter</button>
                            <a href="{{ route('retirements.retired') }}" class="btn btn-outline-secondary">Clear</a>
                        </div>
                    </div>
                </div>
            </form>
            
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Staff No</th>
                            <th>Name</th>
                            <th>Date of Birth</th>
                            <th>Age</th>
                            <th>Years of Service</th>
                            <th>Rank</th>
                            <th>Grade Level/Step</th>
                            <th>Department</th>
                            <th>Retirement Date</th>
                            <th>Retire Reason</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($retiredEmployees as $employee)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $employee->staff_no }}</td>
                                <td>{{ $employee->first_name }} {{ $employee->surname }}</td>
                                <td>{{ $employee->date_of_birth ? \Carbon\Carbon::parse($employee->date_of_birth)->format('Y-m-d') : 'N/A' }}</td>
                                <td>{{ $employee->date_of_birth ? \Carbon\Carbon::parse($employee->date_of_birth)->age : 'N/A' }}</td>
                                <td>{{ $employee->date_of_first_appointment ? round(\Carbon\Carbon::parse($employee->date_of_first_appointment)->diffInYears(\Carbon\Carbon::parse($employee->retirement->retirement_date ?? now()))) : 'N/A' }}</td>
                                <td>{{ $employee->rank ? $employee->rank->name : 'N/A' }}</td>
                                <td>{{ $employee->gradeLevel ? $employee->gradeLevel->name : 'N/A' }}-{{ $employee->step ? $employee->step->name : 'N/A' }}</td>
                                <td>{{ $employee->department ? $employee->department->department_name : 'N/A' }}</td>
                                <td>
                                    @if($employee->retirement && $employee->retirement->retirement_date)
                                        {{ \Carbon\Carbon::parse($employee->retirement->retirement_date)->format('Y-m-d') }}
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>
                                    @if($employee->retirement && $employee->retirement->retire_reason)
                                        {{ $employee->retirement->retire_reason }}
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('employees.show', $employee->employee_id) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="text-center">No retired employees found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-center">
                {{ $retiredEmployees->appends(request()->query())->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>
@endsection
