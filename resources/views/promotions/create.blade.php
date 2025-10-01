@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 text-dark fw-bold">New Promotion/Demotion</h5>
                    <p class="text-muted small mb-0">Manage employee promotions and demotions</p>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <!-- Left Column: Employee Selection & List -->
                        <div class="col-lg-5">
                            <!-- Search and Filter Section -->
                            <div class="card border mb-4">
                                <div class="card-header bg-light py-2">
                                    <h6 class="mb-0 text-dark">Search Employees</h6>
                                </div>
                                <div class="card-body">
                                    <form action="{{ route('promotions.create') }}" method="GET">
                                        <div class="mb-3">
                                            <input type="text" name="search" id="employeeSearch" class="form-control" placeholder="Search by name or staff ID" value="{{ request('search') }}">
                                        </div>
                                        <div class="mb-3">
                                            <select name="department" class="form-select">
                                                <option value="">All Departments</option>
                                                @foreach ($departments as $department)
                                                    <option value="{{ $department->department_id }}" {{ request('department') == $department->department_id ? 'selected' : '' }}>
                                                        {{ $department->department_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <button type="submit" class="btn btn-primary flex-fill">
                                                <i class="fas fa-search me-1"></i> Filter
                                            </button>
                                            <a href="{{ route('promotions.create') }}" class="btn btn-outline-secondary">
                                                <i class="fas fa-redo"></i>
                                            </a>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <!-- Employee List -->
                            <div class="card border">
                                <div class="card-header bg-light py-2">
                                    <h6 class="mb-0 text-dark">Active Employees</h6>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                                        <table class="table table-hover mb-0">
                                            <thead class="table-light sticky-top">
                                                <tr>
                                                    <th class="small">Name</th>
                                                    <th class="small">Staff ID</th>
                                                    <th class="small">Department</th>
                                                    <th class="small text-center">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody id="employeesTable">
                                                @foreach ($employees as $employee)
                                                <tr>
                                                    <td class="small">{{ $employee->first_name }} {{ $employee->surname }}</td>
                                                    <td class="small">{{ $employee->employee_id }}</td>
                                                    <td class="small">{{ $employee->department->department_name ?? 'N/A' }}</td>
                                                    <td class="text-center">
                                                        <button type="button" class="btn btn-sm btn-outline-primary select-employee" 
                                                                data-id="{{ $employee->employee_id }}"
                                                                data-name="{{ $employee->first_name }} {{ $employee->surname }}"
                                                                data-grade="{{ $employee->gradeLevel->name ?? 'N/A' }}"
                                                                data-step="{{ $employee->step->name ?? 'N/A' }}">
                                                            Select
                                                        </button>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="card-footer bg-white">
                                    <div class="d-flex justify-content-center">
                                        {{ $employees->appends(request()->query())->links('pagination::bootstrap-4') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Right Column: Promotion Form -->
                        <div class="col-lg-7">
                            <form action="{{ route('promotions.store') }}" method="POST">
                                @csrf
                                
                                <!-- Employee Selection Section -->
                                <div class="card border mb-4">
                                    <div class="card-header bg-primary text-white py-2">
                                        <h6 class="mb-0">1. Employee Information</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label for="employee_id" class="form-label fw-semibold">Selected Employee <span class="text-danger">*</span></label>
                                                <select name="employee_id" id="employee_id" class="form-select" required>
                                                    <option value="">Select an employee</option>
                                                </select>
                                                @error('employee_id') <div class="text-danger small">{{ $message }}</div> @enderror
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <label for="promotion_type" class="form-label fw-semibold">Type <span class="text-danger">*</span></label>
                                                <select name="promotion_type" id="promotion_type" class="form-select" required>
                                                    <option value="">Select Type</option>
                                                    <option value="promotion">Promotion</option>
                                                    <option value="demotion">Demotion</option>
                                                </select>
                                                @error('promotion_type') <div class="text-danger small">{{ $message }}</div> @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Previous Position Section -->
                                <div class="card border mb-4">
                                    <div class="card-header bg-secondary text-white py-2">
                                        <h6 class="mb-0">2. Current Position Details</h6>
                                    </div>
                                    <div class="card-body bg-light">
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label for="previous_salary_scale" class="form-label fw-semibold small">Current Salary Scale</label>
                                                <input type="text" name="previous_salary_scale" id="previous_salary_scale" class="form-control bg-white" readonly>
                                                @error('previous_salary_scale') <div class="text-danger small">{{ $message }}</div> @enderror
                                            </div>
                                            
                                            <div class="col-md-4">
                                                <label for="previous_grade_level" class="form-label fw-semibold small">Current Grade Level <span class="text-danger">*</span></label>
                                                <input type="text" name="previous_grade_level" id="previous_grade_level" class="form-control bg-white" required readonly>
                                                @error('previous_grade_level') <div class="text-danger small">{{ $message }}</div> @enderror
                                            </div>
                                            
                                            <div class="col-md-4">
                                                <label for="previous_step" class="form-label fw-semibold small">Current Step</label>
                                                <input type="text" name="previous_step" id="previous_step" class="form-control bg-white" readonly>
                                                @error('previous_step') <div class="text-danger small">{{ $message }}</div> @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- New Position Section -->
                                <div class="card border mb-4">
                                    <div class="card-header bg-success text-white py-2">
                                        <h6 class="mb-0">3. New Position Details</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label for="new_salary_scale" class="form-label fw-semibold">New Salary Scale <span class="text-danger">*</span></label>
                                                <select name="new_salary_scale" id="new_salary_scale" class="form-select" required>
                                                    <option value="">Select Salary Scale</option>
                                                </select>
                                                @error('new_salary_scale') <div class="text-danger small">{{ $message }}</div> @enderror
                                            </div>
                                            
                                            <div class="col-md-4">
                                                <label for="new_grade_level" class="form-label fw-semibold">New Grade Level <span class="text-danger">*</span></label>
                                                <select name="new_grade_level" id="new_grade_level" class="form-select" required>
                                                    <option value="">Select Grade Level</option>
                                                </select>
                                                @error('new_grade_level') <div class="text-danger small">{{ $message }}</div> @enderror
                                            </div>
                                            
                                            <div class="col-md-4">
                                                <label for="new_step" class="form-label fw-semibold">New Step</label>
                                                <select name="new_step" id="new_step" class="form-select">
                                                    <option value="">Select Step</option>
                                                </select>
                                                @error('new_step') <div class="text-danger small">{{ $message }}</div> @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Additional Details Section -->
                                <div class="card border mb-4">
                                    <div class="card-header bg-info text-white py-2">
                                        <h6 class="mb-0">4. Additional Details</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label for="promotion_date" class="form-label fw-semibold">Promotion Date <span class="text-danger">*</span></label>
                                                <input type="date" name="promotion_date" id="promotion_date" class="form-control" required>
                                                @error('promotion_date') <div class="text-danger small">{{ $message }}</div> @enderror
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <label for="effective_date" class="form-label fw-semibold">Effective Date <span class="text-danger">*</span></label>
                                                <input type="date" name="effective_date" id="effective_date" class="form-control" required>
                                                @error('effective_date') <div class="text-danger small">{{ $message }}</div> @enderror
                                            </div>
                                            
                                            <div class="col-12">
                                                <label for="approving_authority" class="form-label fw-semibold">Approving Authority</label>
                                                <input type="text" name="approving_authority" id="approving_authority" class="form-control" placeholder="Enter approving authority name">
                                                @error('approving_authority') <div class="text-danger small">{{ $message }}</div> @enderror
                                            </div>
                                            
                                            <div class="col-12">
                                                <label for="reason" class="form-label fw-semibold">Reason <span class="text-danger">*</span></label>
                                                <textarea name="reason" id="reason" class="form-control" rows="4" placeholder="Enter the reason for this promotion/demotion" required>{{ old('reason') }}</textarea>
                                                @error('reason') <div class="text-danger small">{{ $message }}</div> @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="d-flex gap-2 justify-content-end">
                                    <a href="{{ route('promotions.index') }}" class="btn btn-outline-secondary px-4">
                                        <i class="fas fa-times me-1"></i> Cancel
                                    </a>
                                    <button type="submit" class="btn btn-primary px-4">
                                        <i class="fas fa-save me-1"></i> Save Promotion/Demotion
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .card {
        border-radius: 8px;
    }
    
    .card-header {
        border-radius: 8px 8px 0 0 !important;
    }
    
    .form-select, .form-control {
        border-radius: 6px;
    }
    
    .btn {
        border-radius: 6px;
    }
    
    .table > :not(caption) > * > * {
        padding: 0.5rem;
    }
    
    @media (max-width: 991px) {
        .col-lg-5, .col-lg-7 {
            margin-bottom: 1rem;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Select employee functionality
        document.querySelectorAll('.select-employee').forEach(button => {
            button.addEventListener('click', function() {
                const employeeId = this.getAttribute('data-id');
                const employeeName = this.getAttribute('data-name');
                
                // Set the selected employee in the dropdown
                const select = document.getElementById('employee_id');
                
                // Check if option already exists, if not create it
                let option = select.querySelector(`option[value="${employeeId}"]`);
                if (!option) {
                    option = document.createElement('option');
                    option.value = employeeId;
                    option.textContent = employeeName + ' (' + employeeId + ')';
                    select.appendChild(option);
                }
                
                // Select the option
                option.selected = true;
                
                // Fetch and update employee details
                fetchEmployeeDetails(employeeId);
                
                // Scroll to the form section
                document.querySelector('#employee_id').scrollIntoView({ behavior: 'smooth', block: 'center' });
            });
        });
        
        // Function to fetch and display employee details
        function fetchEmployeeDetails(employeeId) {
            fetch('/employees/' + employeeId)
                .then(response => response.json())
                .then(response => {
                    const employee = response.data;

                    // Update previous grade level, step, and salary scale fields
                    if (employee.grade_level && employee.grade_level.name) {
                        document.getElementById('previous_grade_level').value = employee.grade_level.name;
                    }
                    
                    if (employee.step && employee.step.name) {
                        document.getElementById('previous_step').value = employee.step.name;
                    }
                    
                    if (employee.grade_level && employee.grade_level.salary_scale && employee.grade_level.salary_scale.full_name) {
                        document.getElementById('previous_salary_scale').value = employee.grade_level.salary_scale.full_name;
                    } 
                })
                .catch(error => {
                    console.error('Error fetching employee details:', error);
                    const button = document.querySelector(`.select-employee[data-id="${employeeId}"]`);
                    if (button) {
                        const grade = button.getAttribute('data-grade');
                        const step = button.getAttribute('data-step');
                        
                        if (grade && grade !== 'N/A') {
                            document.getElementById('previous_grade_level').value = grade;
                        }
                        
                        if (step && step !== 'N/A') {
                            document.getElementById('previous_step').value = step;
                        }
                    }
                });
        }
        
        // Load salary scales when the page loads
        loadSalaryScales();
        
        // Load salary scales
        function loadSalaryScales() {
            fetch('/api/salary-scales')
                .then(response => response.json())
                .then(salaryScales => {
                    const salaryScaleSelect = document.getElementById('new_salary_scale');
                    salaryScaleSelect.innerHTML = '<option value="">Select Salary Scale</option>';
                    
                    salaryScales.forEach(salaryScale => {
                        const option = document.createElement('option');
                        option.value = salaryScale.id;
                        option.textContent = salaryScale.full_name;
                        salaryScaleSelect.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Error loading salary scales:', error);
                });
        }
        
        // When salary scale is selected, load corresponding grade levels
        document.getElementById('new_salary_scale').addEventListener('change', function() {
            const salaryScaleId = this.value;
            const gradeLevelSelect = document.getElementById('new_grade_level');
            
            gradeLevelSelect.innerHTML = '<option value="">Select Grade Level</option>';
            const stepSelect = document.getElementById('new_step');
            stepSelect.innerHTML = '<option value="">Select Step</option>';
            
            if (salaryScaleId) {
                fetch(`/api/salary-scales/${salaryScaleId}/grade-levels`)
                    .then(response => response.json())
                    .then(gradeLevels => {
                        gradeLevels.forEach(gradeLevel => {
                            const option = document.createElement('option');
                            option.value = gradeLevel.name;
                            option.textContent = gradeLevel.name;
                            gradeLevelSelect.appendChild(option);
                        });
                    })
                    .catch(error => {
                        console.error('Error loading grade levels:', error);
                    });
            }
        });
        
        // When grade level is selected, load corresponding steps
        document.getElementById('new_grade_level').addEventListener('change', function() {
            const gradeLevelName = this.value;
            const salaryScaleId = document.getElementById('new_salary_scale').value;
            const stepSelect = document.getElementById('new_step');
            
            stepSelect.innerHTML = '<option value="">Select Step</option>';
            
            if (gradeLevelName && salaryScaleId) {
                fetch(`/api/salary-scales/${salaryScaleId}/grade-levels/${gradeLevelName}/steps`)
                    .then(response => response.json())
                    .then(steps => {
                        steps.forEach(step => {
                            const option = document.createElement('option');
                            option.value = step.name;
                            option.textContent = step.name;
                            stepSelect.appendChild(option);
                        });
                    })
                    .catch(error => {
                        console.error('Error loading steps:', error);
                    });
            }
        });
    });
</script>
@endsection