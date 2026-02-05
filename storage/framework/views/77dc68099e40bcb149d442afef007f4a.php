<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create_biometrics')): ?>
    <div class="mb-3">
        <a href="<?php echo e(route('biometrics.index')); ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Biometrics
        </a>
    </div>
    
    <div class="card border-primary shadow">
        <div class="card-header" style="background-color: skyblue; color: white;">
            <h5 class="mb-0">Add Biometric Data</h5>
        </div>
        <div class="card-body">
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <div class="card border-primary shadow">
                        <div class="card-header" style="background-color: skyblue; color: white;">
                            <strong>Biometric Entry Form</strong>
                        </div>
                        <div class="card-body">
                            <form action="<?php echo e(route('biometrics.store')); ?>" method="POST">
                                <?php echo csrf_field(); ?>
                                <div class="mb-3">
                                    <label for="employee_id" class="form-label">Employee</label>
                                    <select name="employee_id" class="form-control" required <?php echo e(request('employee_id') ? 'readonly' : ''); ?>>
                                        <?php if(request('employee_id')): ?>
                                            <?php
                                                $selectedEmployee = $employees->firstWhere('employee_id', request('employee_id'));
                                            ?>
                                            <?php if($selectedEmployee): ?>
                                                <option value="<?php echo e($selectedEmployee->employee_id); ?>" selected><?php echo e($selectedEmployee->first_name); ?> <?php echo e($selectedEmployee->middle_name); ?> <?php echo e($selectedEmployee->surname); ?></option>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <option value="">Select an Employee</option>
                                            <?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($employee->employee_id); ?>"><?php echo e($employee->first_name); ?> <?php echo e($employee->middle_name); ?> <?php echo e($employee->surname); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        <?php endif; ?>
                                    </select>
                                    <?php $__errorArgs = ['employee_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>

                                
                                <textarea id="fingerprintData" name="fingerprint_data" hidden></textarea>

                                <div class="mb-3">
                                    <label class="form-label">Fingerprint Capture</label>
                                    <div class="border rounded p-3 bg-light">
                                        <p id="scannerStatus" class="text-muted mb-2">Waiting for fingerprint scan...</p>
                                        <button type="button" class="btn btn-outline-primary" onclick="captureFingerprint()">Capture Fingerprint</button>
                                    </div>
                                    <?php $__errorArgs = ['fingerprint_data'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>

                                <button type="submit" class="btn btn-primary">Save Biometric Data</button>
                                <a href="<?php echo e(route('biometrics.index')); ?>" class="btn btn-secondary">Cancel</a>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="alert alert-warning">
        You don't have permission to create biometric data.
    </div>
    <?php endif; ?>
</div>


<script>
    function captureFingerprint() {
        // Simulate captured fingerprint template (this should come from real scanner in production)
        const fakeFingerprintTemplate = "FAKE_TEMPLATE_<?php echo e(uniqid()); ?>"; // replace with real data from scanner

        document.getElementById('fingerprintData').value = fakeFingerprintTemplate;
        document.getElementById('scannerStatus').innerText = "Fingerprint captured successfully!";
        document.getElementById('scannerStatus').classList.remove('text-muted');
        document.getElementById('scannerStatus').classList.add('text-success');
    }
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Rowwww\Herd\hrm_payroll_system_design\resources\views/biometrics/create.blade.php ENDPATH**/ ?>