<?php $__env->startSection('title', 'Pension Computation'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Pension Computation</h4>
                </div>
                <div class="card-body">
                    <form id="pensionComputationForm">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" id="employee_id" name="employee_id" 
                               <?php if(isset($pre_filled_data) && isset($pre_filled_data['id_no'])): ?> 
                                   value="<?php echo e(request()->query('employee_id')); ?>" 
                               <?php endif; ?>>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                   
                                </div>

                                <div class="form-group mb-3">
                                    <label for="gtype" class="form-label">Gratuity Type <span class="text-danger">*</span></label>
                                    <select class="form-control" id="gtype" name="gtype" required>
                                        <option value="">Select Gratuity Type</option>
                                        <option value="RB">Retirement Benefits</option>
                                        <option value="DG">Death Gratuity</option>
                                    </select>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="fulname" class="form-label">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="fulname" name="fulname" required
                                           <?php if(isset($pre_filled_data) && isset($pre_filled_data['fulname'])): ?> value="<?php echo e($pre_filled_data['fulname']); ?>" <?php endif; ?>>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="lgaid" class="form-label">Local Government Area <span class="text-danger">*</span></label>
                                    <select class="form-control" id="lgaid" name="lgaid" required>
                                        <option value="">Select LGA</option>
                                        <?php $__currentLoopData = $lgas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lga): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($lga->lgaid); ?>"
                                                <?php if(isset($pre_filled_data) && isset($pre_filled_data['lgaid']) && $pre_filled_data['lgaid'] == $lga->lgaid): ?> selected <?php endif; ?>>
                                                <?php echo e($lga->lga); ?>

                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="deptid" class="form-label">Department <span class="text-danger">*</span></label>
                                    <select class="form-control" id="deptid" name="deptid" required>
                                        <option value="">Select Department</option>
                                        <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($dept->deptid); ?>"
                                                <?php if(isset($pre_filled_data) && isset($pre_filled_data['deptid']) && $pre_filled_data['deptid'] == $dept->deptid): ?> selected <?php endif; ?>>
                                                <?php echo e($dept->dept); ?>

                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="rankid" class="form-label">Rank <span class="text-danger">*</span></label>
                                    <select class="form-control" id="rankid" name="rankid" required>
                                        <option value="">Select Rank</option>
                                        <?php $__currentLoopData = $ranks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rank): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($rank->rankid); ?>"
                                                <?php if(isset($pre_filled_data) && isset($pre_filled_data['rankid']) && $pre_filled_data['rankid'] == $rank->rankid): ?> selected <?php endif; ?>>
                                                <?php echo e($rank->rank); ?>

                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="salary_scale_id" class="form-label">Salary Scale <span class="text-danger">*</span></label>
                                    <select class="form-control" id="salary_scale_id" name="salary_scale_id" required>
                                        <option value="">Select Salary Scale</option>
                                        <?php $__currentLoopData = $salaryScales; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $scale): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($scale->salary_scale_id); ?>"
                                                <?php if(isset($pre_filled_data) && isset($pre_filled_data['salary_scale_id']) && $pre_filled_data['salary_scale_id'] == $scale->salary_scale_id): ?> selected <?php endif; ?>>
                                                <?php echo e($scale->salary_scale_title); ?>

                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="gl_id" class="form-label">Grade Level <span class="text-danger">*</span></label>
                                    <select class="form-control" id="gl_id" name="gl_id" required>
                                        <option value="">Select Grade Level</option>
                                        <?php $__currentLoopData = $gradeLevels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $gl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($gl->gl_id); ?>"
                                                <?php if(isset($pre_filled_data) && isset($pre_filled_data['gl_id']) && $pre_filled_data['gl_id'] == $gl->gl_id): ?> selected <?php endif; ?>>
                                                <?php echo e($gl->grade); ?>

                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="stepid" class="form-label">Step <span class="text-danger">*</span></label>
                                    <select class="form-control" id="stepid" name="stepid" required>
                                        <option value="">Select Step</option>
                                        <!-- Steps will be populated dynamically based on selected Grade Level -->
                                    </select>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="id_no" class="form-label">ID No/Staff No <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="id_no" name="id_no" required
                                           <?php if(isset($pre_filled_data) && isset($pre_filled_data['id_no'])): ?> value="<?php echo e($pre_filled_data['id_no']); ?>" <?php endif; ?>>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="appt_date" class="form-label">Date of First Appointment <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="appt_date" name="appt_date" required
                                           <?php if(isset($pre_filled_data) && isset($pre_filled_data['appt_date'])): ?> value="<?php echo e($pre_filled_data['appt_date']); ?>" <?php endif; ?>>
                                </div>

                                <!-- Expected Retirement Date Display -->
                                <?php if(isset($pre_filled_data) && isset($pre_filled_data['dod_r'])): ?>
                                <div class="alert alert-info mb-3" role="alert">
                                    <strong><i class="fas fa-calendar-check"></i> Expected Retirement Date:</strong>
                                    <span class="fs-5 ms-2"><?php echo e(\Carbon\Carbon::parse($pre_filled_data['dod_r'])->format('F d, Y')); ?></span>
                                </div>
                                <?php endif; ?>

                                <div class="form-group mb-3">
                                    <label for="dod_r" class="form-label">Date of Retirement <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="dod_r" name="dod_r" required
                                           <?php if(isset($pre_filled_data) && isset($pre_filled_data['dod_r'])): ?> value="<?php echo e($pre_filled_data['dod_r']); ?>" <?php endif; ?>>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="dob" class="form-label">Date of Birth <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="dob" name="dob" required
                                           <?php if(isset($pre_filled_data) && isset($pre_filled_data['dob'])): ?> value="<?php echo e($pre_filled_data['dob']); ?>" <?php endif; ?>>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="bankid" class="form-label">Bank</label>
                                    <select class="form-control" id="bankid" name="bankid">
                                        <option value="">Select Bank</option>
                                        <?php $__currentLoopData = $banks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bank): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($bank->bankid); ?>"
                                                <?php if(isset($pre_filled_data) && isset($pre_filled_data['bankid']) && $pre_filled_data['bankid'] == $bank->bankid): ?> selected <?php endif; ?>>
                                                <?php echo e($bank->bank); ?>

                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="acc_no" class="form-label">Account Number</label>
                                    <input type="text" class="form-control" id="acc_no" name="acc_no"
                                           <?php if(isset($pre_filled_data) && isset($pre_filled_data['acc_no'])): ?> value="<?php echo e($pre_filled_data['acc_no']); ?>" <?php endif; ?>>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="mobile" class="form-label">Mobile Number</label>
                                    <input type="text" class="form-control" id="mobile" name="mobile" maxlength="11"
                                           <?php if(isset($pre_filled_data) && isset($pre_filled_data['mobile'])): ?> value="<?php echo e($pre_filled_data['mobile']); ?>" <?php endif; ?>>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="nxtkin_fulname" class="form-label">Next of Kin Name</label>
                                    <input type="text" class="form-control" id="nxtkin_fulname" name="nxtkin_fulname"
                                           <?php if(isset($pre_filled_data) && isset($pre_filled_data['nxtkin_fulname'])): ?> value="<?php echo e($pre_filled_data['nxtkin_fulname']); ?>" <?php endif; ?>>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="nxtkin_mobile" class="form-label">Next of Kin Mobile</label>
                                    <input type="text" class="form-control" id="nxtkin_mobile" name="nxtkin_mobile" maxlength="11"
                                           <?php if(isset($pre_filled_data) && isset($pre_filled_data['nxtkin_mobile'])): ?> value="<?php echo e($pre_filled_data['nxtkin_mobile']); ?>" <?php endif; ?>>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="open_file_no" class="form-label">Open File No</label>
                                    <input type="text" class="form-control" id="open_file_no" name="open_file_no">
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="button" class="btn btn-secondary me-md-2" id="resetBtn">Reset</button>
                            <button type="button" class="btn btn-primary me-md-2" id="computeBtn">Compute</button>
                            <button type="submit" class="btn btn-success" id="saveBtn" disabled>Save Computation</button>
                        </div>
                    </form>

                    <!-- Results Section -->
                    <div id="resultsSection" class="mt-4" style="display: none;">
                        <div class="card">
                            <div class="card-header">
                                <h5>Computation Results</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <table class="table table-bordered">
                                            <tr>
                                                <th>Period of Service:</th>
                                                <td id="periodText"></td>
                                            </tr>
                                            <tr>
                                                <th>Years:</th>
                                                <td id="yearsText"></td>
                                            </tr>
                                            <tr>
                                                <th>Months:</th>
                                                <td id="monthsText"></td>
                                            </tr>
                                            <tr>
                                                <th>Days:</th>
                                                <td id="daysText"></td>
                                            </tr>
                                            <tr>
                                                <th>Basic Salary (Annual):</th>
                                                <td id="basicAnnualText"></td>
                                            </tr>
                                            <tr>
                                                <th>Basic Salary (Monthly):</th>
                                                <td id="basicMonthlyText"></td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        <table class="table table-bordered">
                                            <tr>
                                                <th>Gratuity Type:</th>
                                                <td id="gratuityTypeText"></td>
                                            </tr>
                                            <tr>
                                                <th>Gratuity Percentage:</th>
                                                <td id="gratuityPctText"></td>
                                            </tr>
                                            <tr>
                                                <th>Gratuity Amount:</th>
                                                <td id="gratuityAmtText"></td>
                                            </tr>
                                            <tr>
                                                <th>Pension Type:</th>
                                                <td id="pensionTypeText"></td>
                                            </tr>
                                            <tr>
                                                <th>Pension Percentage:</th>
                                                <td id="pensionPctText"></td>
                                            </tr>
                                            <tr>
                                                <th>Pension (Annual):</th>
                                                <td id="pensionAnnualText"></td>
                                            </tr>
                                            <tr>
                                                <th>Pension (Monthly):</th>
                                                <td id="pensionMonthlyText"></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                                <div id="accruedPensionSection" style="display: none;">
                                    <h6>Accrued Pension Details</h6>
                                    <table class="table table-bordered">
                                        <tr>
                                            <th>Years:</th>
                                            <td id="accruedYearsText"></td>
                                        </tr>
                                        <tr>
                                            <th>Amount:</th>
                                            <td id="accruedAmtText"></td>
                                        </tr>
                                    </table>
                                </div>
                                <div id="totalDeathGratuitySection" style="display: none;">
                                    <h6>Total Death Gratuity</h6>
                                    <p id="totalDeathGratuityText"></p>
                                </div>
                                <div id="overstaySection" style="display: none;">
                                    <h6>Overstay Information</h6>
                                    <p id="overstayText"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Hidden form for saving -->
