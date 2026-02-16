<?php $__env->startSection('content'); ?>
<div class="container">
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage_sms')): ?>
    <div class="mb-3">
        <a href="<?php echo e(route('dashboard')); ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>
    
    <h1>Send SMS Notification</h1>
    <form action="<?php echo e(route('sms.store')); ?>" method="POST">
        <?php echo csrf_field(); ?>
        <div class="mb-3">
            <label for="recipient_type" class="form-label">Recipient Type</label>
            <select name="recipient_type" class="form-control" id="recipient_type" required>
                <option value="All">All Employees</option>
                <option value="Department">Department</option>
                <option value="GradeLevel">Grade Level</option>
                <option value="Cadre">Cadre</option>
                <option value="AppointmentType">Appointment Type</option>
                <option value="Status">Status</option>
                <option value="Gender">Gender</option>
                <option value="State">State</option>
            </select>
            <?php $__errorArgs = ['recipient_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <div class="mb-3" id="department_select" style="display:none;">
            <label for="recipient_id" class="form-label">Department</label>
            <select name="recipient_id" class="form-control">
                <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $department): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($department->department_id); ?>"><?php echo e($department->department_name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <?php $__errorArgs = ['recipient_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <div class="mb-3" id="grade_level_select" style="display:none;">
            <label for="grade_level_id" class="form-label">Grade Level</label>
            <select name="grade_level_id" class="form-control">
                <?php $__currentLoopData = $gradeLevels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $level): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($level->id); ?>">
                        Grade <?php echo e($level->grade_level); ?>, Step <?php echo e($level->step_level); ?> (<?php echo e($level->name); ?>)
                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <?php $__errorArgs = ['grade_level_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <div class="mb-3" id="cadre_select" style="display:none;">
            <label for="cadre_id" class="form-label">Cadre</label>
            <select name="cadre_id" class="form-control">
                <?php $__currentLoopData = $cadres; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cadre): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($cadre->cadre_id); ?>"><?php echo e($cadre->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <?php $__errorArgs = ['cadre_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <div class="mb-3" id="appointment_type_select" style="display:none;">
            <label for="appointment_type_id" class="form-label">Appointment Type</label>
            <select name="appointment_type_id" class="form-control">
                <?php $__currentLoopData = $appointmentTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($type->id); ?>"><?php echo e($type->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <?php $__errorArgs = ['appointment_type_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <div class="mb-3" id="status_select" style="display:none;">
            <label for="status" class="form-label">Status</label>
            <select name="status" class="form-control">
                <?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($status); ?>"><?php echo e($status); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <?php $__errorArgs = ['status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <div class="mb-3" id="gender_select" style="display:none;">
            <label for="gender" class="form-label">Gender</label>
            <select name="gender" class="form-control">
                <?php $__currentLoopData = $genders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $gender): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($gender); ?>"><?php echo e($gender); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <?php $__errorArgs = ['gender'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <div class="mb-3" id="state_select" style="display:none;">
            <label for="state_id" class="form-label">State</label>
            <select name="state_id" class="form-control">
                <?php $__currentLoopData = $states; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $state): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($state->state_id); ?>"><?php echo e($state->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <?php $__errorArgs = ['state_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <div class="mb-3">
            <label for="message" class="form-label">Message (Max 160 characters)</label>
            <textarea name="message" class="form-control" required maxlength="160" rows="4"></textarea>
            <?php $__errorArgs = ['message'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>
        <button type="submit" class="btn btn-primary">Send SMS</button>
    </form>
    <?php else: ?>
    <div class="alert alert-warning">
        You don't have permission to send SMS notifications.
    </div>
    <?php endif; ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const recipientTypeSelect = document.getElementById('recipient_type');

        // Function to show/hide filter options
        function toggleFilterOptions() {
            const recipientType = recipientTypeSelect.value;

            // Hide all conditional selects
            document.getElementById('department_select').style.display = 'none';
            document.getElementById('grade_level_select').style.display = 'none';
            document.getElementById('cadre_select').style.display = 'none';
            document.getElementById('appointment_type_select').style.display = 'none';
            document.getElementById('status_select').style.display = 'none';
            document.getElementById('gender_select').style.display = 'none';
            document.getElementById('state_select').style.display = 'none';

            // Show the relevant select based on recipient type
            switch(recipientType) {
                case 'Department':
                    document.getElementById('department_select').style.display = 'block';
                    break;
                case 'GradeLevel':
                    document.getElementById('grade_level_select').style.display = 'block';
                    break;
                case 'Cadre':
                    document.getElementById('cadre_select').style.display = 'block';
                    break;
                case 'AppointmentType':
                    document.getElementById('appointment_type_select').style.display = 'block';
                    break;
                case 'Status':
                    document.getElementById('status_select').style.display = 'block';
                    break;
                case 'Gender':
                    document.getElementById('gender_select').style.display = 'block';
                    break;
                case 'State':
                    document.getElementById('state_select').style.display = 'block';
                    break;
            }
        }

        // Add event listener to the recipient type select
        recipientTypeSelect.addEventListener('change', toggleFilterOptions);

        // Initialize the form on page load
        toggleFilterOptions();
    });
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Rowwww\Herd\hrm_payroll_system_design\resources\views/sms/create.blade.php ENDPATH**/ ?>