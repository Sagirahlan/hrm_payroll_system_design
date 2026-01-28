@extends('layouts.app')

@section('title', 'Bulk Deductions')

@section('content')
<div class="container-fluid">
    <h1 class="mb-4">Bulk Deductions</h1>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Please fix the following errors:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <!-- Left Column: Assignment Details -->
        <div class="col-md-5">
            @can('manage_payroll_adjustments')
            <form action="{{ route('payroll.deductions.bulk.store') }}" method="POST" id="bulk-assignment-form">
                @csrf
                <input type="hidden" name="select_all_pages" id="select_all_pages" value="0">
                <input type="hidden" name="search" value="{{ request('search') }}">
                <input type="hidden" name="department_id" value="{{ request('department_id') }}">
                <input type="hidden" name="grade_level_id" value="{{ request('grade_level_id') }}">

                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">1. Define the Deduction</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <h5>Statutory Deductions</h5>
                            @foreach($statutoryDeductions as $type)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="deduction_types[]" value="{{ $type->id }}" id="deduction_{{ $type->id }}">
                                    <label class="form-check-label" for="deduction_{{ $type->id }}">
                                        {{ $type->name }}
                                    </label>
                                </div>
                            @endforeach

                            <div class="mt-3">
                                <label for="statutory_deduction_month" class="form-label">Deduction Month</label>
                                <input type="month" name="statutory_deduction_month" id="statutory_deduction_month" class="form-control" required>
                            </div>
                        </div>

                        <hr>

                        <div class="mb-3">
                            <h5>Non-Statutory Deductions</h5>
                            <select name="type_id" id="type_id" class="form-select">
                                <option value="" selected disabled>-- Select Type --</option>
                                @foreach($nonStatutoryDeductions as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-sm-8">
                                <div class="mb-3">
                                    <label for="amount" class="form-label">Amount (for non-statutory)</label>
                                    <input type="number" name="amount" id="amount" class="form-control" step="0.01" placeholder="Enter amount or %">
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="mb-3">
                                    <label for="amount_type" class="form-label">Type</label>
                                    <select name="amount_type" id="amount_type" class="form-select">
                                        <option value="fixed">Fixed</option>
                                        <option value="percentage">Percentage</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="period" class="form-label">Frequency</label>
                            <select name="period" id="period" class="form-select" required>
                                <option value="OneTime">One Time</option>
                                <option value="Monthly">Monthly</option>
                                <option value="Perpetual">Perpetual</option>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-sm-6 mb-3">
                                <label for="start_date" class="form-label">Start Date</label>
                                <input type="date" name="start_date" id="start_date" class="form-control" required>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <label for="end_date" class="form-label">End Date </label>
                                <input type="date" name="end_date" id="end_date" class="form-control" required>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg">Assign to Selected Employees</button>
                </div>
            </form>
            @else
            <div class="alert alert-warning">
                You don't have permission to manage payroll adjustments.
            </div>
            @endcan
        </div>

        <!-- Right Column: Employee Selection -->
        <div class="col-md-7">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">2. Select Employees</h5>
                </div>
                <div class="card-body">
                    <!-- Filter Form -->
                    <form action="{{ route('payroll.deductions') }}" method="GET" id="employee-filter-form">
                        <div class="input-group mb-3">
                            <input type="text" name="search" class="form-control" placeholder="Search by name or ID..." value="{{ request('search') }}">
                            <button class="btn btn-outline-secondary" type="submit">Search</button>
                            <a href="{{ route('payroll.deductions') }}" class="btn btn-outline-danger" title="Clear Search">Clear</a>
                        </div>

<div class="row mb-3">
    <div class="col-md-4">
        <label for="department_filter" class="form-label">Department</label>
        <select name="department_id" id="department_filter" class="form-select">
            <option value="">All Departments</option>
            @foreach($departments as $department)
                <option value="{{ $department->department_id }}" {{ request('department_id') == $department->department_id ? 'selected' : '' }}>{{ $department->department_name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label for="grade_level_filter" class="form-label">Grade Level</label>
        <select name="grade_level_id" id="grade_level_filter" class="form-select">
            <option value="">All Grade Levels</option>
            @foreach($gradeLevels as $gradeLevel)
                <option value="{{ $gradeLevel->id }}" {{ request('grade_level_id') == $gradeLevel->id ? 'selected' : '' }}>{{ $gradeLevel->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label for="appointment_type_filter" class="form-label">Appointment Type</label>
        <select name="appointment_type_id" id="appointment_type_filter" class="form-select">
            <option value="">All Types</option>
            @foreach($appointmentTypes as $type)
                <option value="{{ $type->id }}" {{ request('appointment_type_id') == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
            @endforeach
        </select>
    </div>
</div>

                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-primary">Apply Filters</button>
                        </div>
                    </form>

                    <div id="select-all-container" class="alert alert-info" style="display: none;">
                        <span id="select-all-message"></span>
                        <a href="#" id="select-all-link"></a>
                        <a href="#" id="clear-selection-link" class="float-end">Clear selection</a>
                    </div>

                    <div class="table-responsive" id="employee-list-scroll-container" style="max-height: 450px;" data-next-page-url="{{ $employees->nextPageUrl() }}">
                        <table class="table table-bordered table-striped table-hover">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th class="text-center"><input type="checkbox" id="select-all" form="bulk-assignment-form"></th>
                                    <th>Staff No</th>
                                    <th>Name</th>
                                    <th>Department</th>
                                    <th>Grade Level</th>
                                </tr>
                            </thead>
                            <tbody id="employee-list-tbody">
                                @include('payroll._employee_rows', ['employees' => $employees])
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        Showing {{ $employees->firstItem() }} to {{ $employees->lastItem() }} of <span id="total-employees">{{ $employees->total() }}</span> employees
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const scrollContainer = document.getElementById('employee-list-scroll-container');
        const tbody = document.getElementById('employee-list-tbody');
        let isLoading = false;

        // Handle infinite scroll
        scrollContainer.addEventListener('scroll', function() {
            if (isLoading) return;

            let nextPageUrl = scrollContainer.dataset.nextPageUrl;
            if (!nextPageUrl) return;

            if (scrollContainer.scrollTop + scrollContainer.clientHeight >= scrollContainer.scrollHeight - 100) {
                isLoading = true;
                fetch(nextPageUrl, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.html) {
                        tbody.insertAdjacentHTML('beforeend', data.html);
                        addCheckboxListeners();
                    }
                    scrollContainer.dataset.nextPageUrl = data.next_page_url || '';
                    isLoading = false;
                })
                .catch(error => {
                    console.error('Error loading more employees:', error);
                    isLoading = false;
                });
            }
        });

        // Handle select all checkbox
        const selectAllCheckbox = document.getElementById('select-all');
        const selectAllPagesInput = document.getElementById('select_all_pages');
        const assignmentForm = document.getElementById('bulk-assignment-form');
        const selectAllContainer = document.getElementById('select-all-container');
        const selectAllMessage = document.getElementById('select-all-message');
        const selectAllLink = document.getElementById('select-all-link');
        const clearSelectionLink = document.getElementById('clear-selection-link');
        const totalEmployeesSpan = document.getElementById('total-employees');

        function updateSelectAllMessage() {
            const selectedCount = tbody.querySelectorAll('.employee-checkbox:checked').length;
            const totalOnPage = tbody.querySelectorAll('.employee-checkbox').length;
            const totalEmployees = parseInt(totalEmployeesSpan.textContent, 10);

            if (selectAllCheckbox.checked) {
                selectAllContainer.style.display = 'block';
                if (selectAllPagesInput.value === '1') {
                    selectAllMessage.textContent = `All ${totalEmployees} employees are selected.`;
                    selectAllLink.style.display = 'none';
                } else {
                    selectAllMessage.textContent = `All ${selectedCount} employees on this page are selected.`;
                    if (totalEmployees > totalOnPage) {
                        selectAllLink.style.display = 'inline';
                        selectAllLink.textContent = `Select all ${totalEmployees} employees that match this search`;
                    } else {
                        selectAllLink.style.display = 'none';
                    }
                }
            } else {
                selectAllContainer.style.display = 'none';
                selectAllPagesInput.value = '0';
            }
        }

        selectAllCheckbox.addEventListener('change', function () {
            const isChecked = this.checked;
            tbody.querySelectorAll('.employee-checkbox').forEach(checkbox => {
                checkbox.checked = isChecked;
                // Update localStorage based on selection
                if (isChecked) {
                    saveSelection(checkbox.value);
                } else {
                    removeSelection(checkbox.value);
                }
            });
            
            // If unchecking, clear all saved selections
            if (!isChecked) {
                clearAllSelections();
            }
            
            updateSelectAllMessage();
        });

        selectAllLink.addEventListener('click', function (e) {
            e.preventDefault();
            selectAllPagesInput.value = '1';
            updateSelectAllMessage();
        });

        clearSelectionLink.addEventListener('click', function (e) {
            e.preventDefault();
            selectAllCheckbox.checked = false;
            tbody.querySelectorAll('.employee-checkbox').forEach(checkbox => {
                checkbox.checked = false;
            });
            selectAllPagesInput.value = '0';
            selectAllContainer.style.display = 'none';
            clearAllSelections(); // Clear localStorage
        });

        function addCheckboxListeners() {
            tbody.querySelectorAll('.employee-checkbox').forEach(checkbox => {
                // Restore saved checkbox state
                if (getSavedSelection(checkbox.value)) {
                    checkbox.checked = true;
                }
                
                checkbox.addEventListener('change', function() {
                    // Save checkbox state to localStorage
                    if (this.checked) {
                        saveSelection(this.value);
                    } else {
                        removeSelection(this.value);
                    }
                    
                    const allCheckboxes = tbody.querySelectorAll('.employee-checkbox');
                    const checkedCheckboxes = tbody.querySelectorAll('.employee-checkbox:checked');
                    selectAllCheckbox.checked = allCheckboxes.length === checkedCheckboxes.length;
                    updateSelectAllMessage();
                });
            });
        }
        
        // LocalStorage functions for persisting selections
        function saveSelection(employeeId) {
            let selections = JSON.parse(localStorage.getItem('payrollDeductionsSelections') || '[]');
            if (!selections.includes(employeeId)) {
                selections.push(employeeId);
                localStorage.setItem('payrollDeductionsSelections', JSON.stringify(selections));
            }
        }
        
        function removeSelection(employeeId) {
            let selections = JSON.parse(localStorage.getItem('payrollDeductionsSelections') || '[]');
            selections = selections.filter(id => id !== employeeId);
            localStorage.setItem('payrollDeductionsSelections', JSON.stringify(selections));
        }
        
        function getSavedSelection(employeeId) {
            let selections = JSON.parse(localStorage.getItem('payrollDeductionsSelections') || '[]');
            return selections.includes(employeeId);
        }
        
        function clearAllSelections() {
            localStorage.removeItem('payrollDeductionsSelections');
        }
        
        addCheckboxListeners();

        // Add selected employee checkboxes to the assignment form before submission
        assignmentForm.addEventListener('submit', function(e) {
            // Remove previously added hidden inputs
            assignmentForm.querySelectorAll('input[name="employee_ids[]"]').forEach(input => input.remove());

            if (selectAllPagesInput.value !== '1') {
                // Get selections from checkboxes (which are now persisted)
                tbody.querySelectorAll('.employee-checkbox:checked').forEach(checkbox => {
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'employee_ids[]';
                    hiddenInput.value = checkbox.value;
                    assignmentForm.appendChild(hiddenInput);
                });
            }
            
            // Clear localStorage after successful form submission
            clearAllSelections();
        });

        // Handle filter form submission
        const filterForm = document.getElementById('employee-filter-form');
        if (filterForm) {
            filterForm.addEventListener('submit', function(e) {
                // The form will submit normally, but we could add AJAX handling here if needed
            });
        }

        // Handle department filter change
        const departmentFilter = document.getElementById('department_filter');
        const gradeLevelFilter = document.getElementById('grade_level_filter');

        if (departmentFilter) {
            departmentFilter.addEventListener('change', function() {
                filterForm.submit();
            });
        }

        if (gradeLevelFilter) {
            gradeLevelFilter.addEventListener('change', function() {
                filterForm.submit();
            });
        }
    });
</script>
@endpush
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Set default value for statutory deduction month
    const statutoryMonthInput = document.getElementById('statutory_deduction_month');
    if (statutoryMonthInput && !statutoryMonthInput.value) {
        const today = new Date();
        const year = today.getFullYear();
        const month = String(today.getMonth() + 1).padStart(2, '0');
        statutoryMonthInput.value = `${year}-${month}`;
    }

    // Set default value for start date to first day of current month
    const startDateInput = document.getElementById('start_date');
    if (startDateInput && !startDateInput.value) {
        const today = new Date();
        const year = today.getFullYear();
        const month = String(today.getMonth() + 1).padStart(2, '0'); // Month is 0-indexed
        const startDate = `${year}-${month}-01`;
        startDateInput.value = startDate;
    }

    // Set default value for end date to last day of current month
    const endDateInput = document.getElementById('end_date');
    if (endDateInput && !endDateInput.value) {
        const today = new Date();
        const year = today.getFullYear();
        const month = String(today.getMonth() + 1).padStart(2, '0'); // Month is 0-indexed
        const lastDay = new Date(year, today.getMonth() + 1, 0).getDate();
        const endDate = `${year}-${month}-${String(lastDay).padStart(2, '0')}`;
        endDateInput.value = endDate;
    }

    // Show/hide sections based on selection
    const typeIdSelect = document.getElementById('type_id');
    const nonStatutoryFields = document.querySelectorAll('#amount, #amount_type, #period, #start_date, #end_date');

    if (typeIdSelect) {
        typeIdSelect.addEventListener('change', function() {
            const showNonStatutory = this.value !== '';
            nonStatutoryFields.forEach(input => {
                input.closest('.mb-3, .row').style.display = showNonStatutory ? 'block' : 'none';
            });
        });
    }
});
</script>
@endsection