<form id="saveForm" method="POST" action="<?php echo e(route('pension.store')); ?>" style="display: none;">
    <?php echo csrf_field(); ?>
    <input type="hidden" name="gtype" id="save_gtype">
    <input type="hidden" name="fulname" id="save_fulname">
    <input type="hidden" name="lgaid" id="save_lgaid">
    <input type="hidden" name="deptid" id="save_deptid">
    <input type="hidden" name="rankid" id="save_rankid">
    <input type="hidden" name="salary_scale_id" id="save_salary_scale_id">
    <input type="hidden" name="gl_id" id="save_gl_id">
    <input type="hidden" name="stepid" id="save_stepid">
    <input type="hidden" name="id_no" id="save_id_no">
    <input type="hidden" name="appt_date" id="save_appt_date">
    <input type="hidden" name="dod_r" id="save_dod_r">
    <input type="hidden" name="dob" id="save_dob">
    <input type="hidden" name="bankid" id="save_bankid">
    <input type="hidden" name="acc_no" id="save_acc_no">
    <input type="hidden" name="mobile" id="save_mobile">
    <input type="hidden" name="nxtkin_fulname" id="save_nxtkin_fulname">
    <input type="hidden" name="nxtkin_mobile" id="save_nxtkin_mobile">
    <input type="hidden" name="open_file_no" id="save_open_file_no">
    <input type="hidden" name="employee_id" id="save_employee_id">
