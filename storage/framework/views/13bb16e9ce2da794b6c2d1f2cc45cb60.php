<?php $__env->startSection('title', 'Manage Employee Bank Details'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Manage Employee Bank Details</h4>
                    <p class="card-category">Update employee bank details</p>
                </div>
                <div class="card-body">
                    <div class="mb-4 d-flex justify-content-between align-items-center">
                        <form action="<?php echo e(route('bank-details.search')); ?>" method="POST" class="d-flex flex-grow-1 me-3">
                            <?php echo csrf_field(); ?>
                            <input type="text" name="query" class="form-control me-2" placeholder="Search employees by name, ID, or staff number..." value="<?php echo e(request('query', '')); ?>">
                            <button class="btn btn-outline-primary" type="submit">Search</button>
                            <a href="<?php echo e(route('bank-details.index')); ?>" class="btn btn-outline-secondary ms-2">Reset</a>
                        </form>
                        
                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#importBankDetailsModal">
                            <i class="fas fa-file-import"></i> Import Update
                        </button>
                    </div>

                    <!-- Import Modal -->
                    <div class="modal fade" id="importBankDetailsModal" tabindex="-1" aria-labelledby="importBankDetailsModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="importBankDetailsModalLabel">Import Bank Details Update</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form action="<?php echo e(route('bank-details.import')); ?>" method="POST" enctype="multipart/form-data">
                                    <?php echo csrf_field(); ?>
                                    <div class="modal-body">
                                        <div class="alert alert-info">
                                            <small>
                                                Upload an Excel file with the following columns: <br>
                                                <strong>employee_id (optional, prioritized), staff_no, bank_name, account_no, account_name</strong>.<br>
                                                This will update bank details for matching Employees. 
                                                If <strong>employee_id</strong> is provided, it will be used. Otherwise, <strong>staff_no</strong> will be used.
                                            </small>
                                        </div>
                                        <div class="mb-3">
                                            <label for="import_file" class="form-label">Choose Excel File</label>
                                            <input type="file" class="form-control" id="import_file" name="import_file" required accept=".xlsx, .xls, .csv">
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-primary">Import & Update</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead>
                                <tr>
                                    <th>Staff No</th>
                                    <th>Full Name</th>
                                    <th>Department</th>
                                    <th>Bank Name</th>
                                    <th>Bank Code</th>
                                    <th>Account Number</th>
                                    <th>Account Name</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><?php echo e($employee->staff_no); ?></td>
                                        <td><?php echo e($employee->first_name); ?> <?php echo e($employee->middle_name); ?> <?php echo e($employee->surname); ?></td>
                                        <td><?php echo e($employee->department->department_name ?? 'N/A'); ?></td>
                                        <td><?php echo e($employee->bank->bank_name ?? 'Not Set'); ?></td>
                                        <td><?php echo e($employee->bank->bank_code ?? 'Not Set'); ?></td>
                                        <td><?php echo e($employee->bank->account_no ?? 'Not Set'); ?></td>
                                        <td><?php echo e($employee->bank->account_name ?? 'Not Set'); ?></td>
                                        <td>
                                            <a href="<?php echo e(route('bank-details.show', $employee->employee_id)); ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i> Update
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="8" class="text-center">No employees found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-end mt-3">
                        <?php echo e($employees->withQueryString()->links('pagination::bootstrap-5')); ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Rowwww\Herd\hrm_payroll_system_design\resources\views/bank-details/index.blade.php ENDPATH**/ ?>