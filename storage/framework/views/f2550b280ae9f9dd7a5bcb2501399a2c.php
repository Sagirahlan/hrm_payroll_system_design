<?php $__env->startSection('content'); ?>
<div class="container py-4">
    <div class="mb-3">
        <a href="<?php echo e(route('employees.index')); ?>" class="btn btn-secondary shadow-sm">
            <i class="fas fa-arrow-left mr-1"></i> Back to List
        </a>
    </div>

    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create_employees')): ?>
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
                    <form id="employeeForm" action="<?php echo e(route('employees.store')); ?>" method="POST" enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="change_reason" value="New employee creation">

                        <!-- Step 1: Personal Information -->
                        <div class="step-card" id="step1">
                            <h5 class="mb-3 text-info text-center font-weight-bold">Personal Information</h5>
                            <div class="row g-3">
                                <div class="col-md-4 col-12">
                                    <label class="form-label font-weight-bold">First Name <span class="text-danger">*</span></label>
                                    <input type="text" name="first_name" class="form-control" required value="<?php echo e(old('first_name')); ?>">
                                    <?php $__errorArgs = ['first_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="text-danger"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                                <div class="col-md-4 col-12">
                                    <label class="form-label font-weight-bold">Surname <span class="text-danger">*</span></label>
                                    <input type="text" name="surname" class="form-control" required value="<?php echo e(old('surname')); ?>">
                                    <?php $__errorArgs = ['surname'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="text-danger"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                                <div class="col-md-4 col-12">
                                    <label class="form-label font-weight-bold">Middle Name (optional)</label>
                                    <input type="text" name="middle_name" class="form-control" value="<?php echo e(old('middle_name')); ?>">
                                    <?php $__errorArgs = ['middle_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="text-danger"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                                <div class="col-md-4 col-12">
                                    <label class="form-label font-weight-bold">Gender <span class="text-danger">*</span></label>
                                    <select name="gender" class="form-select" required>
                                        <option value="Male" <?php echo e(old('gender') == 'Male' ? 'selected' : ''); ?>>Male</option>
                                        <option value="Female" <?php echo e(old('gender') == 'Female' ? 'selected' : ''); ?>>Female</option>
                                    </select>
                                    <?php $__errorArgs = ['gender'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="text-danger"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                                <div class="col-md-4 col-12">
                                    <label class="form-label font-weight-bold">Date of Birth <span class="text-danger">*</span></label>
                                    <input type="date" name="date_of_birth" class="form-control" required value="<?php echo e(old('date_of_birth')); ?>">
                                    <?php $__errorArgs = ['date_of_birth'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="text-danger"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                                <div class="col-md-4 col-12">
                                    <label class="form-label font-weight-bold">Nationality <span class="text-danger">*</span></label>
                                    <select name="nationality" class="form-select" required>
                                        <option value="">-- Select Nationality --</option>
                                        <option value="Nigeria" <?php echo e(old('nationality') == 'Nigeria' ? 'selected' : ''); ?>>Nigeria</option>
                                       
                                    </select>
                                    <?php $__errorArgs = ['nationality'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="text-danger"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                                <div class="col-md-4 col-12">
                                    <label class="form-label font-weight-bold">State of Origin <span class="text-danger">*</span></label>
                                    <select id="state" name="state_id" class="form-select" required>
                                        <option value="">-- Select State --</option>
                                        <?php $__currentLoopData = $states; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $state): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($state->state_id); ?>" <?php echo e(old('state_id') == $state->state_id ? 'selected' : ''); ?>><?php echo e($state->name); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                    <?php $__errorArgs = ['state_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="text-danger"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                                <div class="col-md-4 col-12">
                                    <label class="form-label font-weight-bold">Local Government Area (LGA) <span class="text-danger">*</span></label>
                                    <select id="lga" name="lga_id" class="form-select" required>
                                        <option value="">-- Select LGA --</option>
                                    </select>
                                    <?php $__errorArgs = ['lga_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="text-danger"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                                <div class="col-md-4 col-12">
                                    <label class="form-label font-weight-bold">Ward (optional)</label>
                                    <select id="ward" name="ward_id" class="form-select">
                                        <option value="">-- Select Ward --</option>
                                    </select>
                                    <?php $__errorArgs = ['ward_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="text-danger"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                                <div class="col-md-4 col-12">
                                    <label class="form-label font-weight-bold">Staff ID <span class="text-danger">*</span></label>
                                    <input type="text" name="staff_no" class="form-control" required value="<?php echo e(old('staff_no')); ?>">
                                    <?php $__errorArgs = ['staff_no'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="text-danger"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                                <div class="col-md-4 col-12">
                                    <label class="form-label font-weight-bold">NIN <span class="text-danger">*</span></label>
                                    <input type="text" name="nin" class="form-control" value="<?php echo e(old('nin')); ?>">
                                    <?php $__errorArgs = ['nin'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="text-danger"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                                <div class="col-md-4 col-12">
                                    <label class="form-label font-weight-bold">Mobile No <span class="text-danger">*</span></label>
                                    <input type="text" name="mobile_no" class="form-control" required value="<?php echo e(old('mobile_no')); ?>">
                                    <?php $__errorArgs = ['mobile_no'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="text-danger"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
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
                                    <input type="email" name="email" class="form-control" value="<?php echo e(old('email')); ?>">
                                    <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="text-danger"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                                <div class="col-md-6 col-12">
                                    <label class="form-label font-weight-bold">Pay Point <span class="text-danger">*</span></label>
                                    <input type="text" name="pay_point" class="form-control" value="<?php echo e(old('pay_point')); ?>">
                                    <?php $__errorArgs = ['pay_point'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="text-danger"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                                <div class="col-md-6 col-12">
                                    <label class="form-label font-weight-bold">Address <span class="text-danger">*</span></label>
                                    <textarea name="address" class="form-control" required><?php echo e(old('address')); ?></textarea>
                                    <?php $__errorArgs = ['address'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="text-danger"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
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
                                        <?php $__currentLoopData = $appointmentTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($type->id); ?>" data-name="<?php echo e($type->name); ?>" <?php echo e(old('appointment_type_id') == $type->id ? 'selected' : ''); ?>><?php echo e($type->name); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                    <?php $__errorArgs = ['appointment_type_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="text-danger"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                                <div class="col-md-6 col-12">
                                    <label class="form-label font-weight-bold">Date of First Appointment <span class="text-danger">*</span></label>
                                    <input type="date" name="date_of_first_appointment" class="form-control" required value="<?php echo e(old('date_of_first_appointment')); ?>">
                                    <?php $__errorArgs = ['date_of_first_appointment'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="text-danger"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>

                                <div class="col-md-6 col-12">
                                    <label class="form-label font-weight-bold">Department <span class="text-danger">*</span></label>
                                    <select name="department_id" class="form-select">
                                        <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $department): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($department->department_id); ?>" <?php echo e(old('department_id') == $department->department_id ? 'selected' : ''); ?>><?php echo e($department->department_name); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                    <?php $__errorArgs = ['department_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="text-danger"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
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
                                            <?php $__currentLoopData = $cadres; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cadre): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($cadre->cadre_id); ?>" <?php echo e(old('cadre_id') == $cadre->cadre_id ? 'selected' : ''); ?>><?php echo e($cadre->name); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                        <?php $__errorArgs = ['cadre_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="text-danger"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                    <div class="col-md-6 col-12">
                                        <label class="form-label font-weight-bold">Salary Scale <span class="text-danger">*</span></label>
                                        <select id="salary_scale_id" name="salary_scale_id" class="form-select">
                                            <option value="">-- Select Salary Scale --</option>
                                            <?php $__currentLoopData = $salaryScales; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $scale): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($scale->id); ?>" <?php echo e(old('salary_scale_id') == $scale->id ? 'selected' : ''); ?>><?php echo e($scale->acronym); ?> - <?php echo e($scale->full_name); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                        <?php $__errorArgs = ['salary_scale_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="text-danger"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                    <div class="col-md-4 col-12">
                                        <label class="form-label font-weight-bold">Grade Level <span class="text-danger">*</span></label>
                                        <select id="grade_level_name" name="grade_level_name" class="form-select">
                                            <option value="">-- Select Grade Level --</option>
                                        </select>
                                        <?php $__errorArgs = ['grade_level_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="text-danger"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                    <div class="col-md-2 col-12">
                                        <label class="form-label font-weight-bold">Step <span class="text-danger">*</span></label>
                                        <select id="step_level" name="step_level" class="form-select">
                                            <option value="">-- Step --</option>
                                        </select>
                                        <?php $__errorArgs = ['step_level'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="text-danger"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                    <input type="hidden" id="grade_level_id" name="grade_level_id" value="<?php echo e(old('grade_level_id')); ?>">
                                    <input type="hidden" id="step_id" name="step_id" value="<?php echo e(old('step_id')); ?>">
                                    <div class="col-md-6 col-12">
                                        <label class="form-label font-weight-bold">Rank <span class="text-danger">*</span></label>
                                        <select name="rank_id" class="form-select">
                                            <?php $__currentLoopData = $ranks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rank): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($rank->id); ?>" <?php echo e(old('rank_id') == $rank->id ? 'selected' : ''); ?>><?php echo e($rank->title); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                        <?php $__errorArgs = ['rank_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="text-danger"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                    <div class="col-md-6 col-12">
                                        <label class="form-label font-weight-bold">Expected Next Promotion (optional)</label>
                                        <input type="date" name="expected_next_promotion" class="form-control" value="<?php echo e(old('expected_next_promotion')); ?>">
                                        <?php $__errorArgs = ['expected_next_promotion'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="text-danger"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                    <div class="col-md-6 col-12">
                                        <label class="form-label font-weight-bold">Expected Retirement Date <span class="text-danger">*</span></label>
                                        <input type="date" name="expected_retirement_date" class="form-control" readonly value="<?php echo e(old('expected_retirement_date')); ?>">
                                        <?php $__errorArgs = ['expected_retirement_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="text-danger"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                </div>

                                <!-- Casual Appointment Fields -->
                                <div class="row g-3 d-none" id="casual_appointment_fields">
                                    <div class="col-12">
                                        <h6 class="text-muted" id="casual_section_title">Casual/Contract Details</h6>
                                    </div>
                                    <div class="col-md-6 col-12">
                                        <label class="form-label font-weight-bold">Casual Start Date <span class="text-danger">*</span></label>
                                        <input type="date" name="contract_start_date" class="form-control" value="<?php echo e(old('contract_start_date')); ?>">
                                        <?php $__errorArgs = ['contract_start_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="text-danger"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                    <div class="col-md-6 col-12">
                                        <label class="form-label font-weight-bold">Casual End Date <span class="text-danger">*</span></label>
                                        <input type="date" name="contract_end_date" class="form-control" value="<?php echo e(old('contract_end_date')); ?>">
                                        <?php $__errorArgs = ['contract_end_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="text-danger"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                    <div class="col-md-6 col-12">
                                        <label class="form-label font-weight-bold">Amount <span class="text-danger">*</span></label>
                                        <input type="number" name="amount" class="form-control" value="<?php echo e(old('amount')); ?>">
                                        <?php $__errorArgs = ['amount'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="text-danger"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
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
                                        <option value="Active" <?php echo e(old('status') == 'Active' ? 'selected' : ''); ?>>Active</option>
                                        <option value="Suspended" <?php echo e(old('status') == 'Suspended' ? 'selected' : ''); ?>>Suspended</option>
                                        <option value="Retired" <?php echo e(old('status') == 'Retired' ? 'selected' : ''); ?>>Retired</option>
                                        <option value="Retired-Active" <?php echo e(old('status') == 'Retired-Active' ? 'selected' : ''); ?>>Retired-Active</option>
                                        <option value="Deceased" <?php echo e(old('status') == 'Deceased' ? 'selected' : ''); ?>>Deceased</option>
                                        <option value="Hold" <?php echo e(old('status') == 'Hold' ? 'selected' : ''); ?>>Hold</option>
                                    </select>
                                    <?php $__errorArgs = ['status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="text-danger"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                                <div class="col-md-4 col-12">
                                    <label class="form-label font-weight-bold">Highest Certificate (optional)</label>
                                    <select name="highest_certificate" class="form-control">
                                        <option value="">-- Select --</option>
                                        <option value="No formal education" <?php echo e(old('highest_certificate') == 'No formal education' ? 'selected' : ''); ?>>No formal education</option>
                                        <option value="Primary education" <?php echo e(old('highest_certificate') == 'Primary education' ? 'selected' : ''); ?>>Primary education</option>
                                        <option value="Secondary education / High school or equivalent" <?php echo e(old('highest_certificate') == 'Secondary education / High school or equivalent' ? 'selected' : ''); ?>>Secondary education / High school or equivalent (e.g. SSCE, WAEC, NECO)</option>
                                        <option value="Vocational qualification" <?php echo e(old('highest_certificate') == 'Vocational qualification' ? 'selected' : ''); ?>>Vocational qualification (e.g. NABTEB, trade certificates, NVC)</option>
                                        <option value="Associate degree / NCE / ND" <?php echo e(old('highest_certificate') == 'Associate degree / NCE / ND' ? 'selected' : ''); ?>>Associate degree / NCE / National Diploma (ND)</option>
                                        <option value="Bachelor’s degree" <?php echo e(old('highest_certificate') == 'Bachelor’s degree' ? 'selected' : ''); ?>>Bachelor’s degree (B.Sc, B.A, B.Eng, LLB, etc.)</option>
                                        <option value="Professional degree/license" <?php echo e(old('highest_certificate') == 'Professional degree/license' ? 'selected' : ''); ?>>Professional degree/license (e.g., BL, ICAN, COREN, TRCN, MDCN)</option>
                                        <option value="Master’s degree" <?php echo e(old('highest_certificate') == 'Master’s degree' ? 'selected' : ''); ?>>Master’s degree (M.Sc, MBA, M.A, etc.)</option>
                                        <option value="Doctorate / Ph.D. or higher" <?php echo e(old('highest_certificate') == 'Doctorate / Ph.D. or higher' ? 'selected' : ''); ?>>Doctorate / Ph.D. or higher</option>
                                    </select>
                                    <?php $__errorArgs = ['highest_certificate'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="text-danger"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                                <div class="col-md-12 col-12">
                                    <label class="form-label font-weight-bold">Photo (optional)</label>
                                    <div class="input-group">
                                        <input type="file" name="photo" class="form-control" accept="image/*" capture="environment" value="<?php echo e(old('photo')); ?>">
                                        <button class="btn btn-outline-secondary" type="button" id="cameraButton">📷 Camera</button>
                                    </div>
                                    <small class="form-text text-muted">Upload from gallery or take a photo with your camera</small>
                                    <?php $__errorArgs = ['photo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="text-danger"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
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
                                    <input type="text" name="kin_name" class="form-control" required value="<?php echo e(old('kin_name')); ?>">
                                    <?php $__errorArgs = ['kin_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="text-danger"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                                <div class="col-md-6 col-12">
                                    <label class="form-label font-weight-bold">Relationship <span class="text-danger">*</span></label>
                                    <input type="text" name="kin_relationship" class="form-control" required value="<?php echo e(old('kin_relationship')); ?>">
                                    <?php $__errorArgs = ['kin_relationship'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="text-danger"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                                <div class="col-md-6 col-12">
                                    <label class="form-label font-weight-bold">Mobile No <span class="text-danger">*</span></label>
                                    <input type="text" name="kin_mobile_no" class="form-control" required value="<?php echo e(old('kin_mobile_no')); ?>">
                                    <?php $__errorArgs = ['kin_mobile_no'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="text-danger"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                                <div class="col-md-6 col-12">
                                    <label class="form-label font-weight-bold">Address <span class="text-danger">*</span></label>
                                    <input type="text" name="kin_address" class="form-control" required value="<?php echo e(old('kin_address')); ?>">
                                    <?php $__errorArgs = ['kin_address'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="text-danger"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                                <div class="col-md-6 col-12">
                                    <label class="form-label font-weight-bold">Occupation (optional)</label>
                                    <input type="text" name="kin_occupation" class="form-control" value="<?php echo e(old('kin_occupation')); ?>">
                                    <?php $__errorArgs = ['kin_occupation'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="text-danger"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                                <div class="col-md-6 col-12">
                                    <label class="form-label font-weight-bold">Place of Work (optional)</label>
                                    <input type="text" name="kin_place_of_work" class="form-control" value="<?php echo e(old('kin_place_of_work')); ?>">
                                    <?php $__errorArgs = ['kin_place_of_work'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="text-danger"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
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
                                        <?php $__currentLoopData = $banks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bank): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($bank->bank_name); ?>" data-code="<?php echo e($bank->bank_code); ?>" <?php echo e(old('bank_name') == $bank->bank_name ? 'selected' : ''); ?>><?php echo e($bank->bank_name); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                    <?php $__errorArgs = ['bank_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="text-danger"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                                <div class="col-md-6 col-12">
                                    <label class="form-label font-weight-bold">Bank Code <span class="text-danger">*</span></label>
                                    <input type="text" name="bank_code" id="bank_code" class="form-control" required value="<?php echo e(old('bank_code')); ?>" readonly>
                                    <?php $__errorArgs = ['bank_code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="text-danger"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                                <div class="col-md-6 col-12">
                                    <label class="form-label font-weight-bold">Account Name <span class="text-danger">*</span></label>
                                    <input type="text" name="account_name" class="form-control" required value="<?php echo e(old('account_name')); ?>">
                                    <?php $__errorArgs = ['account_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="text-danger"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                                <div class="col-md-6 col-12">
                                    <label class="form-label font-weight-bold">Account Number <span class="text-danger">*</span></label>
                                    <input type="text" name="account_no" class="form-control" required value="<?php echo e(old('account_no')); ?>">
                                    <?php $__errorArgs = ['account_no'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="text-danger"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
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
        const casualAppointmentFields = document.getElementById('casual_appointment_fields');

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

            // Validate Casual dates
            if (step === 3 && ['Casual', 'Contract'].includes(appointmentTypeSelect.options[appointmentTypeSelect.selectedIndex].dataset.name)) {
                const casualStartDate = document.querySelector('input[name="contract_start_date"]');
                const casualEndDate = document.querySelector('input[name="contract_end_date"]');
                if (casualStartDate.value && casualEndDate.value) {
                    const startDate = new Date(casualStartDate.value);
                    const endDate = new Date(casualEndDate.value);
                    if (endDate <= startDate) {
                        const error = document.createElement('small');
                        error.className = 'text-danger';
                        error.innerText = 'Casual end date must be after start date.';
                        const existingError = casualEndDate.nextElementSibling;
                        if (existingError && existingError.classList.contains('text-danger')) {
                            existingError.remove();
                        }
                        casualEndDate.parentNode.appendChild(error);
                        isValid = false;
                    }
                }
            }

            if (step === 3) {
                const appointmentTypeName = appointmentTypeSelect.options[appointmentTypeSelect.selectedIndex].dataset.name;
                // Grade level/step only required for Permanent (not Casual or Contract)
                if (appointmentTypeName !== 'Casual' && appointmentTypeName !== 'Contract') {
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
            const sectionTitle = document.getElementById('casual_section_title');
            const statusSelect = document.querySelector('select[name="status"]');
            const retiredActiveOption = statusSelect ? statusSelect.querySelector('option[value="Retired-Active"]') : null;

            regularAppointmentFields.querySelectorAll('input, select').forEach(field => field.disabled = false);
            casualAppointmentFields.querySelectorAll('input, select').forEach(field => field.disabled = false);

            if (appointmentTypeName === 'Casual') {
                // Casual: hide permanent fields, show contract fields
                regularAppointmentFields.classList.add('d-none');
                casualAppointmentFields.classList.remove('d-none');
                regularAppointmentFields.querySelectorAll('input, select').forEach(field => field.disabled = true);
                if (sectionTitle) sectionTitle.textContent = 'Casual Appointment Details';
            } else if (appointmentTypeName === 'Contract') {
                // Contract: show BOTH permanent fields (optional) and contract fields
                regularAppointmentFields.classList.remove('d-none');
                casualAppointmentFields.classList.remove('d-none');
                // Permanent fields are visible but not disabled (optional for contract)
                if (sectionTitle) sectionTitle.textContent = 'Contract Details';
                regularAppointmentFields.classList.remove('d-none');
                casualAppointmentFields.classList.add('d-none');
                casualAppointmentFields.querySelectorAll('input, select').forEach(field => field.disabled = true);
            }

            if (retiredActiveOption) {
                if (appointmentTypeName === 'Contract') {
                    retiredActiveOption.hidden = false;
                    retiredActiveOption.disabled = false;
                } else {
                    retiredActiveOption.hidden = true;
                    retiredActiveOption.disabled = true;
                    if (statusSelect.value === 'Retired-Active') {
                        statusSelect.value = 'Active';
                    }
                }
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

        const states = <?php echo json_encode($states, 15, 512) ?>;
        const lgas = <?php echo json_encode($lgas, 15, 512) ?>;
        const wards = <?php echo json_encode($wards, 15, 512) ?>;

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

        const oldStateId = "<?php echo e(old('state_id')); ?>";
        const oldLgaId = "<?php echo e(old('lga_id')); ?>";
        const oldWardId = "<?php echo e(old('ward_id')); ?>";

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
                            const oldGradeLevelName = "<?php echo e(old('grade_level_name')); ?>";
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
                            const oldStepLevel = "<?php echo e(old('step_level')); ?>";
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

        const oldSalaryScaleId = "<?php echo e(old('salary_scale_id')); ?>";
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

        <?php if(session('step')): ?>
            showStep(<?php echo e(session('step')); ?>);
        <?php else: ?>
            showStep(1);
        <?php endif; ?>
    });
</script>
    <?php endif; ?>
<?php $__env->stopSection(); ?>



<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Rowwww\Herd\hrm_payroll_system_design\resources\views/employees/create.blade.php ENDPATH**/ ?>