<?php $__env->startSection('content'); ?>
<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">Edit Grade Level</h5>
                <small><?php echo e($salaryScale->acronym); ?> - <?php echo e($salaryScale->full_name); ?></small>
            </div>
            <a href="<?php echo e(route('salary-scales.grade-levels', $salaryScale->id)); ?>" class="btn btn-light btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Back to Grade Levels
            </a>
        </div>
        <div class="card-body">
            <form action="<?php echo e(route('salary-scales.grade-levels.update', [$salaryScale->id, $gradeLevel->id])); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">Grade Level Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control" value="<?php echo e(old('name', $gradeLevel->name)); ?>" required maxlength="50">
                        <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="text-danger small mt-1"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="grade_level" class="form-label">Grade Level <span class="text-danger">*</span></label>
                        <input type="text" name="grade_level" id="grade_level" class="form-control" value="<?php echo e(old('grade_level', $gradeLevel->grade_level)); ?>" required>
                        <?php $__errorArgs = ['grade_level'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="text-danger small mt-1"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea name="description" id="description" class="form-control" rows="3"><?php echo e(old('description', $gradeLevel->description)); ?></textarea>
                    <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="text-danger small mt-1"><?php echo e($message); ?></div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                
                <div class="d-flex justify-content-between">
                    <a href="<?php echo e(route('salary-scales.grade-levels', $salaryScale->id)); ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Grade Levels
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Update Grade Level
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Steps Management Section -->
    <div class="card shadow mt-4">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0">Steps for <?php echo e($gradeLevel->name); ?></h5>
        </div>
        <div class="card-body">
            <?php if(session('step_success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo e(session('step_success')); ?>

                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <?php if(session('step_error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo e(session('step_error')); ?>

                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0">Step Management</h6>
                <a href="<?php echo e(route('salary-scales.grade-levels.steps.create', [$salaryScale->id, $gradeLevel->id])); ?>" class="btn btn-info">
                    <i class="fas fa-plus me-1"></i> Add New Step
                </a>
            </div>
            
            <?php if($gradeLevel->steps->count() > 0): ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Step Name</th>
                                <th>Basic Salary</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $gradeLevel->steps->sortBy('name'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $step): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td><?php echo e($step->name); ?></td>
                                    <td>₦<?php echo e(number_format($step->basic_salary, 2)); ?></td>
                                    <td><?php echo e($step->created_at->format('M d, Y')); ?></td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                Actions
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <a class="dropdown-item" href="<?php echo e(route('salary-scales.grade-levels.steps.edit', [$salaryScale->id, $gradeLevel->id, $step->id])); ?>">
                                                        <i class="fas fa-edit me-2"></i> Edit
                                                    </a>
                                                </li>
                                                <li>
                                                    <form action="<?php echo e(route('salary-scales.grade-levels.steps.destroy', [$salaryScale->id, $gradeLevel->id, $step->id])); ?>" method="POST" onsubmit="return confirm('Are you sure you want to delete this step?');">
                                                        <?php echo csrf_field(); ?>
                                                        <?php echo method_field('DELETE'); ?>
                                                        <button type="submit" class="dropdown-item text-danger">
                                                            <i class="fas fa-trash me-2"></i> Delete
                                                        </button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>No steps have been defined for this grade level yet.
                    <a href="<?php echo e(route('salary-scales.grade-levels.steps.create', [$salaryScale->id, $gradeLevel->id])); ?>" class="alert-link ms-2">Add your first step.</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Rowwww\Herd\hrm_payroll_system_design\resources\views/salary-scales/grade-levels/edit.blade.php ENDPATH**/ ?>