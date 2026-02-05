<?php $__env->startSection('title', 'Edit Pensioner'); ?>

<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Edit Pensioner - <?php echo e($pensioner->full_name); ?></h4>
                    <a href="<?php echo e(route('pensioners.show', $pensioner->id)); ?>" class="btn btn-secondary float-end">Cancel</a>
                </div>
                <div class="card-body">
                    <form action="<?php echo e(route('pensioners.update', $pensioner->id)); ?>" method="POST">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PUT'); ?>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="pension_amount" class="form-label">Pension Amount</label>
                                    <input type="number" step="0.01" class="form-control" id="pension_amount" name="pension_amount" value="<?php echo e(old('pension_amount', $pensioner->pension_amount)); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="gratuity_amount" class="form-label">Gratuity Amount</label>
                                    <input type="number" step="0.01" class="form-control" id="gratuity_amount" name="gratuity_amount" value="<?php echo e(old('gratuity_amount', $pensioner->gratuity_amount)); ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="bank_id" class="form-label">Bank</label>
                                    <select class="form-control" id="bank_id" name="bank_id">
                                        <option value="">Select Bank</option>
                                        <?php $__currentLoopData = $banks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bank): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($bank->id); ?>" <?php echo e($pensioner->bank_id == $bank->id ? 'selected' : ''); ?>>
                                                <?php echo e($bank->name); ?>

                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="account_number" class="form-label">Account Number</label>
                                    <input type="text" class="form-control" id="account_number" name="account_number" value="<?php echo e(old('account_number', $pensioner->account_number)); ?>">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="account_name" class="form-label">Account Name</label>
                                    <input type="text" class="form-control" id="account_name" name="account_name" value="<?php echo e(old('account_name', $pensioner->account_name)); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-control" id="status" name="status">
                                        <option value="Active" <?php echo e($pensioner->status == 'Active' ? 'selected' : ''); ?>>Active</option>
                                        <option value="Terminated" <?php echo e($pensioner->status == 'Terminated' ? 'selected' : ''); ?>>Terminated</option>
                                        <option value="Deceased" <?php echo e($pensioner->status == 'Deceased' ? 'selected' : ''); ?>>Deceased</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="next_of_kin_name" class="form-label">Next of Kin Name</label>
                            <input type="text" class="form-control" id="next_of_kin_name" name="next_of_kin_name" value="<?php echo e(old('next_of_kin_name', $pensioner->next_of_kin_name)); ?>">
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="next_of_kin_phone" class="form-label">Next of Kin Phone</label>
                                    <input type="text" class="form-control" id="next_of_kin_phone" name="next_of_kin_phone" value="<?php echo e(old('next_of_kin_phone', $pensioner->next_of_kin_phone)); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="next_of_kin_address" class="form-label">Next of Kin Address</label>
                                    <textarea class="form-control" id="next_of_kin_address" name="next_of_kin_address" rows="2"><?php echo e(old('next_of_kin_address', $pensioner->next_of_kin_address)); ?></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="reason" class="form-label">Reason for Update <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="reason" name="reason" required><?php echo e(old('reason')); ?></textarea>
                            <small class="form-text text-muted">Please provide a reason for this update. This will be reviewed before approval.</small>
                        </div>

                        <button type="submit" class="btn btn-primary">Submit Update Request</button>
                        <a href="<?php echo e(route('pensioners.show', $pensioner->id)); ?>" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Rowwww\Herd\hrm_payroll_system_design\resources\views/pensioners/edit.blade.php ENDPATH**/ ?>