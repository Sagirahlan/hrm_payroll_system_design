@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="card border-primary shadow">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Manage Employee Deductions & Additions</h5>
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
                                <div class="col-md-4">
                                    <label for="search" class="form-label">Search</label>
                                    <input type="text" name="search" id="search" class="form-control" 
                                           placeholder="Search by name, ID, or reg no..." 
                                           value="{{ request()->get('search') }}">
                                </div>
                                
                                <!-- Department Filter -->
                                <div class="col-md-4">
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
                                
                                <!-- Sort By -->
                                <div class="col-md-2">
                                    <label for="sort_by" class="form-label">Sort By</label>
                                    <select name="sort_by" id="sort_by" class="form-select">
                                        <option value="employee_id" {{ request()->get('sort_by') == 'employee_id' ? 'selected' : '' }}>Employee ID</option>
                                        <option value="first_name" {{ request()->get('sort_by') == 'first_name' ? 'selected' : '' }}>First Name</option>
                                        <option value="surname" {{ request()->get('sort_by') == 'surname' ? 'selected' : '' }}>Surname</option>
                                    </select>
                                </div>
                                
                                <!-- Sort Direction -->
                                <div class="col-md-2">
                                    <label for="sort_direction" class="form-label">Order</label>
                                    <select name="sort_direction" id="sort_direction" class="form-select">
                                        <option value="asc" {{ request()->get('sort_direction') == 'asc' ? 'selected' : '' }}>Ascending</option>
                                        <option value="desc" {{ request()->get('sort_direction') == 'desc' ? 'selected' : '' }}>Descending</option>
                                    </select>
                                </div>
                                
                                <!-- Submit Button -->
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
                    Showing {{ $employees->firstItem() }} to {{ $employees->lastItem() }} of {{ $employees->total() }} employees
                </p>
            </div>
            
            <!-- Employees Table -->
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-primary">
                        <tr>
                            <th>Employee ID</th>
                            <th>Name</th>
                            <th>Department</th>
                            <th>Grade Level</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($employees as $employee)
                            <tr>
                                <td>{{ $employee->employee_id }}</td>
                                <td>{{ $employee->first_name }} {{ $employee->surname }}</td>
                                <td>{{ $employee->department->department_name ?? 'N/A' }}</td>
                                <td>{{ $employee->gradeLevel->name ?? 'N/A' }}</td>
                                <td>
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
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">No active employees found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    <p class="text-muted mb-0">
                        Showing {{ $employees->firstItem() }} to {{ $employees->lastItem() }} of {{ $employees->total() }} employees
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