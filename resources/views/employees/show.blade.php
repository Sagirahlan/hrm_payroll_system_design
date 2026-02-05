@extends('layouts.app')

@section('content')
<div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="card border-0 shadow-lg" style="max-width: 800px; width: 100%; background: linear-gradient(135deg, #e0f7fa 0%, #ffffff 100%);">
        <div class="card-header text-center" style="background: #00bcd4;">
            <h4 class="mb-0 text-white font-weight-bold">Employee Details</h4>
        </div>
        <div class="card-body p-4">
            @if ($employee->photo_path)
                <div class="mb-4 text-center">
                    <img src="{{ asset('storage/' . $employee->photo_path) }}" alt="Employee Photo" class="img-thumbnail border border-info shadow-sm" width="120">
                </div>
            @endif

            <!-- Step Navigation -->
            <ul class="nav nav-pills justify-content-center flex-nowrap overflow-auto mb-4" id="employeeTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="personal-tab" data-bs-toggle="tab" data-bs-target="#personal" type="button" role="tab">Personal</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact" type="button" role="tab">Contact</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="work-tab" data-bs-toggle="tab" data-bs-target="#work" type="button" role="tab">Work</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="other-tab" data-bs-toggle="tab" data-bs-target="#other" type="button" role="tab">Other</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="kin-tab" data-bs-toggle="tab" data-bs-target="#kin" type="button" role="tab">Next of Kin</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="bank-tab" data-bs-toggle="tab" data-bs-target="#bank" type="button" role="tab">Bank</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="probation-tab" data-bs-toggle="tab" data-bs-target="#probation" type="button" role="tab">Probation</button>
                </li>

            </ul>

            <!-- Step Content -->
            <div class="tab-content" id="employeeTabContent">
                <!-- Personal Information Step -->
                <div class="tab-pane fade show active" id="personal" role="tabpanel">
                    <h5 class="text-primary mb-3">Personal Information</h5>
                    <div class="row">
                        <div class="col-md-6"><p><strong>First Name:</strong> {{ $employee->first_name }}</p></div>
                        <div class="col-md-6"><p><strong>Surname:</strong> {{ $employee->surname }}</p></div>
                        <div class="col-md-6"><p><strong>Middle Name:</strong> {{ $employee->middle_name ?? 'N/A' }}</p></div>
                        <div class="col-md-6"><p><strong>Gender:</strong> {{ $employee->gender }}</p></div>
                        <div class="col-md-6"><p><strong>Date of Birth:</strong> {{ $employee->date_of_birth }}</p></div>
                        <div class="col-md-6"><p><strong>Age:</strong> {{ \Carbon\Carbon::parse($employee->date_of_birth)->age }} years</p></div>
                        <div class="col-md-6"><p><strong>Nationality:</strong> {{ $employee->nationality }}</p></div>
                        <div class="col-md-6"><p><strong>State of Origin:</strong> {{ $employee->state->name ?? 'N/A' }}</p></div>
                        <div class="col-md-6"><p><strong>LGA:</strong> {{ $employee->lga->name ?? 'N/A' }}</p></div>
                        <div class="col-md-6"><p><strong>Ward:</strong> {{ $employee->ward->ward_name ?? 'N/A' }}</p></div>
                        <div class="col-md-6"><p><strong>Staff ID:</strong> {{ $employee->staff_no ?? 'N/A' }}</p></div>
                        <div class="col-md-6"><p><strong>NIN:</strong> {{ $employee->nin ?? 'N/A' }}</p></div>
                    </div>
                </div>

                <!-- Contact & Address Step -->
                <div class="tab-pane fade" id="contact" role="tabpanel">
                    <h5 class="text-primary mb-3">Contact & Address</h5>
                    <div class="row">
                        <div class="col-md-6"><p><strong>Mobile No:</strong> {{ $employee->mobile_no }}</p></div>
