@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header border-0 pt-6">
        <div class="card-title">
            <h3>New Step Increment</h3>
        </div>
        <div class="card-toolbar">
            <a href="{{ route('promotions.increments.index') }}" class="btn btn-light btn-sm">
                <i class="fas fa-arrow-left"></i> Back to History
            </a>
        </div>
    </div>
    
    <div class="card-body py-4">
        <div class="row mb-5">
            <div class="col-md-12">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i> This tool allows you to increment the <strong>Step</strong> of selected employees by one. 
                    Employees with status <strong>Retired</strong> or <strong>Deceased</strong> are excluded.
                    The Grade Level will remain unchanged.
                </div>
            </div>
        </div>

        <form action="{{ route('promotions.increments.create') }}" method="GET" class="mb-5">
            <div class="row g-3">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="Search by name or ID..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="department" class="form-select" data-control="select2" data-placeholder="Select Department">
                        <option value="">All Departments</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->department_id }}" {{ request('department') == $dept->department_id ? 'selected' : '' }}>
                                {{ $dept->department_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="grade_level" class="form-select" data-control="select2" data-placeholder="Select Grade Level">
                        <option value="">All Grade Levels</option>
                        @foreach($gradeLevels as $level)
                            <option value="{{ $level->id }}" {{ request('grade_level') == $level->id ? 'selected' : '' }}>
                                {{ $level->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('promotions.increments.create') }}" class="btn btn-light">Reset</a>
                </div>
            </div>
        </form>

        <form action="{{ route('promotions.increments.process') }}" method="POST" id="incrementForm">
            @csrf
            
            <div class="d-flex justify-content-end mb-3">
                <button type="button" class="btn btn-success" id="btnIncrement">
                    <i class="fas fa-level-up-alt"></i> Increment Selected
                </button>
            </div>
            
            <input type="hidden" name="select_all_pages" id="select_all_pages" value="0">
            <!-- Include current filters to preserve them on submit if selecting all pages -->
            <input type="hidden" name="filter_search" value="{{ request('search') }}">
            <input type="hidden" name="filter_department" value="{{ request('department') }}">
            <input type="hidden" name="filter_grade_level" value="{{ request('grade_level') }}">

            <div id="selection-helper" class="alert alert-info d-none mb-3">
                <span id="selection-message"></span>
                <a href="#" id="select-all-matching" class="fw-bold text-primary">Select all {{ $employees->total() }} matching employees</a>
            </div>

            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_table_employees">
                    <thead>
                        <tr class="text-start text-muted fw-bolder fs-7 text-uppercase gs-0">
                            <th class="w-10px pe-2">
                                <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                                    <input class="form-check-input" type="checkbox" id="selectAll" />
                                </div>
                            </th>
                            <th>Staff No</th>
                            <th>Name</th>
                            <th>Department</th>
                            <th>Status</th>
                            <th>Date of First Appt.</th>
                            <th>Grade Level</th>
                            <th>Current Step</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 fw-bold">
                        @forelse($employees as $employee)
                            <tr>
                                <td>
                                    <div class="form-check form-check-sm form-check-custom form-check-solid">
                                        <input class="form-check-input employee-checkbox" type="checkbox" name="employee_ids[]" value="{{ $employee->employee_id }}" />
                                    </div>
                                </td>
                                <td>{{ $employee->staff_no ?? $employee->employee_id }}</td>
                                <td>{{ $employee->full_name }}</td>
                                <td>{{ $employee->department->department_name ?? 'N/A' }}</td>
                                <td>
                                    <span class="badge badge-light-{{ strtolower($employee->status) === 'active' ? 'success' : 'warning' }} text-dark">
                                        {{ $employee->status ?? 'N/A' }}
                                    </span>
                                </td>
                                <td>{{ $employee->date_of_first_appointment ? \Carbon\Carbon::parse($employee->date_of_first_appointment)->format('d M, Y') : 'N/A' }}</td>
                                <td>{{ $employee->gradeLevel->name ?? 'N/A' }}</td>
                                <td>{{ $employee->step->name ?? 'N/A' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted">No employees found matching the criteria.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end mt-4">
                {{ $employees->appends(request()->query())->links('pagination::bootstrap-5') }}
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Increment script loaded');
        const STORAGE_KEY = 'hrm_increment_selected_ids';
        const selectAll = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.employee-checkbox'); 
        const btnIncrement = document.getElementById('btnIncrement');
        const form = document.getElementById('incrementForm');
        
        // Global Select Elements
        const selectAllPagesInput = document.getElementById('select_all_pages');
        const selectionHelper = document.getElementById('selection-helper');
        const selectionMessage = document.getElementById('selection-message');
        const selectAllMatchingLink = document.getElementById('select-all-matching');
        
        // Data from server
        const totalMatching = {{ $employees->total() }};
        const visibleCount = {{ $employees->count() }};

        if (!btnIncrement) {
             console.error('Increment button not found!');
             return;
        }

        // Initialize state from session storage
        let storedData = [];
        try {
            storedData = JSON.parse(sessionStorage.getItem(STORAGE_KEY) || '[]');
        } catch (e) {
            console.error('Error parsing session storage', e);
            storedData = [];
        }
        let selectedIds = new Set(storedData);

        // Check for success message to clear storage
        @if(session('success'))
            console.log('Success session detected, clearing storage');
            sessionStorage.removeItem(STORAGE_KEY);
            selectedIds = new Set();
        @endif

        // Function to update UI based on stored state
        function updateUI() {
            checkboxes.forEach(cb => {
                if (selectedIds.has(cb.value)) {
                    cb.checked = true;
                }
            });
            updateSelectAllState();
            updateButtonState();
        }

        function updateSelectAllState() {
            if (!selectAll) return;
            const hasCheckboxes = checkboxes.length > 0;
            const allChecked = hasCheckboxes && Array.from(checkboxes).every(c => c.checked);
            selectAll.checked = allChecked;
            
            checkGlobalSelectVisibility(allChecked);
        }
        
        function checkGlobalSelectVisibility(allVisibleChecked) {
            // Only show helper if:
            // 1. All visible checkboxes are checked
            // 2. Total matching employees > visible employees
            // 3. We haven't already selected all pages
            if (allVisibleChecked && totalMatching > visibleCount && selectAllPagesInput.value === '0') {
                selectionHelper.classList.remove('d-none');
                selectionMessage.textContent = `All ${visibleCount} employees on this page are selected. `;
                selectAllMatchingLink.classList.remove('d-none');
            } else if (selectAllPagesInput.value === '1') {
                selectionHelper.classList.remove('d-none');
                selectionMessage.textContent = `All ${totalMatching} employees matching the search are selected.`;
                selectAllMatchingLink.classList.add('d-none');
            } else {
                selectionHelper.classList.add('d-none');
            }
        }
        
        function updateButtonState() {
            if (btnIncrement) {
                if (selectAllPagesInput.value === '1') {
                    btnIncrement.innerHTML = `<i class="fas fa-level-up-alt"></i> Increment All (${totalMatching})`;
                } else {
                    btnIncrement.innerHTML = `<i class="fas fa-level-up-alt"></i> Increment Selected (${selectedIds.size})`;
                }
            }
        }

        // Handle Select All Matching Link
        if (selectAllMatchingLink) {
            selectAllMatchingLink.addEventListener('click', function(e) {
                e.preventDefault();
                selectAllPagesInput.value = '1';
                checkGlobalSelectVisibility(true); // Update UI text
                updateButtonState();
            });
        }

        // Handle Select All
        if (selectAll) {
            selectAll.addEventListener('change', function() {
                const isChecked = this.checked;
                
                // Reset global select if unchecking
                if (!isChecked) {
                    selectAllPagesInput.value = '0';
                }
                
                checkboxes.forEach(cb => {
                    cb.checked = isChecked;
                    if (isChecked) {
                        selectedIds.add(cb.value);
                    } else {
                        selectedIds.delete(cb.value);
                    }
                });
                saveState();
                updateButtonState();
                checkGlobalSelectVisibility(isChecked);
            });
        }

        // Handle Individual Checkbox
        checkboxes.forEach(cb => {
            cb.addEventListener('change', function() {
                // If any individual checkbox changes, we definitely are NOT in "Select All Pages" mode anymore
                selectAllPagesInput.value = '0';
                
                if (this.checked) {
                    selectedIds.add(this.value);
                } else {
                    selectedIds.delete(this.value);
                }
                saveState();
                updateSelectAllState();
                updateButtonState();
            });
        });

        function saveState() {
            sessionStorage.setItem(STORAGE_KEY, JSON.stringify(Array.from(selectedIds)));
        }

        // Handle Submit
        btnIncrement.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Increment button clicked');
            
            const isGlobal = selectAllPagesInput.value === '1';
            const count = isGlobal ? totalMatching : selectedIds.size;
            
            if (count === 0) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        text: "Please select at least one employee.",
                        icon: "warning",
                        buttonsStyling: false,
                        confirmButtonText: "Ok, got it!",
                        customClass: { confirmButton: "btn btn-primary" }
                    });
                } else {
                    alert("Please select at least one employee.");
                }
                return;
            }

            const confirmMessage = `Are you sure you want to increment the step for ${count} employees?`;

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    text: confirmMessage,
                    icon: "warning",
                    showCancelButton: true,
                    buttonsStyling: false,
                    confirmButtonText: "Yes, increment!",
                    cancelButtonText: "No, cancel",
                    customClass: {
                        confirmButton: "btn btn-primary",
                        cancelButton: "btn btn-light"
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        submitForm();
                    }
                });
            } else {
                if (confirm(confirmMessage)) {
                    submitForm();
                }
            }
        });

        function submitForm() {
            console.log('Submitting form...');
            // Remove existing inputs to avoid duplicates if any
            const existingInputs = form.querySelectorAll('input[name="employee_ids[]"]');
            existingInputs.forEach(el => el.remove());

            // Only append selected IDs if NOT selecting all pages
            // If selecting all pages, the controller will use the filters
            if (selectAllPagesInput.value !== '1') {
                selectedIds.forEach(id => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'employee_ids[]';
                    input.value = id;
                    form.appendChild(input);
                });
            }

            form.submit();
        }

        // Initial UI Update
        updateUI();
    });
</script>
@endpush
