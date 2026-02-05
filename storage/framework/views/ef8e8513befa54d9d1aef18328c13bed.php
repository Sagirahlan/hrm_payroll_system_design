<?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <tr>
        <td class="text-center"><input type="checkbox" class="employee-checkbox" name="employee_ids[]" value="<?php echo e($employee->employee_id); ?>" form="bulk-assignment-form"></td>
        <td><?php echo e($employee->staff_no); ?></td>
        <td>
            <?php echo e($employee->first_name); ?> <?php echo e($employee->surname); ?>

            <?php if($employee->status === 'Retired'): ?>
                <span class="badge bg-warning text-dark ms-2">Retiring this Month</span>
            <?php endif; ?>
        </td>
        <td><?php echo e($employee->department->department_name ?? 'N/A'); ?></td>
        <td><?php echo e($employee->gradeLevel->name ?? 'N/A'); ?></td>
        <td><?php echo e($employee->appointmentType->name ?? 'N/A'); ?></td>
    </tr>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php /**PATH C:\Users\Rowwww\Herd\hrm_payroll_system_design\resources\views/payroll/_employee_rows.blade.php ENDPATH**/ ?>