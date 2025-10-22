@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    @can('manage_biometrics')
    <div class="card border-primary shadow">
        <div class="card-header" style="background-color: skyblue; color: white;">
            <h5 class="mb-0">Biometric Data</h5>
        </div>
        <div class="card-body">
            <!-- Search and Filter Form -->
            <div class="row mb-4">
                <div class="col-md-8">
                    <form method="GET" action="{{ route('biometrics.index') }}" class="row g-3">
                        <div class="col-md-8">
                            <input type="text" class="form-control" name="search" placeholder="Search by employee name or ID" value="{{ request('search') }}">
                        </div>
                        <div class="col-md-4">
                            <select name="status" class="form-select">
                                <option value="">All Statuses</option>
                                <option value="registered" {{ request('status') == 'registered' ? 'selected' : '' }}>Registered</option>
                                <option value="unregistered" {{ request('status') == 'unregistered' ? 'selected' : '' }}>Not Registered</option>
                            </select>
                        </div>
                        <div class="col-md-12 mt-2">
                            <button type="submit" class="btn btn-primary">Search</button>
                            <a href="{{ route('biometrics.index') }}" class="btn btn-secondary">Clear</a>
                        </div>
                    </form>
                </div>
                <div class="col-md-4 text-end">
                    @can('create_biometrics')
                    <a href="{{ route('biometrics.create') }}" class="btn btn-primary">Add Biometric Data</a>
                    @endcan
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="table-responsive">
                <table class="table table-striped table-bordered align-items-center mb-0">
                    <thead class="table-primary">
                        <tr>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Employee ID</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Employee Name</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Department</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Biometric Status</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Verification Status</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Verification Date</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($employees as $employee)
                            <tr>
                                <td>{{ $employee->employee_id }}</td>
                                <td class="fw-bold">{{ $employee->first_name }} {{ $employee->middle_name }} {{ $employee->surname }}</td>
                                <td>{{ $employee->department->department_name ?? 'N/A' }}</td>
                                <td>
                                    @if ($employee->biometricData)
                                        <span class="badge bg-success">Registered</span>
                                    @else
                                        <span class="badge bg-danger">Not Registered</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($employee->biometricData)
                                        <span class="badge bg-{{ $employee->biometricData->verification_status == 'Verified' ? 'success' : 'warning' }}">
                                            {{ $employee->biometricData->verification_status }}
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($employee->biometricData)
                                        <span class="badge bg-secondary">
                                            {{ $employee->biometricData->verification_date ?? 'N/A' }}
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    @can('create_biometrics')
                                    @if ($employee->biometricData)
                                        <span class="text-muted">Registered</span>
                                    @else
                                        <a href="{{ route('biometrics.create', ['employee_id' => $employee->employee_id]) }}" class="btn btn-sm btn-primary">Register</a>
                                    @endif
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">No employees found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3 d-flex justify-content-center">
                {{ $employees->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
    @else
    <div class="alert alert-warning">
        You don't have permission to manage biometric data.
    </div>
    @endcan
</div>
@endsection
