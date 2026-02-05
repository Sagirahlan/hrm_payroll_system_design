<?php $__env->startSection('content'); ?>
<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Salary Scales Management</h5>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create_salary_scales')): ?>
            <a href="<?php echo e(route('salary-scales.create')); ?>" class="btn btn-light btn-sm">
                <i class="fas fa-plus me-1"></i> Add New Salary Scale
            </a>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <!-- Search and Filter Form -->
            <form method="GET" action="<?php echo e(route('salary-scales.index')); ?>" class="mb-4">
                <div class="row g-3 align-items-end">
                    <div class="col-md-6">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" name="search" id="search" class="form-control" placeholder="Search by acronym or full name..." value="<?php echo e(request('search')); ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="sort_by" class="form-label">Sort By</label>
                        <select name="sort_by" id="sort_by" class="form-select">
                            <option value="acronym" <?php echo e(request('sort_by') == 'acronym' ? 'selected' : ''); ?>>Acronym</option>
                            <option value="full_name" <?php echo e(request('sort_by') == 'full_name' ? 'selected' : ''); ?>>Full Name</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="sort_order" class="form-label">Order</label>
                        <select name="sort_order" id="sort_order" class="form-select">
                            <option value="asc" <?php echo e(request('sort_order') == 'asc' ? 'selected' : ''); ?>>Ascending</option>
                            <option value="desc" <?php echo e(request('sort_order') == 'desc' ? 'selected' : ''); ?>>Descending</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Apply Filters</button>
                        <a href="<?php echo e(route('salary-scales.index')); ?>" class="btn btn-secondary">Clear Filters</a>
                    </div>
                </div>
            </form>

            <?php if(session('success')): ?>
                <div class="alert alert-success"><?php echo e(session('success')); ?></div>
            <?php endif; ?>

            <?php if(session('error')): ?>
                <div class="alert alert-danger"><?php echo e(session('error')); ?></div>
            <?php endif; ?>

            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Acronym</th>
                        <th>Full Name</th>
                        <th>Sector Coverage</th>
                        <th>Max Retirement Age</th>
                        <th>Max Years of Service</th>
                        <th>Grade Levels</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $salaryScales; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $scale): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($scale->acronym); ?></td>
                            <td><?php echo e($scale->full_name); ?></td>
                            <td><?php echo e($scale->sector_coverage); ?></td>
                            <td><?php echo e($scale->max_retirement_age); ?></td>
                            <td><?php echo e($scale->max_years_of_service); ?></td>
                            <td><?php echo e($scale->gradeLevels->count()); ?></td>
                            
                            <td>
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view_grade_levels')): ?>
                                <a href="<?php echo e(route('salary-scales.grade-levels', $scale->id)); ?>" class="btn btn-sm btn-info">View Grade Levels</a>
                                <?php endif; ?>
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('edit_salary_scales')): ?>
                                <a href="<?php echo e(route('salary-scales.edit', $scale->id)); ?>" class="btn btn-sm btn-primary">Edit</a>
                                <form action="<?php echo e(route('salary-scales.destroy', $scale->id)); ?>" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this salary scale?');">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7" class="text-center">No salary scales found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <div class="d-flex justify-content-center">
                <?php echo e($salaryScales->links('pagination::bootstrap-5')); ?>

            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Rowwww\Herd\hrm_payroll_system_design\resources\views/salary-scales/index.blade.php ENDPATH**/ ?>