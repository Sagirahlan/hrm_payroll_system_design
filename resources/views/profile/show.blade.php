@extends('layouts.app')

@section('title', 'Profile')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">{{ __('My Profile') }}</h4>
                </div>
                <div class="card-body">
                    @if(session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <!-- Profile Header -->
                    <div class="row mb-4">
                        <div class="col-md-12 text-center">
                            <img src="{{ $user->employee && $user->employee->photo_path ? asset('storage/' . $user->employee->photo_path) : asset('images/default-image.png') }}" 
                                 alt="Profile" class="rounded-circle border border-2 mb-3" 
                                 style="width: 120px; height: 120px; object-fit: cover;">
                            <h5>{{ $user->employee ? $user->employee->first_name . ' ' . $user->employee->surname : $user->username }}</h5>
                            <span class="badge bg-secondary">{{ $user->roles->first()?->name ?? 'No role assigned' }}</span>
                        </div>
                    </div>

                    <!-- Step Navigation -->
                    <ul class="nav nav-pills nav-fill mb-4" id="profileTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="personal-tab" data-bs-toggle="pill" data-bs-target="#personal" type="button" role="tab">
                                <i class="fas fa-user"></i> Personal
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="employment-tab" data-bs-toggle="pill" data-bs-target="#employment" type="button" role="tab">
                                <i class="fas fa-briefcase"></i> Employment
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="contact-tab" data-bs-toggle="pill" data-bs-target="#contact" type="button" role="tab">
                                <i class="fas fa-address-book"></i> Contact
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="banking-tab" data-bs-toggle="pill" data-bs-target="#banking" type="button" role="tab">
                                <i class="fas fa-university"></i> Banking
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="nextofkin-tab" data-bs-toggle="pill" data-bs-target="#nextofkin" type="button" role="tab">
                                <i class="fas fa-users"></i> Next of Kin
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="location-tab" data-bs-toggle="pill" data-bs-target="#location" type="button" role="tab">
                                <i class="fas fa-map-marker-alt"></i> Location
                            </button>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content" id="profileTabContent">
                        <!-- Personal Information -->
                        <div class="tab-pane fade show active" id="personal" role="tabpanel">
                            <h5 class="mb-3">{{ __('Personal Information') }}</h5>
                            <table class="table table-striped">
                                <tr>
                                    <td width="40%"><strong>{{ __('Username') }}</strong></td>
                                    <td>{{ $user->username }}</td>
                                </tr>
                                <tr>
                                    <td><strong>{{ __('Email') }}</strong></td>
                                    <td>{{ $user->email }}</td>
                                </tr>
                                <tr>
                                    <td><strong>{{ __('Employee ID') }}</strong></td>
                                    <td>{{ $user->employee ? $user->employee->employee_id : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>{{ __('Registration No') }}</strong></td>
                                    <td>{{ $user->employee ? $user->employee->staff_no : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>{{ __('Full Name') }}</strong></td>
                                    <td>{{ $user->employee ? $user->employee->first_name . ' ' . $user->employee->middle_name . ' ' . $user->employee->surname : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>{{ __('Gender') }}</strong></td>
                                    <td>{{ $user->employee ? $user->employee->gender : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>{{ __('Date of Birth') }}</strong></td>
                                    <td>{{ $user->employee ? \Carbon\Carbon::parse($user->employee->date_of_birth)->format('d M Y') : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>{{ __('NIN') }}</strong></td>
                                    <td>{{ $user->employee ? $user->employee->nin : 'N/A' }}</td>
                                </tr>
                            </table>
                            <div class="text-end mt-3">
                                <button class="btn btn-primary" onclick="document.getElementById('employment-tab').click()">
                                    Next <i class="fas fa-arrow-right"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Employment Information -->
                        <div class="tab-pane fade" id="employment" role="tabpanel">
                            <h5 class="mb-3">{{ __('Employment Information') }}</h5>
                            <table class="table table-striped">
                                <tr>
                                    <td width="40%"><strong>{{ __('Department') }}</strong></td>
                                    <td>{{ $user->employee && $user->employee->department ? $user->employee->department->department_name : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>{{ __('Cadre') }}</strong></td>
                                    <td>{{ $user->employee && $user->employee->cadre ? $user->employee->cadre->name : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>{{ __('Grade Level') }}</strong></td>
                                    <td>{{ $user->employee && $user->employee->gradeLevel ? $user->employee->gradeLevel->name : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>{{ __('Step') }}</strong></td>
                                    <td>{{ $user->employee && $user->employee->step ? $user->employee->step->name : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>{{ __('Rank') }}</strong></td>
                                    <td>{{ $user->employee && $user->employee->rank ? $user->employee->rank->name : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>{{ __('Status') }}</strong></td>
                                    <td>
                                        @if($user->employee)
                                            <span class="badge 
                                                @if($user->employee->status === 'Active') bg-success 
                                                @elseif($user->employee->status === 'Suspended') bg-warning 
                                                @elseif($user->employee->status === 'Retired') bg-info 
                                                @elseif($user->employee->status === 'Deceased') bg-danger 
                                                @else bg-secondary @endif">
                                                {{ $user->employee->status }}
                                            </span>
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>{{ __('Date of First Appointment') }}</strong></td>
                                    <td>{{ $user->employee ? \Carbon\Carbon::parse($user->employee->date_of_first_appointment)->format('d M Y') : 'N/A' }}</td>
                                </tr>
                            </table>
                            <div class="d-flex justify-content-between mt-3">
                                <button class="btn btn-secondary" onclick="document.getElementById('personal-tab').click()">
                                    <i class="fas fa-arrow-left"></i> Previous
                                </button>
                                <button class="btn btn-primary" onclick="document.getElementById('contact-tab').click()">
                                    Next <i class="fas fa-arrow-right"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Contact Information -->
                        <div class="tab-pane fade" id="contact" role="tabpanel">
                            <h5 class="mb-3">{{ __('Contact Information') }}</h5>
                            <table class="table table-striped">
                                <tr>
                                    <td width="40%"><strong>{{ __('Mobile No') }}</strong></td>
                                    <td>{{ $user->employee ? $user->employee->mobile_no : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>{{ __('Email') }}</strong></td>
                                    <td>{{ $user->employee ? $user->employee->email : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>{{ __('Address') }}</strong></td>
                                    <td>{{ $user->employee ? $user->employee->address : 'N/A' }}</td>
                                </tr>
                            </table>
                            <div class="d-flex justify-content-between mt-3">
                                <button class="btn btn-secondary" onclick="document.getElementById('employment-tab').click()">
                                    <i class="fas fa-arrow-left"></i> Previous
                                </button>
                                <button class="btn btn-primary" onclick="document.getElementById('banking-tab').click()">
                                    Next <i class="fas fa-arrow-right"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Banking Information -->
                        <div class="tab-pane fade" id="banking" role="tabpanel">
                            <h5 class="mb-3">{{ __('Banking Information') }}</h5>
                            <table class="table table-striped">
                                <tr>
                                    <td width="40%"><strong>{{ __('Bank Name') }}</strong></td>
                                    <td>{{ $user->employee && $user->employee->bank ? $user->employee->bank->bank_name : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>{{ __('Account Name') }}</strong></td>
                                    <td>{{ $user->employee ? $user->employee->account_name : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>{{ __('Account Number') }}</strong></td>
                                    <td>{{ $user->employee ? $user->employee->account_no : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>{{ __('Bank Code') }}</strong></td>
                                    <td>{{ $user->employee ? $user->employee->bank_code : 'N/A' }}</td>
                                </tr>
                            </table>
                            <div class="d-flex justify-content-between mt-3">
                                <button class="btn btn-secondary" onclick="document.getElementById('contact-tab').click()">
                                    <i class="fas fa-arrow-left"></i> Previous
                                </button>
                                <button class="btn btn-primary" onclick="document.getElementById('nextofkin-tab').click()">
                                    Next <i class="fas fa-arrow-right"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Next of Kin Information -->
                        <div class="tab-pane fade" id="nextofkin" role="tabpanel">
                            <h5 class="mb-3">{{ __('Next of Kin') }}</h5>
                            <table class="table table-striped">
                                @if($user->employee && $user->employee->nextOfKin)
                                <tr>
                                    <td width="40%"><strong>{{ __('Name') }}</strong></td>
                                    <td>{{ $user->employee->nextOfKin->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>{{ __('Relationship') }}</strong></td>
                                    <td>{{ $user->employee->nextOfKin->relationship }}</td>
                                </tr>
                                <tr>
                                    <td><strong>{{ __('Mobile No') }}</strong></td>
                                    <td>{{ $user->employee->nextOfKin->mobile_no }}</td>
                                </tr>
                                <tr>
                                    <td><strong>{{ __('Address') }}</strong></td>
                                    <td>{{ $user->employee->nextOfKin->address }}</td>
                                </tr>
                                <tr>
                                    <td><strong>{{ __('Occupation') }}</strong></td>
                                    <td>{{ $user->employee->nextOfKin->occupation ?: 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>{{ __('Place of Work') }}</strong></td>
                                    <td>{{ $user->employee->nextOfKin->place_of_work ?: 'N/A' }}</td>
                                </tr>
                                @else
                                <tr>
                                    <td colspan="2" class="text-center text-muted">{{ __('No next of kin information available') }}</td>
                                </tr>
                                @endif
                            </table>
                            <div class="d-flex justify-content-between mt-3">
                                <button class="btn btn-secondary" onclick="document.getElementById('banking-tab').click()">
                                    <i class="fas fa-arrow-left"></i> Previous
                                </button>
                                <button class="btn btn-primary" onclick="document.getElementById('location-tab').click()">
                                    Next <i class="fas fa-arrow-right"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Location Information -->
                        <div class="tab-pane fade" id="location" role="tabpanel">
                            <h5 class="mb-3">{{ __('Location Information') }}</h5>
                            <table class="table table-striped">
                                <tr>
                                    <td width="40%"><strong>{{ __('State of Origin') }}</strong></td>
                                    <td>{{ $user->employee && $user->employee->state ? $user->employee->state->name : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>{{ __('LGA') }}</strong></td>
                                    <td>{{ $user->employee && $user->employee->lga ? $user->employee->lga->name : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>{{ __('Ward') }}</strong></td>
                                    <td>{{ $user->employee && $user->employee->ward ? $user->employee->ward->ward_name : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>{{ __('Nationality') }}</strong></td>
                                    <td>{{ $user->employee ? $user->employee->nationality : 'N/A' }}</td>
                                </tr>
                            </table>
                            <div class="text-start mt-3">
                                <button class="btn btn-secondary" onclick="document.getElementById('nextofkin-tab').click()">
                                    <i class="fas fa-arrow-left"></i> Previous
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection