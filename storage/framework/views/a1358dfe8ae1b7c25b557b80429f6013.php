<?php $__env->startSection('content'); ?>
<div class="container">
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage_sms')): ?>
    <h1>SMS Notifications</h1>
   
        <a href="<?php echo e(route('sms.create')); ?>" class="btn btn-primary mb-3">Send SMS</a>
    

    <?php if(session('success')): ?>
        <div class="alert alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>Sent By</th>
                <th>Recipient Type</th>
                <th>Message</th>
                <th>Status</th>
                <th>Sent At</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $smsNotifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sms): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($sms->user->username); ?></td>
                    <td><?php echo e($sms->recipient_type); ?></td>
                    <td><?php echo e($sms->message); ?></td>
                    <td><?php echo e($sms->status); ?></td>
                    <td><?php echo e($sms->sent_at ?? 'N/A'); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
    <?php echo e($smsNotifications->links()); ?>

    <?php else: ?>
    <div class="alert alert-warning">
        You don't have permission to manage SMS notifications.
    </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Rowwww\Herd\hrm_payroll_system_design\resources\views/sms/index.blade.php ENDPATH**/ ?>