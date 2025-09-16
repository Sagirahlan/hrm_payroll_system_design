@extends('layouts.app')

@section('title', 'Bulk Additions')

@section('content')
<div class="container-fluid">
    <h1 class="mb-4">Bulk Additions</h1>

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
            <form action="{{ route('payroll.additions.bulk.store') }}" method="POST" id="bulk-assignment-form">
                @csrf
                <input type="hidden" name="select_all_pages" id="select_all_pages" value="0">
                <input type="hidden" name="search" value="{{ request('search') }}">
                <input type="hidden" name="department_id" value="{{ request('department_id') }}">
                <input type="hidden" name="grade_level_id" value="{{ request('grade_level_id') }}">

                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">1. Define the Addition</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <h5>Statutory Additions</h5>
                            @foreach($statutoryAdditions as $type)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="addition_types[]" value="{{ $type->id }}" id="addition_{{ $type->id }}">
                                    <label class="form-check-label" for="addition_{{ $type->id }}">
                                        {{ $type->name }}
                                    </label>
                                </div>
                            @endforeach
                        </div>

                        <hr>

                        <div class="mb-3">
                            <h5>Non-Statutory Additions</h5>
                            <select name="type_id" id="type_id" class="form-select">
                                <option value="" selected disabled>-- Select Type --</option>
                                @foreach($nonStatutoryAdditions as $type)
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
                                <label for="end_date" class="form-label">End Date <small class="text-muted">(Optional)</small></label>
                                <input type="date" name="end_date" id="end_date" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg">Assign to Selected Employees</button>
                </div>
            </form>
        </div>

        <!-- Right Column: Employee Selection -->
        <div class="col-md-7">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">2. Select Employees</h5>
                </div>
                <div class="card-body">
                    <!-- Filter Form -->
                    <form action="{{ route('payroll.additions') }}" method="GET" id="employee-filter-form">
                        <div class="input-group mb-3">
                            <input type="text" name="search" class="form-control" placeholder="Search by name or ID..." value="{{ request('search') }}">
                            <button class="btn btn-outline-secondary" type="submit">Search</button>
                            <a href="{{ route('payroll.additions') }}" class="btn btn-outline-danger" title="Clear Search">Clear</a>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="department_filter" class="form-label">Department</label>
                                <select name="department_id" id="department_filter" class="form-select">
                                    <option value="">All Departments</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->department_id }}" {{ request('department_id') == $dept->department_id ? 'selected' : '' }}>{{ $dept->department_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="grade_level_filter" class="form-label">Grade Level</label>
                                <select name="grade_level_id" id="grade_level_filter" class="form-select">
                                    <option value="">All Grade Levels</option>
                                    @foreach($gradeLevels as $level)
                                        <option value="{{ $level->id }}" {{ request('grade_level_id') == $level->id ? 'selected' : '' }}>{{ $level->name }}</option>
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
                                    <th>Employee ID</th>
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
            });
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
        });

        function addCheckboxListeners() {
            tbody.querySelectorAll('.employee-checkbox').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const allCheckboxes = tbody.querySelectorAll('.employee-checkbox');
                    const checkedCheckboxes = tbody.querySelectorAll('.employee-checkbox:checked');
                    selectAllCheckbox.checked = allCheckboxes.length === checkedCheckboxes.length;
                    updateSelectAllMessage();
                });
            });
        }
        addCheckboxListeners();

        // Add selected employee checkboxes to the assignment form before submission
        assignmentForm.addEventListener('submit', function(e) {
            // Remove previously added hidden inputs
            assignmentForm.querySelectorAll('input[name="employee_ids[]"]').forEach(input => input.remove());

            if (selectAllPagesInput.value !== '1') {
                tbody.querySelectorAll('.employee-checkbox:checked').forEach(checkbox => {
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'employee_ids[]';
                    hiddenInput.value = checkbox.value;
                    assignmentForm.appendChild(hiddenInput);
                });
            }
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
@endsection