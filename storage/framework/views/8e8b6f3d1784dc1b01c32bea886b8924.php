<?php $__env->startSection('content'); ?>
<div class="container">
    <h2>Create New Role</h2>

    <form action="<?php echo e(route('roles.store')); ?>" method="POST">
        <?php echo csrf_field(); ?>

        <div class="mb-3">
            <label for="name" class="form-label">Role Name</label>
            <input type="text" name="name" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Description (optional)</label>
            <textarea name="description" class="form-control"></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Permissions</label>
            <div class="mb-3">
                <input type="text" id="permission-search" class="form-control" placeholder="Search permissions...">
            </div>
            <div class="border rounded p-3" style="max-height: 400px; overflow-y: auto;">
                <div class="row" id="permissions-container">
                    <?php $__currentLoopData = $permissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="col-md-6 col-lg-4 mb-2 permission-item" data-permission="<?php echo e(strtolower($name)); ?>">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="permissions[]" value="<?php echo e($id); ?>" id="permission_<?php echo e($id); ?>">
                                <label class="form-check-label" for="permission_<?php echo e($id); ?>">
                                    <?php echo e($name); ?>

                                </label>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Save Role</button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('permission-search');
    const permissionItems = document.querySelectorAll('.permission-item');

    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase().trim();

        permissionItems.forEach(function(item) {
            const permissionName = item.getAttribute('data-permission');
            if (permissionName.includes(searchTerm)) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    });
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Rowwww\Herd\hrm_payroll_system_design\resources\views/roles/create.blade.php ENDPATH**/ ?>