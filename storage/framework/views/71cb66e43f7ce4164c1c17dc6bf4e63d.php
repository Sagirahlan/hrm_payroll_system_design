<?php $__env->startSection('title', 'Pending Pensioner Changes'); ?>

<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Pending Pensioner Changes</h4>
                    
                    <!-- Filters -->
                    <div class="row mt-3">
                        <div class="col-md-4">
                            <select class="form-control" onchange="window.location='?status='+this.value+'&change_type='+getParam('change_type')+'&search='+getParam('search')">
                                <option value="">All Status</option>
                                <option value="pending" <?php echo e(request('status') == 'pending' ? 'selected' : ''); ?>>Pending</option>
                                <option value="approved" <?php echo e(request('status') == 'approved' ? 'selected' : ''); ?>>Approved</option>
                                <option value="rejected" <?php echo e(request('status') == 'rejected' ? 'selected' : ''); ?>>Rejected</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <select class="form-control" onchange="window.location='?status='+getParam('status')+'&change_type='+this.value+'&search='+getParam('search')">
                                <option value="">All Types</option>
                                <option value="create" <?php echo e(request('change_type') == 'create' ? 'selected' : ''); ?>>Create</option>
                                <option value="update" <?php echo e(request('change_type') == 'update' ? 'selected' : ''); ?>>Update</option>
                                <option value="delete" <?php echo e(request('change_type') == 'delete' ? 'selected' : ''); ?>>Delete</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <form method="GET" class="d-flex">
                                <input type="text" class="form-control me-2" name="search" placeholder="Search pensioners..." value="<?php echo e(request('search')); ?>">
                                <button class="btn btn-outline-primary" type="submit">Search</button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <?php if(session('success')): ?>
                        <div class="alert alert-success">
                            <?php echo e(session('success')); ?>

                        </div>
                    <?php endif; ?>

                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Pensioner</th>
                                    <th>Change Type</th>
                                    <th>Requested By</th>
                                    <th>Changes</th>
                                    <th>Status</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $pendingChanges; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $change): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><?php echo e($change->id); ?></td>
                                        <td><?php echo e($change->pensioner_name); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo e($change->change_type == 'update' ? 'warning' : ($change->change_type == 'create' ? 'success' : 'danger')); ?> text-white">
                                                <?php echo e(ucfirst($change->change_type)); ?>

                                            </span>
                                        </td>
                                        <td><?php echo e($change->requestedBy->username ?? 'N/A'); ?></td>
                                        <td><?php echo e($change->change_description); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo e($change->status == 'pending' ? 'secondary' : ($change->status == 'approved' ? 'success' : 'danger')); ?> text-white">
                                                <?php echo e(ucfirst($change->status)); ?>

                                            </span>
                                        </td>
                                        <td><?php echo e($change->created_at->format('d M Y H:i')); ?></td>
                                        <td>
                                            <a href="<?php echo e(route('pending-pensioner-changes.show', $change->id)); ?>" class="btn btn-info btn-sm">View</a>
                                            
                                            <?php if($change->status === 'pending'): ?>
                                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('approve_pensioner_changes')): ?>
                                                    <form action="<?php echo e(route('pending-pensioner-changes.approve', $change->id)); ?>" method="POST" class="d-inline">
                                                        <?php echo csrf_field(); ?>
                                                        <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Are you sure you want to approve this change?')">Approve</button>
                                                    </form>
                                                    <form action="<?php echo e(route('pending-pensioner-changes.reject', $change->id)); ?>" method="POST" class="d-inline">
                                                        <?php echo csrf_field(); ?>
                                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to reject this change?')">Reject</button>
                                                    </form>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="8" class="text-center">No pending changes found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php echo e($pendingChanges->links()); ?>

                </div>
            </div>
        </div>
    </div>
</div>

<script>
function getParam(param) {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get(param) || '';
}
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Rowwww\Herd\hrm_payroll_system_design\resources\views/pending-pensioner-changes/index.blade.php ENDPATH**/ ?>