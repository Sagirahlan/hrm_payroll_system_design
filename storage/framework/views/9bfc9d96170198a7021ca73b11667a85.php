<?php $__env->startSection('title', 'Leave Management'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title">Leave Management</h4>
                    <a href="<?php echo e(route('leaves.create')); ?>" class="btn btn-primary">Request Leave</a>
                </div>
                <div class="card-body">
                    <?php if(session('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo e(session('success')); ?>

                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Search and Filter Form -->
                    <div class="mb-4 p-3 bg-light rounded">
                        <form method="GET" action="<?php echo e(route('leaves.index')); ?>" class="row g-3">
                            <div class="col-md-3">
                                <label for="search" class="form-label">Search</label>
                                <input type="text" class="form-control" id="search" name="search" value="<?php echo e(request('search')); ?>" placeholder="Name or staff no...">
                            </div>
                            <div class="col-md-2">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="">All Statuses</option>
                                    <option value="pending" <?php echo e(request('status') == 'pending' ? 'selected' : ''); ?>>Pending</option>
                                    <option value="approved" <?php echo e(request('status') == 'approved' ? 'selected' : ''); ?>>Approved</option>
                                    <option value="rejected" <?php echo e(request('status') == 'rejected' ? 'selected' : ''); ?>>Rejected</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="leave_type" class="form-label">Leave Type</label>
                                <input type="text" class="form-control" id="leave_type" name="leave_type" value="<?php echo e(request('leave_type')); ?>" placeholder="e.g., Annual">
                            </div>
                            <div class="col-md-2">
                                <label for="date_from" class="form-label">From Date</label>
                                <input type="date" class="form-control" id="date_from" name="date_from" value="<?php echo e(request('date_from')); ?>">
                            </div>
                            <div class="col-md-2">
                                <label for="date_to" class="form-label">To Date</label>
                                <input type="date" class="form-control" id="date_to" name="date_to" value="<?php echo e(request('date_to')); ?>">
                            </div>
                            <div class="col-md-1 d-flex align-items-end">
                                <div>
                                    <button type="submit" class="btn btn-primary w-100 mb-1">Filter</button>
                                    <a href="<?php echo e(route('leaves.index')); ?>" class="btn btn-secondary w-100">Clear</a>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Employee</th>
                                    <th>Leave Type</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Days</th>
                                    <th>Reason</th>
                                    <th>Status</th>
                                    <th>Requested On</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $leaves; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $leave): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td><?php echo e($loop->iteration); ?></td>
                                    <td>
                                        <?php if($leave->employee): ?>
                                            <div><?php echo e($leave->employee->first_name); ?> <?php echo e($leave->employee->surname); ?></div>
                                            <small class="text-muted"><?php echo e($leave->employee->staff_no ?? 'N/A'); ?></small>
                                        <?php else: ?>
                                            <div class="text-danger">Employee Record Not Found</div>
                                            <small class="text-muted">ID: <?php echo e($leave->employee_id); ?> (Deleted)</small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo e($leave->leave_type); ?></td>
                                    <td><?php echo e(\Carbon\Carbon::parse($leave->start_date)->format('d M Y')); ?></td>
                                    <td><?php echo e(\Carbon\Carbon::parse($leave->end_date)->format('d M Y')); ?></td>
                                    <td><?php echo e($leave->days_requested); ?></td>
                                    <td><?php echo e(Str::limit($leave->reason, 50)); ?></td>
                                    <td>
                                        <?php if($leave->status === 'pending'): ?>
                                            <span class="badge bg-warning">Pending</span>
                                        <?php elseif($leave->status === 'approved'): ?>
                                            <span class="badge bg-success">Approved</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Rejected</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo e(\Carbon\Carbon::parse($leave->created_at)->format('d M Y')); ?></td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="<?php echo e(route('leaves.show', $leave->id)); ?>" class="btn btn-sm btn-info">View</a>
                                            
                                            <?php if($leave->status === 'pending'): ?>
                                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage_leaves')): ?>
                                                    <a href="<?php echo e(route('leaves.edit', $leave->id)); ?>" class="btn btn-sm btn-warning">Edit</a>
                                                <?php endif; ?>
                                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('approve_leaves')): ?>
                                                    <form action="<?php echo e(route('leaves.approve', $leave->id)); ?>" method="POST" class="d-inline">
                                                        <?php echo csrf_field(); ?>
                                                        <?php echo method_field('POST'); ?>
                                                        <input type="hidden" name="status" value="approved">
                                                        <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Are you sure you want to approve this leave?')">Approve</button>
                                                    </form>
                                                    <form action="<?php echo e(route('leaves.approve', $leave->id)); ?>" method="POST" class="d-inline">
                                                        <?php echo csrf_field(); ?>
                                                        <?php echo method_field('POST'); ?>
                                                        <input type="hidden" name="status" value="rejected">
                                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to reject this leave?')">Reject</button>
                                                    </form>
                                                <?php endif; ?>
                                            <?php endif; ?>

                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage_leaves')): ?>
                                                <form action="<?php echo e(route('leaves.destroy', $leave->id)); ?>" method="POST" class="d-inline">
                                                    <?php echo csrf_field(); ?>
                                                    <?php echo method_field('DELETE'); ?>
                                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this leave request?')">Delete</button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="10" class="text-center">No leave requests found.</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-3">
                        <?php echo e($leaves->links()); ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Rowwww\Herd\hrm_payroll_system_design\resources\views/leaves/index.blade.php ENDPATH**/ ?>