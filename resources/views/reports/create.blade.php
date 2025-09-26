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

            <!-- Retired Employees Reports Section -->
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-warning text-white">
                    <h5 class="mb-0">Retired Employees Reports</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('reports.bulk_generate') }}" method="POST">
                        @csrf
                        <input type="hidden" name="report_type" value="retired_employees">
                        <div class="row">
                            <div class="col-md-5 mb-3">
                                <label class="form-label">Retired Employees Report</label>
                                <p class="text-muted">Generate a comprehensive report of all retired employees</p>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="retired_export_format" class="form-label">Export Format</label>
                                <select name="export_format" id="retired_export_format" class="form-select">
                                    <option value="PDF">PDF</option>
                                    <option value="Excel">Excel</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-warning">
                                    <i class="fas fa-file-export"></i> Generate Retired Report
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Bulk Reports Section -->
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Bulk Reports for additions/deductions</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('reports.bulk_generate') }}" method="POST" id="bulk-report-form">
                        @csrf
                        <input type="hidden" name="employee_ids" id="employee_ids" value="">
                        <div class="row">
                            <div class="col-md-5 mb-3">
                                <label for="bulk_report_type" class="form-label">Bulk Report Type</label>
                                <select name="report_type" id="bulk_report_type" class="form-select">
                                    <option value="">-- Select Report Type --</option>
                                    <optgroup label="Individual Deduction Reports">
                                        @foreach(App\Models\DeductionType::all() as $deductionType)
                                            <option value="deduction_{{ $deductionType->id }}">Deduction: {{ $deductionType->name }}</option>
                                        @endforeach
                                    </optgroup>
                                    <optgroup label="Individual Addition Reports">
                                        @foreach(App\Models\AdditionType::all() as $additionType)
                                            <option value="addition_{{ $additionType->id }}">Addition: {{ $additionType->name }}</option>
                                        @endforeach
                                    </optgroup>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="bulk_export_format" class="form-label">Export Format</label>
                                <select name="export_format" id="bulk_export_format" class="form-select">
                                    <option value="PDF">PDF</option>
                                    <option value="Excel">Excel</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-success" id="bulk-generate-btn" disabled>
                                    <i class="fas fa-file-export"></i> Generate Bulk Reports
                                </button>
                            </div>
                        </div>
                        
                        <!-- Date Range Selection (for deduction/addition reports) -->
                        <div class="row date-range-section-bulk" id="date_range_section_bulk" style="display: none; margin-top: 15px;">
                            <div class="col-md-12">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6>Select Date Range for Report</h6>
                                        <div class="row">
                                            <div class="col-md-5">
                                                <label for="start_date_bulk" class="form-label">Start Date</label>
                                                <input type="date" name="start_date" id="start_date_bulk" class="form-control">
                                            </div>
                                            <div class="col-md-5">
                                                <label for="end_date_bulk" class="form-label">End Date</label>
                                                <input type="date" name="end_date" id="end_date_bulk" class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
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
                                <form action="{{ route('reports.generate') }}" method="POST" class="report-form">
                                    @csrf
                                    <input type="hidden" name="employee_id" value="{{ $employee->employee_id }}">
                                    <div class="mb-3">
                                        <label for="report_type_{{ $employee->employee_id }}" class="form-label">Report Type</label>
                                        <select name="report_type" id="report_type_{{ $employee->employee_id }}" class="form-select report-type-select">
                                            <option value="">-- Select Report Type --</option>
                                            <option value="comprehensive">Comprehensive Report</option>
                                            <option value="basic">Basic Information</option>
                                            <option value="disciplinary">Disciplinary Records</option>
                                            <option value="payroll">Payroll Information</option>
                                            <option value="retirement">Retirement Planning</option>
                                            <option value="deduction">Deduction Report</option>
                                            <option value="addition">Addition Report</option>
                                        </select>
                                    </div>
                                    
                                    <!-- Dynamic Deduction Type Select -->
                                    <div class="mb-3 deduction-type-section" id="deduction_type_section_{{ $employee->employee_id }}" style="display: none;">
                                        <label for="deduction_type_{{ $employee->employee_id }}" class="form-label">Deduction Type</label>
                                        <select name="deduction_type_id" id="deduction_type_{{ $employee->employee_id }}" class="form-select deduction-type-select">
                                            <option value="">-- Select Deduction Type --</option>
                                            @foreach(App\Models\DeductionType::all() as $deductionType)
                                                <option value="{{ $deductionType->id }}">{{ $deductionType->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    
                                    <!-- Dynamic Addition Type Select -->
                                    <div class="mb-3 addition-type-section" id="addition_type_section_{{ $employee->employee_id }}" style="display: none;">
                                        <label for="addition_type_{{ $employee->employee_id }}" class="form-label">Addition Type</label>
                                        <select name="addition_type_id" id="addition_type_{{ $employee->employee_id }}" class="form-select addition-type-select">
                                            <option value="">-- Select Addition Type --</option>
                                            @foreach(App\Models\AdditionType::all() as $additionType)
                                                <option value="{{ $additionType->id }}">{{ $additionType->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    
                                    <!-- Date Range Selection (for deduction/addition reports) -->
                                    <div class="mb-3 date-range-section" id="date_range_section_{{ $employee->employee_id }}" style="display: none;">
                                        <div class="card bg-light">
                                            <div class="card-body">
                                                <h6>Select Date Range for Report</h6>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <label for="start_date_{{ $employee->employee_id }}" class="form-label">Start Date</label>
                                                        <input type="date" name="start_date" id="start_date_{{ $employee->employee_id }}" class="form-control">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label for="end_date_{{ $employee->employee_id }}" class="form-label">End Date</label>
                                                        <input type="date" name="end_date" id="end_date_{{ $employee->employee_id }}" class="form-control">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="export_format_{{ $employee->employee_id }}" class="form-label">Export Format</label>
                                        <select name="export_format" id="export_format_{{ $employee->employee_id }}" class="form-select">
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

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle bulk report generation
        const bulkGenerateBtn = document.getElementById('bulk-generate-btn');
        const bulkReportType = document.getElementById('bulk_report_type');
        const bulkExportFormat = document.getElementById('bulk_export_format');
        const bulkForm = document.getElementById('bulk-report-form');
        const employeeIdsInput = document.getElementById('employee_ids');
        const dateRangeSectionBulk = document.getElementById('date_range_section_bulk');
        const startDateBulk = document.getElementById('start_date_bulk');
        const endDateBulk = document.getElementById('end_date_bulk');
        
        // Enable/disable bulk generate button based on selection
        bulkReportType.addEventListener('change', function() {
            bulkGenerateBtn.disabled = !this.value;
            
            // Show/hide date range section for deduction/addition reports
            if (this.value.startsWith('deduction_') || this.value.startsWith('addition_')) {
                if (dateRangeSectionBulk) dateRangeSectionBulk.style.display = 'block';
            } else {
                if (dateRangeSectionBulk) dateRangeSectionBulk.style.display = 'none';
            }
        });
        
        // Handle form submission for special report types
        bulkForm.addEventListener('submit', function(e) {
            const reportType = bulkReportType.value;
            
            // For individual deduction and addition reports, we don't need employee_ids
            if (reportType.startsWith('deduction_') || reportType.startsWith('addition_')) {
                // Remove employee_ids input for these report types
                employeeIdsInput.removeAttribute('name');
                
                // Add hidden inputs for date range if provided
                if (startDateBulk && startDateBulk.value) {
                    const startDateInput = document.createElement('input');
                    startDateInput.type = 'hidden';
                    startDateInput.name = 'start_date';
                    startDateInput.value = startDateBulk.value;
                    bulkForm.appendChild(startDateInput);
                }
                
                if (endDateBulk && endDateBulk.value) {
                    const endDateInput = document.createElement('input');
                    endDateInput.type = 'hidden';
                    endDateInput.name = 'end_date';
                    endDateInput.value = endDateBulk.value;
                    bulkForm.appendChild(endDateInput);
                }
            } else {
                // For other report types, ensure employee_ids input has a name
                employeeIdsInput.setAttribute('name', 'employee_ids');
                
                // Validate that at least one employee is selected
                if (!employeeIdsInput.value) {
                    e.preventDefault();
                    alert('Please select at least one employee for this report type.');
                    return false;
                }
            }
        });
        
        // Handle individual employee report forms
        const reportTypeSelects = document.querySelectorAll('.report-type-select');
        
        reportTypeSelects.forEach(function(select) {
            select.addEventListener('change', function() {
                const employeeId = this.id.replace('report_type_', '');
                const deductionSection = document.getElementById(`deduction_type_section_${employeeId}`);
                const additionSection = document.getElementById(`addition_type_section_${employeeId}`);
                const dateRangeSection = document.getElementById(`date_range_section_${employeeId}`);
                const deductionSelect = document.getElementById(`deduction_type_${employeeId}`);
                const additionSelect = document.getElementById(`addition_type_${employeeId}`);
                
                // Hide all sections initially
                if (deductionSection) deductionSection.style.display = 'none';
                if (additionSection) additionSection.style.display = 'none';
                if (dateRangeSection) dateRangeSection.style.display = 'none';
                
                // Show the appropriate section based on selection
                if (this.value === 'deduction') {
                    if (deductionSection) deductionSection.style.display = 'block';
                    if (additionSection) additionSection.style.display = 'none';
                    if (dateRangeSection) dateRangeSection.style.display = 'block';
                } else if (this.value === 'addition') {
                    if (additionSection) additionSection.style.display = 'block';
                    if (deductionSection) deductionSection.style.display = 'none';
                    if (dateRangeSection) dateRangeSection.style.display = 'block';
                } else {
                    // For all other options, hide all sections
                    if (deductionSection) deductionSection.style.display = 'none';
                    if (additionSection) additionSection.style.display = 'none';
                    if (dateRangeSection) dateRangeSection.style.display = 'none';
                }
            });
        });
        
        // Update form submission to handle dynamic report types
        const reportForms = document.querySelectorAll('.report-form');
        
        reportForms.forEach(function(form) {
            form.addEventListener('submit', function(e) {
                const reportTypeSelect = form.querySelector('.report-type-select');
                const deductionSection = form.querySelector('.deduction-type-section');
                const additionSection = form.querySelector('.addition-type-section');
                
                if (reportTypeSelect.value === 'deduction' && deductionSection && deductionSection.style.display !== 'none') {
                    const deductionTypeSelect = form.querySelector('.deduction-type-select');
                    if (deductionTypeSelect && deductionTypeSelect.value) {
                        // Change the report_type to include the deduction type ID
                        reportTypeSelect.value = `deduction_${deductionTypeSelect.value}`;
                    } else {
                        // If no deduction type selected, reset to just 'deduction'
                        reportTypeSelect.value = 'deduction';
                    }
                } else if (reportTypeSelect.value === 'addition' && additionSection && additionSection.style.display !== 'none') {
                    const additionTypeSelect = form.querySelector('.addition-type-select');
                    if (additionTypeSelect && additionTypeSelect.value) {
                        // Change the report_type to include the addition type ID
                        reportTypeSelect.value = `addition_${additionTypeSelect.value}`;
                    } else {
                        // If no addition type selected, reset to just 'addition'
                        reportTypeSelect.value = 'addition';
                    }
                }
            });
        });
    });
</script>
@endsection
