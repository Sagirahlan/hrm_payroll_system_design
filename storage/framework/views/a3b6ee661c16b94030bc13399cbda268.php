<?php $__env->startSection('title', 'Audit Trail Logs'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">
    <div class="card border-primary shadow">
        <div class="card-header" style="background-color: skyblue; color: white;">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h5 class="mb-0">Audit Trail Logs</h5>
                </div>
                <div class="col-md-6">
                    <form method="GET" action="<?php echo e(route('audit-trails.index')); ?>" class="w-100">
                        <div class="row g-2 align-items-center">
                            <div class="col-md-2">
                                <input type="text" name="search" class="form-control form-control-sm" placeholder="Search logs..." value="<?php echo e(request('search')); ?>">
                            </div>
                            <div class="col-md-2">
                                <select name="action_filter" class="form-select form-select-sm">
                                    <option value="">All Actions</option>
                                    <?php
                                        // Group actions by type for better organization
                                        $groupedActions = [];
                                        foreach($actions as $action) {
                                            $actionStr = (string)$action; // Ensure it's a string
                                            if (str_contains($actionStr, 'login')) {
                                                $groupedActions['Login/Logout'][] = $actionStr;
                                            } elseif (str_contains($actionStr, 'employee')) {
                                                $groupedActions['Employee'][] = $actionStr;
                                            } elseif (str_contains($actionStr, 'salary_scale')) {
                                                $groupedActions['Salary Scale'][] = $actionStr;
                                            } elseif (str_contains($actionStr, 'user')) {
                                                $groupedActions['User'][] = $actionStr;
                                            } elseif (str_contains($actionStr, 'payroll')) {
                                                $groupedActions['Payroll'][] = $actionStr;
                                            } elseif (str_contains($actionStr, 'addition')) {
                                                $groupedActions['Additions'][] = $actionStr;
                                            } elseif (str_contains($actionStr, 'deduction')) {
                                                $groupedActions['Deductions'][] = $actionStr;
                                            } elseif (str_contains($actionStr, 'promotion')) {
                                                $groupedActions['Promotions'][] = $actionStr;
                                            } elseif (str_contains($actionStr, 'leave')) {
                                                $groupedActions['Leaves'][] = $actionStr;
                                            } else {
                                                $groupedActions['Other'][] = $actionStr;
                                            }
                                        }
                                    ?>
                                    <?php $__currentLoopData = $groupedActions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group => $groupedActionList): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <optgroup label="<?php echo e($group); ?>">
                                            <?php $__currentLoopData = $groupedActionList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $action): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($action); ?>" <?php if(request('action_filter') == $action): ?> selected <?php endif; ?>>
                                                    <?php echo e(ucfirst(str_replace('_', ' ', $action))); ?>

                                                </option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </optgroup>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="text" name="start_date" class="form-control form-control-sm" placeholder="Start Date" onfocus="(this.type='date')" onblur="(this.type='text')" value="<?php echo e(request('start_date')); ?>">
                            </div>
                            <div class="col-md-2">
                                <input type="text" name="end_date" class="form-control form-control-sm" placeholder="End Date" onfocus="(this.type='date')" onblur="(this.type='text')" value="<?php echo e(request('end_date')); ?>">
                            </div>
                            <div class="col-md-2">
                                <select name="user_id" class="form-select form-select-sm">
                                    <option value="">All Users</option>
                                    <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($user->id); ?>" <?php if(request('user_id') == $user->id): ?> selected <?php endif; ?>>
                                            <?php echo e($user->username); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div class="col-md-1">
                                <button type="submit" class="btn btn-primary btn-sm w-100"><i class="fas fa-filter me-1"></i>Filter</button>
                            </div>
                            <div class="col-md-1">
                                <a href="<?php echo e(route('audit-trails.index')); ?>" class="btn btn-outline-secondary btn-sm w-100"><i class="fas fa-sync-alt me-1"></i>Reset</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="card-body">
            <?php if(session('success')): ?>
                <div class="alert alert-success"><?php echo e(session('success')); ?></div>
            <?php elseif(session('error')): ?>
                <div class="alert alert-danger"><?php echo e(session('error')); ?></div>
            <?php endif; ?>

            <div class="table-responsive">
                <table class="table table-striped table-bordered align-items-center mb-0">
                    <thead class="table-primary">
                        <tr>
                            <th>#</th>
                            <th>User</th>
                            <th>Role</th>
                            <th>Action</th>
                            <th>Description</th>
                            <th>Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $auditLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e($index + $auditLogs->firstItem()); ?></td>
                                <td>
                                    <span class="badge bg-primary">
                                        <?php echo e($log->user?->username ?? 'System'); ?>

                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-info text-dark">
                                        <?php echo e($log->user?->roles->first()->name ?? 'N/A'); ?>

                                    </span>
                                </td>
                                <td>
                                    <?php
                                        $actionClass = 'bg-secondary'; // Default
                                        if (in_array($log->action, ['create', 'created_salary_scale', 'created_employee', 'login', 'created_user', 'created_payroll', 'created_addition'])) {
                                            $actionClass = 'bg-success';
                                        } elseif (in_array($log->action, ['update', 'updated_salary_scale', 'updated_employee', 'updated_user', 'updated_payroll'])) {
                                            $actionClass = 'bg-warning text-dark';
                                        } elseif (in_array($log->action, ['delete', 'deleted_salary_scale', 'deleted_employee', 'logout', 'deleted_user', 'deleted_payroll', 'deleted_deduction'])) {
                                            $actionClass = 'bg-danger';
                                        }
                                    ?>
                                    <span class="badge <?php echo e($actionClass); ?>">
                                        <?php echo e(ucfirst(str_replace('_', ' ', $log->action))); ?>

                                    </span>
                                </td>
                                <td>
                                    <span class="text-muted">
                                        <?php echo e($log->description); ?>

                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-dark">
                                        <?php echo e(\Carbon\Carbon::parse($log->action_timestamp)->format('Y-m-d H:i:s')); ?>

                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="6" class="text-center">No audit logs found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-3">
                <?php echo e($auditLogs->appends(request()->query())->links('pagination::bootstrap-5')); ?>

            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Rowwww\Herd\hrm_payroll_system_design\resources\views/audit_trails/index.blade.php ENDPATH**/ ?>