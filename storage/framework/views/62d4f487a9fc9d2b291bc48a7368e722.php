<?php $__env->startSection('content'); ?>
<div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="card border-0 shadow-lg" style="max-width: 800px; width: 100%; background: linear-gradient(135deg, #e0f7fa 0%, #ffffff 100%);">
        <div class="card-header text-center" style="background: #00bcd4;">
            <h4 class="mb-0 text-white font-weight-bold">Employee Details</h4>
        </div>
        <div class="card-body p-4">
            <?php if($employee->photo_path): ?>
                <div class="mb-4 text-center">
                    <img src="<?php echo e(asset('storage/' . $employee->photo_path)); ?>" alt="Employee Photo" class="img-thumbnail border border-info shadow-sm" width="120">
                </div>
            <?php endif; ?>

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
                        <div class="col-md-6"><p><strong>First Name:</strong> <?php echo e($employee->first_name); ?></p></div>
                        <div class="col-md-6"><p><strong>Surname:</strong> <?php echo e($employee->surname); ?></p></div>
                        <div class="col-md-6"><p><strong>Middle Name:</strong> <?php echo e($employee->middle_name ?? 'N/A'); ?></p></div>
                        <div class="col-md-6"><p><strong>Gender:</strong> <?php echo e($employee->gender); ?></p></div>
                        <div class="col-md-6"><p><strong>Date of Birth:</strong> <?php echo e($employee->date_of_birth); ?></p></div>
                        <div class="col-md-6"><p><strong>Age:</strong> <?php echo e(\Carbon\Carbon::parse($employee->date_of_birth)->age); ?> years</p></div>
                        <div class="col-md-6"><p><strong>Nationality:</strong> <?php echo e($employee->nationality); ?></p></div>
                        <div class="col-md-6"><p><strong>State of Origin:</strong> <?php echo e($employee->state->name ?? 'N/A'); ?></p></div>
                        <div class="col-md-6"><p><strong>LGA:</strong> <?php echo e($employee->lga->name ?? 'N/A'); ?></p></div>
                        <div class="col-md-6"><p><strong>Ward:</strong> <?php echo e($employee->ward->ward_name ?? 'N/A'); ?></p></div>
                        <div class="col-md-6"><p><strong>Staff ID:</strong> <?php echo e($employee->staff_no ?? 'N/A'); ?></p></div>
                        <div class="col-md-6"><p><strong>NIN:</strong> <?php echo e($employee->nin ?? 'N/A'); ?></p></div>
                    </div>
                </div>

                <!-- Contact & Address Step -->
                <div class="tab-pane fade" id="contact" role="tabpanel">
                    <h5 class="text-primary mb-3">Contact & Address</h5>
                    <div class="row">
                        <div class="col-md-6"><p><strong>Mobile No:</strong> <?php echo e($employee->mobile_no); ?></p></div>
