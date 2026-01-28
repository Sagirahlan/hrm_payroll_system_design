@extends('layouts.app')

@section('title', 'Eligible for Retirement')

@section('content')
<div class="container mt-4">
    <div class="mb-3">
        <a href="{{ route('retirements.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Retirements
        </a>
    </div>
    
    <div class="card shadow">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0">Employees Eligible for Retirement</h5>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <!-- Search and Filter Form -->
            <form method="GET" action="{{ route('retirements.create') }}" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" name="search" class="form-control" 
                                   placeholder="Search Staff No or name..." 
                                   value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select name="department_id" class="form-select">
                            <option value="">All Departments</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->department_id }}" {{ request('department_id') == $dept->department_id ? 'selected' : '' }}>
                                    {{ $dept->department_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="eligibility_reason" class="form-select">
                            <option value="">All Eligibility Reasons</option>
                            <option value="By Old Age" {{ request('eligibility_reason') == 'By Old Age' ? 'selected' : '' }}>By Old Age</option>
                            <option value="By Years of Service" {{ request('eligibility_reason') == 'By Years of Service' ? 'selected' : '' }}>By Years of Service</option>
                            <option value="Deceased" {{ request('eligibility_reason') == 'Deceased' ? 'selected' : '' }}>Deceased</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <div class="btn-group w-100" role="group">
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-filter me-1"></i> Filter
                            </button>
                            <a href="{{ route('retirements.create') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i> Clear
                            </a>
                        </div>
                    </div>
                </div>
                
                @if(request()->hasAny(['search', 'department_id', 'eligibility_reason']))
                    <div class="mt-2">
                        <small class="text-muted">Active filters:</small>
                        @if(request('search'))
                            <span class="badge bg-info ms-1">Search: "{{ request('search') }}"</span>
                        @endif
                        @if(request('department_id'))
                            <span class="badge bg-primary ms-1">Department: {{ $departments->find(request('department_id'))->department_name ?? 'Unknown' }}</span>
                        @endif
                        @if(request('eligibility_reason'))
                            <span class="badge bg-warning text-dark ms-1">Reason: {{ request('eligibility_reason') }}</span>
                        @endif
                        <span class="badge bg-secondary ms-2">{{ $eligibleEmployees->total() }} employee(s) found</span>
                    </div>
                @endif
            </form>

            <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Staff ID</th>
                        <th>Name</th>
                        <th>Date of Birth</th>
                        <th>Age</th>
                        <th>Appointment Date</th>
                        <th>Expected Retirement Date</th>
                        <th>Years of Service</th>
                        <th>GL/Step</th>
                        <th>Department</th>
                        <th>Rank</th>
                        <th>Eligibility Reason</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($eligibleEmployees as $employee)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $employee->staff_no }}</td>
                            <td>{{ $employee->first_name }} {{ $employee->surname }}</td>
                            <td>{{ \Carbon\Carbon::parse($employee->date_of_birth)->format('Y-m-d') }}</td>
                            <td>{{ \Carbon\Carbon::parse($employee->date_of_birth)->age }}</td>
                            <td>{{ \Carbon\Carbon::parse($employee->date_of_first_appointment)->format('Y-m-d') }}</td>
                            <td>{{ $employee->expected_retirement_date ? \Carbon\Carbon::parse($employee->expected_retirement_date)->format('Y-m-d') : 'N/A' }}</td>
                            <td>{{ round(\Carbon\Carbon::parse($employee->date_of_first_appointment)->diffInYears(\Carbon\Carbon::now())) }} years</td>
                            <td>{{ $employee->gradeLevel ? $employee->gradeLevel->name : 'N/A' }}-{{ $employee->step ? $employee->step->name : 'N/A' }}</td>
                            <td>{{ $employee->department->department_name ?? 'N/A' }}</td>
                            <td>{{ $employee->rank ? $employee->rank->name : 'N/A' }}</td>
                            <td>
                                @php
                                    $retireReason = 'N/A';
                                    if ($employee->status === 'Deceased') {
                                        $retireReason = 'Death in Service';
                                    } elseif ($employee->gradeLevel && $employee->gradeLevel->salaryScale) {
                                        $retirementAge = (int) $employee->gradeLevel->salaryScale->max_retirement_age;
                                        $yearsOfService = (int) $employee->gradeLevel->salaryScale->max_years_of_service;
                                        $age = \Carbon\Carbon::parse($employee->date_of_birth)->age;
                                        $serviceDuration = \Carbon\Carbon::parse($employee->date_of_first_appointment)->diffInYears(\Carbon\Carbon::now());

                                        // Check if the employee has reached the maximum years of service first
                                        if ($serviceDuration >= $yearsOfService) {
                                            $retireReason = 'By Years of Service';
                                        } elseif ($age >= $retirementAge) {
                                            $retireReason = 'By Old Age';
                                        } else {
                                            // If neither condition is met, determine by which will happen first
                                            $actualRetirementDate = \Carbon\Carbon::parse($employee->date_of_birth)->addYears($retirementAge)->min(\Carbon\Carbon::parse($employee->date_of_first_appointment)->addYears($yearsOfService));
                                            if ($actualRetirementDate->eq(\Carbon\Carbon::parse($employee->date_of_birth)->addYears($retirementAge))) {
                                                $retireReason = 'By Old Age';
                                            } else {
                                                $retireReason = 'By Years of Service';
                                            }
                                        }
                                    } else {
                                        $retireReason = 'Missing grade/salary scale information';
                                    }
                                @endphp
                                {{ $retireReason }}
                            </td>
                            <td>
                                <div class="btn-group" role="group" aria-label="Action buttons">
                                  
                                    <a href="{{ route('retirements.pension-compute', $employee->employee_id) }}" class="btn btn-primary btn-sm">
                                        Pension Compute
                                    </a>
                                </div>
                            </td>
                        </tr>

                        <!-- Retire Modal -->
                        <div class="modal fade" id="retireModal{{ $employee->employee_id }}" tabindex="-1" aria-labelledby="retireModalLabel{{ $employee->employee_id }}" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="retireModalLabel{{ $employee->employee_id }}">Confirm Retirement for {{ $employee->first_name }} {{ $employee->surname }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form action="{{ route('retirements.store') }}" method="POST">
                                        <div class="modal-body">
                                            @csrf
                                            <input type="hidden" name="employee_id" value="{{ $employee->employee_id }}">
                                            <input type="hidden" name="retirement_date" value="{{ now()->toDateString() }}">
                                            <input type="hidden" name="status" value="complete">
                                            <div class="mb-3">
                                                <label for="retire_reason_{{ $employee->employee_id }}" class="form-label">Retire Reason</label>
                                                @php
                                                    $retireReason = 'N/A';
                                                    if ($employee->status === 'Deceased') {
                                                        $retireReason = 'Death in Service';
                                                    } elseif ($employee->gradeLevel && $employee->gradeLevel->salaryScale) {
                                                        $retirementAge = (int) $employee->gradeLevel->salaryScale->max_retirement_age;
                                                        $yearsOfService = (int) $employee->gradeLevel->salaryScale->max_years_of_service;
                                                        $age = \Carbon\Carbon::parse($employee->date_of_birth)->age;
                                                        $serviceDuration = \Carbon\Carbon::parse($employee->date_of_first_appointment)->diffInYears(\Carbon\Carbon::now());

                                                        // Check if the employee has reached the maximum years of service first
                                                        if ($serviceDuration >= $yearsOfService) {
                                                            $retireReason = 'By Years of Service';
                                                        } elseif ($age >= $retirementAge) {
                                                            $retireReason = 'By Old Age';
                                                        } else {
                                                            // If neither condition is met, determine by which will happen first
                                                            $actualRetirementDate = \Carbon\Carbon::parse($employee->date_of_birth)->addYears($retirementAge)->min(\Carbon\Carbon::parse($employee->date_of_first_appointment)->addYears($yearsOfService));
                                                            if ($actualRetirementDate->eq(\Carbon\Carbon::parse($employee->date_of_birth)->addYears($retirementAge))) {
                                                                $retireReason = 'By Old Age';
                                                            } else {
                                                                $retireReason = 'By Years of Service';
                                                            }
                                                        }
                                                    } else {
                                                        $retireReason = 'Missing grade/salary scale information';
                                                    }
                                                @endphp
                                                <input type="text" name="retire_reason" id="retire_reason_{{ $employee->employee_id }}" class="form-control" value="{{ $retireReason }}" readonly>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-success">Confirm Retirement</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="13" class="text-center">No employees are currently eligible for retirement.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            </div>
            
            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                {{ $eligibleEmployees->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>
@endsection