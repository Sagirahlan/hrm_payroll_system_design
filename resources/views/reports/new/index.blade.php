@extends('layouts.app')

@section('title', 'Comprehensive Reports')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Comprehensive Reports</h3>
                    <div class="btn-group">
                        <!-- <a href="{{ route('reports.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Generate individual Report
                        </a> -->
                        <a href="{{ route('reports.index') }}" class="btn btn-secondary">
                            <i class="fas fa-list"></i> View All Reports
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Employee Reports Section -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h4 class="text-primary mb-3"><i class="fas fa-users"></i> Employee Reports</h4>
                        </div>

                        <div class="col-md-4 mb-3">
                            <div class="card border-primary">
                                <div class="card-body">
                                    <h5 class="card-title">Employee Master Report</h5>
                                    <p class="card-text text-muted">Complete employee information with all details</p>
                                    <button class="btn btn-primary generate-report-btn"
                                            data-report-type="employee_master"
                                            data-title="Employee Master Report">
                                        Generate Report
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4 mb-3">
                            <div class="card border-info">
                                <div class="card-body">
                                    <h5 class="card-title">Employee Status Report</h5>
                                    <p class="card-text text-muted">Employee status breakdown by category</p>
                                    <button class="btn btn-info generate-report-btn"
                                            data-report-type="employee_status"
                                            data-title="Employee Status Report">
                                        Generate Report
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4 mb-3">
                            <!-- Empty column to maintain layout -->
                        </div>
                    </div>

                    <!-- Compensation Reports Section -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h4 class="text-primary mb-3"><i class="fas fa-money-bill-wave"></i> Compensation Reports</h4>
                        </div>

                        <div class="col-md-4 mb-3">
                            <div class="card border-warning">
                                <div class="card-body">
                                    <h5 class="card-title">Payroll Summary</h5>
                                    <p class="card-text text-muted">Payroll records summary by period</p>
                                    <button class="btn btn-warning generate-report-btn"
                                            data-report-type="payroll_summary"
                                            data-title="Payroll Summary Report">
                                        Generate Report
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4 mb-3">
                            <div class="card border-primary">
                                <div class="card-body">
                                    <h5 class="card-title">Payroll Journal</h5>
                                    <p class="card-text text-muted">Detailed payroll journal with deductions/additions</p>
                                    <button class="btn btn-primary generate-report-btn"
                                            data-report-type="payroll_journal"
                                            data-title="Payroll Journal Report">
                                        Generate Report
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4 mb-3">
                            <div class="card border-danger">
                                <div class="card-body">
                                    <h5 class="card-title">Deduction Summary</h5>
                                    <p class="card-text text-muted">All employee deductions by type</p>
                                    <button class="btn btn-danger generate-report-btn"
                                            data-report-type="deduction_summary"
                                            data-title="Deduction Summary Report">
                                        Generate Report
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4 mb-3">
                            <div class="card border-success">
                                <div class="card-body">
                                    <h5 class="card-title">Addition Summary</h5>
                                    <p class="card-text text-muted">All employee additions by type</p>
                                    <button class="btn btn-success generate-report-btn"
                                            data-report-type="addition_summary"
                                            data-title="Addition Summary Report">
                                        Generate Report
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Performance & Career Reports Section -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h4 class="text-primary mb-3"><i class="fas fa-chart-line"></i> Performance & Career Reports</h4>
                        </div>

                        <div class="col-md-4 mb-3">
                            <div class="card border-primary">
                                <div class="card-body">
                                    <h5 class="card-title">Promotion History</h5>
                                    <p class="card-text text-muted">All employee promotions with details</p>
                                    <button class="btn btn-primary generate-report-btn"
                                            data-report-type="promotion_history"
                                            data-title="Promotion History Report">
                                        Generate Report
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4 mb-3">
                            <div class="card border-warning">
                                <div class="card-body">
                                    <h5 class="card-title">Disciplinary Actions</h5>
                                    <p class="card-text text-muted">All disciplinary records by employee</p>
                                    <button class="btn btn-warning generate-report-btn"
                                            data-report-type="disciplinary"
                                            data-title="Disciplinary Action Report">
                                        Generate Report
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4 mb-3">
                            <div class="card border-info">
                                <div class="card-body">
                                    <h5 class="card-title">Retirement Planning</h5>
                                    <p class="card-text text-muted">Employees approaching retirement</p>
                                    <button class="btn btn-info generate-report-btn"
                                            data-report-type="retirement_planning"
                                            data-title="Retirement Planning Report">
                                        Generate Report
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Retirement-Specific Reports Section -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h4 class="text-primary mb-3"><i class="fas fa-calendar-alt"></i> Retirement-Specific Reports</h4>
                        </div>

                        <div class="col-md-4 mb-3">
                            <div class="card border-success">
                                <div class="card-body">
                                    <h5 class="card-title">Approaching Retirement (6 Months)</h5>
                                    <p class="card-text text-muted">Employees retiring within 6 months</p>
                                    <button class="btn btn-success generate-report-btn"
                                            data-report-type="retirement_6months"
                                            data-title="Retirement Planning Report (6 Months)">
                                        Generate Report
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Financial Reports Section -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h4 class="text-primary mb-3"><i class="fas fa-credit-card"></i> Financial Reports</h4>
                        </div>

                        <div class="col-md-4 mb-3">
                            <div class="card border-success">
                                <div class="card-body">
                                    <h5 class="card-title">Loan Status Report</h5>
                                    <p class="card-text text-muted">Complete loan tracking and status</p>
                                    <button class="btn btn-success generate-report-btn"
                                            data-report-type="loan_status"
                                            data-title="Loan Status Report">
                                        Generate Report
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Organizational Reports Section -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h4 class="text-primary mb-3"><i class="fas fa-building"></i> Organizational Reports</h4>
                        </div>

                        <div class="col-md-4 mb-3">
                            <div class="card border-primary">
                                <div class="card-body">
                                    <h5 class="card-title">Department Summary</h5>
                                    <p class="card-text text-muted">Employee breakdown by department</p>
                                    <button class="btn btn-primary generate-report-btn"
                                            data-report-type="department_summary"
                                            data-title="Department Summary Report">
                                        Generate Report
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4 mb-3">
                            <div class="card border-success">
                                <div class="card-body">
                                    <h5 class="card-title">Grade Level Summary</h5>
                                    <p class="card-text text-muted">Employee breakdown by grade level</p>
                                    <button class="btn btn-success generate-report-btn"
                                            data-report-type="grade_level_summary"
                                            data-title="Grade Level Summary Report">
                                        Generate Report
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Audit Reports Section -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h4 class="text-primary mb-3"><i class="fas fa-clipboard-list"></i> Audit Reports</h4>
                        </div>

                        <div class="col-md-4 mb-3">
                            <div class="card border-info">
                                <div class="card-body">
                                    <h5 class="card-title">Audit Trail Report</h5>
                                    <p class="card-text text-muted">System activity and audit logs</p>
                                    <button class="btn btn-info generate-report-btn"
                                            data-report-type="audit_trail"
                                            data-title="Audit Trail Report">
                                        Generate Report
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Generate Report Modal -->
    <div class="modal fade" id="generateReportModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Generate Report</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="generateReportForm" method="POST" action="{{ route('reports.comprehensive.generate') }}">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" id="report_type" name="report_type" value="">

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="export_format" class="form-label">Export Format</label>
                                <select class="form-select" id="export_format" name="export_format" required>
                                    <option value="">Select Format</option>
                                    <option value="PDF">PDF</option>
                                    <option value="Excel">Excel</option>
                                </select>
                            </div>
                        </div>

                        <!-- Filters will be dynamically added here -->
                        <div id="filters-section"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Generate Report</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const generateButtons = document.querySelectorAll('.generate-report-btn');
    const modal = document.getElementById('generateReportModal');
    const modalTitle = document.getElementById('modalTitle');
    const reportTypeInput = document.getElementById('report_type');
    const exportFormat = document.getElementById('export_format');
    const filtersSection = document.getElementById('filters-section');
    const form = document.getElementById('generateReportForm');

    generateButtons.forEach(button => {
        button.addEventListener('click', function() {
            const reportType = this.getAttribute('data-report-type');
            const title = this.getAttribute('data-title');

            modalTitle.textContent = title;
            reportTypeInput.value = reportType;

            // Clear previous filters
            filtersSection.innerHTML = '';

            // Add specific filters based on report type
            addFiltersForReportType(reportType);

            // Show modal
            const bsModal = new bootstrap.Modal(modal);
            bsModal.show();
        });
    });

    // Prevent default form submission and handle it with AJAX
    form.addEventListener('submit', function(e) {
        e.preventDefault(); // Prevent default form submission

        // Get form data
        const formData = new FormData(form);

        // Show loading state
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Generating...';
        submitBtn.disabled = true;

        // Send AJAX request
        fetch(form.getAttribute('action'), {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
            }
        })
        .then(response => {
            // Check if the response is JSON or HTML
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                return response.json();
            } else {
                // If it's not JSON, it's likely an error page, so try to get text and throw an error
                return response.text().then(text => {
                    console.error('Server returned HTML instead of JSON:', text);
                    throw new Error('Server error: Non-JSON response received. Check server logs.');
                });
            }
        })
        .then(data => {
            if (data.success) {
                // Close modal and show success message
                const bsModal = bootstrap.Modal.getInstance(modal);
                bsModal.hide();

                // Show success message
                alert('Report generated successfully!');

                // Optionally reload the page or redirect
                window.location.href = data.redirect || "{{ route('reports.comprehensive.index') }}";
            } else {
                // Show error message
                let errorMessage = data.message || 'Unknown error occurred';
                if (data.errors) {
                    // If there are validation errors, format them
                    errorMessage = 'Validation errors:\n';
                    Object.keys(data.errors).forEach(field => {
                        errorMessage += '- ' + data.errors[field].join(', ') + '\n';
                    });
                }
                alert('Error generating report: ' + errorMessage);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while generating the report: ' + error.message + '. Please check server logs for more details.');
        })
        .finally(() => {
            // Restore button state
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
    });

    function addFiltersForReportType(reportType) {
        switch(reportType) {
            case 'employee_master':
                filtersSection.innerHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            <label for="department_filter" class="form-label">Department</label>
                            <select class="form-select" id="department_filter" name="filters[department_id]">
                                <option value="">All Departments</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->department_id }}">{{ $dept->department_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="status_filter" class="form-label">Status</label>
                            <select class="form-select" id="status_filter" name="filters[status]">
                                <option value="">All Statuses</option>
                                <option value="Active">Active</option>
                                <option value="Suspended">Suspended</option>
                                <option value="Retired">Retired</option>
                                <option value="Deceased">Deceased</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label for="appointment_type_filter" class="form-label">Appointment Type</label>
                            <select class="form-select" id="appointment_type_filter" name="filters[appointment_type_id]">
                                <option value="">All Appointment Types</option>
                                @foreach($appointmentTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                `;
                break;

            case 'payroll_summary':
                filtersSection.innerHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            <label for="year_filter" class="form-label">Year</label>
                            <input type="number" class="form-control" id="year_filter" name="filters[year]"
                                   min="2000" max="{{ date('Y') }}" value="{{ date('Y') }}">
                        </div>
                        <div class="col-md-6">
                            <label for="month_filter" class="form-label">Month</label>
                            <select class="form-select" id="month_filter" name="filters[month]">
                                <option value="">All Months</option>
                                <option value="1">January</option>
                                <option value="2">February</option>
                                <option value="3">March</option>
                                <option value="4">April</option>
                                <option value="5">May</option>
                                <option value="6">June</option>
                                <option value="7">July</option>
                                <option value="8">August</option>
                                <option value="9">September</option>
                                <option value="10">October</option>
                                <option value="11">November</option>
                                <option value="12">December</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label for="appointment_type_filter" class="form-label">Appointment Type</label>
                            <select class="form-select" id="appointment_type_filter" name="filters[appointment_type_id]">
                                <option value="">All Appointment Types</option>
                                @foreach($appointmentTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="status_filter" class="form-label">Status</label>
                            <select class="form-select" id="status_filter" name="filters[status]">
                                <option value="">All Statuses</option>
                                <option value="Active">Active</option>
                                <option value="Suspended">Suspended</option>
                                <option value="Retired">Retired</option>
                                <option value="Deceased">Deceased</option>
                            </select>
                        </div>
                    </div>
                `;
                break;

            case 'payroll_journal':
                filtersSection.innerHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            <label for="year_filter" class="form-label">Year</label>
                            <input type="number" class="form-control" id="year_filter" name="filters[year]"
                                   min="2000" max="{{ date('Y') }}" value="{{ date('Y') }}">
                        </div>
                        <div class="col-md-6">
                            <label for="month_filter" class="form-label">Month</label>
                            <select class="form-select" id="month_filter" name="filters[month]">
                                <option value="">All Months</option>
                                <option value="1">January</option>
                                <option value="2">February</option>
                                <option value="3">March</option>
                                <option value="4">April</option>
                                <option value="5">May</option>
                                <option value="6">June</option>
                                <option value="7">July</option>
                                <option value="8">August</option>
                                <option value="9">September</option>
                                <option value="10">October</option>
                                <option value="11">November</option>
                                <option value="12">December</option>
                            </select>
                        </div>
                    </div>
                `;
                break;

            case 'deduction_summary':
                filtersSection.innerHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            <label for="deduction_type_filter" class="form-label">Deduction Type</label>
                            <select class="form-select" id="deduction_type_filter" name="filters[deduction_type_id]">
                                <option value="">All Deduction Types</option>
                                @foreach($deductionTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                `;
                break;

            case 'addition_summary':
                filtersSection.innerHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            <label for="addition_type_filter" class="form-label">Addition Type</label>
                            <select class="form-select" id="addition_type_filter" name="filters[addition_type_id]">
                                <option value="">All Addition Types</option>
                                @foreach($additionTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                `;
                break;

            case 'employee_status':
                // No additional filters needed for employee status report
                filtersSection.innerHTML = '';
                break;

            case 'loan_status':
                filtersSection.innerHTML = `
                    <div class="row">
                        <div class="col-md-4">
                            <label for="loan_deduction_type_filter" class="form-label">Loan Deduction Type</label>
                            <select class="form-select" id="loan_deduction_type_filter" name="filters[loan_type]">
                                <option value="">All Loan Types</option>
                                @foreach($loanDeductionTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="year_filter" class="form-label">Year</label>
                            <input type="number" class="form-control" id="year_filter" name="filters[year]"
                                   min="2000" max="{{ date('Y') }}" value="{{ date('Y') }}">
                        </div>
                        <div class="col-md-4">
                            <label for="month_filter" class="form-label">Month</label>
                            <select class="form-select" id="month_filter" name="filters[month]">
                                <option value="">All Months</option>
                                <option value="1">January</option>
                                <option value="2">February</option>
                                <option value="3">March</option>
                                <option value="4">April</option>
                                <option value="5">May</option>
                                <option value="6">June</option>
                                <option value="7">July</option>
                                <option value="8">August</option>
                                <option value="9">September</option>
                                <option value="10">October</option>
                                <option value="11">November</option>
                                <option value="12">December</option>
                            </select>
                        </div>
                    </div>
                `;
                break;

            case 'promotion_history':
            case 'disciplinary':
                // No additional filters needed for promotion history and disciplinary reports
                filtersSection.innerHTML = '';
                break;

            case 'retirement_planning':
                filtersSection.innerHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            <label for="retirement_within_months" class="form-label">Retirement Period</label>
                            <select class="form-select" id="retirement_within_months" name="filters[retirement_within_months]">
                                <option value="6" selected>Within 6 Months</option>
                                <option value="12">Within 1 Year</option>
                                <option value="18">Within 18 Months</option>
                                <option value="24">Within 2 Years</option>
                                <option value="36">Within 3 Years</option>
                            </select>
                        </div>
                    </div>
                `;
                break;

            case 'retirement_6months':
                // No additional filters needed for the 6-months specific report
                filtersSection.innerHTML = '';
                break;

            case 'audit_trail':
                filtersSection.innerHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            <label for="user_filter" class="form-label">User (Optional)</label>
                            <select class="form-select" id="user_filter" name="filters[user_id]">
                                <option value="">All Users</option>
                                @php
                                    $users = App\Models\User::select('user_id', 'username')->get();
                                @endphp
                                @foreach($users as $user)
                                    <option value="{{ $user->user_id }}">{{ $user->username }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="action_filter" class="form-label">Action (Optional)</label>
                            <select class="form-select" id="action_filter" name="filters[action]">
                                <option value="">All Actions</option>
                                <option value="created">Created</option>
                                <option value="updated">Updated</option>
                                <option value="deleted">Deleted</option>
                                <option value="generated_report">Generated Report</option>
                                <option value="downloaded_report">Downloaded Report</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label for="start_date_filter" class="form-label">Start Date (Optional)</label>
                            <input type="date" class="form-control" id="start_date_filter" name="filters[start_date]">
                        </div>
                        <div class="col-md-6">
                            <label for="end_date_filter" class="form-label">End Date (Optional)</label>
                            <input type="date" class="form-control" id="end_date_filter" name="filters[end_date]">
                        </div>
                    </div>
                `;
                break;
        }
    }
});
</script>
@endsection