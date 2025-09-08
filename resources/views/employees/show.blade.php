@extends('layouts.app')

@section('content')
<div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="card border-0 shadow-lg" style="max-width: 700px; width: 100%; background: linear-gradient(135deg, #e0f7fa 0%, #ffffff 100%);">
        <div class="card-header text-center" style="background: #00bcd4;">
            <h4 class="mb-0 text-white font-weight-bold">Employee Details</h4>
        </div>
        <div class="card-body p-4">
            @if ($employee->photo_path)
                <div class="mb-4 text-center">
                    <img src="{{ asset('storage/' . $employee->photo_path) }}" alt="Employee Photo" class="img-thumbnail border border-info shadow-sm" width="120">
                </div>
            @endif
            <div class="row">
                <div class="col-md-6 mb-3">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item"><strong>Name:</strong> {{ $employee->first_name }}</li>
                        <li class="list-group-item"><strong>Surname:</strong> {{ $employee->surname }}</li>
                        <li class="list-group-item"><strong>Middle Name:</strong> {{ $employee->middle_name ?? 'N/A' }}</li>
                        <li class="list-group-item"><strong>Gender:</strong> {{ $employee->gender }}</li>
                        <li class="list-group-item"><strong>Date of Birth:</strong> {{ $employee->date_of_birth }}</li>
                        <li class="list-group-item"><strong>Age:</strong> {{ \Carbon\Carbon::parse($employee->date_of_birth)->age }} years</li>
                        <li class="list-group-item"><strong>Mobile No:</strong> {{ $employee->mobile_no }}</li>
                        <li class="list-group-item"><strong>Email:</strong> {{ $employee->email ?? 'N/A' }}</li>
                        <li class="list-group-item"><strong>Address:</strong> {{ $employee->address }}</li>
                        <li class="list-group-item"><strong>ID:</strong> {{ $employee->employee_id }}</li>
                        <li class="list-group-item"><strong>NIN:</strong> {{ $employee->nin ?? 'N/A' }}</li>
                        <li class="list-group-item"><strong>Staff ID:</strong> {{ $employee->reg_no ?? 'N/A' }}</li>
                        <li class="list-group-item"><strong>Nationality:</strong> {{ $employee->nationality }}</li>
                        <li class="list-group-item"><strong>State of Origin:</strong> {{ $employee->state->name ?? 'N/A' }}</li>
                        <li class="list-group-item"><strong>LGA:</strong> {{ $employee->lga->name ?? 'N/A' }}</li>
                    </ul>
                </div>
                <div class="col-md-6 mb-3">
                <ul class="list-group list-group-flush">
    <li class="list-group-item"><strong>Appointment Type:</strong> {{ $employee->appointmentType->name ?? 'N/A' }}</li>
    <li class="list-group-item"><strong>Cadre:</strong> {{ $employee->cadre->name }}</li>

    {{-- date hired --}}
    <li class="list-group-item">
        <strong>Date of First Appointment:</strong>
        @php
            $dfa = $employee->date_of_first_appointment;
        @endphp
        {{ $dfa ? \Carbon\Carbon::parse($dfa)->format('j M Y') : '—' }}
    </li>

    {{-- NEW – years of service --}}
    <li class="list-group-item">
        <strong>Years of Service:</strong>
        {{ $employee->years_of_service !== null
            ? $employee->years_of_service . ' ' . Str::plural('year', $employee->years_of_service)
            : '—' }}
    </li>

    <li class="list-group-item"><strong>Department:</strong> {{ $employee->department->department_name }}</li>
    <li class="list-group-item">
        <strong>Expected Next Promotion:</strong>
        @php
            $enp = $employee->expected_next_promotion;
        @endphp
        {{ $enp ? \Carbon\Carbon::parse($enp)->format('j M Y') : 'N/A' }}
    </li>
    <li class="list-group-item">
        <strong>Expected Retirement Date:</strong>
        @php
            $erd = $employee->expected_retirement_date;
        @endphp
        {{ $erd ? \Carbon\Carbon::parse($erd)->format('j M Y') : '—' }}
    </li>
    <li class="list-group-item"><strong>Grade Level Limit:</strong> {{ $employee->grade_level_limit ?? 'N/A' }}</li>
    <li class="list-group-item"><strong>Highest Certificate:</strong> {{ $employee->highest_certificate ?? 'N/A' }}</li>
    
    <!-- Updated to show salary scale and grade level information -->
    @if($employee->gradeLevel)
        @if($employee->gradeLevel->salaryScale)
            <li class="list-group-item"><strong>Salary Scale:</strong> {{ $employee->gradeLevel->salaryScale->acronym }} - {{ $employee->gradeLevel->salaryScale->full_name }}</li>
        @endif
        <li class="list-group-item"><strong>Grade Level:</strong> {{ $employee->gradeLevel->name }}</li>
    @else
        <li class="list-group-item"><strong>Salary Scale:</strong> N/A</li>
        <li class="list-group-item"><strong>Grade Level:</strong> N/A</li>
    @endif
    
    <li class="list-group-item"><strong>Status:</strong> {{ $employee->status }}</li>
</ul>
                    {{-- New Individual Export Button --}}
                    <a href="{{ route('employee.export', $employee->employee_id) }}" class="btn btn-success btn-sm rounded-pill me-1 font-weight-bold shadow-sm">Export</a>
                </div>
            </div>
            {{-- Bank Details --}}
            @if ($employee->bank)
                <h5 class="mt-4 text-primary">Bank Information</h5>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><strong>Bank Name:</strong> {{ $employee->bank->bank_name }}</li>
                    <li class="list-group-item"><strong>Bank Code:</strong> {{ $employee->bank->bank_code }}</li>
                    <li class="list-group-item"><strong>Account Name:</strong> {{ $employee->bank->account_name }}</li>
                    <li class="list-group-item"><strong>Account Number:</strong> {{ $employee->bank->account_no }}</li>
                </ul>
            @else
                <div class="alert alert-warning mt-4">No bank information available.</div>
            @endif
            {{-- Next of Kin Details --}}
            @if ($employee->nextOfKin)
                <h5 class="mt-4 text-primary">Next of Kin Details</h5>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><strong>Name:</strong> {{ $employee->nextOfKin->name }}</li>
                    <li class="list-group-item"><strong>Relationship:</strong> {{ $employee->nextOfKin->relationship }}</li>
                    <li class="list-group-item"><strong>Phone:</strong> {{ $employee->nextOfKin->mobile_no }}</li>
                    <li class="list-group-item"><strong>Address:</strong> {{ $employee->nextOfKin->address }}</li>
                    <li class="list-group-item"><strong>Occupation:</strong> {{ $employee->nextOfKin->occupation ?? 'N/A' }}</li>
                    <li class="list-group-item"><strong>Place of Work:</strong> {{ $employee->nextOfKin->place_of_work ?? 'N/A' }}</li>
                </ul>
            @else
                <div class="alert alert-warning mt-4">No next of kin details available.</div>
            @endif

            <div class="d-flex justify-content-between mt-4">
                <a href="{{ route('employees.index') }}" class="btn btn-secondary rounded-pill px-4">Back</a>
                <a href="{{ route('employees.edit', $employee) }}" class="btn btn-warning rounded-pill px-4">Edit</a>
            </div>
        </div>
    </div>
</div>
@endsection