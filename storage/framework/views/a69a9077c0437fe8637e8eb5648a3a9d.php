<?php $__env->startSection('content'); ?>
<div class="container">
    <h1>Addition Types</h1>
     <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create_addition_types')): ?>
    <a href="<?php echo e(route('addition-types.create')); ?>" class="btn btn-primary">Create Addition Type</a>
    <?php endif; ?>
    <table class="table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Code</th>
                <th>Statutory</th>
                <th>Calculation Type</th>
                <th>Rate/Amount</th>
                 <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('edit_addition_types')): ?>
                <th>Actions</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $additionTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $additionType): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($additionType->name); ?></td>
                    <td><?php echo e($additionType->code); ?></td>
                    <td><?php echo e($additionType->is_statutory ? 'Yes' : 'No'); ?></td>
                    <td><?php echo e($additionType->calculation_type); ?></td>
                    <td><?php echo e($additionType->rate_or_amount); ?></td>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('edit_addition_types')): ?>
                    <td>
                        <a href="<?php echo e(route('addition-types.edit', $additionType)); ?>" class="btn btn-sm btn-primary">Edit</a>
                        <form action="<?php echo e(route('addition-types.destroy', $additionType)); ?>" method="POST" style="display: inline-block;">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Rowwww\Herd\hrm_payroll_system_design\resources\views/addition-types/index.blade.php ENDPATH**/ ?>