<div class="col-md-6">
                                <p><strong>Email:</strong> {{ $employee->email ?? 'N/A' }}</p>
                                <p><strong>Pay Point:</strong> {{ $employee->pay_point ?? 'N/A' }}</p>
                                <p><strong>Address:</strong> {{ $employee->address ?? 'N/A' }}</p>
                            </div>
                    </div>
                </div>

                <!-- Work Information Step -->
                <div class="tab-pane fade" id="work" role="tabpanel">
                    <h5 class="text-primary mb-3">Work Information</h5>
                    <div class="row">
                        @if ($employee->appointmentType->name !== 'Casual')
                            <div class="col-md-6"><p><strong>Date of First Appointment:</strong> {{ $employee->date_of_first_appointment ? \Carbon\Carbon::parse($employee->date_of_first_appointment)->format('j M Y') : '—' }}</p></div>
                            <div class="col-md-6"><p><strong>Years of Service:</strong> {{ $employee->years_of_service !== null ? $employee->years_of_service . ' ' . Str::plural('year', $employee->years_of_service) : '—' }}</p></div>
                            <div class="col-md-6"><p><strong>Cadre:</strong> {{ $employee->cadre->name ?? 'N/A' }}</p></div>
                            <div class="col-md-6"><p><strong>Salary Scale:</strong> {{ $employee->gradeLevel->salaryScale->acronym ?? 'N/A' }} - {{ $employee->gradeLevel->salaryScale->full_name ?? 'N/A' }}</p></div>
                            <div class="col-md-6"><p><strong>Grade Level:</strong> {{ $employee->gradeLevel->name ?? 'N/A' }}</p></div>
                            <div class="col-md-6"><p><strong>Step:</strong> {{ $employee->step->name ?? 'N/A' }}</p></div>
                            <div class="col-md-6"><p><strong>Rank:</strong> {{ $employee->rank->title ?? 'N/A' }}</p></div>
                            <div class="col-md-6"><p><strong>Department:</strong> {{ $employee->department->department_name ?? 'N/A' }}</p></div>
                            <div class="col-md-6"><p><strong>Expected Next Promotion:</strong> {{ $employee->expected_next_promotion ? \Carbon\Carbon::parse($employee->expected_next_promotion)->format('j M Y') : 'N/A' }}</p></div>
                            <div class="col-md-6"><p><strong>Expected Retirement Date:</strong> {{ $employee->expected_retirement_date ? \Carbon\Carbon::parse($employee->expected_retirement_date)->format('j M Y') : '—' }}</p></div>
                        @else
                            <div class="col-md-6"><p><strong>Casual Start Date:</strong> {{ $employee->Casual_start_date ? \Carbon\Carbon::parse($employee->Casual_start_date)->format('j M Y') : '—' }}</p></div>
                            <div class="col-md-6"><p><strong>Casual End Date:</strong> {{ $employee->Casual_end_date ? \Carbon\Carbon::parse($employee->Casual_end_date)->format('j M Y') : '—' }}</p></div>
                            <div class="col-md-6"><p><strong>Amount:</strong> {{ $employee->amount ? number_format($employee->amount, 2) : 'N/A' }}</p></div>
                            <div class="col-md-6"><p><strong>Department:</strong> {{ $employee->department->department_name ?? 'N/A' }}</p></div>
                        @endif
                    </div>
                </div>

                <!-- Other Details Step -->
                <div class="tab-pane fade" id="other" role="tabpanel">
                    <h5 class="text-primary mb-3">Other Details</h5>
                    <div class="row">
                        <div class="col-md-6"><p><strong>Status:</strong> {{ $employee->status }}</p></div>
                        <div class="col-md-6"><p><strong>Highest Certificate:</strong> {{ $employee->highest_certificate ?? 'N/A' }}</p></div>
                        <div class="col-md-6"><p><strong>Appointment Type:</strong> {{ $employee->appointmentType->name ?? 'N/A' }}</p></div>
                    </div>
                </div>

                <!-- Next of Kin Step -->
                <div class="tab-pane fade" id="kin" role="tabpanel">
                    @if ($employee->nextOfKin)
                        <h5 class="text-primary">Next of Kin Details</h5>
                        <div class="row">
                            <div class="col-md-6"><p><strong>Name:</strong> {{ $employee->nextOfKin->name }}</p></div>
                            <div class="col-md-6"><p><strong>Relationship:</strong> {{ $employee->nextOfKin->relationship }}</p></div>
                            <div class="col-md-6"><p><strong>Phone:</strong> {{ $employee->nextOfKin->mobile_no }}</p></div>
                            <div class="col-md-6"><p><strong>Address:</strong> {{ $employee->nextOfKin->address }}</p></div>
                            <div class="col-md-6"><p><strong>Occupation:</strong> {{ $employee->nextOfKin->occupation ?? 'N/A' }}</p></div>
                            <div class="col-md-6"><p><strong>Place of Work:</strong> {{ $employee->nextOfKin->place_of_work ?? 'N/A' }}</p></div>
                        </div>
                    @else
                        <div class="alert alert-warning mt-4">No next of kin details available.</div>
                    @endif
                </div>

                <!-- Bank Details Step -->
                <div class="tab-pane fade" id="bank" role="tabpanel">
                    @if ($employee->bank)
                        <h5 class="text-primary">Bank Information</h5>
                        <div class="row">
                            <div class="col-md-6"><p><strong>Bank Name:</strong> {{ $employee->bank->bank_name }}</p></div>
                            <div class="col-md-6"><p><strong>Bank Code:</strong> {{ $employee->bank->bank_code }}</p></div>
                            <div class="col-md-6"><p><strong>Account Name:</strong> {{ $employee->bank->account_name }}</p></div>
                            <div class="col-md-6"><p><strong>Account Number:</strong> {{ $employee->bank->account_no }}</p></div>
                        </div>
                    @else
                        <div class="alert alert-warning mt-4">No bank information available.</div>
                    @endif
                </div>

                <!-- Probation Details Step -->
                <div class="tab-pane fade" id="probation" role="tabpanel">
                    <h5 class="text-primary mb-3">Probation Information</h5>
                    <div class="row">
                        <div class="col-md-6"><p><strong>On Probation:</strong>
                            @if($employee->on_probation)
                                <span class="badge bg-warning">Yes</span>
                            @else
                                <span class="badge bg-secondary">No</span>
                            @endif
                        </p></div>
                        <div class="col-md-6"><p><strong>Probation Status:</strong>
                            @if($employee->probation_status == 'pending')
                                <span class="badge bg-warning">On Probation</span>
                            @elseif($employee->probation_status == 'approved')
                                <span class="badge bg-success">Approved</span>
                            @elseif($employee->probation_status == 'rejected')
                                <span class="badge bg-danger">Rejected</span>
                            @else
                                <span class="badge bg-secondary">N/A</span>
                            @endif
                        </p></div>
                        <div class="col-md-6"><p><strong>Probation Start Date:</strong> {{ $employee->probation_start_date ? \Carbon\Carbon::parse($employee->probation_start_date)->format('j M Y') : 'N/A' }}</p></div>
                        <div class="col-md-6"><p><strong>Probation End Date:</strong> {{ $employee->probation_end_date ? \Carbon\Carbon::parse($employee->probation_end_date)->format('j M Y') : 'N/A' }}</p></div>
                        @if($employee->on_probation)
                            <div class="col-md-6">
                                <p><strong>Days Remaining:</strong>
                                    @if($employee->hasProbationPeriodEnded())
                                        <span class="text-danger">Probation Ended</span>
                                    @else
                                        {{ $employee->getRemainingProbationDays() }} days
                                    @endif
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Can be Evaluated:</strong>
                                    @if($employee->canBeEvaluatedForProbation())
                                        <span class="badge bg-success">Yes</span>
                                    @else
                                        <span class="badge bg-warning">No ({{ $employee->getRemainingProbationDays() }} days remaining)</span>
                                    @endif
                                </p>
                            </div>
                        @endif
                        @if($employee->probation_notes)
                            <div class="col-md-12">
                                <p><strong>Probation Notes:</strong></p>
                                <div class="alert alert-info">
                                    {{ $employee->probation_notes }}
                                </div>
                            </div>
                        @endif
                    </div>

                    @if($employee->on_probation)
                        <div class="mt-3">
                            <a href="{{ route('probation.show', $employee) }}" class="btn btn-info rounded-pill">
                                <i class="fas fa-clock me-1"></i>Manage Probation
                            </a>
                        </div>
                    @endif
                </div>


            <div class="d-flex justify-content-between mt-4">
                <a href="{{ route('employees.index') }}" class="btn btn-secondary rounded-pill px-4">Back</a>
                @can('edit_employees')
                <div>
                    <a href="{{ route('employees.edit', $employee) }}" class="btn btn-warning rounded-pill px-4">Edit</a>
                </div>
                @endcan
            </div>
        </div>
    </div>
</div>
@endsection

