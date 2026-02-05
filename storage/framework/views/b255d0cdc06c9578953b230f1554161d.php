<?php $__env->startSection('title', 'Disciplinary Action Details'); ?>

<?php $__env->startSection('content'); ?>
<div class="container py-4">
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view_disciplinary')): ?>
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card border-primary shadow">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Disciplinary Action Details</h5>
                        <a href="<?php echo e(route('disciplinary.index')); ?>" class="btn btn-light">
                            <i class="fas fa-arrow-left me-1"></i> Back to List
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Main Disciplinary Action Details -->
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0">Action Details</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th>Employee:</th>
                                            <td><?php echo e($action->employee ? $action->employee->first_name . ' ' . $action->employee->surname : 'N/A'); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Employee ID:</th>
                                            <td><?php echo e($action->employee ? $action->employee->employee_id : 'N/A'); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Action Type:</th>
                                            <td><?php echo e($action->action_type); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Description:</th>
                                            <td><?php echo e($action->description ?? 'N/A'); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Action Date:</th>
                                            <td><?php echo e($action->action_date ? \Carbon\Carbon::parse($action->action_date)->format('M d, Y') : 'N/A'); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Resolution Date:</th>
                                            <td><?php echo e($action->resolution_date ? \Carbon\Carbon::parse($action->resolution_date)->format('M d, Y') : 'N/A'); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Status:</th>
                                            <td>
                                                <span class="badge bg-<?php echo e($action->status === 'Open' ? 'warning' : ($action->status === 'Resolved' ? 'success' : 'secondary')); ?>">
                                                    <?php echo e($action->status); ?>

                                                </span>
                                            </td>
                                        </tr>
                                    </table>
                                    
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['edit_disciplinary', 'delete_disciplinary'])): ?>
                                    <div class="d-flex gap-2 mt-3">
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('edit_disciplinary')): ?>
                                        <a href="<?php echo e(route('disciplinary.edit', $action->action_id)); ?>" class="btn btn-warning">
                                            <i class="fas fa-edit me-1"></i> Edit
                                        </a>
                                        <?php endif; ?>
                                        
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete_disciplinary')): ?>
                                        <form action="<?php echo e(route('disciplinary.destroy', $action->action_id)); ?>" method="POST" onsubmit="return confirm('Are you sure you want to delete this disciplinary action?')">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="btn btn-danger">
                                                <i class="fas fa-trash me-1"></i> Delete
                                            </button>
                                        </form>
                                        <?php endif; ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Employee Information -->
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0">Employee Information</h6>
                                </div>
                                <div class="card-body">
                                    <?php if($action->employee): ?>
                                        <table class="table table-borderless">
                                            <tr>
                                                <th>Full Name:</th>
                                                <td><?php echo e($action->employee->first_name); ?> <?php echo e($action->employee->middle_name); ?> <?php echo e($action->employee->surname); ?></td>
                                            </tr>
                                            <tr>
                                                <th>Department:</th>
                                                <td><?php echo e($action->employee->department ? $action->employee->department->department_name : 'N/A'); ?></td>
                                            </tr>
                                            <tr>
                                                <th>Grade Level:</th>
                                                <td><?php echo e($action->employee->gradeLevel ? $action->employee->gradeLevel->name : 'N/A'); ?></td>
                                            </tr>
                                            <tr>
                                                <th>Step:</th>
                                                <td><?php echo e($action->employee->step ? $action->employee->step->name : 'N/A'); ?></td>
                                            </tr>
                                            <tr>
                                                <th>Status:</th>
                                                <td>
                                                    <span class="badge bg-<?php echo e($action->employee->status === 'Active' ? 'success' : ($action->employee->status === 'Suspended' ? 'warning' : 'secondary')); ?>">
                                                        <?php echo e($action->employee->status); ?>

                                                    </span>
                                                </td>
                                            </tr>
                                        </table>
                                    <?php else: ?>
                                        <p class="text-muted">Employee information not available.</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Disciplinary History Section -->
                    <div class="card">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0">Disciplinary History for <?php echo e($action->employee ? $action->employee->first_name . ' ' . $action->employee->surname : 'N/A'); ?></h6>
                        </div>
                        <div class="card-body">
                            <?php if($disciplinaryHistory->isEmpty()): ?>
                                <p class="text-center">No other disciplinary actions found for this employee.</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-striped align-middle">
                                        <thead class="table-primary">
                                            <tr>
                                                <th>Action Type</th>
                                                <th>Description</th>
                                                <th>Action Date</th>
                                                <th>Resolution Date</th>
                                                <th>Status</th>
                                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view_disciplinary')): ?>
                                                <th>Actions</th>
                                                <?php endif; ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $__currentLoopData = $disciplinaryHistory; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $history): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <tr>
                                                    <td><?php echo e($history->action_type); ?></td>
                                                    <td><?php echo e($history->description ?? 'N/A'); ?></td>
                                                    <td><?php echo e($history->action_date ? \Carbon\Carbon::parse($history->action_date)->format('M d, Y') : 'N/A'); ?></td>
                                                    <td><?php echo e($history->resolution_date ? \Carbon\Carbon::parse($history->resolution_date)->format('M d, Y') : 'N/A'); ?></td>
                                                    <td>
                                                        <span class="badge bg-<?php echo e($history->status === 'Open' ? 'warning' : ($history->status === 'Resolved' ? 'success' : 'secondary')); ?>">
                                                            <?php echo e($history->status); ?>

                                                        </span>
                                                    </td>
                                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view_disciplinary')): ?>
                                                    <td>
                                                        <a href="<?php echo e(route('disciplinary.show', $history->action_id)); ?>" class="btn btn-sm btn-primary">
                                                            <i class="fas fa-eye me-1"></i> View
                                                        </a>
                                                    </td>
                                                    <?php endif; ?>
                                                </tr>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="alert alert-warning">
        You don't have permission to view disciplinary actions.
    </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Rowwww\Herd\hrm_payroll_system_design\resources\views/disciplinary/show.blade.php ENDPATH**/ ?>