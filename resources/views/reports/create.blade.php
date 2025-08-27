@extends('layouts.app')

@section('title', 'Generate Reports')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="bg-light p-4 rounded mb-4">
                <form method="GET" action="{{ route('reports.create') }}" id="filter-form">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="search" class="form-label">Search Employees</label>
                            <input type="text" id="search" name="search" class="form-control" placeholder="Search by name or ID..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="department" class="form-label">Department</label>
                            <select id="department" name="department" class="form-select">
                                <option value="">All Departments</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->department_id }}" {{ request('department') == $dept->department_id ? 'selected' : '' }}>
                                        {{ $dept->department_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select id="status" name="status" class="form-select">
                                <option value="">All Statuses</option>
                                <option value="Active" {{ request('status') == 'Active' ? 'selected' : '' }}>Active</option>
                                <option value="Suspended" {{ request('status') == 'Suspended' ? 'selected' : '' }}>Suspended</option>
                                <option value="Retired" {{ request('status') == 'Retired' ? 'selected' : '' }}>Retired</option>
                                <option value="Deceased" {{ request('status') == 'Deceased' ? 'selected' : '' }}>Deceased</option>
                            </select>
                        </div>
                        <div class="col-md-2 mb-3 d-flex align-items-end">
                            <div>
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="fas fa-search"></i> Search
                                </button>
                                <a href="{{ route('reports.create') }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Reset
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="row">
                @forelse($employees as $employee)
                    <div class="col-md-6">
                        <div class="card mb-4 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <img src="{{ asset('storage/' . $employee->photo_path) }}" alt="" class="rounded-circle me-3" style="width: 60px; height: 60px; object-fit: cover;">
                                    <div>
                                        <h5 class="mb-1">{{ $employee->first_name }} {{ $employee->surname }}</h5>
                                        <p class="mb-0 text-muted">{{ $employee->department->department_name ?? 'N/A' }}</p>
                                    </div>
                                </div>
                                <form action="{{ route('reports.generate') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="employee_id" value="{{ $employee->employee_id }}">
                                    <div class="mb-3">
                                        <label for="report_type" class="form-label">Report Type</label>
                                        <select name="report_type" class="form-select">
                                            <option value="">-- Select Report Type --</option>
                                            <option value="comprehensive">Comprehensive Report</option>
                                            <option value="basic">Basic Information</option>
                                            <option value="disciplinary">Disciplinary Records</option>
                                            <option value="payroll">Payroll Information</option>
                                            <option value="retirement">Retirement Planning</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="export_format" class="form-label">Export Format</label>
                                        <select name="export_format" class="form-select">
                                            <option value="PDF">PDF</option>
                                            <option value="Excel">Excel</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-file-export"></i> Generate Report
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="alert alert-info text-center">
                            No employees found matching your criteria.
                        </div>
                    </div>
                @endforelse
            </div>

            @if($employees->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $employees->appends(request()->query())->links('pagination::bootstrap-4') }}
            </div>
            @endif

            <div class="mt-3">
                <a href="{{ route('reports.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Reports
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
