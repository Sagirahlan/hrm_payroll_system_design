<?php $__env->startSection('title', 'Update Employee Bank Details - ' . $employee->first_name . ' ' . $employee->surname); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <a href="<?php echo e(route('bank-details.index')); ?>" class="btn btn-outline-primary">
                <i class="fa fa-chevron-left"></i> Back to List
            </a>
        </div>
    </div>
    
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Update Bank Details for <?php echo e($employee->first_name); ?> <?php echo e($employee->surname); ?></h4>
                    <p class="card-category">Staff No: <?php echo e($employee->staff_no); ?></p>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Current Bank Details</h5>
                            <div class="border p-3 rounded mb-4">
                                <?php if($currentBankDetails): ?>
                                    <p><strong>Bank Name:</strong> <?php echo e($currentBankDetails->bank_name); ?></p>
                                    <p><strong>Account Number:</strong> <?php echo e($currentBankDetails->account_no); ?></p>
                                    <p><strong>Account Name:</strong> <?php echo e($currentBankDetails->account_name); ?></p>
                                    <p><strong>Bank Code:</strong> <?php echo e($currentBankDetails->bank_code); ?></p>
                                <?php else: ?>
                                    <p>No bank details found for this employee.</p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <h5>Employee Information</h5>
                            <div class="border p-3 rounded mb-4">
                                <p><strong>Name:</strong> <?php echo e($employee->first_name); ?> <?php echo e($employee->middle_name); ?> <?php echo e($employee->surname); ?></p>
                                <p><strong>Staff Number:</strong> <?php echo e($employee->staff_no); ?></p>
                                <p><strong>Department:</strong> <?php echo e($employee->department->department_name ?? 'N/A'); ?></p>
                                <p><strong>Status:</strong> <span class="badge bg-<?php echo e($employee->status == 'Active' ? 'success' : 'warning'); ?>"><?php echo e($employee->status); ?></span></p>
                            </div>
                        </div>
                    </div>

                    <form action="<?php echo e(route('bank-details.update', $employee->employee_id)); ?>" method="POST">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PUT'); ?>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="bank_name" class="form-label">Bank Name *</label>
                                    <select name="bank_name" id="bank_name" class="form-control <?php $__errorArgs = ['bank_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required>
                                        <option value="">Select Bank</option>
                                        <?php $__currentLoopData = $banks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bank): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($bank->bank_name); ?>"
                                                    data-code="<?php echo e($bank->bank_code); ?>"
                                                    <?php echo e((old('bank_name', $currentBankDetails->bank_name ?? '') == $bank->bank_name) ? 'selected' : ''); ?>>
                                                <?php echo e($bank->bank_name); ?>

                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                    <?php $__errorArgs = ['bank_name'];
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
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="bank_code" class="form-label">Bank Code *</label>
                                    <input type="text" name="bank_code" id="bank_code" class="form-control <?php $__errorArgs = ['bank_code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                           value="<?php echo e(old('bank_code', $currentBankDetails->bank_code ?? '')); ?>"
                                           required readonly>
                                    <?php $__errorArgs = ['bank_code'];
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
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="account_name" class="form-label">Account Name *</label>
                                    <input type="text" name="account_name" id="account_name" class="form-control <?php $__errorArgs = ['account_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                           value="<?php echo e(old('account_name', $currentBankDetails->account_name ?? '')); ?>" required>
                                    <?php $__errorArgs = ['account_name'];
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
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="account_no" class="form-label">Account Number *</label>
                                    <input type="text" name="account_no" id="account_no" class="form-control <?php $__errorArgs = ['account_no'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                           value="<?php echo e(old('account_no', $currentBankDetails->account_no ?? '')); ?>" required>
                                    <?php $__errorArgs = ['account_no'];
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
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Bank Details
                            </button>
                            <a href="<?php echo e(route('bank-details.index')); ?>" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to List
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const bankNameSelect = document.getElementById('bank_name');
    const bankCodeInput = document.getElementById('bank_code');

    if (bankNameSelect && bankCodeInput) {
        // Set the bank code field to readonly initially
        bankCodeInput.readOnly = true;

        // Set initial value if a bank is already selected
        if (bankNameSelect.value) {
            const selectedOption = bankNameSelect.options[bankNameSelect.selectedIndex];
            if (selectedOption) {
                const bankCode = selectedOption.getAttribute('data-code');
                bankCodeInput.value = bankCode || '';
            }
        }

        // Add event listener for bank name change
        bankNameSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption) {
                const bankCode = selectedOption.getAttribute('data-code');
                bankCodeInput.value = bankCode || '';
            }
        });
    }
});
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Rowwww\Herd\hrm_payroll_system_design\resources\views/bank-details/show.blade.php ENDPATH**/ ?>