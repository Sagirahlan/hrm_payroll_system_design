@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="card border-primary shadow">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Manage Staff Deductions & Additions</h5>
        </div>
        <div class="card-body">
            <!-- Search and Filter Section -->
            <div class="card border-info mb-4 shadow">
                <div class="card-header" style="background-color: #17a2b8; color: white;">
                    <strong>Search & Filter</strong>
                    <button class="btn btn-sm btn-outline-light float-end" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse" aria-expanded="false">
                        <i class="fas fa-filter"></i> Toggle Filters
                    </button>
                </div>
                <div class="collapse show" id="filterCollapse">
                    <div class="card-body">
                        <form method="GET" action="{{ route('payroll.adjustments.manage') }}" class="mb-3">
                            <div class="row g-3">
                                <!-- Search -->
                                <div class="col-md-3">
                                    <label for="search" class="form-label">Search</label>
                                    <input type="text" name="search" id="search" class="form-control"
                                           placeholder="Search by name, Staff No, or reg no..."
                                           value="{{ request()->get('search') }}">
                                </div>

                                <!-- Department Filter -->
                                <div class="col-md-3">
                                    <label for="department_id" class="form-label">Department</label>
                                    <select name="department_id" id="department_id" class="form-select">
                                        <option value="">All Departments</option>
                                        @foreach($departments as $department)
                                            <option value="{{ $department->department_id }}"
                                                    {{ request()->get('department_id') == $department->department_id ? 'selected' : '' }}>
                                                {{ $department->department_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Status Filter -->
                                <div class="col-md-3">
                                    <label for="employee_status" class="form-label">Status</label>
                                    <select name="employee_status" id="employee_status" class="form-select">
                                        <option value="">All Statuses</option>
                                        <option value="Active" {{ request()->get('employee_status') == 'Active' ? 'selected' : '' }}>Active</option>
                                        <option value="Suspended" {{ request()->get('employee_status') == 'Suspended' ? 'selected' : '' }}>Suspended</option>
                                    </select>
                                </div>

                                <!-- Sort By -->
                                <div class="col-md-3">
                                    <label for="sort_by" class="form-label">Sort By</label>
                                    <select name="sort_by" id="sort_by" class="form-select">
                                        <option value="staff_no" {{ request()->get('sort_by') == 'staff_no' ? 'selected' : '' }}>Staff No</option>
                                        <option value="first_name" {{ request()->get('sort_by') == 'first_name' ? 'selected' : '' }}>First Name</option>
                                        <option value="surname" {{ request()->get('sort_by') == 'surname' ? 'selected' : '' }}>Surname</option>
                                    </select>
                                </div>

                                <!-- Sort Direction -->
                                <div class="col-md-12">
                                    <div class="d-flex justify-content-end">
                                        <button type="submit" class="btn btn-primary me-2">
                                            <i class="fas fa-search"></i> Apply Filters
                                        </button>
                                        <a href="{{ route('payroll.adjustments.manage') }}" class="btn btn-secondary">
                                            <i class="fas fa-times"></i> Clear
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Results Info -->
            <div class="mb-3">
                <p class="text-muted mb-0">
                    Showing {{ $employees->firstItem() }} to {{ $employees->lastItem() }} of {{ $employees->total() }} staff
                </p>
            </div>

            <!-- Staff Table -->
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-primary">
                        <tr>
                            <th>Staff No</th>
                            <th>Name</th>
                            <th>Department</th>
                            <th>Grade Level</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($employees as $employee)
                            <tr>
                                <td>{{ $employee->staff_no ?? $employee->employee_id }}</td>
                                <td>{{ $employee->first_name }} {{ $employee->surname }}</td>
                                <td>{{ $employee->department->department_name ?? 'N/A' }}</td>
                                <td>{{ $employee->gradeLevel->name ?? 'N/A' }}</td>
                                <td>
                                    <span class="badge
                                        @if($employee->status === 'Active') bg-success
                                        @elseif($employee->status === 'Suspended') bg-warning text-dark
                                        @else bg-secondary @endif">
                                        {{ $employee->status }}
                                    </span>
                                </td>
                                <td>
                                    @can('manage_payroll_adjustments')
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-primary dropdown-toggle" type="button" id="actionsDropdown{{ $employee->employee_id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                            Manage
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="actionsDropdown{{ $employee->employee_id }}">
                                            <li>
                                                <a class="dropdown-item" href="{{ route('payroll.deductions.show', $employee->employee_id) }}">
                                                    <i class="fas fa-minus-circle"></i> Manage Deductions
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="{{ route('payroll.additions.show', $employee->employee_id) }}">
                                                    <i class="fas fa-plus-circle"></i> Manage Additions
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                    @else
                                    <span class="text-muted">No permissions</span>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">No staff found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    <p class="text-muted mb-0">
                        Showing {{ $employees->firstItem() }} to {{ $employees->lastItem() }} of {{ $employees->total() }} staff
                    </p>
                </div>
                <div>
                    {{ $employees->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection