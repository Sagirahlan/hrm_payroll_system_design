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

                        <!-- Step 1: Personal Information -->
                        <div class="step-card" id="step1">
                            <h5 class="mb-3 text-info text-center font-weight-bold">Personal Information</h5>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label font-weight-bold">First Name</label>
                                    <input type="text" name="first_name" class="form-control" value="{{ $employee->first_name }}" required>
                                    @error('first_name') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label font-weight-bold">Surname</label>
                                    <input type="text" name="surname" class="form-control" value="{{ $employee->surname }}" required>
                                    @error('surname') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label font-weight-bold">Middle Name</label>
                                    <input type="text" name="middle_name" class="form-control" value="{{ $employee->middle_name }}">
                                    @error('middle_name') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label font-weight-bold">Gender</label>
                                    <select name="gender" class="form-select" required>
                                        <option value="Male" {{ $employee->gender == 'Male' ? 'selected' : '' }}>Male</option>
                                        <option value="Female" {{ $employee->gender == 'Female' ? 'selected' : '' }}>Female</option>
                                    </select>
                                    @error('gender') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label font-weight-bold">Date of Birth</label>
                                    <input type="date" name="date_of_birth" class="form-control" value="{{ $employee->date_of_birth }}" required>
                                    @error('date_of_birth') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label font-weight-bold">State of Origin</label>
                                    <select name="state_id" id="state_id" class="form-select" required>
                                        <option value="">Select State</option>
                                        @foreach($states as $state)
                                            <option value="{{ $state->state_id }}" {{ $employee->state_id == $state->state_id ? 'selected' : '' }}>{{ $state->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('state_id') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label font-weight-bold">LGA</label>
                                    <select name="lga_id" id="lga_id" class="form-select" required>
                                        <option value="">Select LGA</option>
                                        @foreach($lgas as $lga)
                                            <option value="{{ $lga->id }}" {{ $employee->lga_id == $lga->id ? 'selected' : '' }}>{{ $lga->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('lga_id') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label font-weight-bold">Staff ID</label>
                                    <input type="text" name="reg_no" class="form-control" value="{{ $employee->reg_no }}" required>
                                    @error('reg_no') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label font-weight-bold">Nationality</label>
                                    <input type="text" name="nationality" class="form-control" value="{{ $employee->nationality }}" required>
                                    @error('nationality') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label font-weight-bold">NIN</label>
                                    <input type="text" name="nin" class="form-control" value="{{ $employee->nin }}">
                                    @error('nin') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label font-weight-bold">Mobile No</label>
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
                                    <label class="form-label font-weight-bold">Email</label>
                                    <input type="email" name="email" class="form-control" value="{{ $employee->email }}">
                                    @error('email') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label font-weight-bold">Address</label>
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
                                    <label class="form-label font-weight-bold">Date of First Appointment</label>
                                    <input type="date" name="date_of_first_appointment" class="form-control" value="{{ $employee->date_of_first_appointment }}" required>
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
                                            <option value="{{ $cadre->cadre_id }}" {{ $employee->cadre_id == $cadre->cadre_id ? 'selected' : '' }}>{{ $cadre->cadre_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('cadre_id') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label font-weight-bold">Salary Scale</label>
                                    <select name="salary_scale_id" class="form-select" required>
                                        @foreach ($salaryScales as $scale)
                                            <option value="{{ $scale->scale_id }}" {{ $employee->salary_scale_id == $scale->scale_id ? 'selected' : '' }}>{{ $scale->scale_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('salary_scale_id') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label font-weight-bold">Department</label>
                                    <select name="department_id" class="form-select" required>
                                        @foreach ($departments as $department)
                                            <option value="{{ $department->department_id }}" {{ $employee->department_id == $department->department_id ? 'selected' : '' }}>{{ $department->department_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('department_id') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label font-weight-bold">Expected Next Promotion</label>
                                    <input type="date" name="expected_next_promotion" class="form-control" value="{{ $employee->expected_next_promotion }}">
                                    @error('expected_next_promotion') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label font-weight-bold">Expected Retirement Date</label>
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
                                    <label class="form-label font-weight-bold">Status</label>
                                    <select name="status" class="form-select" required>
                                        <option value="Active" {{ $employee->status == 'Active' ? 'selected' : '' }}>Active</option>
                                        <option value="Suspended" {{ $employee->status == 'Suspended' ? 'selected' : '' }}>Suspended</option>
                                        <option value="Retired" {{ $employee->status == 'Retired' ? 'selected' : '' }}>Retired</option>
                                        <option value="Deceased" {{ $employee->status == 'Deceased' ? 'selected' : '' }}>Deceased</option>
                                    </select>
                                    @error('status') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label font-weight-bold">Highest Certificate</label>
                                    <input type="text" name="highest_certificate" class="form-control" value="{{ $employee->highest_certificate }}">
                                    @error('highest_certificate') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label font-weight-bold">Appointment Type</label>
                                    <select name="appointment_type" class="form-select" required>
                                        <option value="Permanent" {{ $employee->appointment_type == 'Permanent' ? 'selected' : '' }}>Permanent</option>
                                        <option value="Contract" {{ $employee->appointment_type == 'Contract' ? 'selected' : '' }}>Contract</option>
                                        <option value="Temporary" {{ $employee->appointment_type == 'Temporary' ? 'selected' : '' }}>Temporary</option>
                                    </select>
                                    @error('appointment_type') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label font-weight-bold">Photo</label>
                                    <input type="file" name="photo" class="form-control">
                                    @if ($employee->photo_path)
                                        <img src="{{ asset('storage/' . $employee->photo_path) }}" alt="Employee Photo" width="100" class="mt-2 rounded border">
                                    @endif
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
                                    <input type="text" name="kin_name" class="form-control" value="{{ $employee->nextOfKin->name ?? '' }}" required>
                                    @error('kin_name') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label font-weight-bold">Relationship</label>
                                    <input type="text" name="kin_relationship" class="form-control" value="{{ $employee->nextOfKin->relationship ?? '' }}" required>
                                    @error('kin_relationship') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label font-weight-bold">Mobile No</label>
                                    <input type="text" name="kin_mobile_no" class="form-control" value="{{ $employee->nextOfKin->mobile_no ?? '' }}" required>
                                    @error('kin_mobile_no') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label font-weight-bold">Address</label>
                                    <input type="text" name="kin_address" class="form-control" value="{{ $employee->nextOfKin->address ?? '' }}" required>
                                    @error('kin_address') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label font-weight-bold">Occupation</label>
                                    <input type="text" name="kin_occupation" class="form-control" value="{{ $employee->nextOfKin->occupation ?? '' }}">
                                    @error('kin_occupation') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label font-weight-bold">Place of Work</label>
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
                                    <label class="form-label font-weight-bold">Bank Name</label>
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
                                    <label class="form-label font-weight-bold">Bank Code</label>
                                    <input type="text" name="bank_code" class="form-control" value="{{ $employee->bank->bank_code ?? '' }}" required>
                                    @error('bank_code') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label font-weight-bold">Account Name</label>
                                    <input type="text" name="account_name" class="form-control" value="{{ $employee->bank->account_name ?? '' }}" required>
                                    @error('account_name') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label font-weight-bold">Account Number</label>
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
        showStep(1);

        const stateSelect = document.getElementById('state_id');
        const lgaSelect = document.getElementById('lga_id');

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
    });
</script>
@endsection