</form>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    // Initialize all functionality when document is ready with vanilla JavaScript
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Starting pension computation page initialization with vanilla JS');

        // Helper function to display results
        function displayResults(computation) {
            document.getElementById('periodText').textContent = `${computation.period.from} to ${computation.period.to}`;
            document.getElementById('yearsText').textContent = computation.period.years;
            document.getElementById('monthsText').textContent = computation.period.months;
            document.getElementById('daysText').textContent = computation.period.days;
            document.getElementById('basicAnnualText').textContent = computation.basic_salary.per_annum;
            document.getElementById('basicMonthlyText').textContent = computation.basic_salary.per_month;

            document.getElementById('gratuityTypeText').textContent = computation.gratuity.type;
            document.getElementById('gratuityPctText').textContent = computation.gratuity.percentage + '%';
            document.getElementById('gratuityAmtText').textContent = computation.gratuity.amount;

            document.getElementById('pensionTypeText').textContent = computation.pension.type;
            document.getElementById('pensionPctText').textContent = computation.pension.percentage + '%';
            document.getElementById('pensionAnnualText').textContent = computation.pension.per_annum;
            document.getElementById('pensionMonthlyText').textContent = computation.pension.per_month;

            if (computation.accrued_pension.amount > 0) {
                document.getElementById('accruedYearsText').textContent = computation.accrued_pension.years;
                document.getElementById('accruedAmtText').textContent = computation.accrued_pension.amount;
                document.getElementById('accruedPensionSection').style.display = '';
            } else {
                document.getElementById('accruedPensionSection').style.display = 'none';
            }

            if (computation.total_death_gratuity && parseFloat(computation.total_death_gratuity) > 0) {
                document.getElementById('totalDeathGratuityText').textContent = computation.total_death_gratuity;
                document.getElementById('totalDeathGratuitySection').style.display = '';
            } else {
                document.getElementById('totalDeathGratuitySection').style.display = 'none';
            }

            if (computation.overstay) {
                let overstayHtml = `<strong>${computation.overstay}</strong>`;
                // Check if amount exists and is not zero (handle string or number)
                let amt = computation.overstay_amount;
                if (amt && amt != 0 && amt !== '0.00') {
                    overstayHtml += `<br><span class="text-danger">Deduction Amount: ₦${amt}</span>`;
                }
                document.getElementById('overstayText').innerHTML = overstayHtml;
                document.getElementById('overstaySection').style.display = '';
            } else {
                document.getElementById('overstaySection').style.display = 'none';
            }

            document.getElementById('resultsSection').style.display = '';
        }

        // Basic functionality for buttons
        function handleReset() {
            document.getElementById('pensionComputationForm').reset();
            document.getElementById('resultsSection').style.display = 'none';
            document.getElementById('saveBtn').disabled = true;

            // Show all step options again
            const stepOptions = document.querySelectorAll('#stepid option');
            stepOptions.forEach(option => {
                option.style.display = '';
            });

            console.log('Reset functionality executed');
        }

        function handleCompute() {
            console.log('Compute button clicked manually');
            // Get form data using plain JavaScript to avoid potential jQuery issues
            const form = document.getElementById('pensionComputationForm');
            const formData = new FormData(form);
            const data = Object.fromEntries(formData);

            // Basic validation
            const required = ['gtype', 'fulname', 'lgaid', 'deptid', 'rankid', 'salary_scale_id', 'gl_id', 'stepid', 'id_no', 'appt_date', 'dod_r', 'dob'];
            for (let field of required) {
                if (!data[field]) {
                    alert('Please fill in all required fields: ' + field);
                    return;
                }
            }

            // Submit via AJAX using fetch instead of jQuery
            fetch("<?php echo e(route('pension.compute')); ?>", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    displayResults(result.computation);
                    document.getElementById('saveBtn').disabled = false;

                    // Store data for saving
                    document.getElementById('save_gtype').value = data.gtype;
                    document.getElementById('save_fulname').value = data.fulname;
                    document.getElementById('save_lgaid').value = data.lgaid;
                    document.getElementById('save_deptid').value = data.deptid;
                    document.getElementById('save_rankid').value = data.rankid;
                    document.getElementById('save_salary_scale_id').value = data.salary_scale_id;
                    document.getElementById('save_gl_id').value = data.gl_id;
                    document.getElementById('save_stepid').value = data.stepid;
                    document.getElementById('save_id_no').value = data.id_no;
                    document.getElementById('save_appt_date').value = data.appt_date;
                    document.getElementById('save_dod_r').value = data.dod_r;
                    document.getElementById('save_dob').value = data.dob;
                    document.getElementById('save_bankid').value = data['bankid'] || '';
                    document.getElementById('save_acc_no').value = data['acc_no'] || '';
                    document.getElementById('save_mobile').value = data['mobile'] || '';
                    document.getElementById('save_nxtkin_fulname').value = data['nxtkin_fulname'] || '';
                    document.getElementById('save_nxtkin_mobile').value = data['nxtkin_mobile'] || '';
                    document.getElementById('save_open_file_no').value = data['open_file_no'] || '';
                    
                    // Map retired_employee_id to employee_id for saving
                    document.getElementById('save_employee_id').value = data['retired_employee_id'] || data['employee_id'] || '';
                } else {
                    let errorMessage = result.message || 'Unknown error occurred';
                    if (result.errors) {
                        errorMessage += '\n';
                        for (const [key, messages] of Object.entries(result.errors)) {
                            errorMessage += `\n- ${messages.join(', ')}`;
                        }
                    }
                    alert('Error: ' + errorMessage);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while computing pension: ' + error.message);
            });
        }

        function handleSave(e) {
            e.preventDefault();
            
            const form = document.getElementById('saveForm');
            const saveBtn = document.getElementById('saveBtn');
            const originalText = saveBtn.innerText;
            
            saveBtn.disabled = true;
            saveBtn.innerText = 'Saving...';

            const formData = new FormData(form);

            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    alert(result.message);
                    if (result.redirect_url) {
                        window.location.href = result.redirect_url;
                    } else {
                        // Fallback or maintain current behavior if no redirect
                        location.reload(); 
                    }
                } else {
                    let errorMessage = result.message || 'Error saving beneficiary';
                    if (result.errors) {
                        errorMessage += '\n';
                        for (const [key, messages] of Object.entries(result.errors)) {
                            errorMessage += `\n- ${messages.join(', ')}`;
                        }
                    }
                    alert(errorMessage);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while saving: ' + error.message);
            })
            .finally(() => {
                saveBtn.disabled = false;
                saveBtn.innerText = originalText;
            });
        }

        // Add event listeners to buttons
        const resetBtn = document.getElementById('resetBtn');
        if (resetBtn) {
            resetBtn.addEventListener('click', function(e) {
                e.preventDefault();
                handleReset();
            });
        }

        const computeBtn = document.getElementById('computeBtn');
        if (computeBtn) {
            computeBtn.addEventListener('click', function(e) {
                e.preventDefault();
                handleCompute();
            });
        }

        const saveBtn = document.getElementById('saveBtn');
        if (saveBtn) {
            saveBtn.addEventListener('click', handleSave);
        }

        // Retired Employee Selection Handler
        const retiredEmployeeSelect = document.getElementById('retired_employee_id');
        if (retiredEmployeeSelect) {
            retiredEmployeeSelect.addEventListener('change', function() {
                const employeeId = this.value;
                if (!employeeId) return;

                console.log('Fetching details for employee ID: ' + employeeId);

                fetch("<?php echo e(route('pension.employee-details')); ?>?employee_id=" + employeeId)
                .then(response => response.json())
                .then(response => {
                    console.log('Response received:', response);
                    if (response.success) {
                        const data = response.data;

                        // Populate fields
                        document.getElementById('fulname').value = data.fulname;
                        document.getElementById('id_no').value = data.id_no;
                        document.getElementById('appt_date').value = data.appt_date;
                        document.getElementById('dob').value = data.dob;
                        document.getElementById('mobile').value = data.mobile;

                        // Select Dropdowns (if values match options)
                        if (data.lgaid) document.getElementById('lgaid').value = data.lgaid;
                        if (data.deptid) document.getElementById('deptid').value = data.deptid;
                        if (data.rankid) document.getElementById('rankid').value = data.rankid;
                        if (data.salary_scale_id) document.getElementById('salary_scale_id').value = data.salary_scale_id;
                        if (data.gl_id) {
                            document.getElementById('gl_id').value = data.gl_id;

                            // Trigger the change to load steps
                            const event = new Event('change');
                            document.getElementById('gl_id').dispatchEvent(event);

                            // Wait for steps to load/filter before setting stepid
                            setTimeout(function() {
                                if (data.stepid) document.getElementById('stepid').value = data.stepid;
                            }, 1000);
                        }

                        if (data.dod_r) document.getElementById('dod_r').value = data.dod_r;
                        if (data.bankid) document.getElementById('bankid').value = data.bankid;
                        if (data.acc_no) document.getElementById('acc_no').value = data.acc_no;

                        if (data.nxtkin_fulname) document.getElementById('nxtkin_fulname').value = data.nxtkin_fulname;
                        if (data.nxtkin_mobile) document.getElementById('nxtkin_mobile').value = data.nxtkin_mobile;

                    } else {
                        alert('Could not fetch employee details: ' + response.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error fetching employee details. Check console for details.');
                });
            });
        }

        // Function to load steps based on selected grade level
        function loadStepsForGradeLevel(selectedGlId, preselectedStepId = null) {
            // Clear current options except the first one
            const stepSelect = document.getElementById('stepid');
            stepSelect.innerHTML = '<option value="">Select Step</option>';

            if (!selectedGlId) {
                return;
            }

            // Show loading state
            stepSelect.innerHTML += '<option value="">Loading steps...</option>';
            stepSelect.disabled = true;

            // Fetch steps for the selected grade level
            fetch("<?php echo e(route('pension.steps')); ?>?gl_id=" + selectedGlId)
            .then(response => response.json())
            .then(data => {
                // Clear the loading option
                stepSelect.innerHTML = '<option value="">Select Step</option>';

                if (data.steps && data.steps.length > 0) {
                    data.steps.forEach(function(step) {
                        const option = document.createElement('option');
                        option.value = step.stepid;
                        option.textContent = step.step;
                        stepSelect.appendChild(option);
                    });

                    // Pre-select the step if provided (for when loading from URL with stepid)
                    if (preselectedStepId) {
                        stepSelect.value = preselectedStepId;
                    }
                } else {
                    const option = document.createElement('option');
                    option.value = '';
                    option.textContent = 'No steps available';
                    stepSelect.appendChild(option);
                }

                stepSelect.disabled = false;
            })
            .catch(error => {
                console.error('Error loading steps:', error);
                stepSelect.innerHTML = '<option value="">Error loading steps</option>';
                stepSelect.disabled = false;
            });
        }

        // Update steps when grade level changes
        const glElement = document.getElementById('gl_id');
        if (glElement) {
            glElement.addEventListener('change', function() {
                const selectedGlId = this.value;
                document.getElementById('stepid').value = ''; // Reset step selection
                loadStepsForGradeLevel(selectedGlId);
            });
        }

        // Load steps if grade level is pre-filled
        if (glElement && glElement.value) {
            // Check if we have stepid in URL parameters
            const urlParams = new URLSearchParams(window.location.search);
            const stepInUrl = urlParams.get('stepid');

            loadStepsForGradeLevel(glElement.value, stepInUrl); // Pass the stepid to be selected after loading
        }

        // Auto-compute if employee_id is in URL and all required fields are filled
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('employee_id')) {
            // Check if required fields are filled
            const requiredFields = [
                'fulname', 'lgaid', 'deptid', 'rankid', 'salary_scale_id',
                'gl_id', 'stepid', 'id_no', 'appt_date', 'dod_r', 'dob'
            ];

            let allFilled = true;
            for (let field of requiredFields) {
                const element = document.getElementById(field);
                if (!element || !element.value) {
                    allFilled = false;
                    break;
                }
            }

            if (allFilled && document.getElementById('resultsSection').style.display === 'none') {
                console.log('Auto-computing pension...');
                // Add a small delay to ensure DOM is ready
                setTimeout(() => {
                    handleCompute();
                }, 1000);
            }
        }

        console.log('All functionality initialized');
    });
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Rowwww\Herd\hrm_payroll_system_design\resources\views/pension/computation.blade.php ENDPATH**/ ?>