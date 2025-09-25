@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card border-0 shadow-lg rounded-lg">
                <div class="card-header bg-info text-white text-center rounded-top">
                    <h4 class="mb-0 font-weight-bold">Edit Employee</h4>
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
                    <form id="employeeForm" action="{{ route('employees.update', $employee) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="change_reason" value="Employee update">

                        <!-- Step 1: Personal Information -->
                        <div class="step-card" id="step1">
                            <h5 class="mb-3 text-info text-center font-weight-bold">Personal Information</h5>
                            <div class="row g-3">
                                <div class="col-md-4 col-12">
                                    <label class="form-label font-weight-bold">First Name <span class="text-danger">*</span></label>
                                    <input type="text" name="first_name" class="form-control" required value="{{ old('first_name', $employee->first_name) }}">
                                    @error('first_name') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-4 col-12">
                                    <label class="form-label font-weight-bold">Surname <span class="text-danger">*</span></label>
                                    <input type="text" name="surname" class="form-control" required value="{{ old('surname', $employee->surname) }}">
                                    @error('surname') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-4 col-12">
                                    <label class="form-label font-weight-bold">Middle Name (optional)</label>
                                    <input type="text" name="middle_name" class="form-control" value="{{ old('middle_name', $employee->middle_name) }}">
                                    @error('middle_name') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-4 col-12">
                                    <label class="form-label font-weight-bold">Gender <span class="text-danger">*</span></label>
                                    <select name="gender" class="form-select" required>
                                        <option value="Male" {{ old('gender', $employee->gender) == 'Male' ? 'selected' : '' }}>Male</option>
                                        <option value="Female" {{ old('gender', $employee->gender) == 'Female' ? 'selected' : '' }}>Female</option>
                                    </select>
                                    @error('gender') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-4 col-12">
                                    <label class="form-label font-weight-bold">Date of Birth <span class="text-danger">*</span></label>
                                    <input type="date" name="date_of_birth" class="form-control" required value="{{ old('date_of_birth', $employee->date_of_birth) }}">
                                    @error('date_of_birth') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                 <div class="col-md-4 col-12">
                                    <label class="form-label font-weight-bold">Nationality <span class="text-danger">*</span></label>
                                    <select name="nationality" class="form-select" required>
                                        <option value="">-- Select Nationality --</option>
                                        <option value="Nigeria" {{ old('nationality', $employee->nationality) == 'Nigeria' ? 'selected' : '' }}>Nigeria</option>
                                        <option value="Benin" {{ old('nationality', $employee->nationality) == 'Benin' ? 'selected' : '' }}>Benin</option>
                                        <option value="Cameroon" {{ old('nationality', $employee->nationality) == 'Cameroon' ? 'selected' : '' }}>Cameroon</option>
                                        <option value="Chad" {{ old('nationality', $employee->nationality) == 'Chad' ? 'selected' : '' }}>Chad</option>
                                        <option value="Ghana" {{ old('nationality', $employee->nationality) == 'Ghana' ? 'selected' : '' }}>Ghana</option>
                                        <option value="Niger" {{ old('nationality', $employee->nationality) == 'Niger' ? 'selected' : '' }}>Niger</option>
                                        <option value="Togo" {{ old('nationality', $employee->nationality) == 'Togo' ? 'selected' : '' }}>Togo</option>
                                        <option value="Burkina Faso" {{ old('nationality', $employee->nationality) == 'Burkina Faso' ? 'selected' : '' }}>Burkina Faso</option>
                                        <option value="Equatorial Guinea" {{ old('nationality', $employee->nationality) == 'Equatorial Guinea' ? 'selected' : '' }}>Equatorial Guinea</option>
                                        <option value="Central African Republic" {{ old('nationality', $employee->nationality) == 'Central African Republic' ? 'selected' : '' }}>Central African Republic</option>
                                    </select>
                                    @error('nationality') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-4 col-12">
                                    <label class="form-label font-weight-bold">State of origin <span class="text-danger">*</span></label>
                                    <select id="state" name="state_id" class="form-select" required>
                                        <option value="">-- Select State --</option>
                                        @foreach($states as $state)
                                            <option value="{{ $state->state_id }}" {{ old('state_id', $employee->state_id) == $state->state_id ? 'selected' : '' }}>{{ $state->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('state_of_origin') <small class="text-danger">{{ $message }}</small> @enderror
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
                                    <input type="text" name="reg_no" class="form-control" required value="{{ old('reg_no', $employee->reg_no) }}">
                                    @error('reg_no') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                               
                                <div class="col-md-4 col-12">
                                    <label class="form-label font-weight-bold">NIN (optional)</label>
                                    <input type="text" name="nin" class="form-control" value="{{ old('nin', $employee->nin) }}">
                                    @error('nin') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-4 col-12">
                                    <label class="form-label font-weight-bold">Mobile No <span class="text-danger">*</span></label>
                                    <input type="text" name="mobile_no" class="form-control" required value="{{ old('mobile_no', $employee->mobile_no) }}">
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
                                    <input type="email" name="email" class="form-control" value="{{ old('email', $employee->email) }}">
                                    @error('email') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-6 col-12">
                                    <label class="form-label font-weight-bold">Address <span class="text-danger">*</span></label>
                                    <textarea name="address" class="form-control" required>{{ old('address', $employee->address) }}</textarea>
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
                            <div class="row g-3">
                                <div class="col-md-6 col-12">
                                    <label class="form-label font-weight-bold">Date of First Appointment <span class="text-danger">*</span></label>
                                    <input type="date" name="date_of_first_appointment" class="form-control" required value="{{ old('date_of_first_appointment', $employee->date_of_first_appointment) }}">
                                    @error('date_of_first_appointment') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-6 col-12">
                                    <label class="form-label font-weight-bold">Years of Service</label>
                                    <input type="text" id="years_of_service" name="years_of_service" class="form-control" readonly>
                                </div>
                                <div class="col-md-6 col-12">
                                    <label class="form-label font-weight-bold">Cadre <span class="text-danger">*</span></label>
                                    <select name="cadre_id" class="form-select" required>
                                        @foreach ($cadres as $cadre)
                                            <option value="{{ $cadre->cadre_id }}" {{ old('cadre_id', $employee->cadre_id) == $cadre->cadre_id ? 'selected' : '' }}>{{ $cadre->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('cadre_id') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                
                                <div class="col-md-6 col-12">
                                    <label class="form-label font-weight-bold">Salary Scale <span class="text-danger">*</span></label>
                                    <select id="salary_scale_id" name="salary_scale_id" class="form-select" required>
                                        <option value="">-- Select Salary Scale --</option>
                                        @foreach ($salaryScales as $scale)
                                            <option value="{{ $scale->id }}" {{ old('salary_scale_id', $employee->gradeLevel->salary_scale_id ?? '') == $scale->id ? 'selected' : '' }}>{{ $scale->acronym }} - {{ $scale->full_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('salary_scale_id') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-4 col-12">
                                    <label class="form-label font-weight-bold">Grade Level <span class="text-danger">*</span></label>
                                    <select id="grade_level_name" name="grade_level_name" class="form-select" required>
                                        <option value="">-- Select Grade Level --</option>
                                        <!-- Grade levels will be populated dynamically -->
                                    </select>
                                </div>
                                <div class="col-md-2 col-12">
                                    <label class="form-label font-weight-bold">Step <span class="text-danger">*</span></label>
                                    <select id="step_level" name="step_level" class="form-select" required>
                                        <option value="">-- Step --</option>
                                        <!-- Steps will be populated dynamically -->
                                    </select>
                                </div>
                                <input type="hidden" id="grade_level_id" name="grade_level_id" value="{{ old('grade_level_id', $employee->grade_level_id) }}">
                                <input type="hidden" id="step_id" name="step_id" value="{{ old('step_id', $employee->step_id) }}">
                                <div class="col-md-6 col-12">
                                    <label class="form-label font-weight-bold">Rank <span class="text-danger">*</span></label>
                                    <select name="rank_id" class="form-select" required>
                                        @foreach ($ranks as $rank)
                                            <option value="{{ $rank->id }}" {{ old('rank_id', $employee->rank_id) == $rank->id ? 'selected' : '' }}>{{ $rank->title }}</option>
                                        @endforeach
                                    </select>
                                    @error('rank_id') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-6 col-12">
                                    <label class="form-label font-weight-bold">Department <span class="text-danger">*</span></label>
                                    <select name="department_id" class="form-select" required>
                                        @foreach ($departments as $department)
                                            <option value="{{ $department->department_id }}" {{ old('department_id', $employee->department_id) == $department->department_id ? 'selected' : '' }}>{{ $department->department_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('department_id') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-6 col-12">
                                    <label class="form-label font-weight-bold">Expected Next Promotion (optional)</label>
                                    <input type="date" name="expected_next_promotion" class="form-control" value="{{ old('expected_next_promotion', $employee->expected_next_promotion) }}">
                                    @error('expected_next_promotion') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-6 col-12">
                                    <label class="form-label font-weight-bold">Expected Retirement Date <span class="text-danger">*</span></label>
                                    <input type="date" name="expected_retirement_date" class="form-control" readonly required value="{{ old('expected_retirement_date', $employee->expected_retirement_date) }}">
                                    @error('expected_retirement_date') <small class="text-danger">{{ $message }}</small> @enderror
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
                                        <option value="Active" {{ old('status', $employee->status) == 'Active' ? 'selected' : '' }}>Active</option>
                                        <option value="Suspended" {{ old('status', $employee->status) == 'Suspended' ? 'selected' : '' }}>Suspended</option>
                                        <option value="Retired" {{ old('status', $employee->status) == 'Retired' ? 'selected' : '' }}>Retired</option>
                                        <option value="Deceased" {{ old('status', $employee->status) == 'Deceased' ? 'selected' : '' }}>Deceased</option>
                                    </select>
                                    @error('status') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-4 col-12">
                                <label class="form-label font-weight-bold">Highest Certificate (optional)</label>
                                <select name="highest_certificate" class="form-control">
                                    <option value="">-- Select --</option>
                                    <option value="No formal education" {{ old('highest_certificate', $employee->highest_certificate) == 'No formal education' ? 'selected' : '' }}>No formal education</option>
                                    <option value="Primary education" {{ old('highest_certificate', $employee->highest_certificate) == 'Primary education' ? 'selected' : '' }}>Primary education</option>
                                    <option value="Secondary education / High school or equivalent" {{ old('highest_certificate', $employee->highest_certificate) == 'Secondary education / High school or equivalent' ? 'selected' : '' }}>Secondary education / High school or equivalent (e.g. SSCE, WAEC, NECO)</option>
                                    <option value="Vocational qualification" {{ old('highest_certificate', $employee->highest_certificate) == 'Vocational qualification' ? 'selected' : '' }}>Vocational qualification (e.g. NABTEB, trade certificates, NVC)</option>
                                    <option value="Associate degree / NCE / ND" {{ old('highest_certificate', $employee->highest_certificate) == 'Associate degree / NCE / ND' ? 'selected' : '' }}>Associate degree / NCE / National Diploma (ND)</option>
                                    <option value="Bachelor’s degree" {{ old('highest_certificate', $employee->highest_certificate) == 'Bachelor’s degree' ? 'selected' : '' }}>Bachelor’s degree (B.Sc, B.A, B.Eng, LLB, etc.)</option>
                                    <option value="Professional degree/license" {{ old('highest_certificate', $employee->highest_certificate) == 'Professional degree/license' ? 'selected' : '' }}>Professional degree/license (e.g., BL, ICAN, COREN, TRCN, MDCN)</option>
                                    <option value="Master’s degree" {{ old('highest_certificate', $employee->highest_certificate) == 'Master’s degree' ? 'selected' : '' }}>Master’s degree (M.Sc, MBA, M.A, etc.)</option>
                                    <option value="Doctorate / Ph.D. or higher" {{ old('highest_certificate', $employee->highest_certificate) == 'Doctorate / Ph.D. or higher' ? 'selected' : '' }}>Doctorate / Ph.D. or higher</option>
                                </select>
                                @error('highest_certificate') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>

                                <div class="col-md-4 col-12">
                                    <label class="form-label font-weight-bold">Appointment Type <span class="text-danger">*</span></label>
                                    <select name="appointment_type_id" class="form-select" required>
                                        @foreach($appointmentTypes as $type)
                                            <option value="{{ $type->id }}" {{ old('appointment_type_id', $employee->appointment_type_id) == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('appointment_type_id') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-12 col-12">
                                    <label class="form-label font-weight-bold">Photo (optional)</label>
                                    <div class="input-group">
                                        <input type="file" name="photo" class="form-control" accept="image/*" capture="environment">
                                        <button class="btn btn-outline-secondary" type="button" id="cameraButton">📷 Camera</button>
                                    </div>
                                    <small class="form-text text-muted">Upload from gallery or take a photo with your camera</small>
                                    @if ($employee->photo_path)
                                        <img src="{{ asset('storage/' . $employee->photo_path) }}" alt="Employee Photo" width="100" class="mt-2 rounded border">
                                    @endif
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
                                    <input type="text" name="kin_name" class="form-control" required value="{{ old('kin_name', $employee->nextOfKin->name ?? '') }}">
                                    @error('kin_name') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-6 col-12">
                                    <label class="form-label font-weight-bold">Relationship <span class="text-danger">*</span></label>
                                    <input type="text" name="kin_relationship" class="form-control" required value="{{ old('kin_relationship', $employee->nextOfKin->relationship ?? '') }}">
                                    @error('kin_relationship') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-6 col-12">
                                    <label class="form-label font-weight-bold">Mobile No <span class="text-danger">*</span></label>
                                    <input type="text" name="kin_mobile_no" class="form-control" required value="{{ old('kin_mobile_no', $employee->nextOfKin->mobile_no ?? '') }}">
                                    @error('kin_mobile_no') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-6 col-12">
                                    <label class="form-label font-weight-bold">Address <span class="text-danger">*</span></label>
                                    <input type="text" name="kin_address" class="form-control" required value="{{ old('kin_address', $employee->nextOfKin->address ?? '') }}">
                                    @error('kin_address') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-6 col-12">
                                    <label class="form-label font-weight-bold">Occupation (optional)</label>
                                    <input type="text" name="kin_occupation" class="form-control" value="{{ old('kin_occupation', $employee->nextOfKin->occupation ?? '') }}">
                                    @error('kin_occupation') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-6 col-12">
                                    <label class="form-label font-weight-bold">Place of Work (optional)</label>
                                    <input type="text" name="kin_place_of_work" class="form-control" value="{{ old('kin_place_of_work', $employee->nextOfKin->place_of_work ?? '') }}">
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
                                    <select name="bank_name" class="form-select" required>
                                        <option value="">Select Bank</option>
                                        @foreach($banks as $bank)
                                            <option value="{{ $bank->bank_name }}" data-code="{{ $bank->bank_code }}" {{ old('bank_name', $employee->bank->bank_name ?? '') == $bank->bank_name ? 'selected' : '' }}>{{ $bank->bank_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('bank_name') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-6 col-12">
                                    <label class="form-label font-weight-bold">Bank Code <span class="text-danger">*</span></label>
                                    <input type="text" name="bank_code" class="form-control" required value="{{ old('bank_code', $employee->bank->bank_code ?? '') }}">
                                    @error('bank_code') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-6 col-12">
                                    <label class="form-label font-weight-bold">Account Name <span class="text-danger">*</span></label>
                                    <input type="text" name="account_name" class="form-control" required value="{{ old('account_name', $employee->bank->account_name ?? '') }}">
                                    @error('account_name') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-6 col-12">
                                    <label class="form-label font-weight-bold">Account Number <span class="text-danger">*</span></label>
                                    <input type="text" name="account_no" class="form-control" required value="{{ old('account_no', $employee->bank->account_no ?? '') }}">
                                    @error('account_no') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                
                            </div>
                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-secondary px-4" onclick="prevStep(5)">Previous</button>
                                <button type="submit" class="btn btn-success px-4 shadow-sm">Update Employee</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    // Define the data
    const states = @json($states);
    const lgas = @json($lgas);
    const wards = @json($wards);

    // Get DOM elements
    const stateSelect = document.getElementById('state');
    const lgaSelect = document.getElementById('lga');
    const wardSelect = document.getElementById('ward');

    // Get existing employee values
    const selectedStateId = "{{ old('state_id', $employee->state_id) }}";
    const selectedLgaId = "{{ old('lga_id', $employee->lga_id) }}";
    const selectedWardId = "{{ old('ward_id', $employee->ward_id) }}";

    // Function to populate LGAs based on selected state
    function populateLgas(stateId, selectedLga = null) {
        // Clear existing options
        lgaSelect.innerHTML = '<option value="">-- Select LGA --</option>';
        // Also clear wards when state changes
        wardSelect.innerHTML = '<option value="">-- Select Ward --</option>';

        if (stateId) {
            // Filter LGAs by state
            const filteredLgas = lgas.filter(lga => {
                return lga.state_id == stateId;
            });
            
            // Add LGAs to dropdown
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

    // Function to populate wards based on selected LGA
    function populateWards(lgaId, selectedWard = null) {
        // Clear existing options
        wardSelect.innerHTML = '<option value="">-- Select Ward --</option>';

        if (lgaId) {
            // Filter wards by LGA
            const filteredWards = wards.filter(ward => {
                return ward.lga_id == lgaId;
            });
            
            // Add wards to dropdown
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

    // Initialize LGA and Ward dropdowns with existing employee data
    document.addEventListener('DOMContentLoaded', function() {
        // Get existing employee values
        const selectedStateId = "{{ old('state_id', $employee->state_id) }}";
        const selectedLgaId = "{{ old('lga_id', $employee->lga_id) }}";
        const selectedWardId = "{{ old('ward_id', $employee->ward_id) }}";
        
        if (selectedStateId) {
            // Set the state dropdown value
            stateSelect.value = selectedStateId;
            
            // Populate LGAs for the selected state
            populateLgas(selectedStateId, selectedLgaId);
            
            // If an LGA is selected, populate wards
            if (selectedLgaId) {
                populateWards(selectedLgaId, selectedWardId);
            }
        }
    });

    // Event listeners
    stateSelect.addEventListener('change', function () {
        populateLgas(this.value);
    });

    lgaSelect.addEventListener('change', function () {
        populateWards(this.value);
    });

    // Initialize the form with existing employee data
    document.addEventListener('DOMContentLoaded', function() {
        if (selectedStateId) {
            // Set the state dropdown value
            stateSelect.value = selectedStateId;
            
            // Populate LGAs for the selected state
            populateLgas(selectedStateId, selectedLgaId);
            
            // If an LGA is selected, populate wards
            if (selectedLgaId) {
                populateWards(selectedLgaId, selectedWardId);
            }
        }
    });

    // Rest of the existing script from the original file...
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
        stepIdInput.value = '';

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

    function validateStep(step) {
        let isValid = true;
        const currentStep = document.getElementById('step' + step);
        const inputs = currentStep.querySelectorAll('input[required], select[required], textarea[required]');

        inputs.forEach(input => {
            if (!input.value) {
                isValid = false;
                const error = document.createElement('small');
                error.className = 'text-danger';
                error.innerText = 'This field is required.';
                const existingError = input.nextElementSibling;
                if (existingError && existingError.classList.contains('text-danger')) {
                    existingError.remove();
                }
                input.parentNode.appendChild(error);
            } else {
                const existingError = input.nextElementSibling;
                if (existingError && existingError.classList.contains('text-danger')) {
                    existingError.remove();
                }
            }
        });

        return isValid;
    }

    function nextStep(step) {
        if (!validateStep(step - 1)) {
            return;
        }
        document.querySelectorAll('.step-card').forEach(card => card.classList.add('d-none'));
        document.getElementById('step' + step).classList.remove('d-none');
        updateNav(step);
    }
    function prevStep(step) {
        document.querySelectorAll('.step-card').forEach(card => card.classList.add('d-none'));
        document.getElementById('step' + step).classList.remove('d-none');
        updateNav(step);
    }
    function showStep(step) {
        document.querySelectorAll('.step-card').forEach(card => card.classList.add('d-none'));
        document.getElementById('step' + step).classList.remove('d-none');
        updateNav(step);
    }
    function updateNav(step) {
        document.querySelectorAll('#stepNav .nav-link').forEach((nav, idx) => {
            nav.classList.remove('active');
            if (idx === step - 1) nav.classList.add('active');
        });
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        @if(session('step'))
            showStep({{ session('step') }});
        @else
            showStep(1);
        @endif
        
        const cameraButton = document.getElementById('cameraButton');
        const cameraContainer = document.getElementById('cameraContainer');
        const video = document.getElementById('video');
        const canvas = document.getElementById('canvas');
        const snapButton = document.getElementById('snapButton');
        const cancelButton = document.getElementById('cancelButton');
        const capturedImage = document.getElementById('capturedImage');
        const fileInput = document.querySelector('input[name="photo"]');
        
        let stream = null;
        
        cameraButton.addEventListener('click', async function() {
            cameraContainer.classList.remove('d-none');
            
            try {
                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    throw new Error('Your browser does not support camera access. Please try a modern browser or upload a photo instead.');
                }
                
                if (location.protocol !== 'https:' && location.hostname !== 'localhost' && location.hostname !== '127.0.0.1') {
                    throw new Error('Camera access requires a secure connection (HTTPS). Please upload a photo instead.');
                }
                
                stream = await navigator.mediaDevices.getUserMedia({ 
                    video: {
                        facingMode: 'environment'
                    } 
                });
                video.srcObject = stream;
            } catch (err) {
                console.error("Error accessing camera: ", err);
                cameraContainer.classList.add('d-none');
                
                let errorMessage = "Could not access the camera. ";
                switch (err.name) {
                    case 'NotAllowedError':
                        errorMessage += "Please grant camera permission and try again.";
                        break;
                    case 'NotFoundError':
                        errorMessage += "No camera was found on your device.";
                        break;
                    case 'NotReadableError':
                        errorMessage += "Camera is being used by another application.";
                        break;
                    case 'OverconstrainedError':
                        errorMessage += "Your camera does not support the required constraints.";
                        break;
                    default:
                        errorMessage += "Please ensure you've granted permission and that your camera is working. Alternatively, upload a photo from your device.";
                }
                
                alert(errorMessage);
            }
        });
        
        snapButton.addEventListener('click', function() {
            const context = canvas.getContext('2d');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            context.drawImage(video, 0, 0, canvas.width, canvas.height);
            
            canvas.toBlob(function(blob) {
                const file = new File([blob], "captured_photo.jpg", { type: "image/jpeg" });
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                fileInput.files = dataTransfer.files;
                
                capturedImage.value = canvas.toDataURL('image/jpeg');
            }, 'image/jpeg', 0.95);
            
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
            }
            cameraContainer.classList.add('d-none');
        });
        
        cancelButton.addEventListener('click', function() {
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
            }
            cameraContainer.classList.add('d-none');
        });

        // Pre-fill and handle dynamic dropdowns for edit form
        const selectedSalaryScaleId = "{{ old('salary_scale_id', $employee->gradeLevel->salary_scale_id ?? '') }}";
        const selectedGradeLevelName = "{{ old('grade_level_name', $employee->gradeLevel->name ?? '') }}";
        const selectedStepLevel = "{{ old('step_level', $employee->step->name ?? '') }}";

        if (selectedSalaryScaleId) {
            fetch(`/api/salary-scales/${selectedSalaryScaleId}/grade-levels`)
                .then(response => response.json())
                .then(data => {
                    gradeLevelsData = data;
                    if (data.length > 0) {
                        const uniqueGradeLevels = [...new Set(data.map(item => item.name))];
                        uniqueGradeLevels.forEach(name => {
                            const option = document.createElement('option');
                            option.value = name;
                            option.text = name;
                            if (name === selectedGradeLevelName) {
                                option.selected = true;
                            }
                            gradeLevelNameSelect.appendChild(option);
                        });

                        if (selectedGradeLevelName) {
                            fetch(`/api/salary-scales/${selectedSalaryScaleId}/grade-levels/${selectedGradeLevelName}/steps`)
                                .then(response => response.json())
                                .then(steps => {
                                    stepsData = steps;
                                    if (steps.length > 0) {
                                        steps.forEach(step => {
                                            const option = document.createElement('option');
                                            option.value = step.name;
                                            option.text = step.name;
                                            if (step.name == selectedStepLevel) {
                                                option.selected = true;
                                            }
                                            stepLevelSelect.appendChild(option);
                                        });
                                        setGradeAndStep(); // Set hidden fields
                                    }
                                });
                        }
                    }
                });
        }
    });
    
    document.getElementById('employeeForm').addEventListener('submit', function(e) {
        let isValid = true;
        document.querySelectorAll('.step-card').forEach(card => {
            const inputs = card.querySelectorAll('input[required], select[required], textarea[required]');
            inputs.forEach(input => {
                if (!input.value) {
                    isValid = false;
                }
            });
        });
        
        if (!gradeLevelIdInput.value) {
            isValid = false;
        }
        
        if (!stepIdInput.value) {
            isValid = false;
        }
        
        if (!isValid) {
            e.preventDefault();
            alert('Please fill in all required fields before submitting.');
            return false;
        }
    });

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
            yearsOfServiceDisplay.value = years + (years === 1 ? ' year' : ' years');
        } else {
            yearsOfServiceDisplay.value = '';
        }
    });
    dateOfAppointmentInput.dispatchEvent(new Event('change'));

    const dateOfBirthInput = document.querySelector('input[name="date_of_birth"]');
    const statusSelect = document.querySelector('select[name="status"]');
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
    salaryScaleSelect.dispatchEvent(new Event('change'));

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
</script>
@endsection

    