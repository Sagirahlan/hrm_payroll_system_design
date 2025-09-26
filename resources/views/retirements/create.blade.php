@extends('layouts.app')

@section('title', 'Eligible for Retirement')

@section('content')
<div class="container mt-4">
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
                            <td>{{ $employee->reg_no }}</td>
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
                                    if ($employee->gradeLevel && $employee->gradeLevel->salaryScale) {
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
                                <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#retireModal{{ $employee->employee_id }}">
                                    Retire
                                </button>
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
                                                    if ($employee->gradeLevel && $employee->gradeLevel->salaryScale) {
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
    </div>
</div>
@endsection