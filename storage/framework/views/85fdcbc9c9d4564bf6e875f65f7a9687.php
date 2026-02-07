<?php $__env->startSection('title', 'Profile'); ?>

<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0"><?php echo e(__('My Profile')); ?></h4>
                </div>
                <div class="card-body">
                    <?php if(session('status')): ?>
                        <div class="alert alert-success" role="alert">
                            <?php echo e(session('status')); ?>

                        </div>
                    <?php endif; ?>

                    <!-- Profile Header -->
                    <div class="row mb-4">
                        <div class="col-md-12 text-center">
                            <img src="<?php echo e($user->employee && $user->employee->photo_path ? asset('storage/' . $user->employee->photo_path) : asset('images/default-image.png')); ?>"
                                 alt="Profile" class="rounded-circle border border-2 mb-3"
                                 style="width: 120px; height: 120px; object-fit: cover;">
                            <h5><?php echo e($user->employee ? $user->employee->first_name . ' ' . $user->employee->surname : $user->username); ?></h5>
                            <span class="badge bg-secondary"><?php echo e($user->roles->first()?->name ?? 'No role assigned'); ?></span>
                        </div>
                    </div>

                    <!-- Action buttons -->
                    <div class="row mb-4">
                        <div class="col-md-12 text-center">
                            <a href="<?php echo e(route('profile.change-password')); ?>" class="btn btn-warning">
                                <i class="fas fa-key"></i> <?php echo e(__('Change Password')); ?>

                            </a>
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
                            <h5 class="mb-3"><?php echo e(__('Personal Information')); ?></h5>
                            <table class="table table-striped">
                                <tr>
                                    <td width="40%"><strong><?php echo e(__('Username')); ?></strong></td>
                                    <td><?php echo e($user->username); ?></td>
                                </tr>
                                <tr>
                                    <td><strong><?php echo e(__('Email')); ?></strong></td>
                                    <td><?php echo e($user->email); ?></td>
                                </tr>
                                <tr>
                                    <td><strong><?php echo e(__('Staff No')); ?></strong></td>
                                    <td><?php echo e($user->employee ? $user->employee->staff_no : 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <td><strong><?php echo e(__('Registration No')); ?></strong></td>
                                    <td><?php echo e($user->employee ? $user->employee->staff_no : 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <td><strong><?php echo e(__('Full Name')); ?></strong></td>
                                    <td><?php echo e($user->employee ? $user->employee->first_name . ' ' . $user->employee->middle_name . ' ' . $user->employee->surname : 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <td><strong><?php echo e(__('Gender')); ?></strong></td>
                                    <td><?php echo e($user->employee ? $user->employee->gender : 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <td><strong><?php echo e(__('Date of Birth')); ?></strong></td>
                                    <td><?php echo e($user->employee ? \Carbon\Carbon::parse($user->employee->date_of_birth)->format('d M Y') : 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <td><strong><?php echo e(__('NIN')); ?></strong></td>
                                    <td><?php echo e($user->employee ? $user->employee->nin : 'N/A'); ?></td>
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
                            <h5 class="mb-3"><?php echo e(__('Employment Information')); ?></h5>
                            <table class="table table-striped">
                                <tr>
                                    <td width="40%"><strong><?php echo e(__('Department')); ?></strong></td>
                                    <td><?php echo e($user->employee && $user->employee->department ? $user->employee->department->department_name : 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <td><strong><?php echo e(__('Cadre')); ?></strong></td>
                                    <td><?php echo e($user->employee && $user->employee->cadre ? $user->employee->cadre->name : 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <td><strong><?php echo e(__('Grade Level')); ?></strong></td>
                                    <td><?php echo e($user->employee && $user->employee->gradeLevel ? $user->employee->gradeLevel->name : 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <td><strong><?php echo e(__('Step')); ?></strong></td>
                                    <td><?php echo e($user->employee && $user->employee->step ? $user->employee->step->name : 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <td><strong><?php echo e(__('Rank')); ?></strong></td>
                                    <td><?php echo e($user->employee && $user->employee->rank ? $user->employee->rank->name : 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <td><strong><?php echo e(__('Status')); ?></strong></td>
                                    <td>
                                        <?php if($user->employee): ?>
                                            <span class="badge
                                                <?php if($user->employee->status === 'Active'): ?> bg-success
                                                <?php elseif($user->employee->status === 'Suspended'): ?> bg-warning
                                                <?php elseif($user->employee->status === 'Retired'): ?> bg-info
                                                <?php elseif($user->employee->status === 'Deceased'): ?> bg-danger
                                                <?php else: ?> bg-secondary <?php endif; ?>">
                                                <?php echo e($user->employee->status); ?>

                                            </span>
                                        <?php else: ?>
                                            N/A
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong><?php echo e(__('Date of First Appointment')); ?></strong></td>
                                    <td><?php echo e($user->employee ? \Carbon\Carbon::parse($user->employee->date_of_first_appointment)->format('d M Y') : 'N/A'); ?></td>
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
                            <h5 class="mb-3"><?php echo e(__('Contact Information')); ?></h5>
                            <table class="table table-striped">
                                <tr>
                                    <td width="40%"><strong><?php echo e(__('Mobile No')); ?></strong></td>
                                    <td><?php echo e($user->employee ? $user->employee->mobile_no : 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <td><strong><?php echo e(__('Email')); ?></strong></td>
                                    <td><?php echo e($user->employee ? $user->employee->email : 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <td><strong><?php echo e(__('Address')); ?></strong></td>
                                    <td><?php echo e($user->employee ? $user->employee->address : 'N/A'); ?></td>
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
                            <h5 class="mb-3"><?php echo e(__('Banking Information')); ?></h5>
                            <table class="table table-striped">
                                <tr>
                                    <td width="40%"><strong><?php echo e(__('Bank Name')); ?></strong></td>
                                    <td><?php echo e($user->employee && $user->employee->bank ? $user->employee->bank->bank_name : 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <td><strong><?php echo e(__('Account Name')); ?></strong></td>
                                    <td><?php echo e($user->employee ? $user->employee->account_name : 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <td><strong><?php echo e(__('Account Number')); ?></strong></td>
                                    <td><?php echo e($user->employee ? $user->employee->account_no : 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <td><strong><?php echo e(__('Bank Code')); ?></strong></td>
                                    <td><?php echo e($user->employee ? $user->employee->bank_code : 'N/A'); ?></td>
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
                            <h5 class="mb-3"><?php echo e(__('Next of Kin')); ?></h5>
                            <table class="table table-striped">
                                <?php if($user->employee && $user->employee->nextOfKin): ?>
                                <tr>
                                    <td width="40%"><strong><?php echo e(__('Name')); ?></strong></td>
                                    <td><?php echo e($user->employee->nextOfKin->name); ?></td>
                                </tr>
                                <tr>
                                    <td><strong><?php echo e(__('Relationship')); ?></strong></td>
                                    <td><?php echo e($user->employee->nextOfKin->relationship); ?></td>
                                </tr>
                                <tr>
                                    <td><strong><?php echo e(__('Mobile No')); ?></strong></td>
                                    <td><?php echo e($user->employee->nextOfKin->mobile_no); ?></td>
                                </tr>
                                <tr>
                                    <td><strong><?php echo e(__('Address')); ?></strong></td>
                                    <td><?php echo e($user->employee->nextOfKin->address); ?></td>
                                </tr>
                                <tr>
                                    <td><strong><?php echo e(__('Occupation')); ?></strong></td>
                                    <td><?php echo e($user->employee->nextOfKin->occupation ?: 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <td><strong><?php echo e(__('Place of Work')); ?></strong></td>
                                    <td><?php echo e($user->employee->nextOfKin->place_of_work ?: 'N/A'); ?></td>
                                </tr>
                                <?php else: ?>
                                <tr>
                                    <td colspan="2" class="text-center text-muted"><?php echo e(__('No next of kin information available')); ?></td>
                                </tr>
                                <?php endif; ?>
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
                            <h5 class="mb-3"><?php echo e(__('Location Information')); ?></h5>
                            <table class="table table-striped">
                                <tr>
                                    <td width="40%"><strong><?php echo e(__('State of Origin')); ?></strong></td>
                                    <td><?php echo e($user->employee && $user->employee->state ? $user->employee->state->name : 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <td><strong><?php echo e(__('LGA')); ?></strong></td>
                                    <td><?php echo e($user->employee && $user->employee->lga ? $user->employee->lga->name : 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <td><strong><?php echo e(__('Ward')); ?></strong></td>
                                    <td><?php echo e($user->employee && $user->employee->ward ? $user->employee->ward->ward_name : 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <td><strong><?php echo e(__('Nationality')); ?></strong></td>
                                    <td><?php echo e($user->employee ? $user->employee->nationality : 'N/A'); ?></td>
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
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Rowwww\Herd\hrm_payroll_system_design\resources\views/profile/show.blade.php ENDPATH**/ ?>