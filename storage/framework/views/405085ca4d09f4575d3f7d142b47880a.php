<?php $__env->startSection('title', 'Promotions & Demotions'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Promotions & Demotions</h3>
                    <div>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create_promotions')): ?>
                        <a href="<?php echo e(route('promotions.increments.index')); ?>" class="btn btn-info btn-sm me-2">
                            <i class="fas fa-level-up-alt"></i> Step Increment
                        </a>
                        <a href="<?php echo e(route('promotions.create')); ?>" class="btn btn-primary">
                            <i class="fas fa-plus"></i> New Promotion/Demotion
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Search and Filter Form -->
                    <form method="GET" action="<?php echo e(route('promotions.index')); ?>" class="mb-4">
                        <div class="row">
                            <div class="col-md-2">
                                <input type="text" name="search" class="form-control" placeholder="Search by name, ID, or staff no..." value="<?php echo e(request('search')); ?>">
                            </div>
                            <div class="col-md-2">
                                <select name="type" class="form-control">
                                    <option value="">All Types</option>
                                    <option value="promotion" <?php echo e(request('type') == 'promotion' ? 'selected' : ''); ?>>Promotion</option>
                                    <option value="demotion" <?php echo e(request('type') == 'demotion' ? 'selected' : ''); ?>>Demotion</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="status" class="form-control">
                                    <option value="">All Statuses</option>
                                    <option value="pending" <?php echo e(request('status') == 'pending' ? 'selected' : ''); ?>>Pending</option>
                                    <option value="approved" <?php echo e(request('status') == 'approved' ? 'selected' : ''); ?>>Approved</option>
                                    <option value="rejected" <?php echo e(request('status') == 'rejected' ? 'selected' : ''); ?>>Rejected</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="employee_id" class="form-control select2">
                                    <option value="">All Employees</option>
                                    <?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($employee->employee_id); ?>" <?php echo e(request('employee_id') == $employee->employee_id ? 'selected' : ''); ?>>
                                            <?php echo e(trim($employee->first_name . ' ' . $employee->middle_name . ' ' . $employee->surname)); ?> (<?php echo e($employee->staff_no); ?>)
                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="date" name="promotion_date" class="form-control" placeholder="Promotion Date" value="<?php echo e(request('promotion_date')); ?>">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">Filter</button>
                            </div>
                        </div>
                    </form>

                    <!-- Promotions Table -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Type</th>
                                    <th>Previous Grade</th>
                                    <th>New Grade</th>
                                    <th>Promotion Date</th>
                                    <th>Effective Date</th>
                                    <th>Status</th>
                                    <th>Created By</th>
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view_promotions')): ?>
                                    <th>Actions</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $promotions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $promotion): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td>
                                            <?php echo e(trim($promotion->employee->first_name . ' ' . $promotion->employee->middle_name . ' ' . $promotion->employee->surname) ?? 'N/A'); ?><br>
                                            <small class="text-muted"><?php echo e($promotion->employee->staff_no ?? 'N/A'); ?></small>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?php echo e($promotion->promotion_type === 'promotion' ? 'success' : 'warning'); ?> text-black">
                                                <?php echo e(ucfirst($promotion->promotion_type)); ?>

                                            </span>
                                        </td>
                                        <td><?php echo e($promotion->previous_grade_level); ?></td>
                                        <td><?php echo e($promotion->new_grade_level); ?></td>
                                        <td><?php echo e(\Carbon\Carbon::parse($promotion->promotion_date)->format('Y-m-d')); ?></td>
                                        <td><?php echo e(\Carbon\Carbon::parse($promotion->effective_date)->format('Y-m-d')); ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo e($promotion->status === 'approved' ? 'success' : ($promotion->status === 'rejected' ? 'danger' : 'warning')); ?> text-black">
                                                <?php echo e(ucfirst($promotion->status)); ?>

                                            </span>
                                        </td>
                                        <td><?php echo e($promotion->creator->name ?? 'System'); ?></td>
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view_promotions')): ?>
                                        <td>
                                            <a href="<?php echo e(route('promotions.show', $promotion->id)); ?>" class="btn btn-sm btn-info">View</a>
                                        </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="9" class="text-center">No promotions or demotions found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center">
                        <?php echo e($promotions->appends(request()->query())->links()); ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
$(document).ready(function() {
    $('.select2').select2({
        placeholder: 'Select an employee',
        allowClear: true
    });
});
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Rowwww\Herd\hrm_payroll_system_design\resources\views/promotions/index.blade.php ENDPATH**/ ?>