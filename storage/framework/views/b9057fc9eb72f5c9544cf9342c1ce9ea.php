<?php $__env->startSection('title', 'Probation Management'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
<div class="mb-3">
    <a href="<?php echo e(route('employees.index')); ?>" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Employees
    </a>
</div>

            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Employees on Probation</h4>
                </div>
                <div class="card-body">
                    <!-- Filters -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <input type="text" class="form-control" id="search" name="search" placeholder="Search employees..." value="<?php echo e(request('search')); ?>">
                        </div>
                        <div class="col-md-3">
                            <select class="form-control" name="department" id="department">
                                <option value="">All Departments</option>
                                <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($dept->department_id); ?>" <?php echo e(request('department') == $dept->department_id ? 'selected' : ''); ?>>
                                        <?php echo e($dept->department_name); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-control" name="probation_status" id="probation_status">
                                <option value="">All Probation Status</option>
                                <option value="pending" <?php echo e(request('probation_status') == 'pending' ? 'selected' : ''); ?>>Pending</option>
                                <option value="approved" <?php echo e(request('probation_status') == 'approved' ? 'selected' : ''); ?>>Approved</option>
                                <option value="rejected" <?php echo e(request('probation_status') == 'rejected' ? 'selected' : ''); ?>>Rejected</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-primary" id="filterBtn">Filter</button>
                            <a href="<?php echo e(route('probation.index')); ?>" class="btn btn-secondary">Clear</a>
                        </div>
                    </div>

                    <!-- Employee Table -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Staff No.</th>
                                    <th>Name</th>
                                    <th>Department</th>
                                    <th>Probation Start</th>
                                    <th>Probation End</th>
                                    <th>Days Remaining</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="employeeTableBody">
                                <?php $__empty_1 = true; $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td><?php echo e($employee->staff_no); ?></td>
                                    <td>
                                        <a href="<?php echo e(route('probation.show', $employee)); ?>">
                                            <?php echo e($employee->first_name); ?> <?php echo e($employee->middle_name); ?> <?php echo e($employee->surname); ?>

                                        </a>
                                    </td>
                                    <td><?php echo e($employee->department->department_name ?? 'N/A'); ?></td>
                                    <td><?php echo e($employee->probation_start_date ? \Carbon\Carbon::parse($employee->probation_start_date)->format('d/m/Y') : 'N/A'); ?></td>
                                    <td><?php echo e($employee->probation_end_date ? \Carbon\Carbon::parse($employee->probation_end_date)->format('d/m/Y') : 'N/A'); ?></td>
                                    <td>
                                        <?php if($employee->on_probation): ?>
                                            <?php if($employee->hasProbationPeriodEnded()): ?>
                                                <span class="text-danger">Probation Ended</span>
                                            <?php else: ?>
                                                <?php echo e($employee->getRemainingProbationDays()); ?> days
                                            <?php endif; ?>
                                        <?php else: ?>
                                            N/A
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($employee->probation_status == 'pending'): ?>
                                            <span class="badge bg-warning">On Probation</span>
                                        <?php elseif($employee->probation_status == 'approved'): ?>
                                            <span class="badge bg-success">Approved</span>
                                        <?php elseif($employee->probation_status == 'rejected'): ?>
                                            <span class="badge bg-danger">Rejected</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($employee->probation_status == 'pending'): ?>
                                            <?php if($employee->canBeEvaluatedForProbation()): ?>
                                                <form method="POST" action="<?php echo e(route('probation.approve', $employee)); ?>" style="display:inline;" onsubmit="return confirm('Are you sure you want to approve this employee\'s probation?')">
                                                    <?php echo csrf_field(); ?>
                                                    <?php echo method_field('POST'); ?>
                                                    <button type="submit" class="btn btn-success btn-sm">Approve</button>
                                                </form>
                                            <?php else: ?>
                                                <button class="btn btn-sm btn-secondary" disabled title="Wait <?php echo e($employee->getRemainingProbationDays()); ?> days for approval">
                                                    Approve (Wait <?php echo e($employee->getRemainingProbationDays()); ?> days)
                                                </button>
                                            <?php endif; ?>

                                            
                                            <button type="button" class="btn btn-danger btn-sm" onclick="openRejectModal('<?php echo e(route('probation.reject', $employee)); ?>', '<?php echo e($employee->first_name); ?> <?php echo e($employee->surname); ?>')">
                                                Reject
                                            </button>
                                        <?php else: ?>
                                            <span class="text-muted">Action Complete</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="8" class="text-center">No employees on probation found</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center">
                        <?php echo e($employees->links('pagination::bootstrap-5')); ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Rejection Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="rejectForm" method="POST" action="">
                <?php echo csrf_field(); ?>
                <?php echo method_field('POST'); ?>
                <div class="modal-header">
                    <h5 class="modal-title" id="rejectModalLabel">Reject Probation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to reject probation for <strong id="rejectEmployeeName"></strong>?</p>
                    <p class="text-danger">This action will terminate the employee.</p>
                    
                    <div class="mb-3">
                        <label for="probation_notes" class="form-label">Reason for Rejection <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="probation_notes" name="probation_notes" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject Probation</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openRejectModal(url, employeeName) {
    document.getElementById('rejectForm').action = url;
    document.getElementById('rejectEmployeeName').textContent = employeeName;
    var myModal = new bootstrap.Modal(document.getElementById('rejectModal'));
    myModal.show();
}

document.getElementById('filterBtn').addEventListener('click', function() {
    const search = document.getElementById('search').value;
    const department = document.getElementById('department').value;
    const probationStatus = document.getElementById('probation_status').value;
    
    let url = '<?php echo e(route("probation.index")); ?>';
    const params = [];
    
    if (search) params.push('search=' + encodeURIComponent(search));
    if (department) params.push('department=' + encodeURIComponent(department));
    if (probationStatus) params.push('probation_status=' + encodeURIComponent(probationStatus));
    
    if (params.length > 0) {
        url += '?' + params.join('&');
    }
    
    window.location.href = url;
});

// Allow pressing Enter in search field to trigger filter
document.getElementById('search').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        document.getElementById('filterBtn').click();
    }
});
</script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Rowwww\Herd\hrm_payroll_system_design\resources\views/probation/index.blade.php ENDPATH**/ ?>