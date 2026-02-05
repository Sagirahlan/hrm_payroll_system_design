<?php $__env->startSection('title', 'Edit Disciplinary Action'); ?>

<?php $__env->startSection('content'); ?>
<div class="container">
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('edit_disciplinary')): ?>
    <h1>Edit Disciplinary Action</h1>
    <div class="card">
        <div class="card-body">
            <form action="<?php echo e(route('disciplinary.update', $action->action_id)); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>
                <div class="mb-3">
                    <label for="employee_id" class="form-label">Employee</label>
                    <select class="form-select <?php $__errorArgs = ['employee_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="employee_id" name="employee_id" required>
                        <option value="">Select Employee</option>
                        <?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($employee->employee_id); ?>" <?php echo e(old('employee_id', $action->employee_id) == $employee->employee_id ? 'selected' : ''); ?> <?php echo e(old('employee_id', $action->employee_id) == $employee->employee_id ? 'readonly disabled' : 'disabled'); ?>>
                                <?php echo e($employee->first_name); ?> <?php echo e($employee->surname); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <?php $__errorArgs = ['employee_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div class="mb-3">
                    <label for="action_type" class="form-label">Action Type</label>
                    <input type="text" class="form-control" value="<?php echo e(ucfirst($action->action_type)); ?>" readonly>
                    <input type="hidden" name="action_type" value="<?php echo e($action->action_type); ?>">
                    <?php $__errorArgs = ['action_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="description" name="description" rows="4"><?php echo e(old('description', $action->description)); ?></textarea>
                    <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div class="mb-3">
                    <label for="action_date" class="form-label">Action Date</label>
                    <input type="text" class="form-control" value="<?php echo e(\Carbon\Carbon::parse($action->action_date)->format('Y-m-d')); ?>" readonly>
                    <input type="hidden" name="action_date" value="<?php echo e($action->action_date); ?>">
                    <?php $__errorArgs = ['action_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div class="mb-3">
                    <label for="resolution_date" class="form-label">Resolution Date</label>
                    <input type="date" class="form-control <?php $__errorArgs = ['resolution_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="resolution_date" name="resolution_date" value="<?php echo e(old('resolution_date', $action->resolution_date)); ?>">
                    <?php $__errorArgs = ['resolution_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div class="mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select <?php $__errorArgs = ['status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="status" name="status" required>
                        <option value="Resolved" <?php echo e(old('status', $action->status) == 'Resolved' ? 'selected' : ''); ?>>Resolved</option>
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                const statusSelect = document.getElementById('status');
                                const actionTypeInput = document.getElementById('action_type');
                                statusSelect.addEventListener('change', function() {
                                    const selectedOption = statusSelect.options[statusSelect.selectedIndex];
                                    if (selectedOption.value === 'Resolved') {
                                        // Update action_type to 'active'
                                        if(actionTypeInput) actionTypeInput.value = 'active';
                                    }
                                });
                            });
                        </script>
                    </select>
                    <?php $__errorArgs = ['status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <button type="submit" class="btn btn-primary">Update</button>
                <a href="<?php echo e(route('disciplinary.index')); ?>" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
    <?php else: ?>
    <div class="alert alert-warning">
        You don't have permission to edit disciplinary actions.
    </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Rowwww\Herd\hrm_payroll_system_design\resources\views/disciplinary/edit.blade.php ENDPATH**/ ?>