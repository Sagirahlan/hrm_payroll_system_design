@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-lg rounded-lg">
                <div class="card-header bg-info text-white text-center rounded-top">
                    <h4 class="mb-0 font-weight-bold">Add Employee</h4>
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
                    <form id="employeeForm" action="{{ route('employees.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <!-- Step 1: Personal Information -->
                        <div class="step-card" id="step1">
                            <h5 class="mb-3 text-info text-center font-weight-bold">Personal Information</h5>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label font-weight-bold">First Name</label>
                                    <input type="text" name="first_name" class="form-control" required>
                                    @error('first_name') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label font-weight-bold">Surname</label>
                                    <input type="text" name="surname" class="form-control" required>
                                    @error('surname') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label font-weight-bold">Middle Name</label>
                                    <input type="text" name="middle_name" class="form-control">
                                    @error('middle_name') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label font-weight-bold">Gender</label>
                                    <select name="gender" class="form-select" required>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                    </select>
                                    @error('gender') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label font-weight-bold">Date of Birth</label>
                                    <input type="date" name="date_of_birth" class="form-control" required>
                                    @error('date_of_birth') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label font-weight-bold">State</label>
                                    <select id="state" name="state_id" class="form-select" required>
                                        <option value="">-- Select State --</option>
                                        @foreach($states as $state)
                                            <option value="{{ $state->state_id }}">{{ $state->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('state_of_origin') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label font-weight-bold">Local Government Area (LGA)</label>
                                    <select id="lga" name="lga_id" class="form-select" required>
                                        <option value="">-- Select LGA --</option>
                                    </select>
                                    @error('lga_id') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label font-weight-bold">Ward</label>
                                    <select id="ward" name="ward_id" class="form-select">
                                        <option value="">-- Select Ward --</option>
                                    </select>
                                    @error('ward_id') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                              
                               
                                <div class="col-md-4">
                                    <label class="form-label font-weight-bold">Staff ID</label>
                                    <input type="text" name="reg_no" class="form-control" required>
                                    @error('reg_no') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label font-weight-bold">Nationality</label>
                                    <select name="nationality" class="form-select" required>
                                        <option value="">-- Select Nationality --</option>
                                        <option value="Nigeria">Nigeria</option>
                                        <option value="Benin">Benin</option>
                                        <option value="Cameroon">Cameroon</option>
                                        <option value="Chad">Chad</option>
                                        <option value="Ghana">Ghana</option>
                                        <option value="Niger">Niger</option>
                                        <option value="Togo">Togo</option>
                                        <option value="Burkina Faso">Burkina Faso</option>
                                        <option value="Equatorial Guinea">Equatorial Guinea</option>
                                        <option value="Central African Republic">Central African Republic</option>
                                    </select>
                                    @error('nationality') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label font-weight-bold">NIN</label>
                                    <input type="text" name="nin" class="form-control">
                                    @error('nin') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label font-weight-bold">Mobile No</label>
                                    <input type="text" name="mobile_no" class="form-control" required>
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
                                    <label class="form-label font-weight-bold">Email</label>
                                    <input type="email" name="email" class="form-control">
                                    @error('email') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label font-weight-bold">Address</label>
                                    <textarea name="address" class="form-control" required></textarea>
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
                                    <label class="form-label font-weight-bold">Date of First Appointment</label>
                                    <input type="date" name="date_of_first_appointment" class="form-control" required>
                                    @error('date_of_first_appointment') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label font-weight-bold">Years of Service</label>
                                    <div class="form-control bg-light text-muted">
                                        Will be calculated after saving
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label font-weight-bold">Cadre</label>
                                    <select name="cadre_id" class="form-select" required>
                                        @foreach ($cadres as $cadre)
                                            <option value="{{ $cadre->cadre_id }}">{{ $cadre->cadre_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('cadre_id') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label font-weight-bold">Salary Scale</label>
                                    <select name="salary_scale_id" class="form-select" required>
                                        @foreach ($salaryScales as $scale)
                                            <option value="{{ $scale->scale_id }}">
                                                {{ $scale->scale_name }} 
                                                @if(isset($scale->step_level))
                                                    (Step {{ $scale->step_level }})
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('salary_scale_id') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label font-weight-bold">Department</label>
                                    <select name="department_id" class="form-select" required>
                                        @foreach ($departments as $department)
                                            <option value="{{ $department->department_id }}">{{ $department->department_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('department_id') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label font-weight-bold">Expected Next Promotion</label>
                                    <input type="date" name="expected_next_promotion" class="form-control">
                                    @error('expected_next_promotion') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label font-weight-bold">Expected Retirement Date</label>
                                    <input type="date" name="expected_retirement_date" class="form-control" required>
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
                                    <label class="form-label font-weight-bold">Status</label>
                                    <select name="status" class="form-select" required>
                                        <option value="Active">Active</option>
                                        <option value="Suspended">Suspended</option>
                                        <option value="Retired">Retired</option>
                                        <option value="Deceased">Deceased</option>
                                    </select>
                                    @error('status') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-4">
                                <label class="form-label font-weight-bold">Highest Certificate</label>
                                <select name="highest_certificate" class="form-control">
                                    <option value="">-- Select --</option>
                                    <option value="No formal education">No formal education</option>
                                    <option value="Primary education">Primary education</option>
                                    <option value="Secondary education / High school or equivalent">Secondary education / High school or equivalent (e.g. SSCE, WAEC, NECO)</option>
                                    <option value="Vocational qualification">Vocational qualification (e.g. NABTEB, trade certificates, NVC)</option>
                                    <option value="Associate degree / NCE / ND">Associate degree / NCE / National Diploma (ND)</option>
                                    <option value="Bachelor’s degree">Bachelor’s degree (B.Sc, B.A, B.Eng, LLB, etc.)</option>
                                    <option value="Professional degree/license">Professional degree/license (e.g., BL, ICAN, COREN, TRCN, MDCN)</option>
                                    <option value="Master’s degree">Master’s degree (M.Sc, MBA, M.A, etc.)</option>
                                    <option value="Doctorate / Ph.D. or higher">Doctorate / Ph.D. or higher</option>
                                </select>
                                @error('highest_certificate') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>

                                <div class="col-md-4">
                                    <label class="form-label font-weight-bold">Appointment Type</label>
                                    <select name="appointment_type" class="form-select" required>
                                        <option value="Permanent">Permanent</option>
                                        <option value="Contract">Contract</option>
                                        <option value="Temporary">Temporary</option>
                                    </select>
                                    @error('appointment_type') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label font-weight-bold">Photo</label>
                                    <input type="file" name="photo" class="form-control">
                                    @error('photo') <small class="text-danger">{{ $message }}</small> @enderror
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
                                    <label class="form-label font-weight-bold">Full Name</label>
                                    <input type="text" name="kin_name" class="form-control" required>
                                    @error('kin_name') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label font-weight-bold">Relationship</label>
                                    <input type="text" name="kin_relationship" class="form-control" required>
                                    @error('kin_relationship') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label font-weight-bold">Mobile No</label>
                                    <input type="text" name="kin_mobile_no" class="form-control" required>
                                    @error('kin_mobile_no') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label font-weight-bold">Address</label>
                                    <input type="text" name="kin_address" class="form-control" required>
                                    @error('kin_address') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label font-weight-bold">Occupation</label>
                                    <input type="text" name="kin_occupation" class="form-control">
                                    @error('kin_occupation') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label font-weight-bold">Place of Work</label>
                                    <input type="text" name="kin_place_of_work" class="form-control">
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
                                    <label class="form-label font-weight-bold">Bank Name</label>
                                    <select name="bank_name" class="form-select" required>
                                        <option value="">Select Bank</option>
                                        <option value="Access Bank">Access Bank</option>
                                        <option value="Citibank Nigeria">Citibank Nigeria</option>
                                        <option value="Ecobank Nigeria">Ecobank Nigeria</option>
                                        <option value="Fidelity Bank">Fidelity Bank</option>
                                        <option value="First Bank of Nigeria">First Bank of Nigeria</option>
                                        <option value="First City Monument Bank">First City Monument Bank</option>
                                        <option value="Globus Bank">Globus Bank</option>
                                        <option value="Guaranty Trust Bank">Guaranty Trust Bank</option>
                                        <option value="Heritage Bank">Heritage Bank</option>
                                        <option value="Keystone Bank">Keystone Bank</option>
                                        <option value="Polaris Bank">Polaris Bank</option>
                                        <option value="Providus Bank">Providus Bank</option>
                                        <option value="Stanbic IBTC Bank">Stanbic IBTC Bank</option>
                                        <option value="Standard Chartered Bank">Standard Chartered Bank</option>
                                        <option value="Sterling Bank">Sterling Bank</option>
                                        <option value="Titan Trust Bank">Titan Trust Bank</option>
                                        <option value="Union Bank of Nigeria">Union Bank of Nigeria</option>
                                        <option value="United Bank for Africa">United Bank for Africa</option>
                                        <option value="Unity Bank">Unity Bank</option>
                                        <option value="Wema Bank">Wema Bank</option>
                                        <option value="Zenith Bank">Zenith Bank</option>
                                    </select>
                                    @error('bank_name') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label font-weight-bold">Bank Code</label>
                                    <input type="text" name="bank_code" class="form-control" required>
                                    @error('bank_code') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label font-weight-bold">Account Name</label>
                                    <input type="text" name="account_name" class="form-control" required readonly>
                                    @error('account_name') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label font-weight-bold">Account Number</label>
                                    <input type="text" name="account_no" class="form-control" required>
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
    const states = @json($states);
    const lgas = @json($lgas);
    const wards = @json($wards);

    const stateSelect = document.getElementById('state');
    const lgaSelect = document.getElementById('lga');
    const wardSelect = document.getElementById('ward');

    stateSelect.addEventListener('change', function () {
        const stateId = this.value;
        lgaSelect.innerHTML = '<option value="">-- Select LGA --</option>';
        wardSelect.innerHTML = '<option value="">-- Select Ward --</option>';

        if (stateId) {
            const filteredLgas = lgas.filter(lga => lga.state_id == stateId);
            filteredLgas.forEach(lga => {
                const option = document.createElement('option');
                option.value = lga.id;
                option.text = lga.name;
                lgaSelect.appendChild(option);
            });
        }
    });

    lgaSelect.addEventListener('change', function () {
        const lgaId = this.value;
        wardSelect.innerHTML = '<option value="">-- Select Ward --</option>';

        if (lgaId) {
            const filteredWards = wards.filter(ward => ward.lga_id == lgaId);
            filteredWards.forEach(ward => {
                const option = document.createElement('option');
                option.value = ward.ward_id;
                option.text = ward.ward_name;
                wardSelect.appendChild(option);
            });
        }
    });

    const firstNameInput = document.querySelector('input[name="first_name"]');
    const surnameInput = document.querySelector('input[name="surname"]');
    const middleNameInput = document.querySelector('input[name="middle_name"]');
    const accountNameInput = document.querySelector('input[name="account_name"]');

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
    // Show first step on load
    document.addEventListener('DOMContentLoaded', function() {
        showStep(1);
    });
</script>
@endsection
