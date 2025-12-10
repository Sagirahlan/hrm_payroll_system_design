@extends('layouts.app')

@section('content')
<div class="container py-4">
    @can('create_employees')
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card border-0 shadow-lg rounded-lg">
                <div class="card-header bg-info text-white text-center rounded-top">
                    <h4 class="mb-0 font-weight-bold">Add Employee</h4>
                </div>
                <div class="card-body px-3 px-md-5 py-4">
                    <div class="mb-4">
                        <ul class="nav nav-pills justify-content-center flex-nowrap overflow-auto" id="stepNav">
                            <li class="nav-item"><a class="nav-link active" href="#" onclick="showStep(1)">Personal</a></li>
                            <li class="nav-item"><a class="nav-link" href="#" onclick="showStep(2)">Contact</a></li>
                            <li class="nav-item"><a class="nav-link" href="#" onclick="showStep(3)">Work</a></li>
                            <li class="nav-item"><a class="nav-link" href="#" onclick="showStep(4)">Other</a></li>
                            <li class="nav-item"><a class="nav-link" href="#" onclick="showStep(5)">Next of Kin</a></li>
                            <li class="nav-item"><a class="nav-link" href="#" onclick="showStep(6)">Bank</a></li>
                        </ul>
                    </div>
                    <form id="employeeForm" action="{{ route('employees.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="change_reason" value="New employee creation">

                        <!-- Step 1: Personal Information -->
                        <div class="step-card" id="step1">
                            <h5 class="mb-3 text-info text-center font-weight-bold">Personal Information</h5>
                            <div class="row g-3">
                                <div class="col-md-4 col-12">
                                    <label class="form-label font-weight-bold">First Name <span class="text-danger">*</span></label>
                                    <input type="text" name="first_name" class="form-control" required value="{{ old('first_name') }}">
                                    @error('first_name') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-4 col-12">
                                    <label class="form-label font-weight-bold">Surname <span class="text-danger">*</span></label>
                                    <input type="text" name="surname" class="form-control" required value="{{ old('surname') }}">
                                    @error('surname') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-4 col-12">
                                    <label class="form-label font-weight-bold">Middle Name (optional)</label>
                                    <input type="text" name="middle_name" class="form-control" value="{{ old('middle_name') }}">
                                    @error('middle_name') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-4 col-12">
                                    <label class="form-label font-weight-bold">Gender <span class="text-danger">*</span></label>
                                    <select name="gender" class="form-select" required>
                                        <option value="Male" {{ old('gender') == 'Male' ? 'selected' : '' }}>Male</option>
                                        <option value="Female" {{ old('gender') == 'Female' ? 'selected' : '' }}>Female</option>
                                    </select>
                                    @error('gender') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-4 col-12">
                                    <label class="form-label font-weight-bold">Date of Birth <span class="text-danger">*</span></label>
                                    <input type="date" name="date_of_birth" class="form-control" required value="{{ old('date_of_birth') }}">
                                    @error('date_of_birth') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-4 col-12">
                                    <label class="form-label font-weight-bold">Nationality <span class="text-danger">*</span></label>
                                    <select name="nationality" class="form-select" required>
                                        <option value="">-- Select Nationality --</option>
                                        <option value="Nigeria" {{ old('nationality') == 'Nigeria' ? 'selected' : '' }}>Nigeria</option>
                                       
                                    </select>
                                    @error('nationality') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-4 col-12">
                                    <label class="form-label font-weight-bold">State of Origin <span class="text-danger">*</span></label>
                                    <select id="state" name="state_id" class="form-select" required>
                                        <option value="">-- Select State --</option>
                                        @foreach($states as $state)
                                            <option value="{{ $state->state_id }}" {{ old('state_id') == $state->state_id ? 'selected' : '' }}>{{ $state->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('state_id') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-4 col-12">
                                    <label class="form-label font-weight-bold">Local Government Area (LGA) <span class="text-danger">*</span></label>
                                    <select id="lga" name="lga_id" class="form-select" required>
                                        <option value="">-- Select LGA --</option>
                                    </select>
                                    @error('lga_id') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-4 col-12">
                                    <label class="form-label font-weight-bold">Ward (optional)</label>
                                    <select id="ward" name="ward_id" class="form-select">
                                        <option value="">-- Select Ward --</option>
                                    </select>
                                    @error('ward_id') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-4 col-12">
                                    <label class="form-label font-weight-bold">Staff ID <span class="text-danger">*</span></label>
                                    <input type="text" name="staff_no" class="form-control" required value="{{ old('staff_no') }}">
                                    @error('staff_no') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-4 col-12">
                                    <label class="form-label font-weight-bold">NIN <span class="text-danger">*</span></label>
                                    <input type="text" name="nin" class="form-control" value="{{ old('nin') }}">
                                    @error('nin') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-4 col-12">
                                    <label class="form-label font-weight-bold">Mobile No <span class="text-danger">*</span></label>
                                    <input type="text" name="mobile_no" class="form-control" required value="{{ old('mobile_no') }}">
                                    @error('mobile_no') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                            </div>
                            <div class="d-flex justify-content-end mt-4">
                                <button type="button" class="btn btn-info px-4" onclick="nextStep(2)">Next</button>
                            </div>
                        </div>

                        <!-- Step 2: Contact & Address -->
                        <div class="step-card d-none" id="step2">
                            <h5 class="mb-3 text-info text-center font-weight-bold">Contact & Address</h5>
                            <div class="row g-3">
                                <div class="col-md-6 col-12">
                                    <label class="form-label font-weight-bold">Email (optional)</label>
                                    <input type="email" name="email" class="form-control" value="{{ old('email') }}">
                                    @error('email') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-6 col-12">
                                    <label class="form-label font-weight-bold">Pay Point <span class="text-danger">*</span></label>
                                    <input type="text" name="pay_point" class="form-control" value="{{ old('pay_point') }}">
                                    @error('pay_point') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-6 col-12">
                                    <label class="form-label font-weight-bold">Address <span class="text-danger">*</span></label>
                                    <textarea name="address" class="form-control" required>{{ old('address') }}</textarea>
                                    @error('address') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                            </div>
                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-secondary px-4" onclick="prevStep(1)">Previous</button>
                                <button type="button" class="btn btn-info px-4" onclick="nextStep(3)">Next</button>
                            </div>
                        </div>

                        <!-- Step 3: Appointment & Work Details -->
                        <div class="step-card d-none" id="step3">
                            <h5 class="mb-3 text-info text-center font-weight-bold">Appointment & Work Details</h5>
                            <div class="alert alert-info" role="alert">
                                <strong>Note:</strong> Fields displayed depend on the selected appointment type. Ensure all required fields (*) are filled.
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6 col-12">
                                    <label class="form-label font-weight-bold">Appointment Type <span class="text-danger">*</span></label>
                                    <select id="appointment_type_id" name="appointment_type_id" class="form-select" required>
                                        @foreach($appointmentTypes as $type)
                                            <option value="{{ $type->id }}" data-name="{{ $type->name }}" {{ old('appointment_type_id') == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('appointment_type_id') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-6 col-12">
                                    <label class="form-label font-weight-bold">Date of First Appointment <span class="text-danger">*</span></label>
                                    <input type="date" name="date_of_first_appointment" class="form-control" required value="{{ old('date_of_first_appointment') }}">
                                    @error('date_of_first_appointment') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                <div class="col-md-6 col-12">
                                    <label class="form-label font-weight-bold">Department <span class="text-danger">*</span></label>
                                    <select name="department_id" class="form-select">
                                        @foreach ($departments as $department)
                                            <option value="{{ $department->department_id }}" {{ old('department_id') == $department->department_id ? 'selected' : '' }}>{{ $department->department_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('department_id') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                <!-- Regular Appointment Fields -->
                                <div class="row g-3" id="regular_appointment_fields">
                                    <div class="col-12">
                                        <h6 class="text-muted">Permanent Appointment Details</h6>
                                    </div>
                                    <div class="col-md-6 col-12">
                                        <label class="form-label font-weight-bold">Years of Service</label>
                                        <input type="text" id="years_of_service" name="years_of_service" class="form-control" readonly>
                                    </div>
                                    <div class="col-md-6 col-12">
                                        <label class="form-label font-weight-bold">Cadre <span class="text-danger">*</span></label>
                                        <select name="cadre_id" class="form-select">
                                            @foreach ($cadres as $cadre)
                                                <option value="{{ $cadre->cadre_id }}" {{ old('cadre_id') == $cadre->cadre_id ? 'selected' : '' }}>{{ $cadre->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('cadre_id') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>
                                    <div class="col-md-6 col-12">
                                        <label class="form-label font-weight-bold">Salary Scale <span class="text-danger">*</span></label>
                                        <select id="salary_scale_id" name="salary_scale_id" class="form-select">
                                            <option value="">-- Select Salary Scale --</option>
                                            @foreach ($salaryScales as $scale)
                                                <option value="{{ $scale->id }}" {{ old('salary_scale_id') == $scale->id ? 'selected' : '' }}>{{ $scale->acronym }} - {{ $scale->full_name }}</option>
                                            @endforeach
                                        </select>
                                        @error('salary_scale_id') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>
                                    <div class="col-md-4 col-12">
                                        <label class="form-label font-weight-bold">Grade Level <span class="text-danger">*</span></label>
                                        <select id="grade_level_name" name="grade_level_name" class="form-select">
                                            <option value="">-- Select Grade Level --</option>
                                        </select>
                                        @error('grade_level_name') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>
                                    <div class="col-md-2 col-12">
                                        <label class="form-label font-weight-bold">Step <span class="text-danger">*</span></label>
                                        <select id="step_level" name="step_level" class="form-select">
                                            <option value="">-- Step --</option>
                                        </select>
                                        @error('step_level') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>
                                    <input type="hidden" id="grade_level_id" name="grade_level_id" value="{{ old('grade_level_id') }}">
                                    <input type="hidden" id="step_id" name="step_id" value="{{ old('step_id') }}">
                                    <div class="col-md-6 col-12">
                                        <label class="form-label font-weight-bold">Rank <span class="text-danger">*</span></label>
                                        <select name="rank_id" class="form-select">
                                            @foreach ($ranks as $rank)
                                                <option value="{{ $rank->id }}" {{ old('rank_id') == $rank->id ? 'selected' : '' }}>{{ $rank->title }}</option>
                                            @endforeach
                                        </select>
                                        @error('rank_id') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>
                                    <div class="col-md-6 col-12">
                                        <label class="form-label font-weight-bold">Expected Next Promotion (optional)</label>
                                        <input type="date" name="expected_next_promotion" class="form-control" value="{{ old('expected_next_promotion') }}">
                                        @error('expected_next_promotion') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>
                                    <div class="col-md-6 col-12">
                                        <label class="form-label font-weight-bold">Expected Retirement Date <span class="text-danger">*</span></label>
                                        <input type="date" name="expected_retirement_date" class="form-control" readonly value="{{ old('expected_retirement_date') }}">
                                        @error('expected_retirement_date') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>
                                </div>

                                <!-- Contract Appointment Fields -->
                                <div class="row g-3 d-none" id="contract_appointment_fields">
                                    <div class="col-12">
                                        <h6 class="text-muted">Contract Appointment Details</h6>
                                    </div>
                                    <div class="col-md-6 col-12">
                                        <label class="form-label font-weight-bold">Contract Start Date <span class="text-danger">*</span></label>
                                        <input type="date" name="contract_start_date" class="form-control" value="{{ old('contract_start_date') }}">
                                        @error('contract_start_date') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>
                                    <div class="col-md-6 col-12">
                                        <label class="form-label font-weight-bold">Contract End Date <span class="text-danger">*</span></label>
                                        <input type="date" name="contract_end_date" class="form-control" value="{{ old('contract_end_date') }}">
                                        @error('contract_end_date') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>
                                    <div class="col-md-6 col-12">
                                        <label class="form-label font-weight-bold">Amount <span class="text-danger">*</span></label>
                                        <input type="number" name="amount" class="form-control" value="{{ old('amount') }}">
                                        @error('amount') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-secondary px-4" onclick="prevStep(2)">Previous</button>
                                <button type="button" class="btn btn-info px-4" onclick="nextStep(4)">Next</button>
                            </div>
                        </div>

                        <!-- Step 4: Other Details -->
                        <div class="step-card d-none" id="step4">
                            <h5 class="mb-3 text-info text-center font-weight-bold">Other Details</h5>
                            <div class="row g-3">
                                <div class="col-md-4 col-12">
                                    <label class="form-label font-weight-bold">Status <span class="text-danger">*</span></label>
                                    <select name="status" class="form-select" required>
                                        <option value="Active" {{ old('status') == 'Active' ? 'selected' : '' }}>Active</option>
                                        <option value="Suspended" {{ old('status') == 'Suspended' ? 'selected' : '' }}>Suspended</option>
                                        <option value="Retired" {{ old('status') == 'Retired' ? 'selected' : '' }}>Retired</option>
                                        <option value="Deceased" {{ old('status') == 'Deceased' ? 'selected' : '' }}>Deceased</option>
                                        <option value="Hold" {{ old('status') == 'Hold' ? 'selected' : '' }}>Hold</option>
                                    </select>
                                    @error('status') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-4 col-12">
                                    <label class="form-label font-weight-bold">Highest Certificate (optional)</label>
                                    <select name="highest_certificate" class="form-control">
                                        <option value="">-- Select --</option>
                                        <option value="No formal education" {{ old('highest_certificate') == 'No formal education' ? 'selected' : '' }}>No formal education</option>
                                        <option value="Primary education" {{ old('highest_certificate') == 'Primary education' ? 'selected' : '' }}>Primary education</option>
                                        <option value="Secondary education / High school or equivalent" {{ old('highest_certificate') == 'Secondary education / High school or equivalent' ? 'selected' : '' }}>Secondary education / High school or equivalent (e.g. SSCE, WAEC, NECO)</option>
                                        <option value="Vocational qualification" {{ old('highest_certificate') == 'Vocational qualification' ? 'selected' : '' }}>Vocational qualification (e.g. NABTEB, trade certificates, NVC)</option>
                                        <option value="Associate degree / NCE / ND" {{ old('highest_certificate') == 'Associate degree / NCE / ND' ? 'selected' : '' }}>Associate degree / NCE / National Diploma (ND)</option>
                                        <option value="Bachelor’s degree" {{ old('highest_certificate') == 'Bachelor’s degree' ? 'selected' : '' }}>Bachelor’s degree (B.Sc, B.A, B.Eng, LLB, etc.)</option>
                                        <option value="Professional degree/license" {{ old('highest_certificate') == 'Professional degree/license' ? 'selected' : '' }}>Professional degree/license (e.g., BL, ICAN, COREN, TRCN, MDCN)</option>
                                        <option value="Master’s degree" {{ old('highest_certificate') == 'Master’s degree' ? 'selected' : '' }}>Master’s degree (M.Sc, MBA, M.A, etc.)</option>
                                        <option value="Doctorate / Ph.D. or higher" {{ old('highest_certificate') == 'Doctorate / Ph.D. or higher' ? 'selected' : '' }}>Doctorate / Ph.D. or higher</option>
                                    </select>
                                    @error('highest_certificate') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-12 col-12">
                                    <label class="form-label font-weight-bold">Photo (optional)</label>
                                    <div class="input-group">
                                        <input type="file" name="photo" class="form-control" accept="image/*" capture="environment" value="{{ old('photo') }}">
                                        <button class="btn btn-outline-secondary" type="button" id="cameraButton">📷 Camera</button>
                                    </div>
                                    <small class="form-text text-muted">Upload from gallery or take a photo with your camera</small>
                                    @error('photo') <small class="text-danger">{{ $message }}</small> @enderror
                                    <div id="cameraContainer" class="mt-2 d-none">
                                        <video id="video" width="100%" height="200" class="border rounded"></video>
                                        <canvas id="canvas" class="d-none"></canvas>
                                        <div class="mt-2">
                                            <button id="snapButton" class="btn btn-primary btn-sm">Take Photo</button>
                                            <button id="cancelButton" class="btn btn-secondary btn-sm">Cancel</button>
                                        </div>
                                    </div>
                                    <input type="hidden" id="capturedImage" name="captured_image">
                                </div>
                            </div>
                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-secondary px-4" onclick="prevStep(3)">Previous</button>
                                <button type="button" class="btn btn-info px-4" onclick="nextStep(5)">Next</button>
                            </div>
                        </div>

                        <!-- Step 5: Next of Kin -->
                        <div class="step-card d-none" id="step5">
                            <h5 class="mb-3 text-info text-center font-weight-bold">Next of Kin Information</h5>
                            <div class="row g-3">
                                <div class="col-md-6 col-12">
                                    <label class="form-label font-weight-bold">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" name="kin_name" class="form-control" required value="{{ old('kin_name') }}">
                                    @error('kin_name') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-6 col-12">
                                    <label class="form-label font-weight-bold">Relationship <span class="text-danger">*</span></label>
                                    <input type="text" name="kin_relationship" class="form-control" required value="{{ old('kin_relationship') }}">
                                    @error('kin_relationship') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-6 col-12">
                                    <label class="form-label font-weight-bold">Mobile No <span class="text-danger">*</span></label>
                                    <input type="text" name="kin_mobile_no" class="form-control" required value="{{ old('kin_mobile_no') }}">
                                    @error('kin_mobile_no') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-6 col-12">
                                    <label class="form-label font-weight-bold">Address <span class="text-danger">*</span></label>
                                    <input type="text" name="kin_address" class="form-control" required value="{{ old('kin_address') }}">
                                    @error('kin_address') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-6 col-12">
                                    <label class="form-label font-weight-bold">Occupation (optional)</label>
                                    <input type="text" name="kin_occupation" class="form-control" value="{{ old('kin_occupation') }}">
                                    @error('kin_occupation') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-6 col-12">
                                    <label class="form-label font-weight-bold">Place of Work (optional)</label>
                                    <input type="text" name="kin_place_of_work" class="form-control" value="{{ old('kin_place_of_work') }}">
                                    @error('kin_place_of_work') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                            </div>
                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-secondary px-4" onclick="prevStep(4)">Previous</button>
                                <button type="button" class="btn btn-info px-4" onclick="nextStep(6)">Next</button>
                            </div>
                        </div>

                        <!-- Step 6: Bank Information -->
                        <div class="step-card d-none" id="step6">
                            <h5 class="mb-3 text-info text-center font-weight-bold">Bank Information</h5>
                            <div class="row g-3">
                                <div class="col-md-6 col-12">
                                    <label class="form-label font-weight-bold">Bank Name <span class="text-danger">*</span></label>
                                    <select name="bank_name" id="bank_name" class="form-select" required>
                                        <option value="">Select Bank</option>
                                        @foreach($banks as $bank)
                                            <option value="{{ $bank->bank_name }}" data-code="{{ $bank->bank_code }}" {{ old('bank_name') == $bank->bank_name ? 'selected' : '' }}>{{ $bank->bank_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('bank_name') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-6 col-12">
                                    <label class="form-label font-weight-bold">Bank Code <span class="text-danger">*</span></label>
                                    <input type="text" name="bank_code" id="bank_code" class="form-control" required value="{{ old('bank_code') }}" readonly>
                                    @error('bank_code') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-6 col-12">
                                    <label class="form-label font-weight-bold">Account Name <span class="text-danger">*</span></label>
                                    <input type="text" name="account_name" class="form-control" required value="{{ old('account_name') }}">
                                    @error('account_name') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-6 col-12">
                                    <label class="form-label font-weight-bold">Account Number <span class="text-danger">*</span></label>
                                    <input type="text" name="account_no" class="form-control" required value="{{ old('account_no') }}">
                                    @error('account_no') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                            </div>
                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-secondary px-4" onclick="prevStep(5)">Previous</button>
                                <button type="submit" class="btn btn-success px-4 shadow-sm">Save Employee</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const stepNav = document.getElementById('stepNav');
        const stepCards = document.querySelectorAll('.step-card');
        const employeeForm = document.getElementById('employeeForm');
        const appointmentTypeSelect = document.getElementById('appointment_type_id');
        const regularAppointmentFields = document.getElementById('regular_appointment_fields');
        const contractAppointmentFields = document.getElementById('contract_appointment_fields');

        let currentStep = 1;

        function showStep(step) {
            stepCards.forEach(card => card.classList.add('d-none'));
            document.getElementById('step' + step).classList.remove('d-none');
            updateNav(step);
            currentStep = step;
        }

        function updateNav(step) {
            document.querySelectorAll('#stepNav .nav-link').forEach((nav, idx) => {
                nav.classList.remove('active');
                if (idx === step - 1) nav.classList.add('active');
            });
        }

        function showToast(message) {
            const toast = document.createElement('div');
            toast.className = 'alert alert-danger alert-dismissible fade show position-fixed bottom-0 end-0 m-3';
            toast.style.zIndex = '1000';
            toast.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 5000);
        }

        function validateStep(step) {
            let isValid = true;
            const currentStepCard = document.getElementById('step' + step);
            const inputs = currentStepCard.querySelectorAll('input[required]:not([disabled]), select[required]:not([disabled]), textarea[required]:not([disabled])');
            const phoneRegex = /^(\+234|0)[789]\d{9}$/;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            inputs.forEach(input => {
                let errorMessage = '';
                if (!input.value) {
                    errorMessage = 'This field is required.';
                    isValid = false;
                } else if (input.name === 'mobile_no' && !phoneRegex.test(input.value)) {
                    errorMessage = 'Invalid phone number format.';
                    isValid = false;
                } else if (input.name === 'kin_mobile_no' && !phoneRegex.test(input.value)) {
                    errorMessage = 'Invalid phone number format.';
                    isValid = false;
                } else if (input.name === 'email' && input.value && !emailRegex.test(input.value)) {
                    errorMessage = 'Invalid email format.';
                    isValid = false;
                } else if (input.name === 'account_no' && !/^\d{10}$/.test(input.value)) {
                    errorMessage = 'Account number must be 10 digits.';
                    isValid = false;
                }

                const existingError = input.nextElementSibling;
                if (existingError && existingError.classList.contains('text-danger')) {
                    existingError.remove();
                }

                if (errorMessage) {
                    const error = document.createElement('small');
                    error.className = 'text-danger';
                    error.innerText = errorMessage;
                    input.parentNode.appendChild(error);
                }
            });

            // Validate contract dates
            if (step === 3 && appointmentTypeSelect.options[appointmentTypeSelect.selectedIndex].dataset.name === 'Contract') {
                const contractStartDate = document.querySelector('input[name="contract_start_date"]');
                const contractEndDate = document.querySelector('input[name="contract_end_date"]');
                if (contractStartDate.value && contractEndDate.value) {
                    const startDate = new Date(contractStartDate.value);
                    const endDate = new Date(contractEndDate.value);
                    if (endDate <= startDate) {
                        const error = document.createElement('small');
                        error.className = 'text-danger';
                        error.innerText = 'Contract end date must be after start date.';
                        const existingError = contractEndDate.nextElementSibling;
                        if (existingError && existingError.classList.contains('text-danger')) {
                            existingError.remove();
                        }
                        contractEndDate.parentNode.appendChild(error);
                        isValid = false;
                    }
                }
            }

            if (step === 3) {
                const appointmentTypeName = appointmentTypeSelect.options[appointmentTypeSelect.selectedIndex].dataset.name;
                if (appointmentTypeName !== 'Contract') {
                    const gradeLevelIdInput = document.getElementById('grade_level_id');
                    const stepIdInput = document.getElementById('step_id');
                    if (!gradeLevelIdInput.value) {
                        isValid = false;
                        const gradeLevelSelect = document.getElementById('grade_level_name');
                        let errorMessage = 'Grade Level is required.';
                        const existingError = gradeLevelSelect.nextElementSibling;
                        if (existingError && existingError.classList.contains('text-danger')) {
                            existingError.remove();
                        }
                        const error = document.createElement('small');
                        error.className = 'text-danger';
                        error.innerText = errorMessage;
                        gradeLevelSelect.parentNode.appendChild(error);
                    }
                    if (!stepIdInput.value) {
                        isValid = false;
                        const stepLevelSelect = document.getElementById('step_level');
                        let errorMessage = 'Step is required.';
                        const existingError = stepLevelSelect.nextElementSibling;
                        if (existingError && existingError.classList.contains('text-danger')) {
                            existingError.remove();
                        }
                        const error = document.createElement('small');
                        error.className = 'text-danger';
                        error.innerText = errorMessage;
                        stepLevelSelect.parentNode.appendChild(error);
                    }
                }
            }

            return isValid;
        }

        window.nextStep = function(step) {
            if (!validateStep(currentStep)) {
                alert('Please fill in all required fields in this section before proceeding.');
                return;
            }
            showStep(step);
        }

        window.prevStep = function(step) {
            showStep(step);
        }

        window.showStep = showStep;

        function toggleAppointmentFields() {
            const selectedOption = appointmentTypeSelect.options[appointmentTypeSelect.selectedIndex];
            const appointmentTypeName = selectedOption.dataset.name;

            regularAppointmentFields.querySelectorAll('input, select').forEach(field => field.disabled = false);
            contractAppointmentFields.querySelectorAll('input, select').forEach(field => field.disabled = false);

            if (appointmentTypeName === 'Contract') {
                regularAppointmentFields.classList.add('d-none');
                contractAppointmentFields.classList.remove('d-none');
                regularAppointmentFields.querySelectorAll('input, select').forEach(field => field.disabled = true);
            } else {
                regularAppointmentFields.classList.remove('d-none');
                contractAppointmentFields.classList.add('d-none');
                contractAppointmentFields.querySelectorAll('input, select').forEach(field => field.disabled = true);
            }
        }

        appointmentTypeSelect.addEventListener('change', toggleAppointmentFields);
        toggleAppointmentFields();

        employeeForm.addEventListener('submit', function(e) {
            console.log('Form submission triggered');
            let isFormValid = true;
            let firstInvalidStep = -1;

            for (let i = 1; i <= 6; i++) {
                if (!validateStep(i)) {
                    isFormValid = false;
                    if (firstInvalidStep === -1) {
                        firstInvalidStep = i;
                    }
                }
            }

            if (!isFormValid) {
                e.preventDefault();
                console.log('Form submission prevented due to validation errors in step ' + firstInvalidStep);
                showStep(firstInvalidStep);
                const stepName = document.querySelector(`#stepNav li:nth-child(${firstInvalidStep}) .nav-link`).textContent;
                alert(`Please fill in all required fields. The first error is in the "${stepName}" section.`);
            } else {
                console.log('Form is valid, proceeding with submission');
            }
        });

        const states = @json($states);
        const lgas = @json($lgas);
        const wards = @json($wards);

        const stateSelect = document.getElementById('state');
        const lgaSelect = document.getElementById('lga');
        const wardSelect = document.getElementById('ward');

        function populateLgas(stateId, selectedLga = null) {
            lgaSelect.innerHTML = '<option value="">-- Select LGA --</option>';
            wardSelect.innerHTML = '<option value="">-- Select Ward --</option>';

            if (stateId) {
                const filteredLgas = lgas.filter(lga => lga.state_id == stateId);
                filteredLgas.forEach(lga => {
                    const option = document.createElement('option');
                    option.value = lga.id;
                    option.text = lga.name;
                    if (selectedLga && lga.id == selectedLga) {
                        option.selected = true;
                    }
                    lgaSelect.appendChild(option);
                });
            }
        }

        function populateWards(lgaId, selectedWard = null) {
            wardSelect.innerHTML = '<option value="">-- Select Ward --</option>';

            if (lgaId) {
                const filteredWards = wards.filter(ward => ward.lga_id == lgaId);
                filteredWards.forEach(ward => {
                    const option = document.createElement('option');
                    option.value = ward.ward_id;
                    option.text = ward.ward_name;
                    if (selectedWard && ward.ward_id == selectedWard) {
                        option.selected = true;
                    }
                    wardSelect.appendChild(option);
                });
            }
        }

        const oldStateId = "{{ old('state_id') }}";
        const oldLgaId = "{{ old('lga_id') }}";
        const oldWardId = "{{ old('ward_id') }}";

        if (oldStateId) {
            stateSelect.value = oldStateId;
            populateLgas(oldStateId, oldLgaId);
            if (oldLgaId) {
                populateWards(oldLgaId, oldWardId);
            }
        }

        stateSelect.addEventListener('change', function () {
            populateLgas(this.value);
        });

        lgaSelect.addEventListener('change', function () {
            populateWards(this.value);
        });

        const firstNameInput = document.querySelector('input[name="first_name"]');
        const surnameInput = document.querySelector('input[name="surname"]');
        const middleNameInput = document.querySelector('input[name="middle_name"]');
        const accountNameInput = document.querySelector('input[name="account_name"]');
        const salaryScaleSelect = document.getElementById('salary_scale_id');
        const gradeLevelNameSelect = document.getElementById('grade_level_name');
        const stepLevelSelect = document.getElementById('step_level');
        const gradeLevelIdInput = document.getElementById('grade_level_id');
        const stepIdInput = document.getElementById('step_id');

        let gradeLevelsData = [];
        let stepsData = [];

        function updateAccountName() {
            const firstName = firstNameInput.value.trim();
            const surname = surnameInput.value.trim();
            const middleName = middleNameInput.value.trim();
            let accountName = `${firstName} ${middleName} ${surname}`;
            accountNameInput.value = accountName.replace(/\s+/g, ' ').trim();
        }

        firstNameInput.addEventListener('input', updateAccountName);
        surnameInput.addEventListener('input', updateAccountName);
        middleNameInput.addEventListener('input', updateAccountName);

        function setGradeAndStep() {
            const selectedGradeLevelName = gradeLevelNameSelect.value;
            const selectedStep = stepLevelSelect.value;

            if (selectedGradeLevelName && selectedStep) {
                const selectedGradeLevel = gradeLevelsData.find(item => item.name === selectedGradeLevelName);
                if (selectedGradeLevel) {
                    const selectedStepData = stepsData.find(step => step.name == selectedStep && step.grade_level_id == selectedGradeLevel.id);
                    if (selectedStepData) {
                        gradeLevelIdInput.value = selectedGradeLevel.id;
                        stepIdInput.value = selectedStepData.id;
                    }
                }
            }
        }

        salaryScaleSelect.addEventListener('change', function() {
            const salaryScaleId = this.value;
            gradeLevelNameSelect.innerHTML = '<option value="">-- Select Grade Level --</option>';
            stepLevelSelect.innerHTML = '<option value="">-- Step --</option>';
            gradeLevelIdInput.value = '';
            stepIdInput.value = '';

            if (salaryScaleId) {
                fetch(`/api/salary-scales/${salaryScaleId}/grade-levels`)
                    .then(response => response.json())
                    .then(data => {
                        gradeLevelsData = data;
                        if (data.length > 0) {
                            const uniqueGradeLevels = [...new Set(data.map(item => item.name))];
                            uniqueGradeLevels.forEach(name => {
                                const option = document.createElement('option');
                                option.value = name;
                                option.text = name;
                                gradeLevelNameSelect.appendChild(option);
                            });
                            const oldGradeLevelName = "{{ old('grade_level_name') }}";
                            if (oldGradeLevelName) {
                                gradeLevelNameSelect.value = oldGradeLevelName;
                                gradeLevelNameSelect.dispatchEvent(new Event('change'));
                            }
                        } else {
                            const option = document.createElement('option');
                            option.value = '';
                            option.text = 'No grade levels available';
                            option.disabled = true;
                            gradeLevelNameSelect.appendChild(option);
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching grade levels:', error);
                        const option = document.createElement('option');
                        option.value = '';
                        option.text = 'Error loading grade levels';
                        option.disabled = true;
                        gradeLevelNameSelect.appendChild(option);
                    });
            }
        });

        gradeLevelNameSelect.addEventListener('change', function() {
            const selectedGradeLevelName = this.value;
            const salaryScaleId = salaryScaleSelect.value;
            stepLevelSelect.innerHTML = '<option value="">-- Step --</option>';
            gradeLevelIdInput.value = '';

            if (selectedGradeLevelName && salaryScaleId) {
                fetch(`/api/salary-scales/${salaryScaleId}/grade-levels/${selectedGradeLevelName}/steps`)
                    .then(response => response.json())
                    .then(steps => {
                        stepsData = steps;
                        if (steps.length > 0) {
                            steps.forEach(step => {
                                const option = document.createElement('option');
                                option.value = step.name;
                                option.text = step.name;
                                stepLevelSelect.appendChild(option);
                            });
                            const oldStepLevel = "{{ old('step_level') }}";
                            if (oldStepLevel) {
                                stepLevelSelect.value = oldStepLevel;
                                setGradeAndStep();
                            }
                        } else {
                            const option = document.createElement('option');
                            option.value = '';
                            option.text = 'No steps available';
                            option.disabled = true;
                            stepLevelSelect.appendChild(option);
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching steps:', error);
                        const option = document.createElement('option');
                        option.value = '';
                        option.text = 'Error loading steps';
                        option.disabled = true;
                        stepLevelSelect.appendChild(option);
                    });
            }
        });

        stepLevelSelect.addEventListener('change', function() {
            setGradeAndStep();
        });

        const oldSalaryScaleId = "{{ old('salary_scale_id') }}";
        if (oldSalaryScaleId) {
            salaryScaleSelect.value = oldSalaryScaleId;
            salaryScaleSelect.dispatchEvent(new Event('change'));
        }

        const dateOfAppointmentInput = document.querySelector('input[name="date_of_first_appointment"]');
        const yearsOfServiceDisplay = document.getElementById('years_of_service');

        dateOfAppointmentInput.addEventListener('change', function() {
            const appointmentDate = new Date(this.value);
            if (!isNaN(appointmentDate.getTime())) {
                const today = new Date();
                let years = today.getFullYear() - appointmentDate.getFullYear();
                const m = today.getMonth() - appointmentDate.getMonth();
                if (m < 0 || (m === 0 && today.getDate() < appointmentDate.getDate())) {
                    years--;
                }
                yearsOfServiceDisplay.value = years;
            } else {
                yearsOfServiceDisplay.value = '';
            }
        });

        const dateOfBirthInput = document.querySelector('input[name="date_of_birth"]');
        const expectedRetirementDateInput = document.querySelector('input[name="expected_retirement_date"]');
        let maxRetirementAge = null;
        let maxYearsOfService = null;

        salaryScaleSelect.addEventListener('change', function() {
            const salaryScaleId = this.value;
            if (salaryScaleId) {
                fetch(`/salary-scales/${salaryScaleId}/retirement-info`)
                    .then(response => response.json())
                    .then(data => {
                        if (data) {
                            maxRetirementAge = parseInt(data.max_retirement_age, 10);
                            maxYearsOfService = parseInt(data.max_years_of_service, 10);
                            calculateRetirementDate();
                        }
                    });
            }
        });

        function calculateRetirementDate() {
            if (maxRetirementAge === null || maxYearsOfService === null) {
                return;
            }

            const birthDateStr = dateOfBirthInput.value;
            const appointmentDateStr = dateOfAppointmentInput.value;

            if (birthDateStr && appointmentDateStr) {
                const birthDate = new Date(birthDateStr);
                const appointmentDate = new Date(appointmentDateStr);

                const birthYear = birthDate.getUTCFullYear();
                const birthMonth = birthDate.getUTCMonth();
                const birthDay = birthDate.getUTCDate();

                const appointmentYear = appointmentDate.getUTCFullYear();
                const appointmentMonth = appointmentDate.getUTCMonth();
                const appointmentDay = appointmentDate.getUTCDate();

                const retirementDateByAge = new Date(Date.UTC(birthYear + maxRetirementAge, birthMonth, birthDay));
                const retirementDateByService = new Date(Date.UTC(appointmentYear + maxYearsOfService, appointmentMonth, appointmentDay));

                const expectedRetirementDate = new Date(Math.min(retirementDateByAge, retirementDateByService));

                const formattedDate = expectedRetirementDate.toISOString().split('T')[0];
                expectedRetirementDateInput.value = formattedDate;
            }
        }

        dateOfBirthInput.addEventListener('change', calculateRetirementDate);
        dateOfAppointmentInput.addEventListener('change', calculateRetirementDate);

        const bankNameSelect = document.getElementById('bank_name');
        const bankCodeInput = document.getElementById('bank_code');

        if (bankNameSelect && bankCodeInput) {
            bankNameSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const bankCode = selectedOption.getAttribute('data-code');
                bankCodeInput.value = bankCode || '';
            });
        }

        @if(session('step'))
            showStep({{ session('step') }});
        @else
            showStep(1);
        @endif
    });
</script>
    @endcan
@endsection
