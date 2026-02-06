@extends('layouts.app')

@section('title', 'Generate New Report')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Generate New Report</h3>
                    <a href="{{ route('reports.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Reports
                    </a>
                </div>

                <div class="card-body">
                    <form id="report-form" method="POST" action="{{ route('reports.comprehensive.generate') }}">
                        @csrf

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="report_type" class="form-label">Report Type</label>
                                <select class="form-select" id="report_type" name="report_type" required>
                                    <option value="">Select Report Type</option>
                                    <optgroup label="Employee Reports">
                                        <option value="employee_master">Employee Master Report</option>
                                        <option value="employee_directory">Employee Directory Report</option>
                                        <option value="employee_status">Employee Status Report</option>
                                    </optgroup>
                                    <optgroup label="Compensation Reports">
                                        <option value="payroll_summary">Payroll Summary Report</option>
                                        <option value="payroll_journal">Payroll Journal Report</option>
                                        <option value="payroll_analysis">Payroll Analysis Report</option>
                                        <option value="deduction_summary">Deduction Summary Report</option>
                                        <option value="addition_summary">Addition Summary Report</option>
                                    </optgroup>
                                    <optgroup label="Performance & Career Reports">
                                        <option value="promotion_history">Promotion History Report</option>
                                        <option value="disciplinary">Disciplinary Action Report</option>
                                        <option value="retirement_planning">Retirement Planning Report</option>
                                    </optgroup>
                                    <optgroup label="Financial Reports">
                                        <option value="loan_status">Loan Status Report</option>
                                    </optgroup>
                                    <optgroup label="Organizational Reports">
                                        <option value="department_summary">Department Summary Report</option>
                                        <option value="grade_level_summary">Grade Level Summary Report</option>
                                    </optgroup>
                                    <optgroup label="Audit Reports">
                                        <option value="audit_trail">Audit Trail Report</option>
                                    </optgroup>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="export_format" class="form-label">Export Format</label>
                                <select class="form-select" id="export_format" name="export_format" required>
                                    <option value="">Select Format</option>
                                    <option value="PDF">PDF</option>
                                    <option value="Excel">Excel</option>
                                </select>
                            </div>
                        </div>

                        <!-- Filters Section -->
                        <div id="filters-section" class="mb-4"></div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-file-export"></i> Generate Report
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const reportTypeSelect = document.getElementById('report_type');
    const filtersSection = document.getElementById('filters-section');

    // Parse the JSON data passed from the controller
    const jsonData = {
        departments: {!! $departments_json !!},
        deductionTypes: {!! $deduction_types_json !!},
        additionTypes: {!! $addition_types_json !!},
        employees: {!! $employees_json !!},
        users: {!! $users_json !!},
        loanDeductionTypes: {!! $loanDeductionTypes_json !!}
    };

    reportTypeSelect.addEventListener('change', function() {
        const reportType = this.value;
        filtersSection.innerHTML = ''; // Clear previous filters

        if (!reportType) return;

        // Add specific filters based on report type
        addFiltersForReportType(reportType);
    });

    function addFiltersForReportType(reportType) {
        let filtersHtml = '';

        switch(reportType) {
            case 'employee_master':
            case 'employee_directory':
                filtersHtml = `
                    <div class="row">
                        <div class="col-md-6">
                            <label for="department_filter" class="form-label">Department</label>
                            <select class="form-select" name="filters[department_id]">
                                <option value="">All Departments</option>
                                ${jsonData.departments.map(dept => `<option value="${dept.department_id}">${dept.department_name}</option>`).join('')}
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="status_filter" class="form-label">Status</label>
                            <select class="form-select" name="filters[status]">
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
                            <select class="form-select" name="filters[appointment_type_id]">
                                <option value="">All Appointment Types</option>
                                ${jsonData.appointmentTypes.map(type => `<option value="${type.id}">${type.name}</option>`).join('')}
                            </select>
                        </div>
                    </div>
                `;
                break;

            case 'payroll_summary':
            case 'payroll_analysis':
            case 'payroll_journal':
                filtersHtml = `
                    <div class="row">
                        <div class="col-md-4">
                            <label for="year_filter" class="form-label">Year</label>
                            <input type="number" class="form-control" name="filters[year]"
                                   min="2000" max="{{ date('Y') }}" value="{{ date('Y') }}">
                        </div>
                        <div class="col-md-4">
                            <label for="month_filter" class="form-label">Month</label>
                            <select class="form-select" name="filters[month]">
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
                        <div class="col-md-4">
                            <label for="payment_type_filter" class="form-label">Payment Type</label>
                            <select class="form-select" name="filters[payment_type]">
                                <option value="">All Types</option>
                                <option value="Regular">Regular (Salary)</option>
                                <option value="Permanent">Permanent Staff</option>
                                <option value="Casual">Casual Staff</option>
                                <option value="Pension">Pensioners</option>
                                <option value="Gratuity">Gratuity</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label for="appointment_type_filter" class="form-label">Appointment Type</label>
                            <select class="form-select" name="filters[appointment_type_id]">
                                <option value="">All Appointment Types</option>
                                ${jsonData.appointmentTypes.map(type => \`<option value="\${type.id}">\${type.name}</option>\`).join('')}
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="status_filter" class="form-label">Status</label>
                            <select class="form-select" name="filters[status]">
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

            case 'deduction_summary':
                // Render deduction type + year + month together so the date fields always show
                filtersHtml = `
                    <div class="row">
                        <div class="col-md-4">
                            <label for="deduction_type_filter" class="form-label">Deduction Type</label>
                            <select class="form-select" name="filters[deduction_type_id]">
                                <option value="">All Deduction Types</option>
                                ${jsonData.deductionTypes.map(type => `<option value="${type.id}">${type.name}</option>`).join('')}
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="year_filter" class="form-label">Year</label>
                            <input type="number" class="form-control" name="filters[year]"
                                   min="2000" max="{{ date('Y') }}" value="{{ date('Y') }}">
                        </div>
                        <div class="col-md-4">
                            <label for="month_filter" class="form-label">Month</label>
                            <select class="form-select" name="filters[month]">
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

            case 'addition_summary':
                filtersHtml = `
                    <div class="row">
                        <div class="col-md-4">
                            <label for="addition_type_filter" class="form-label">Addition Type</label>
                            <select class="form-select" name="filters[addition_type_id]">
                                <option value="">All Addition Types</option>
                                ${jsonData.additionTypes.map(type => `<option value="${type.id}">${type.name}</option>`).join('')}
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="year_filter" class="form-label">Year</label>
                            <input type="number" class="form-control" name="filters[year]"
                                   min="2000" max="{{ date('Y') }}" value="{{ date('Y') }}">
                        </div>
                        <div class="col-md-4">
                            <label for="month_filter" class="form-label">Month</label>
                            <select class="form-select" name="filters[month]">
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

            case 'loan_status':
                filtersHtml = `
                    <div class="row">
                        <div class="col-md-4">
                            <label for="loan_deduction_type_filter" class="form-label">Loan Deduction Type</label>
                            <select class="form-select" name="filters[loan_type]">
                                <option value="">All Loan Types</option>
                                ${jsonData.loanDeductionTypes.map(type => \`
                                    <option value="\${type.id}">\${type.name}</option>
                                \`).join('')}
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="year_filter" class="form-label">Year</label>
                            <input type="number" class="form-control" name="filters[year]"
                                   min="2000" max="{{ date('Y') }}" value="{{ date('Y') }}">
                        </div>
                        <div class="col-md-4">
                            <label for="month_filter" class="form-label">Month</label>
                            <select class="form-select" name="filters[month]">
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
                filtersHtml = '';
                break;

            case 'retirement_planning':
                filtersHtml = `
                    <div class="row">
                        <div class="col-md-6">
                            <label for="retirement_within_months" class="form-label">Retirement Period</label>
                            <select class="form-select" name="filters[retirement_within_months]">
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

            case 'audit_trail':
                filtersHtml = `
                    <div class="row">
                        <div class="col-md-6">
                            <label for="user_filter" class="form-label">User (Optional)</label>
                            <select class="form-select" name="filters[user_id]">
                                <option value="">All Users</option>
                                ${jsonData.users.map(user => `<option value="${user.user_id}">${user.username}</option>`).join('')}
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="action_filter" class="form-label">Action (Optional)</label>
                            <select class="form-select" name="filters[action]">
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
                            <input type="date" class="form-control" name="filters[start_date]">
                        </div>
                        <div class="col-md-6">
                            <label for="end_date_filter" class="form-label">End Date (Optional)</label>
                            <input type="date" class="form-control" name="filters[end_date]">
                        </div>
                    </div>
                `;
                break;
        }

        if (filtersHtml) {
            filtersSection.innerHTML = filtersHtml;
        }
    }
});
</script>
@endsection