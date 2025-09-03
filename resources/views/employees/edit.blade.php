@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-lg rounded-lg">
                <div class="card-header bg-info text-white text-center rounded-top">
                    <h4 class="mb-0 font-weight-bold">Edit Employee</h4>
                </div>
                <div class="card-body px-5 py-4">
                    <div class="mb-4">
                        <ul class="nav nav-pills justify-content-center" id="stepNav">
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
                                <div class="col-md-4">
                                    <label class="form-label font-weight-bold">First Name <span class="text-danger">*</span></label>
                                    <input type="text" name="first_name" class="form-control" value="{{ $employee->first_name }}" required>
                                    @error('first_name') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label font-weight-bold">Surname <span class="text-danger">*</span></label>
                                    <input type="text" name="surname" class="form-control" value="{{ $employee->surname }}" required>
                                    @error('surname') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label font-weight-bold">Middle Name (optional)</label>
                                    <input type="text" name="middle_name" class="form-control" value="{{ $employee->middle_name }}">
                                    @error('middle_name') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label font-weight-bold">Gender <span class="text-danger">*</span></label>
                                    <select name="gender" class="form-select" required>
                                        <option value="Male" {{ $employee->gender == 'Male' ? 'selected' : '' }}>Male</option>
                                        <option value="Female" {{ $employee->gender == 'Female' ? 'selected' : '' }}>Female</option>
                                    </select>
                                    @error('gender') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label font-weight-bold">Date of Birth <span class="text-danger">*</span></label>
                                    <input type="date" name="date_of_birth" class="form-control" value="{{ $employee->date_of_birth }}" required>
                                    @error('date_of_birth') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label font-weight-bold">State of Origin <span class="text-danger">*</span></label>
                                    <select name="state_id" id="state_id" class="form-select" required>
                                        <option value="">Select State</option>
                                        @foreach($states as $state)
                                            <option value="{{ $state->state_id }}" {{ $employee->state_id == $state->state_id ? 'selected' : '' }}>{{ $state->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('state_id') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label font-weight-bold">LGA <span class="text-danger">*</span></label>
                                    <select name="lga_id" id="lga_id" class="form-select" required>
                                        <option value="">Select LGA</option>
                                        @foreach($lgas as $lga)
                                            <option value="{{ $lga->id }}" {{ $employee->lga_id == $lga->id ? 'selected' : '' }}>{{ $lga->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('lga_id') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label font-weight-bold">Staff ID <span class="text-danger">*</span></label>
                                    <input type="text" name="reg_no" class="form-control" value="{{ $employee->reg_no }}" required>
                                    @error('reg_no') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label font-weight-bold">Nationality <span class="text-danger">*</span></label>
                                    <input type="text" name="nationality" class="form-control" value="{{ $employee->nationality }}" required>
                                    @error('nationality') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label font-weight-bold">NIN (optional)</label>
                                    <input type="text" name="nin" class="form-control" value="{{ $employee->nin }}">
                                    @error('nin') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label font-weight-bold">Mobile No <span class="text-danger">*</span></label>
                                    <input type="text" name="mobile_no" class="form-control" value="{{ $employee->mobile_no }}" required>
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
                                <div class="col-md-6">
                                    <label class="form-label font-weight-bold">Email (optional)</label>
                                    <input type="email" name="email" class="form-control" value="{{ $employee->email }}">
                                    @error('email') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label font-weight-bold">Address <span class="text-danger">*</span></label>
                                    <textarea name="address" class="form-control" required>{{ $employee->address }}</textarea>
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
                                <div class="col-md-6">
                                    <label class="form-label font-weight-bold">Date of First Appointment <span class="text-danger">*</span></label>
                                    <input type="date" name="date_of_first_appointment" class="form-control" value="{{ $employee->date_of_first_appointment }}" required>
                                    @error('date_of_first_appointment') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label font-weight-bold">Years of Service</label>
                                    <input type="text" id="years_of_service" name="years_of_service" class="form-control" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label font-weight-bold">Cadre <span class="text-danger">*</span></label>
                                    <select name="cadre_id" class="form-select" required>
                                        @foreach ($cadres as $cadre)
                                            <option value="{{ $cadre->cadre_id }}" {{ $employee->cadre_id == $cadre->cadre_id ? 'selected' : '' }}>{{ $cadre->cadre_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('cadre_id') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <!-- Updated to include salary scale and dynamic grade level -->
                                <div class="col-md-6">
                                    <label class="form-label font-weight-bold">Salary Scale <span class="text-danger">*</span></label>
                                    <select id="salary_scale_id" name="salary_scale_id" class="form-select" required>
                                        <option value="">-- Select Salary Scale --</option>
                                        @foreach ($salaryScales as $scale)
                                            <option value="{{ $scale->id }}" {{ $employee->gradeLevel && $employee->gradeLevel->salary_scale_id == $scale->id ? 'selected' : '' }}>{{ $scale->acronym }} - {{ $scale->full_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('salary_scale_id') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label font-weight-bold">Grade Level <span class="text-danger">*</span></label>
                                    <select id="grade_level_id" name="grade_level_id" class="form-select" required>
                                        <option value="">-- Select Grade Level --</option>
                                        <!-- Grade levels will be populated dynamically -->
                                        @if($employee->gradeLevel)
                                            <option value="{{ $employee->grade_level_id }}" selected>{{ $employee->gradeLevel->name }}</option>
                                        @endif
                                    </select>
                                    @error('grade_level_id') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label font-weight-bold">Department <span class="text-danger">*</span></label>
                                    <select name="department_id" class="form-select" required>
                                        @foreach ($departments as $department)
                                            <option value="{{ $department->department_id }}" {{ $employee->department_id == $department->department_id ? 'selected' : '' }}>{{ $department->department_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('department_id') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label font-weight-bold">Expected Next Promotion (optional)</label>
                                    <input type="date" name="expected_next_promotion" class="form-control" value="{{ $employee->expected_next_promotion }}">
                                    @error('expected_next_promotion') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label font-weight-bold">Expected Retirement Date <span class="text-danger">*</span></label>
                                    <input type="date" name="expected_retirement_date" class="form-control" value="{{ $employee->expected_retirement_date }}" required>
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
                                <div class="col-md-4">
                                    <label class="form-label font-weight-bold">Status <span class="text-danger">*</span></label>
                                    <select name="status" class="form-select" required>
                                        <option value="Active" {{ $employee->status == 'Active' ? 'selected' : '' }}>Active</option>
                                        <option value="Suspended" {{ $employee->status == 'Suspended' ? 'selected' : '' }}>Suspended</option>
                                        <option value="Retired" {{ $employee->status == 'Retired' ? 'selected' : '' }}>Retired</option>
                                        <option value="Deceased" {{ $employee->status == 'Deceased' ? 'selected' : '' }}>Deceased</option>
                                    </select>
                                    @error('status') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label font-weight-bold">Highest Certificate (optional)</label>
                                    <input type="text" name="highest_certificate" class="form-control" value="{{ $employee->highest_certificate }}">
                                    @error('highest_certificate') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label font-weight-bold">Appointment Type <span class="text-danger">*</span></label>
                                    <select name="appointment_type_id" class="form-select" required>
                                        @foreach($appointmentTypes as $type)
                                            <option value="{{ $type->id }}" {{ $employee->appointment_type_id == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('appointment_type_id') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-12">
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
                                <div class="col-md-6">
                                    <label class="form-label font-weight-bold">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" name="kin_name" class="form-control" value="{{ $employee->nextOfKin->name ?? '' }}" required>
                                    @error('kin_name') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label font-weight-bold">Relationship <span class="text-danger">*</span></label>
                                    <input type="text" name="kin_relationship" class="form-control" value="{{ $employee->nextOfKin->relationship ?? '' }}" required>
                                    @error('kin_relationship') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label font-weight-bold">Mobile No <span class="text-danger">*</span></label>
                                    <input type="text" name="kin_mobile_no" class="form-control" value="{{ $employee->nextOfKin->mobile_no ?? '' }}" required>
                                    @error('kin_mobile_no') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label font-weight-bold">Address <span class="text-danger">*</span></label>
                                    <input type="text" name="kin_address" class="form-control" value="{{ $employee->nextOfKin->address ?? '' }}" required>
                                    @error('kin_address') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label font-weight-bold">Occupation (optional)</label>
                                    <input type="text" name="kin_occupation" class="form-control" value="{{ $employee->nextOfKin->occupation ?? '' }}">
                                    @error('kin_occupation') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label font-weight-bold">Place of Work (optional)</label>
                                    <input type="text" name="kin_place_of_work" class="form-control" value="{{ $employee->nextOfKin->place_of_work ?? '' }}">
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
                                <div class="col-md-6">
                                    <label class="form-label font-weight-bold">Bank Name <span class="text-danger">*</span></label>
                                    <select name="bank_name" class="form-select" required>
                                        <option value="">Select Bank</option>
                                        <option value="Access Bank" {{ $employee->bank->bank_name ?? '' == 'Access Bank' ? 'selected' : '' }}>Access Bank</option>
                                        <option value="Citibank Nigeria" {{ $employee->bank->bank_name ?? '' == 'Citibank Nigeria' ? 'selected' : '' }}>Citibank Nigeria</option>
                                        <option value="Ecobank Nigeria" {{ $employee->bank->bank_name ?? '' == 'Ecobank Nigeria' ? 'selected' : '' }}>Ecobank Nigeria</option>
                                        <option value="Fidelity Bank" {{ $employee->bank->bank_name ?? '' == 'Fidelity Bank' ? 'selected' : '' }}>Fidelity Bank</option>
                                        <option value="First Bank of Nigeria" {{ $employee->bank->bank_name ?? '' == 'First Bank of Nigeria' ? 'selected' : '' }}>First Bank of Nigeria</option>
                                        <option value="First City Monument Bank" {{ $employee->bank->bank_name ?? '' == 'First City Monument Bank' ? 'selected' : '' }}>First City Monument Bank</option>
                                        <option value="Globus Bank" {{ $employee->bank->bank_name ?? '' == 'Globus Bank' ? 'selected' : '' }}>Globus Bank</option>
                                        <option value="Guaranty Trust Bank" {{ $employee->bank->bank_name ?? '' == 'Guaranty Trust Bank' ? 'selected' : '' }}>Guaranty Trust Bank</option>
                                        <option value="Heritage Bank" {{ $employee->bank->bank_name ?? '' == 'Heritage Bank' ? 'selected' : '' }}>Heritage Bank</option>
                                        <option value="Keystone Bank" {{ $employee->bank->bank_name ?? '' == 'Keystone Bank' ? 'selected' : '' }}>Keystone Bank</option>
                                        <option value="Polaris Bank" {{ $employee->bank->bank_name ?? '' == 'Polaris Bank' ? 'selected' : '' }}>Polaris Bank</option>
                                        <option value="Providus Bank" {{ $employee->bank->bank_name ?? '' == 'Providus Bank' ? 'selected' : '' }}>Providus Bank</option>
                                        <option value="Stanbic IBTC Bank" {{ $employee->bank->bank_name ?? '' == 'Stanbic IBTC Bank' ? 'selected' : '' }}>Stanbic IBTC Bank</option>
                                        <option value="Standard Chartered Bank" {{ $employee->bank->bank_name ?? '' == 'Standard Chartered Bank' ? 'selected' : '' }}>Standard Chartered Bank</option>
                                        <option value="Sterling Bank" {{ $employee->bank->bank_name ?? '' == 'Sterling Bank' ? 'selected' : '' }}>Sterling Bank</option>
                                        <option value="Titan Trust Bank" {{ $employee->bank->bank_name ?? '' == 'Titan Trust Bank' ? 'selected' : '' }}>Titan Trust Bank</option>
                                        <option value="Union Bank of Nigeria" {{ $employee->bank->bank_name ?? '' == 'Union Bank of Nigeria' ? 'selected' : '' }}>Union Bank of Nigeria</option>
                                        <option value="United Bank for Africa" {{ $employee->bank->bank_name ?? '' == 'United Bank for Africa' ? 'selected' : '' }}>United Bank for Africa</option>
                                        <option value="Unity Bank" {{ $employee->bank->bank_name ?? '' == 'Unity Bank' ? 'selected' : '' }}>Unity Bank</option>
                                        <option value="Wema Bank" {{ $employee->bank->bank_name ?? '' == 'Wema Bank' ? 'selected' : '' }}>Wema Bank</option>
                                        <option value="Zenith Bank" {{ $employee->bank->bank_name ?? '' == 'Zenith Bank' ? 'selected' : '' }}>Zenith Bank</option>
                                    </select>
                                    @error('bank_name') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label font-weight-bold">Bank Code <span class="text-danger">*</span></label>
                                    <input type="text" name="bank_code" class="form-control" value="{{ $employee->bank->bank_code ?? '' }}" required>
                                    @error('bank_code') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label font-weight-bold">Account Name <span class="text-danger">*</span></label>
                                    <input type="text" name="account_name" class="form-control" value="{{ $employee->bank->account_name ?? '' }}" required>
                                    @error('account_name') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label font-weight-bold">Account Number <span class="text-danger">*</span></label>
                                    <input type="text" name="account_no" class="form-control" value="{{ $employee->bank->account_no ?? '' }}" required>
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
    function nextStep(step) {
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
        console.log('Employee edit form loaded');
        showStep(1);

        const stateSelect = document.getElementById('state_id');
        const lgaSelect = document.getElementById('lga_id');
        
        // Updated JavaScript for salary scale and grade level
        const salaryScaleSelect = document.getElementById('salary_scale_id');
        const gradeLevelSelect = document.getElementById('grade_level_id');

        stateSelect.addEventListener('change', function() {
            const stateId = this.value;
            lgaSelect.innerHTML = '<option value="">Select LGA</option>';

            if (stateId) {
                fetch(`/get-lgas-by-state?state_id=${stateId}`)
                    .then(response => response.json())
                    .then(data => {
                        data.forEach(lga => {
                            const option = document.createElement('option');
                            option.value = lga.id;
                            option.textContent = lga.name;
                            lgaSelect.appendChild(option);
                        });
                    });
            }
        });
        
        // Function to populate grade levels based on selected salary scale
        function populateGradeLevels(salaryScaleId, selectedGradeLevelId = null) {
            // Clear existing options
            gradeLevelSelect.innerHTML = '<option value="">-- Select Grade Level --</option>';
            
            if (!salaryScaleId) {
                return;
            }
            
            // Make an AJAX request to get grade levels for the selected salary scale
            fetch(`/salary-scales/${salaryScaleId}/grade-levels`)
                .then(response => response.json())
                .then(data => {
                    data.forEach(level => {
                        const option = document.createElement('option');
                        option.value = level.id;
                        option.text = `${level.name} ${level.step_level ? `(Step ${level.step_level})` : ''}`;
                        if (selectedGradeLevelId && level.id == selectedGradeLevelId) {
                            option.selected = true;
                        }
                        gradeLevelSelect.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Error fetching grade levels:', error);
                });
        }
        
        // Event listener for salary scale selection
        salaryScaleSelect.addEventListener('change', function() {
            populateGradeLevels(this.value);
        });
        
        // Populate grade levels on page load if a salary scale is already selected
        if (salaryScaleSelect.value) {
            // Get the currently selected grade level ID
            const selectedGradeLevelId = gradeLevelSelect.value;
            populateGradeLevels(salaryScaleSelect.value, selectedGradeLevelId);
        }
        
        // Camera functionality
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
                // Check if MediaDevices API is supported
                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    throw new Error('Your browser does not support camera access. Please try a modern browser or upload a photo instead.');
                }
                
                // Check if we're on a secure context (HTTPS or localhost)
                if (location.protocol !== 'https:' && location.hostname !== 'localhost' && location.hostname !== '127.0.0.1') {
                    throw new Error('Camera access requires a secure connection (HTTPS). Please upload a photo instead.');
                }
                
                stream = await navigator.mediaDevices.getUserMedia({ 
                    video: {
                        facingMode: 'environment' // Prefer back camera on mobile devices
                    } 
                });
                video.srcObject = stream;
            } catch (err) {
                console.error("Error accessing camera: ", err);
                cameraContainer.classList.add('d-none');
                
                // Show specific error messages
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
            
            // Convert to blob and set as file input value
            canvas.toBlob(function(blob) {
                const file = new File([blob], "captured_photo.jpg", { type: "image/jpeg" });
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                fileInput.files = dataTransfer.files;
                
                // Also store as base64 in hidden input for server-side processing
                capturedImage.value = canvas.toDataURL('image/jpeg');
            }, 'image/jpeg', 0.95);
            
            // Stop camera and hide container
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
            }
            cameraContainer.classList.add('d-none');
        });
        
        cancelButton.addEventListener('click', function() {
            // Stop camera and hide container
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
            }
            cameraContainer.classList.add('d-none');
        });
    });
    
    // Handle form submission
    document.getElementById('employeeForm').addEventListener('submit', function(e) {
        // Form will submit normally, no need to prevent default
        console.log('Form is being submitted');
    });

    const dateOfAppointmentInput = document.querySelector('input[name="date_of_first_appointment"]');
    const yearsOfServiceDisplay = document.getElementById('years_of_service');

    function calculateYearsOfService() {
        const appointmentDate = new Date(dateOfAppointmentInput.value);
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
    }

    dateOfAppointmentInput.addEventListener('change', calculateYearsOfService);

    // Calculate on page load
    calculateYearsOfService();

    const dateOfBirthInput = document.querySelector('input[name="date_of_birth"]');
    const statusSelect = document.querySelector('select[name="status"]');
    let maxRetirementAge = null;
    let maxYearsOfService = null;

    function fetchRetirementInfoAndCheckStatus() {
        const salaryScaleId = salaryScaleSelect.value;
        if (salaryScaleId) {
            fetch(`/salary-scales/${salaryScaleId}/retirement-info`)
                .then(response => response.json())
                .then(data => {
                    if (data) {
                        maxRetirementAge = parseInt(data.max_retirement_age, 10);
                        maxYearsOfService = parseInt(data.max_years_of_service, 10);
                        checkRetirementStatus();
                    }
                });
        }
    }

    salaryScaleSelect.addEventListener('change', fetchRetirementInfoAndCheckStatus);

    function checkRetirementStatus() {
        if (maxRetirementAge === null || maxYearsOfService === null) {
            return;
        }

        const birthDate = new Date(dateOfBirthInput.value);
        const appointmentDate = new Date(dateOfAppointmentInput.value);

        if (!isNaN(birthDate.getTime()) && !isNaN(appointmentDate.getTime())) {
            const today = new Date();

            // Calculate age
            let age = today.getFullYear() - birthDate.getFullYear();
            const mAge = today.getMonth() - birthDate.getMonth();
            if (mAge < 0 || (mAge === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }

            // Calculate years of service
            let yearsOfService = today.getFullYear() - appointmentDate.getFullYear();
            const mService = today.getMonth() - appointmentDate.getMonth();
            if (mService < 0 || (mService === 0 && today.getDate() < appointmentDate.getDate())) {
                yearsOfService--;
            }

            if ((age + yearsOfService) >= maxRetirementAge || (age + yearsOfService) >= maxYearsOfService) {
                statusSelect.value = 'Retired';
            }
        }
    }

    dateOfBirthInput.addEventListener('change', checkRetirementStatus);
    dateOfAppointmentInput.addEventListener('change', checkRetirementStatus);

    // Check on page load
    fetchRetirementInfoAndCheckStatus();
</script>
@endsection