<div class="col-md-6">
                                <p><strong>Email:</strong> <?php echo e($employee->email ?? 'N/A'); ?></p>
                                <p><strong>Pay Point:</strong> <?php echo e($employee->pay_point ?? 'N/A'); ?></p>
                                <p><strong>Address:</strong> <?php echo e($employee->address ?? 'N/A'); ?></p>
                            </div>
                    </div>
                </div>

                <!-- Work Information Step -->
                <div class="tab-pane fade" id="work" role="tabpanel">
                    <h5 class="text-primary mb-3">Work Information</h5>
                    <div class="row">
                        <?php if($employee->appointmentType->name !== 'Casual'): ?>
                            <div class="col-md-6"><p><strong>Date of First Appointment:</strong> <?php echo e($employee->date_of_first_appointment ? \Carbon\Carbon::parse($employee->date_of_first_appointment)->format('j M Y') : '—'); ?></p></div>
                            <div class="col-md-6"><p><strong>Years of Service:</strong> <?php echo e($employee->years_of_service !== null ? $employee->years_of_service . ' ' . Str::plural('year', $employee->years_of_service) : '—'); ?></p></div>
                            <div class="col-md-6"><p><strong>Cadre:</strong> <?php echo e($employee->cadre->name ?? 'N/A'); ?></p></div>
                            <div class="col-md-6"><p><strong>Salary Scale:</strong> <?php echo e($employee->gradeLevel->salaryScale->acronym ?? 'N/A'); ?> - <?php echo e($employee->gradeLevel->salaryScale->full_name ?? 'N/A'); ?></p></div>
                            <div class="col-md-6"><p><strong>Grade Level:</strong> <?php echo e($employee->gradeLevel->name ?? 'N/A'); ?></p></div>
                            <div class="col-md-6"><p><strong>Step:</strong> <?php echo e($employee->step->name ?? 'N/A'); ?></p></div>
                            <div class="col-md-6"><p><strong>Rank:</strong> <?php echo e($employee->rank->title ?? 'N/A'); ?></p></div>
                            <div class="col-md-6"><p><strong>Department:</strong> <?php echo e($employee->department->department_name ?? 'N/A'); ?></p></div>
                            <div class="col-md-6"><p><strong>Expected Next Promotion:</strong> <?php echo e($employee->expected_next_promotion ? \Carbon\Carbon::parse($employee->expected_next_promotion)->format('j M Y') : 'N/A'); ?></p></div>
                            <div class="col-md-6"><p><strong>Expected Retirement Date:</strong> <?php echo e($employee->expected_retirement_date ? \Carbon\Carbon::parse($employee->expected_retirement_date)->format('j M Y') : '—'); ?></p></div>
                        <?php else: ?>
                            <div class="col-md-6"><p><strong>Casual Start Date:</strong> <?php echo e($employee->Casual_start_date ? \Carbon\Carbon::parse($employee->Casual_start_date)->format('j M Y') : '—'); ?></p></div>
                            <div class="col-md-6"><p><strong>Casual End Date:</strong> <?php echo e($employee->Casual_end_date ? \Carbon\Carbon::parse($employee->Casual_end_date)->format('j M Y') : '—'); ?></p></div>
                            <div class="col-md-6"><p><strong>Amount:</strong> <?php echo e($employee->amount ? number_format($employee->amount, 2) : 'N/A'); ?></p></div>
                            <div class="col-md-6"><p><strong>Department:</strong> <?php echo e($employee->department->department_name ?? 'N/A'); ?></p></div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Other Details Step -->
                <div class="tab-pane fade" id="other" role="tabpanel">
                    <h5 class="text-primary mb-3">Other Details</h5>
                    <div class="row">
                        <div class="col-md-6"><p><strong>Status:</strong> <?php echo e($employee->status); ?></p></div>
                        <div class="col-md-6"><p><strong>Highest Certificate:</strong> <?php echo e($employee->highest_certificate ?? 'N/A'); ?></p></div>
                        <div class="col-md-6"><p><strong>Appointment Type:</strong> <?php echo e($employee->appointmentType->name ?? 'N/A'); ?></p></div>
                    </div>
                </div>

                <!-- Next of Kin Step -->
                <div class="tab-pane fade" id="kin" role="tabpanel">
                    <?php if($employee->nextOfKin): ?>
                        <h5 class="text-primary">Next of Kin Details</h5>
                        <div class="row">
                            <div class="col-md-6"><p><strong>Name:</strong> <?php echo e($employee->nextOfKin->name); ?></p></div>
                            <div class="col-md-6"><p><strong>Relationship:</strong> <?php echo e($employee->nextOfKin->relationship); ?></p></div>
                            <div class="col-md-6"><p><strong>Phone:</strong> <?php echo e($employee->nextOfKin->mobile_no); ?></p></div>
                            <div class="col-md-6"><p><strong>Address:</strong> <?php echo e($employee->nextOfKin->address); ?></p></div>
                            <div class="col-md-6"><p><strong>Occupation:</strong> <?php echo e($employee->nextOfKin->occupation ?? 'N/A'); ?></p></div>
                            <div class="col-md-6"><p><strong>Place of Work:</strong> <?php echo e($employee->nextOfKin->place_of_work ?? 'N/A'); ?></p></div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning mt-4">No next of kin details available.</div>
                    <?php endif; ?>
                </div>

                <!-- Bank Details Step -->
                <div class="tab-pane fade" id="bank" role="tabpanel">
                    <?php if($employee->bank): ?>
                        <h5 class="text-primary">Bank Information</h5>
                        <div class="row">
                            <div class="col-md-6"><p><strong>Bank Name:</strong> <?php echo e($employee->bank->bank_name); ?></p></div>
                            <div class="col-md-6"><p><strong>Bank Code:</strong> <?php echo e($employee->bank->bank_code); ?></p></div>
                            <div class="col-md-6"><p><strong>Account Name:</strong> <?php echo e($employee->bank->account_name); ?></p></div>
                            <div class="col-md-6"><p><strong>Account Number:</strong> <?php echo e($employee->bank->account_no); ?></p></div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning mt-4">No bank information available.</div>
                    <?php endif; ?>
                </div>

                <!-- Probation Details Step -->
                <div class="tab-pane fade" id="probation" role="tabpanel">
                    <h5 class="text-primary mb-3">Probation Information</h5>
                    <div class="row">
                        <div class="col-md-6"><p><strong>On Probation:</strong>
                            <?php if($employee->on_probation): ?>
                                <span class="badge bg-warning">Yes</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">No</span>
                            <?php endif; ?>
                        </p></div>
                        <div class="col-md-6"><p><strong>Probation Status:</strong>
                            <?php if($employee->probation_status == 'pending'): ?>
                                <span class="badge bg-warning">On Probation</span>
                            <?php elseif($employee->probation_status == 'approved'): ?>
                                <span class="badge bg-success">Approved</span>
                            <?php elseif($employee->probation_status == 'rejected'): ?>
                                <span class="badge bg-danger">Rejected</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">N/A</span>
                            <?php endif; ?>
                        </p></div>
                        <div class="col-md-6"><p><strong>Probation Start Date:</strong> <?php echo e($employee->probation_start_date ? \Carbon\Carbon::parse($employee->probation_start_date)->format('j M Y') : 'N/A'); ?></p></div>
                        <div class="col-md-6"><p><strong>Probation End Date:</strong> <?php echo e($employee->probation_end_date ? \Carbon\Carbon::parse($employee->probation_end_date)->format('j M Y') : 'N/A'); ?></p></div>
                        <?php if($employee->on_probation): ?>
                            <div class="col-md-6">
                                <p><strong>Days Remaining:</strong>
                                    <?php if($employee->hasProbationPeriodEnded()): ?>
                                        <span class="text-danger">Probation Ended</span>
                                    <?php else: ?>
                                        <?php echo e($employee->getRemainingProbationDays()); ?> days
                                    <?php endif; ?>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Can be Evaluated:</strong>
                                    <?php if($employee->canBeEvaluatedForProbation()): ?>
                                        <span class="badge bg-success">Yes</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning">No (<?php echo e($employee->getRemainingProbationDays()); ?> days remaining)</span>
                                    <?php endif; ?>
                                </p>
                            </div>
                        <?php endif; ?>
                        <?php if($employee->probation_notes): ?>
                            <div class="col-md-12">
                                <p><strong>Probation Notes:</strong></p>
                                <div class="alert alert-info">
                                    <?php echo e($employee->probation_notes); ?>

                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if($employee->on_probation): ?>
                        <div class="mt-3">
                            <a href="<?php echo e(route('probation.show', $employee)); ?>" class="btn btn-info rounded-pill">
                                <i class="fas fa-clock me-1"></i>Manage Probation
                            </a>
                        </div>
                    <?php endif; ?>
                </div>


            <div class="d-flex justify-content-between mt-4">
                <a href="<?php echo e(route('employees.index')); ?>" class="btn btn-secondary rounded-pill px-4">Back</a>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('edit_employees')): ?>
                <div>
                    <a href="<?php echo e(route('employees.edit', $employee)); ?>" class="btn btn-warning rounded-pill px-4">Edit</a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Rowwww\Herd\hrm_payroll_system_design\resources\views/employees/show.blade.php ENDPATH**/ ?>