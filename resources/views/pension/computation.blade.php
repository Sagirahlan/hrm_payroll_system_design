@extends('layouts.app')

@section('title', 'New Pension Computation')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">New Pension Computation</h4>
                </div>
                <div class="card-body">
                    <form id="pensionComputationForm">
                        @csrf

                        <!-- Select Retired Employee -->
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="retired_employee_id" class="form-label">Select Retired Employee (Optional)</label>
                                <select class="form-select" id="retired_employee_id" name="retired_employee_id">
                                    <option value="">Select Retired Employee</option>
                                    @foreach($retiredEmployees as $emp)
                                        <option value="{{ $emp->employee_id }}"
                                            data-fullName="{{ $emp->first_name }} {{ $emp->middle_name ?? '' }} {{ $emp->surname }}"
                                            data-idNo="{{ $emp->staff_no }}"
                                            data-dob="{{ $emp->date_of_birth ?? '' }}"
                                            data-apptDate="{{ $emp->date_of_first_appointment ?? '' }}"
                                            data-department="{{ $emp->department_id ?? '' }}"
                                            data-rank="{{ $emp->rank_id ?? '' }}"
                                            data-gradeLevel="{{ $emp->grade_level_id ?? '' }}"
                                            data-step="{{ $emp->step_id ?? '' }}"
                                            data-mobile="{{ $emp->mobile_no ?? '' }}"
                                            data-accNo="{{ $emp->account_no ?? '' }}"
                                            data-bankName="{{ $emp->bank_name ?? '' }}">
                                            {{ $emp->first_name }} {{ $emp->middle_name ?? '' }} {{ $emp->surname }} ({{ $emp->staff_no }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Beneficiary Type -->
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label class="form-label">Beneficiary Type <span class="text-danger">*</span></label>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="gtype" id="optRetirement" value="RB" required>
                                    <label class="form-check-label" for="optRetirement">Retirement</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="gtype" id="optDeathGratuity" value="DG" required>
                                    <label class="form-check-label" for="optDeathGratuity">Death Gratuity</label>
                                </div>
                                <div class="invalid-feedback" id="gtype-error"></div>
                            </div>
                        </div>

                        <!-- Personal Information -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="fulname" class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="fulname" name="fulname" required>
                                <div class="invalid-feedback" id="fulname-error"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="id_no" class="form-label">ID Number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="id_no" name="id_no" required>
                                <div class="invalid-feedback" id="id_no-error"></div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="dob" class="form-label">Date of Birth <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="dob" name="dob" required>
                                <div class="invalid-feedback" id="dob-error"></div>
                            </div>
                            <div class="col-md-4">
                                <label for="appt_date" class="form-label">Appointment Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="appt_date" name="appt_date" required>
                                <div class="invalid-feedback" id="appt_date-error"></div>
                            </div>
                            <div class="col-md-4">
                                <label for="dod_r" class="form-label">Date of Retirement/Death <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="dod_r" name="dod_r" required>
                                <div class="invalid-feedback" id="dod_r-error"></div>
                            </div>
                        </div>

                        <!-- Employment Information -->
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="lgaid" class="form-label">Local Government <span class="text-danger">*</span></label>
                                <select class="form-select" id="lgaid" name="lgaid" required>
                                    <option value="">Select LGA</option>
                                    @foreach($lgas as $lga)
                                        <option value="{{ $lga->lgaid }}">{{ $lga->lga }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback" id="lgaid-error"></div>
                            </div>
                            <div class="col-md-4">
                                <label for="deptid" class="form-label">Department <span class="text-danger">*</span></label>
                                <select class="form-select" id="deptid" name="deptid" required>
                                    <option value="">Select Department</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->deptid }}">{{ $dept->dept }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback" id="deptid-error"></div>
                            </div>
                            <div class="col-md-4">
                                <label for="rankid" class="form-label">Rank <span class="text-danger">*</span></label>
                                <select class="form-select" id="rankid" name="rankid" required>
                                    <option value="">Select Rank</option>
                                    @foreach($ranks as $rank)
                                        <option value="{{ $rank->rankid }}">{{ $rank->rank }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback" id="rankid-error"></div>
                            </div>
                        </div>

                        <!-- Salary Scale Information -->
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="salary_scale_id" class="form-label">Salary Scale <span class="text-danger">*</span></label>
                                <select class="form-select" id="salary_scale_id" name="salary_scale_id" required>
                                    <option value="">Select Salary Scale</option>
                                    @foreach($salaryScales as $scale)
                                        <option value="{{ $scale->salary_scale_id }}">{{ $scale->salary_scale_title }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback" id="salary_scale_id-error"></div>
                            </div>
                            <div class="col-md-4">
                                <label for="gl_id" class="form-label">Grade Level <span class="text-danger">*</span></label>
                                <select class="form-select" id="gl_id" name="gl_id" required>
                                    <option value="">Select Grade Level</option>
                                    @foreach($gradeLevels as $gl)
                                        <option value="{{ $gl->gl_id }}">{{ $gl->grade }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback" id="gl_id-error"></div>
                            </div>
                            <div class="col-md-4">
                                <label for="stepid" class="form-label">Step <span class="text-danger">*</span></label>
                                <select class="form-select" id="stepid" name="stepid" required>
                                    <option value="">Select Step</option>
                                </select>
                                <div class="invalid-feedback" id="stepid-error"></div>
                            </div>
                        </div>

                        <!-- Bank Information -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="bankid" class="form-label">Bank</label>
                                <select class="form-select" id="bankid" name="bankid">
                                    <option value="">Select Bank</option>
                                    @foreach($banks as $bank)
                                        <option value="{{ $bank->bankid }}">{{ $bank->bank }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="acc_no" class="form-label">Account Number</label>
                                <input type="text" class="form-control" id="acc_no" name="acc_no">
                                <div class="invalid-feedback" id="acc_no-error"></div>
                            </div>
                        </div>

                        <!-- Contact Information -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="mobile" class="form-label">Mobile Number</label>
                                <input type="text" class="form-control" id="mobile" name="mobile" maxlength="11">
                                <div class="invalid-feedback" id="mobile-error"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="nxtkin_fulname" class="form-label">Next of Kin Name</label>
                                <input type="text" class="form-control" id="nxtkin_fulname" name="nxtkin_fulname">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="nxtkin_mobile" class="form-label">Next of Kin Mobile</label>
                                <input type="text" class="form-control" id="nxtkin_mobile" name="nxtkin_mobile" maxlength="11">
                                <div class="invalid-feedback" id="nxtkin_mobile-error"></div>
                            </div>
                            <div class="col-md-3">
                                <label for="open_file_no" class="form-label">Open File No</label>
                                <input type="text" class="form-control" id="open_file_no" name="open_file_no">
                            </div>
                            <div class="col-md-3">
                                <label for="secret_file_no" class="form-label">Secret File No</label>
                                <input type="text" class="form-control" id="secret_file_no" name="secret_file_no">
                            </div>
                        </div>

                        <!-- Apportionment (Optional) -->
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="apportion_fg_pct" class="form-label">Federal Government %</label>
                                <input type="number" class="form-control" id="apportion_fg_pct" name="apportion_fg_pct" min="0" max="100" value="0">
                            </div>
                            <div class="col-md-4">
                                <label for="apportion_state_pct" class="form-label">State %</label>
                                <input type="number" class="form-control" id="apportion_state_pct" name="apportion_state_pct" min="0" max="100" value="0">
                            </div>
                            <div class="col-md-4">
                                <label for="apportion_lga_pct" class="form-label">LGA %</label>
                                <input type="number" class="form-control" id="apportion_lga_pct" name="apportion_lga_pct" min="0" max="100" value="0">
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <button type="button" class="btn btn-primary" id="btnCompute">Compute</button>
                                <button type="button" class="btn btn-success" id="btnSave" disabled>Save</button>
                                <button type="button" class="btn btn-secondary" id="btnClear">Clear</button>
                                <button type="button" class="btn btn-danger" id="btnClose">Close</button>
                            </div>
                        </div>

                        <!-- Computation Results -->
                        <div id="computationResults" class="card mt-4" style="display: none;">
                            <div class="card-header bg-info text-white">
                                <h5 class="mb-0">Computation Results</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <strong>Period:</strong> <span id="lblPeriod"></span>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <strong>Years:</strong> <span id="lblYear"></span>
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Months:</strong> <span id="lblMonths"></span>
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Days:</strong> <span id="lblDays"></span>
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Total Months:</strong> <span id="lblTotalMnths"></span>
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>Basic Salary (Per Annum):</strong> <span id="lblBasicSalary"></span>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Total Emolument:</strong> <span id="lblTotalEmolument"></span>
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong id="lblGratuityType">Gratuity:</strong>
                                        <span id="lblGratuityPct"></span> X <span id="lblCalculateBasicSalary"></span> = <span id="lblTotalGratuity"></span>
                                    </div>
                                    <div class="col-md-6">
                                        <strong id="lblPensionType">Pension:</strong>
                                        <span id="lblPensionPct"></span> X <span id="lblCalculatedBasicSalary"></span> = <span id="lblTotalPension"></span>
                                    </div>
                                </div>
                                <div class="row mt-2" id="pensionMonthlyRow" style="display: none;">
                                    <div class="col-md-6">
                                        <strong>Pension Per Month:</strong> <span id="lblPensionPerMnth"></span>
                                    </div>
                                </div>
                                <div class="row mt-2" id="accruedPensionRow" style="display: none;">
                                    <div class="col-md-6">
                                        <strong>Accrued Pension:</strong> <span id="lblAccruePensionTotal"></span> X <span id="lblPensionYrs"></span>
                                    </div>
                                    <div class="col-md-6">
                                        <strong id="lblTotalDeathGratuity">Total Death Gratuity:</strong> <span id="lblTotalDeathGratuityAmt"></span>
                                    </div>
                                </div>
                                <div class="row mt-2" id="overstayRow" style="display: none;">
                                    <div class="col-md-12">
                                        <strong class="text-danger">Overstay:</strong> <span id="lblOverStay"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Load steps when GL is selected
    $('#gl_id').on('change', function() {
        const glId = $(this).val();
        if (glId) {
            loadSteps(glId);
        } else {
            $('#stepid').html('<option value="">Select Step</option>');
        }
    });

    // Mobile number validation
    $('#mobile, #nxtkin_mobile').on('input', function() {
        const value = $(this).val();
        if (value && (value.length < 11 || !/^\d+$/.test(value))) {
            $(this).addClass('is-invalid');
            $(this).next('.invalid-feedback').text(
                value.length < 11 ? 'Mobile No. is less than 11 digits' : 'Mobile No. must be numbers only'
            );
        } else {
            $(this).removeClass('is-invalid');
        }
    });

    // Account number validation when bank is selected
    $('#bankid').on('change', function() {
        if ($(this).val() && !$('#acc_no').val()) {
            $('#acc_no').addClass('is-invalid');
            $('#acc_no-error').text('Account number is required when bank is selected');
        } else {
            $('#acc_no').removeClass('is-invalid');
        }
    });

    // Compute button
    $('#btnCompute').on('click', function() {
        computePension();
    });

    // Save button
    $('#btnSave').on('click', function() {
        saveBeneficiary();
    });

    // Clear button
    $('#btnClear').on('click', function() {
        clearFields();
    });

    // Close button
    $('#btnClose').on('click', function() {
        window.location.href = '{{ route("pensioners.index") }}';
    });

    // Beneficiary type change
    $('input[name="gtype"]').on('change', function() {
        const gtype = $(this).val();
        if (gtype === 'RB') {
            $('#lblGratuityType').text('Gratuity:');
            $('#lblPensionType').text('Pension:');
            $('#pensionMonthlyRow').show();
            $('#accruedPensionRow').hide();
        } else if (gtype === 'DG') {
            $('#lblGratuityType').text('Death Gratuity:');
            $('#lblPensionType').text('Accrued Pension:');
            $('#pensionMonthlyRow').hide();
            $('#accruedPensionRow').show();
        }
    });

    function loadSteps(glId) {
        $.ajax({
            url: '{{ route("pension.get-steps") }}',
            method: 'GET',
            data: { gl_id: glId },
            success: function(response) {
                let options = '<option value="">Select Step</option>';
                response.steps.forEach(function(step) {
                    options += `<option value="${step.stepid}">${step.step}</option>`;
                });
                $('#stepid').html(options);
            },
            error: function() {
                alert('Error loading steps');
            }
        });
    }

    function computePension() {
        const form = $('#pensionComputationForm')[0];
        if (!form.checkValidity()) {
            form.classList.add('was-validated');
            return;
        }

        const formData = new FormData(form);

        $.ajax({
            url: '{{ route("pension.compute") }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    displayResults(response.computation);
                    $('#btnSave').prop('disabled', false);
                } else {
                    Swal.fire('Error', response.message || 'Error computing pension', 'error');
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    displayErrors(errors);
                } else {
                    Swal.fire('Error', 'Error computing pension. Please try again.', 'error');
                }
            }
        });
    }

    function displayResults(computation) {
        $('#lblPeriod').text(computation.period.from + ' TO ' + computation.period.to);
        $('#lblYear').text(computation.period.years);
        $('#lblMonths').text(computation.period.months);
        $('#lblDays').text(computation.period.days);
        $('#lblTotalMnths').text(computation.period.total_months);

        $('#lblBasicSalary').text('N' + computation.basic_salary.per_annum);
        $('#lblTotalEmolument').text('N' + computation.total_emolument);

        $('#lblGratuityPct').text(computation.gratuity.percentage + '%');
        $('#lblCalculateBasicSalary').text('N' + computation.basic_salary.per_annum);
        $('#lblTotalGratuity').text('N' + computation.gratuity.amount);

        $('#lblPensionPct').text(computation.pension.percentage + '%');
        $('#lblCalculatedBasicSalary').text('N' + computation.basic_salary.per_annum);
        $('#lblTotalPension').text('N' + computation.pension.per_annum);

        if (computation.pension.per_month) {
            $('#lblPensionPerMnth').text('N' + computation.pension.per_month);
        }

        if (computation.accrued_pension.years > 0) {
            $('#lblPensionYrs').text('X ' + computation.accrued_pension.years + ' YRS');
            $('#lblAccruePensionTotal').text('N' + computation.accrued_pension.amount);
            $('#lblTotalDeathGratuityAmt').text('N' + computation.total_death_gratuity);
        }

        if (computation.overstay) {
            $('#lblOverStay').text(computation.overstay);
            $('#overstayRow').show();
        } else {
            $('#overstayRow').hide();
        }

        $('#computationResults').show();
    }

    function displayErrors(errors) {
        // Clear previous errors
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');

        // Display new errors
        $.each(errors, function(field, messages) {
            const input = $('[name="' + field + '"]');
            input.addClass('is-invalid');
            const errorDiv = input.next('.invalid-feedback') || $('#' + field + '-error');
            errorDiv.text(messages[0]);
        });
    }

    function saveBeneficiary() {
        const form = $('#pensionComputationForm')[0];
        const formData = new FormData(form);

        $.ajax({
            url: '{{ route("pension.store") }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message + '\\nComputation ID: ' + response.computation_id
                    }).then(() => {
                        clearFields();
                    });
                } else {
                    Swal.fire('Error', response.message || 'Error saving beneficiary', 'error');
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    displayErrors(errors);
                } else {
                    Swal.fire('Error', 'Error saving beneficiary. Please try again.', 'error');
                }
            }
        });
    }

    // Handle retired employee selection
    $(document).on('change', '#retired_employee_id', function() {
        const selectedOption = $(this).find(':selected');
        const fullName = selectedOption.data('fullName');
        const idNo = selectedOption.data('idNo');
        const dob = selectedOption.data('dob');
        const apptDate = selectedOption.data('apptDate');
        const department = selectedOption.data('department');
        const rank = selectedOption.data('rank');
        const gradeLevel = selectedOption.data('gradeLevel');
        const step = selectedOption.data('step');
        const mobile = selectedOption.data('mobile');
        const accNo = selectedOption.data('accNo');
        const bankName = selectedOption.data('bankName');

        // Populate the fields if data is available
        if (fullName) {
            $('#fulname').val(fullName);
        }
        if (idNo) {
            $('#id_no').val(idNo);
        }
        if (dob) {
            $('#dob').val(dob);
        }
        if (apptDate) {
            $('#appt_date').val(apptDate);
        }
        if (mobile) {
            $('#mobile').val(mobile);
        }

        // Handle Bank and Account Number
        if (bankName) {
            // Try to find the bank by name text in the dropdown
            $("#bankid option").filter(function() {
                // Normalize text for comparison (trim and lowercase)
                return $(this).text().trim().toLowerCase() === bankName.trim().toLowerCase(); 
            }).prop('selected', true);
            
            // Trigger change to update UI or validation if needed (but we override acc_no manually below)
             $('#bankid').trigger('change');
        }
        
        if (accNo) {
            // Set timeout to ensure it runs after any potential bank change handlers clearing it
            setTimeout(() => {
                 $('#acc_no').val(accNo);
                 // Re-validate if it was invalid
                 $('#acc_no').removeClass('is-invalid');
            }, 100);
        }

        // Set department, rank, grade level, and step if available
        if (department) {
            $('#deptid').val(department);
        }
        if (rank) {
            $('#rankid').val(rank);
        }
        if (gradeLevel) {
            $('#gl_id').val(gradeLevel);

            // Load steps for the selected grade level after a brief delay
            if (typeof loadSteps !== 'undefined') {
                setTimeout(function() {
                    loadSteps(gradeLevel);

                    // If specific step is available, set it after steps are loaded
                    if (step) {
                        setTimeout(function() {
                            $('#stepid').val(step);
                        }, 500); // Increased wait time for steps to load
                    }
                }, 100); // Small delay for dropdown to update
            }
        }
    });

    // Debug function to log data when employee is selected
    $(document).on('change', '#retired_employee_id', function() {
        console.log('Employee selected:', $(this).val());
        const selectedOption = $(this).find(':selected');
        console.log('Selected data:', {
            fullName: selectedOption.data('fullName'),
            gradeLevel: selectedOption.data('gradeLevel'),
            step: selectedOption.data('step')
        });
    });

    function clearFields() {
        $('#pensionComputationForm')[0].reset();
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        $('#computationResults').hide();
        $('#btnSave').prop('disabled', true);
        $('#stepid').html('<option value="">Select Step</option>');
        $('#retired_employee_id').val(''); // Clear the retired employee selection too
    }
});
</script>
@endpush
@endsection

