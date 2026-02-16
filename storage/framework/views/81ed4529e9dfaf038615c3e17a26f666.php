<?php $__env->startSection('title', 'Add Pensioner'); ?>

<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Add Pensioner</h4>
                    <a href="<?php echo e(route('pensioners.index')); ?>" class="btn btn-secondary float-end">Cancel</a>
                </div>
                <div class="card-body">
                    <?php if($retirements->count() > 0): ?>
                        <div class="alert alert-info">
                            Found <?php echo e($retirements->count()); ?> retired employees without pensioner records. 
                            <a href="#" onclick="moveAllRetiredToPensioners(); return false;" class="btn btn-sm btn-success">Process All</a>
                        </div>
                        
                        <form action="<?php echo e(route('pensioners.store')); ?>" method="POST">
                            <?php echo csrf_field(); ?>
                            
                            <div class="mb-3">
                                <label for="retirement_id" class="form-label">Retired Employee</label>
                                <select class="form-control" id="retirement_id" name="retirement_id" required>
                                    <option value="">Select Retired Employee</option>
                                    <?php $__currentLoopData = $retirements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $retirement): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($retirement->id); ?>">
                                            <?php echo e($retirement->employee->full_name); ?> (<?php echo e($retirement->employee->employee_id); ?>) - <?php echo e($retirement->retirement_date); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="pension_amount" class="form-label">Pension Amount</label>
                                        <input type="number" step="0.01" class="form-control" id="pension_amount" name="pension_amount" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="gratuity_amount" class="form-label">Gratuity Amount</label>
                                        <input type="number" step="0.01" class="form-control" id="gratuity_amount" name="gratuity_amount" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="bank_id" class="form-label">Bank</label>
                                        <select class="form-control" id="bank_id" name="bank_id">
                                            <option value="">Select Bank</option>
                                            <?php
                                                $banks = DB::table('banks')->get(['bank_id as id', 'bank_name as name']);
                                            ?>
                                            <?php $__currentLoopData = $banks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bank): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($bank->id); ?>"><?php echo e($bank->name); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="status" class="form-label">Status</label>
                                        <select class="form-control" id="status" name="status">
                                            <option value="Active">Active</option>
                                            <option value="Terminated">Terminated</option>
                                            <option value="Deceased">Deceased</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="account_number" class="form-label">Account Number</label>
                                        <input type="text" class="form-control" id="account_number" name="account_number">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="account_name" class="form-label">Account Name</label>
                                        <input type="text" class="form-control" id="account_name" name="account_name">
                                    </div>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Add Pensioner</button>
                            <a href="<?php echo e(route('pensioners.index')); ?>" class="btn btn-secondary">Cancel</a>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-info">
                            No retired employees found without pensioner records. 
                            All retired employees have been moved to the pensioners table.
                        </div>
                        <a href="<?php echo e(route('pensioners.index')); ?>" class="btn btn-primary">View Pensioners</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function moveAllRetiredToPensioners() {
    if (confirm('Are you sure you want to process all retired employees to pensioners? This will move all eligible retired employees to the pensioners table.')) {
        fetch('<?php echo e(route("pensioners.move-retired")); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while processing retired employees.');
        });
    }
}
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Rowwww\Herd\hrm_payroll_system_design\resources\views/pensioners/create.blade.php ENDPATH**/ ?>