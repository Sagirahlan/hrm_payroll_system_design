<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <!-- Pending Employee Data Changes Table -->
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Pending Employee Data Changes</h6>
                    </div>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    <!-- Search and Filter Form -->
                    <div class="px-4 py-3 bg-light">
                        <form method="GET" action="<?php echo e(route('pending-changes.index')); ?>" class="row g-3">
                            <div class="col-md-4">
                                <label for="search" class="form-label">Search</label>
                                <input type="text" class="form-control" id="search" name="search" value="<?php echo e(request('search')); ?>" placeholder="Search by employee name, ID, or staff no">
                            </div>
                            <div class="col-md-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="">All Statuses</option>
                                    <option value="pending" <?php echo e(request('status') == 'pending' ? 'selected' : ''); ?>>Pending</option>
                                    <option value="approved" <?php echo e(request('status') == 'approved' ? 'selected' : ''); ?>>Approved</option>
                                    <option value="rejected" <?php echo e(request('status') == 'rejected' ? 'selected' : ''); ?>>Rejected</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="change_type" class="form-label">Change Type</label>
                                <select class="form-select" id="change_type" name="change_type">
                                    <option value="">All Types</option>
                                    <option value="create" <?php echo e(request('change_type') == 'create' ? 'selected' : ''); ?>>Create</option>
                                    <option value="update" <?php echo e(request('change_type') == 'update' ? 'selected' : ''); ?>>Update</option>
                                    <option value="delete" <?php echo e(request('change_type') == 'delete' ? 'selected' : ''); ?>>Delete</option>
                                </select>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <div>
                                    <button type="submit" class="btn btn-primary me-2">Filter</button>
                                    <a href="<?php echo e(route('pending-changes.index')); ?>" class="btn btn-secondary">Clear</a>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-black">Employee</th>
                                    <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-black">Change Type</th>
                                    <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-black">Description</th>
                                    <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-black">Requested By</th>
                                    <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-black">Requested At</th>
                                    <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-black">Status</th>
                                    <th class="text-secondary opacity-7 text-black"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $pendingChanges; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $change): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td>
                                        <div class="d-flex px-2 py-1">
                                            <div class="d-flex flex-column justify-content-center">
                                                <h6 class="mb-0 text-sm text-black">
                                                    <?php echo e($change->employee_name); ?>

                                                </h6>
                                                <?php if($change->employee): ?>
                                                    <p class="text-xs text-secondary mb-0 text-black">
                                                        <?php echo e($change->employee->staff_no); ?>

                                                    </p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-sm bg-gradient-<?php echo e($change->change_type == 'create' ? 'success' : ($change->change_type == 'update' ? 'warning' : 'danger')); ?> text-black">
                                            <?php echo e(ucfirst($change->change_type)); ?>

                                        </span>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0 text-black"><?php echo e($change->change_description); ?></p>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0 text-black"><?php echo e($change->requestedBy->username); ?></p>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0 text-black"><?php echo e($change->created_at->format('M d, Y H:i')); ?></p>
                                    </td>
                                    <td>
                                        <span class="badge badge-sm bg-gradient-<?php echo e($change->status == 'pending' ? 'secondary' : ($change->status == 'approved' ? 'success' : 'danger')); ?> text-black">
                                            <?php echo e(ucfirst($change->status)); ?>

                                        </span>
                                    </td>
                                    <td class="align-middle">
                                        <a href="<?php echo e(route('pending-changes.show', $change)); ?>" class="text-secondary font-weight-bold text-xs text-black" data-toggle="tooltip" data-original-title="View">
                                            View
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="7" class="text-center">
                                        <p class="text-sm text-muted text-black">No pending employee data changes found.</p>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="d-flex justify-content-center mt-4">
                        <?php echo e($pendingChanges->links()); ?>

                    </div>
                </div>
            </div>

            <!-- Pending Promotions/Demotions Table -->
            <?php if($pendingPromotions && $pendingPromotions->count() > 0): ?>
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Pending Promotions/Demotions</h6>
                    </div>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-black">Employee</th>
                                    <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-black">Change Type</th>
                                    <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-black">Details</th>
                                    <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-black">Requested By</th>
                                    <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-black">Requested At</th>
                                    <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-black">Status</th>
                                    <th class="text-secondary opacity-7 text-black">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $pendingPromotions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $promotion): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td>
                                        <div class="d-flex px-2 py-1">
                                            <div class="d-flex flex-column justify-content-center">
                                                <h6 class="mb-0 text-sm text-black">
                                                    <?php echo e($promotion->employee->first_name); ?> <?php echo e($promotion->employee->surname); ?>

                                                </h6>
                                                <p class="text-xs text-secondary mb-0 text-black">
                                                    <?php echo e($promotion->employee->staff_no); ?>

                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-sm bg-gradient-<?php echo e($promotion->promotion_type == 'promotion' ? 'success' : 'danger'); ?> text-black">
                                            <?php echo e(ucfirst($promotion->promotion_type)); ?>

                                        </span>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0 text-black">
                                            Grade: <?php echo e($promotion->previous_grade_level); ?> → <?php echo e($promotion->new_grade_level); ?>

                                        </p>
                                        <p class="text-xs text-secondary mb-0 text-black">
                                            Reason: <?php echo e($promotion->reason); ?>

                                        </p>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0 text-black">
                                            <?php if($promotion->creator): ?>
                                                <?php echo e($promotion->creator->username); ?>

                                            <?php else: ?>
                                                N/A
                                            <?php endif; ?>
                                        </p>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0 text-black"><?php echo e($promotion->created_at->format('M d, Y H:i')); ?></p>
                                    </td>
                                    <td>
                                        <span class="badge badge-sm bg-gradient-secondary text-black">
                                            <?php echo e(ucfirst($promotion->status)); ?>

                                        </span>
                                    </td>
                                    <td class="align-middle">
                                        <a href="<?php echo e(route('promotions.show', $promotion->id)); ?>" class="text-secondary font-weight-bold text-xs text-black me-2" data-toggle="tooltip" data-original-title="View">
                                            View
                                        </a>
                                        <div class="btn-group btn-group-xs">
                                            <form action="<?php echo e(route('promotions.approve', $promotion)); ?>" method="POST" class="d-inline">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('POST'); ?>
                                                <button type="submit" class="btn btn-xs btn-success" onclick="return confirm('Are you sure you want to approve this promotion/demotion?')">Approve</button>
                                            </form>
                                            <form action="<?php echo e(route('promotions.reject', $promotion)); ?>" method="POST" class="d-inline ms-1">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('POST'); ?>
                                                <button type="submit" class="btn btn-xs btn-danger" onclick="return confirm('Are you sure you want to reject this promotion/demotion?')">Reject</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Rowwww\Herd\hrm_payroll_system_design\resources\views/pending-changes/index.blade.php ENDPATH**/ ?>