

<?php $__env->startSection('title', 'Import Legacy Pensioners'); ?>

<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Import Legacy Pensioners</h4>
                    <a href="<?php echo e(route('pensioners.index')); ?>" class="btn btn-secondary btn-sm">Back</a>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h5 class="alert-heading"><i class="fas fa-info-circle me-2"></i> Instructions</h5>
                        <p class="mb-0">This feature allows you to bulk import pensioners who are already retired and have pre-computed amounts.</p>
                        <p>The system will:</p>
                        <ul class="mb-2">
                            <li>Find the employee by <strong>Staff No</strong>.</li>
                            <li>Create a <strong>Retirement</strong> record if one doesn't exist.</li>
                            <li>Set the status to <strong>Gratuity Paid</strong>.</li>
                            <li>Update/Create the <strong>Pensioner</strong> record with your provided amounts.</li>
                        </ul>
                        <hr>
                        <p class="mb-0">Please use the template below to ensure your data is formatted correctly.</p>
                    </div>

                    <div class="d-grid gap-2 mb-4">
                        <a href="<?php echo e(route('pensioners.legacy.template')); ?>" class="btn btn-outline-primary">
                            <i class="fas fa-download me-2"></i> Download CSV Template
                        </a>
                    </div>

                    <form action="<?php echo e(route('pensioners.legacy.process')); ?>" method="POST" enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>
                        
                        <div class="mb-3">
                            <label for="file" class="form-label">Upload CSV/Excel File</label>
                            <input type="file" class="form-control <?php $__errorArgs = ['file'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="file" name="file" required accept=".csv, .xlsx, .xls">
                            <?php $__errorArgs = ['file'];
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

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary" onclick="return confirm('Are you sure you want to import this file? This will modify database records.')">
                                <i class="fas fa-file-import me-2"></i> Import Pensioners
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Rowwww\Herd\hrm_payroll_system_design\resources\views/pensioners/import_legacy.blade.php ENDPATH**/ ?>