@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card border-0 shadow-lg" style="background: linear-gradient(135deg, #e0f7fa 0%, #ffffff 100%);">
        <!-- Header Section -->
        <div class="card-header d-flex justify-content-between align-items-center" style="background: #00bcd4;">
            <h4 class="mb-0 text-white font-weight-bold">
                <i class="fas fa-users me-2"></i>Employees Management
            </h4>
            <div>
                <a href="{{ route('employees.create') }}" class="btn btn-light btn-sm rounded-pill me-2 font-weight-bold shadow-sm">
                    <i class="fas fa-plus me-1"></i>Add Employee
                </a>
                <div class="btn-group">
                    <button type="button" class="btn btn-light btn-sm rounded-pill dropdown-toggle font-weight-bold shadow-sm" data-bs-toggle="dropdown">
                        <i class="fas fa-download me-1"></i>Export
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('employees.export.pdf') }}">
                            <i class="fas fa-file-pdf me-2 text-danger"></i>Export to PDF
                        </a></li>
                        <li><a class="dropdown-item" href="{{ route('employees.export.excel') }}">
                            <i class="fas fa-file-excel me-2 text-success"></i>Export to Excel
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="#" onclick="exportFiltered('pdf')">
                            <i class="fas fa-filter me-2 text-info"></i>Export Filtered (PDF)
                        </a></li>
                        <li><a class="dropdown-item" href="#" onclick="exportFiltered('excel')">
                            <i class="fas fa-filter me-2 text-info"></i>Export Filtered (Excel)
                        </a></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="card-body p-4">
            <!-- Success Message -->
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Import Section -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm" style="background: #f8f9fa;">
                        <div class="card-body">
                            <form action="{{ route('employees.import') }}" method="POST" enctype="multipart/form-data" class="row g-3 align-items-center">
                                @csrf
                                <div class="col-auto">
                                    <label for="import_file" class="form-label mb-0 fw-bold">
                                        <i class="fas fa-upload me-2"></i>Import Employees
                                    </label>
                                </div>
                                <div class="col-md-4">
                                    <input type="file" class="form-control" name="import_file" id="import_file" accept=".xlsx,.xls" required>
                                </div>
                                <div class="col-auto">
                                    <button type="submit" class="btn btn-primary btn-sm rounded-pill fw-bold shadow-sm">
                                        <i class="fas fa-cloud-upload-alt me-1"></i>Import
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Advanced Search and Filter Section -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header d-flex justify-content-between align-items-center" style="background: #f1f8ff;">
                            <h6 class="mb-0 fw-bold text-primary">
                                <i class="fas fa-search me-2"></i>Search & Filter Options
                            </h6>
                            <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#advancedFilters">
                                <i class="fas fa-chevron-down"></i>
                            </button>
                        </div>
                        <div class="card-body">
                            <form method="GET" action="{{ route('employees.index') }}" id="filterForm">
                                <!-- Quick Search Row -->
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                                            <input type="text" name="search" class="form-control" 
                                                   placeholder="Search by name, ID, email, phone..." 
                                                   value="{{ request('search') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex gap-2">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-search me-1"></i>Search
                                            </button>
                                            <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary">
                                                <i class="fas fa-times me-1"></i>Clear
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Advanced Filters (Collapsible) -->
                                <div class="collapse {{ request()->hasAny(['department', 'cadre', 'status', 'gender', 'appointment_type_id', 'state_of_origin', 'age_from', 'age_to']) ? 'show' : '' }}" id="advancedFilters">
                                    <div class="row g-3">
                                        <!-- Row 1 -->
                                        <div class="col-md-3">
                                            <label class="form-label fw-bold">Department</label>
                                            <select name="department" class="form-select">
                                                <option value="">All Departments</option>
                                                @foreach($departments as $dept)
                                                    <option value="{{ $dept->department_id }}" 
                                                            {{ request('department') == $dept->department_id ? 'selected' : '' }}>
                                                        {{ $dept->department_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label fw-bold">Cadre</label>
                                            <select name="cadre" class="form-select">
                                                <option value="">All Cadres</option>
                                                @foreach($cadres as $cadre)
                                                    <option value="{{ $cadre->cadre_id }}" 
                                                            {{ request('cadre') == $cadre->cadre_id ? 'selected' : '' }}>
                                                        {{ $cadre->cadre_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label fw-bold">Status</label>
                                            <select name="status" class="form-select">
                                                <option value="">All Statuses</option>
                                                @foreach($statuses as $status)
                                                    <option value="{{ $status }}" 
                                                            {{ request('status') == $status ? 'selected' : '' }}>
                                                        {{ $status }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label fw-bold">Gender</label>
                                            <select name="gender" class="form-select">
                                                <option value="">All Genders</option>
                                                @foreach($genders as $gender)
                                                    <option value="{{ $gender }}" 
                                                            {{ request('gender') == $gender ? 'selected' : '' }}>
                                                        {{ $gender }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row g-3 mt-2">
                                        <!-- Row 2 -->
                                        <div class="col-md-3">
                                            <label class="form-label fw-bold">Appointment Type</label>
                                            <select name="appointment_type_id" class="form-select">
                                                <option value="">All Types</option>
                                                @foreach($appointmentTypes as $type)
                                                    <option value="{{ $type->id }}" 
                                                            {{ request('appointment_type_id') == $type->id ? 'selected' : '' }}>
                                                        {{ $type->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label fw-bold">State of Origin</label>
                                            <select name="state_of_origin" class="form-select">
                                                <option value="">All States</option>
                                                @foreach($states as $state)
                                                    <option value="{{ $state->name }}" 
                                                            {{ request('state_of_origin') == $state->name ? 'selected' : '' }}>
                                                        {{ $state->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label fw-bold">Grade Level</label>
                                            <select name="grade_level_id" class="form-select">
                                                <option value="">All Grade Levels</option>
                                                @foreach($gradeLevels as $level)
                                                    <option value="{{ $level->id }}" 
                                                            {{ request('grade_level_id') == $level->id ? 'selected' : '' }}>
                                                        {{ $level->name }} / {{ $level->step_level }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label fw-bold">Results Per Page</label>
                                            <select name="per_page" class="form-select">
                                                <option value="10" {{ request('per_page') == '10' ? 'selected' : '' }}>10</option>
                                                <option value="25" {{ request('per_page') == '25' ? 'selected' : '' }}>25</option>
                                                <option value="50" {{ request('per_page') == '50' ? 'selected' : '' }}>50</option>
                                                <option value="100" {{ request('per_page') == '100' ? 'selected' : '' }}>100</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row g-3 mt-2">
                                        <!-- Row 3 - Date Ranges -->
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Age Range</label>
                                            <div class="row">
                                                <div class="col-6">
                                                    <input type="number" name="age_from" class="form-control" 
                                                           placeholder="From" min="18" max="70" 
                                                           value="{{ request('age_from') }}">
                                                </div>
                                                <div class="col-6">
                                                    <input type="number" name="age_to" class="form-control" 
                                                           placeholder="To" min="18" max="70" 
                                                           value="{{ request('age_to') }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Appointment Date Range</label>
                                            <div class="row">
                                                <div class="col-6">
                                                    <input type="date" name="appointment_from" class="form-control" 
                                                           value="{{ request('appointment_from') }}">
                                                </div>
                                                <div class="col-6">
                                                    <input type="date" name="appointment_to" class="form-control" 
                                                           value="{{ request('appointment_to') }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mt-3">
                                        <div class="col-12 d-flex gap-2">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-filter me-1"></i>Apply Filters
                                            </button>
                                            <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary">
                                                <i class="fas fa-undo me-1"></i>Reset All
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Results Summary and Sorting -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <p class="mb-0 text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        Showing {{ $employees->firstItem() ?? 0 }} to {{ $employees->lastItem() ?? 0 }} 
                        of {{ $employees->total() }} results
                        @if(request()->hasAny(['search', 'department', 'cadre', 'status']))
                            <span class="badge bg-info ms-2">Filtered</span>
                        @endif
                    </p>
                </div>
                <div class="col-md-6 text-end">
                    <div class="d-flex justify-content-end align-items-center gap-2">
                        <label class="form-label mb-0 fw-bold">Sort by:</label>
                        <select class="form-select" style="width: auto;" onchange="changeSorting(this.value)">
                            <option value="created_at|desc" {{ request('sort_by') == 'created_at' && request('sort_order') == 'desc' ? 'selected' : '' }}>
                                Newest First
                            </option>
                            <option value="first_name|asc" {{ request('sort_by') == 'first_name' && request('sort_order') == 'asc' ? 'selected' : '' }}>
                                Name A-Z
                            </option>
                            <option value="first_name|desc" {{ request('sort_by') == 'first_name' && request('sort_order') == 'desc' ? 'selected' : '' }}>
                                Name Z-A
                            </option>
                            <option value="date_of_first_appointment|desc" {{ request('sort_by') == 'date_of_first_appointment' && request('sort_order') == 'desc' ? 'selected' : '' }}>
                                Latest Appointment
                            </option>
                            <option value="expected_retirement_date|asc" {{ request('sort_by') == 'expected_retirement_date' && request('sort_order') == 'asc' ? 'selected' : '' }}>
                                Earliest Retirement
                            </option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Employee Table -->
            <div class="table-responsive">
                <table class="table table-striped table-bordered align-middle shadow-sm" style="background: #fff; border-radius: 12px;">
                    <thead style="background: #b2ebf2;">
                        <tr>
                            <th>#</th>
                            <th>Staff no</th>
                            <th>Photo</th>
                            <th>Name</th>
                            <th>Department</th>
                            <th>Cadre</th>
                            <th>Appointment Type</th>
                            <th>Status</th>
                            <th>Contact</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($employees as $employee)
                            <tr>
                                <td>{{ $loop->iteration + ($employees->firstItem() ? $employees->firstItem() - 1 : 0) }}</td>
                                <td>
                                    <span class="badge bg-secondary">{{ $employee->reg_no }}</span>
                                </td>
                                <td>
                                    @if($employee->photo_path)
                                        <img src="{{ asset('storage/' . $employee->photo_path) }}" 
                                             alt="{{ $employee->first_name }}" 
                                             class="rounded-circle" 
                                             style="width: 40px; height: 40px; object-fit: cover;">
                                    @else
                                        <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center text-white" 
                                             style="width: 40px; height: 40px;">
                                            {{ substr($employee->first_name, 0, 1) }}{{ substr($employee->surname, 0, 1) }}
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div>
                                        <strong>{{ $employee->first_name }} {{ $employee->surname }}</strong>
                                        @if($employee->middle_name)
                                            <br><small class="text-muted">{{ $employee->middle_name }}</small>
                                        @endif
                                    </div>
                                </td>
                                <td>{{ $employee->department->department_name ?? 'N/A' }}</td>
                                <td>{{ $employee->cadre->cadre_name ?? 'N/A' }}</td>
                                <td>{{ $employee->appointmentType->name ?? 'N/A' }}</td>
                                <td>
                                    <span class="badge {{ 
                                        $employee->status == 'Active' ? 'bg-success' : 
                                        ($employee->status == 'Suspended' ? 'bg-warning' : 
                                        ($employee->status == 'Retired' ? 'bg-info' : 'bg-dark'))
                                    }}">
                                        {{ $employee->status }}
                                    </span>
                                </td>
                                <td>
                                    <small>
                                        <i class="fas fa-phone me-1"></i>{{ $employee->mobile_no }}<br>
                                        @if($employee->email)
                                            <i class="fas fa-envelope me-1"></i>{{ $employee->email }}
                                        @endif
                                    </small>
                                </td>
                                <td>
                                    @if(auth()->user()->hasPermissionTo('manage_employees'))
                                        <div class="dropdown">
                                            <button class="btn btn-secondary btn-sm rounded-pill dropdown-toggle" 
                                                    type="button" data-bs-toggle="dropdown">
                                                Actions
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('employees.show', $employee) }}">
                                                        <i class="fas fa-eye me-2"></i>View
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('employees.edit', $employee) }}">
                                                        <i class="fas fa-edit me-2"></i>Edit
                                                    </a>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <a href="#" class="dropdown-item text-danger" onclick="deleteEmployee({{ $employee->employee_id }}, '{{ $employee->first_name }} {{ $employee->surname }}')">
                                                        <i class="fas fa-trash me-2"></i>Delete
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    @else
                                        <a class="btn btn-outline-secondary btn-sm rounded-pill" href="{{ route('employees.show', $employee) }}">
                                            <i class="fas fa-eye me-1"></i>View
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-users fa-3x mb-3"></i>
                                        <h5>No employees found</h5>
                                        <p>Try adjusting your search criteria or add new employees.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4 d-flex justify-content-between align-items-center">
                <div>
                    {{ $employees->withQueryString()->links('pagination::bootstrap-5') }}
                </div>
                <div class="text-muted">
                    <small>
                        Total: {{ $employees->total() }} employees
                        @if(request()->hasAny(['search', 'department', 'cadre', 'status']))
                            | <span class="text-info">Filtered results</span>
                        @endif
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function changeSorting(value) {
    const [sortBy, sortOrder] = value.split('|');
    const url = new URL(window.location);
    url.searchParams.set('sort_by', sortBy);
    url.searchParams.set('sort_order', sortOrder);
    window.location.href = url.toString();
}

function exportFiltered(format) {
    const form = document.getElementById('filterForm');
    const formData = new FormData(form);
    
    // Build the export URL with all current filter parameters
    const exportUrl = new URL('{{ route("employees.export.filtered") }}', window.location.origin);
    
    // Add all form parameters to the URL
    for (let [key, value] of formData.entries()) {
        if (value) {
            exportUrl.searchParams.append(key, value);
        }
    }
    
    // Add the format parameter
    exportUrl.searchParams.set('format', format);
    
    // Open the export URL
    window.open(exportUrl.toString(), '_blank');
}

// Auto-submit form when filters change (optional)
document.addEventListener('DOMContentLoaded', function() {
    console.log('Employee index page loaded');
    const filterInputs = document.querySelectorAll('#filterForm select:not([name="per_page"]), #filterForm input[type="date"], #filterForm input[type="number"]');
    
    filterInputs.forEach(input => {
        input.addEventListener('change', function() {
            // Optional: Auto-submit on change
            // document.getElementById('filterForm').submit();
        });
    });
});

// Clear individual filters
function clearFilter(filterName) {
    const input = document.querySelector(`[name="${filterName}"]`);
    if (input) {
        input.value = '';
        document.getElementById('filterForm').submit();
    }
}

// Delete employee function
function deleteEmployee(employeeId, employeeName) {
    if (confirm(`Are you sure you want to delete employee ${employeeName}?`)) {
        const reason = prompt('Please provide a reason for deleting this employee:');
        if (reason !== null && reason.trim() !== '') {
            // Create form dynamically
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/employees/${employeeId}`;
            
            // Add CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = csrfToken;
            form.appendChild(csrfInput);
            
            // Add method field
            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';
            form.appendChild(methodInput);
            
            // Add delete reason
            const reasonInput = document.createElement('input');
            reasonInput.type = 'hidden';
            reasonInput.name = 'delete_reason';
            reasonInput.value = reason.trim();
            form.appendChild(reasonInput);
            
            // Submit form
            document.body.appendChild(form);
            form.submit();
        }
    }
}
</script>

@endsection