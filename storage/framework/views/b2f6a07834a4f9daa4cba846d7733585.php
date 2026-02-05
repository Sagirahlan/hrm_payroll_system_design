<?php $__env->startSection('content'); ?>
<div class="container-fluid mt-4">
    <!-- Single Main Card Container -->
    <div class="card shadow-lg">
        <!-- Card Header with Title and Navigation -->
        <div class="card-header bg-gradient-primary text-white">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h3 class="mb-1"><i class="fas fa-layer-group me-2"></i>Grade Levels Management</h3>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-dark mb-0">
                            <li class="breadcrumb-item">
                                <a href="<?php echo e(route('salary-scales.index')); ?>" class="text-white-50">Salary Scales</a>
                            </li>
                            <li class="breadcrumb-item active text-white" aria-current="page">
                                <?php echo e($salaryScale->acronym); ?> - <?php echo e($salaryScale->full_name); ?>

                            </li>
                        </ol>
                    </nav>
                </div>
                <div class="col-md-4 text-end">
                    <a href="<?php echo e(route('salary-scales.index')); ?>" class="btn btn-light me-2">
                        <i class="fas fa-arrow-left me-1"></i>Back
                    </a>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create_grade_levels')): ?>
                    <a href="<?php echo e(route('salary-scales.grade-levels.create', $salaryScale->id)); ?>" class="btn btn-success">
                        <i class="fas fa-plus me-1"></i>Add Grade Level
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <!-- Summary Statistics Section -->
            <div class="bg-light border-bottom p-4">
            <h5 class="mb-3"><i class="fas fa-chart-bar me-2"></i>Summary Overview</h5>
            <div class="row g-3">
                <div class="col-xl-3 col-md-6">
                <div class="card border-0 bg-primary h-100">
                    <div class="card-body text-center">
                    <i class="fas fa-tag fa-2x mb-2"></i>
                    <h6 class="card-title text-dark">Salary Scale</h6>
                    <h4 class="mb-0 text-dark"><?php echo e($salaryScale->acronym); ?></h4>
                    </div>
                </div>
                </div>
                <div class="col-xl-3 col-md-6">
                <div class="card border-0 bg-info h-100">
                    <div class="card-body text-center">
                    <i class="fas fa-file-alt fa-2x mb-2"></i>
                    <h6 class="card-title text-dark">Full Name</h6>
                    <p class="mb-0 small text-dark"><?php echo e(Str::limit($salaryScale->full_name, 25)); ?></p>
                    </div>
                </div>
                </div>
                <div class="col-xl-3 col-md-6">
                <div class="card border-0 bg-success h-100">
                    <div class="card-body text-center">
                    <i class="fas fa-layer-group fa-2x mb-2"></i>
                    <h6 class="card-title text-dark">Total Levels</h6>
                    <h4 class="mb-0 text-dark"><?php echo e($gradeLevels->total()); ?></h4>
                    </div>
                </div>
                </div>
                <div class="col-xl-3 col-md-6">
                <div class="card border-0 bg-warning h-100">
                    <div class="card-body text-center">
                    <i class="fas fa-building fa-2x mb-2"></i>
                    <h6 class="card-title text-dark">Sector</h6>
                    <p class="mb-0 small text-dark"><?php echo e($salaryScale->sector_coverage); ?></p>
                    </div>
                </div>
                </div>
            </div>
            </div>

            <!-- Alerts Section -->
            <div class="p-4 pb-0">
                <?php if(session('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i><?php echo e(session('success')); ?>

                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if(session('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i><?php echo e(session('error')); ?>

                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Filter Section -->
            <div class="p-4">
                <div class="card bg-light border-0">
                    <div class="card-body">
                        <h6 class="card-title mb-3">
                            <i class="fas fa-filter me-2"></i>Search & Filter
                        </h6>
                        <form action="<?php echo e(route('salary-scales.grade-levels', $salaryScale->id)); ?>" method="GET">
                            <div class="row g-3">
                                <div class="col-lg-5 col-md-6">
                                    <label for="search" class="form-label fw-semibold">Search</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-search"></i>
                                        </span>
                                        <input type="text" name="search" id="search" class="form-control" 
                                               placeholder="Search name or description..." 
                                               value="<?php echo e(request('search')); ?>">
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-6">
                                    <label for="filter_grade_level" class="form-label fw-semibold">Grade Level</label>
                                    <select name="filter_grade_level" id="filter_grade_level" class="form-select">
                                        <option value="">All Grade Levels</option>
                                        <?php $__currentLoopData = $distinctGradeLevels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $grade): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($grade->grade_level); ?>" 
                                                    <?php echo e(request('filter_grade_level') == $grade->grade_level ? 'selected' : ''); ?>>
                                                Grade <?php echo e($grade->grade_level); ?>

                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                                <div class="col-lg-3 d-flex align-items-end">
                                    <div class="btn-group w-100" role="group">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-search me-1"></i>Apply
                                        </button>
                                        <a href="<?php echo e(route('salary-scales.grade-levels', $salaryScale->id)); ?>" 
                                           class="btn btn-outline-secondary" title="Clear filters">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Grade Levels Content -->
            <div class="p-4 pt-0">
                <?php if($gradeLevels->count() > 0): ?>
                    <!-- Results Header -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">
                            <i class="fas fa-list me-2"></i>Grade Levels 
                            <span class="badge bg-secondary ms-2"><?php echo e($gradeLevels->total()); ?></span>
                        </h5>
                        <div class="text-muted small">
                            Showing <?php echo e($gradeLevels->firstItem()); ?> to <?php echo e($gradeLevels->lastItem()); ?> of <?php echo e($gradeLevels->total()); ?> results
                        </div>
                    </div>

                    <!-- Grade Levels Table -->
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th width="30%">
                                        <i class="fas fa-tag me-1"></i>Name
                                    </th>
                                    <th width="15%" class="text-center">
                                        <i class="fas fa-layer-group me-1"></i>Grade
                                    </th>
                                    <th width="30%">
                                        <i class="fas fa-file-text me-1"></i>Description
                                    </th>
                                    
                                    <th width="10%" class="text-center">
                                        <i class="fas fa-cog me-1"></i>Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $gradeLevels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $level): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr class="border-bottom">
                                        <td>
                                            <div class="fw-bold text-dark"><?php echo e($level->name); ?></div>
                                            <div class="small text-muted">ID: <?php echo e($level->id); ?></div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-primary fs-6"><?php echo e($level->grade_level); ?></span>
                                        </td>
                                        <td>
                                            <span class="text-muted"><?php echo e(Str::limit($level->description ?? 'No description provided', 35)); ?></span>
                                        </td>
                                       
                                        <td class="text-center">
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="actionDropdown<?php echo e($level->id); ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu" aria-labelledby="actionDropdown<?php echo e($level->id); ?>">
                                                    <li>
                                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('edit_grade_levels')): ?>
                                                        <a class="dropdown-item" href="<?php echo e(route('salary-scales.grade-levels.edit', [$salaryScale->id, $level->id])); ?>">
                                                            <i class="fas fa-edit text-primary me-2"></i>Edit
                                                        </a>
                                                        <?php endif; ?>
                                                    </li>
                                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view_grade_levels')): ?>
                                                    <li>
                                                        <button class="dropdown-item" type="button" 
                                                                data-bs-toggle="modal" data-bs-target="#stepsModal<?php echo e($level->id); ?>">
                                                            <i class="fas fa-list text-info me-2"></i>View Steps
                                                        </button>
                                                    </li>
                                                    <?php endif; ?>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete_grade_levels')): ?>
                                                    <li>
                                                        <form action="<?php echo e(route('salary-scales.grade-levels.destroy', [$salaryScale->id, $level->id])); ?>" 
                                                              method="POST" 
                                                              onsubmit="return confirm('Are you sure you want to delete this grade level? This action cannot be undone.');">
                                                            <?php echo csrf_field(); ?>
                                                            <?php echo method_field('DELETE'); ?>
                                                            <button type="submit" class="dropdown-item text-danger">
                                                                <i class="fas fa-trash me-2"></i>Delete
                                                            </button>
                                                        </form>
                                                    </li>
                                                    <?php endif; ?>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-4">
                        <?php echo e($gradeLevels->links('pagination::bootstrap-5')); ?>

                    </div>
                <?php else: ?>
                    <!-- Empty State -->
                    <div class="text-center py-5">
                        <div class="mb-4">
                            <i class="fas fa-folder-open fa-4x text-muted"></i>
                        </div>
                        <h4 class="text-muted mb-3">No Grade Levels Found</h4>
                        <?php if(request()->hasAny(['search', 'filter_grade_level'])): ?>
                            <p class="text-muted mb-4">No grade levels match your current search criteria. Try adjusting your filters.</p>
                            <a href="<?php echo e(route('salary-scales.grade-levels', $salaryScale->id)); ?>" class="btn btn-outline-secondary me-3">
                                <i class="fas fa-times me-1"></i>Clear Filters
                            </a>
                        <?php else: ?>
                            <p class="text-muted mb-4">Get started by adding the first grade level for this salary scale.</p>
                        <?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create_grade_levels')): ?>
                        <a href="<?php echo e(route('salary-scales.grade-levels.create', $salaryScale->id)); ?>" class="btn btn-success btn-lg">
                            <i class="fas fa-plus me-2"></i>Add New Grade Level
                        </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Steps Modals -->
<?php if($gradeLevels->count() > 0): ?>
    <?php $__currentLoopData = $gradeLevels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $level): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="modal fade" id="stepsModal<?php echo e($level->id); ?>" tabindex="-1" 
             aria-labelledby="stepsModalLabel<?php echo e($level->id); ?>" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title" id="stepsModalLabel<?php echo e($level->id); ?>">
                            <i class="fas fa-list me-2"></i>Steps for <?php echo e($level->name); ?> (Grade <?php echo e($level->grade_level); ?>)
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <?php if($level->steps->count() > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th width="15%">#</th>
                                            <th width="50%">Step Name</th>
                                            <th width="35%" class="text-end">Basic Salary</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $__currentLoopData = $level->steps->sortBy('name'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $step): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <tr>
                                                <td>
                                                    <span class="badge bg-secondary"><?php echo e($index + 1); ?></span>
                                                </td>
                                                <td>
                                                    <span class="fw-semibold"><?php echo e($step->name); ?></span>
                                                </td>
                                                <td class="text-end">
                                                    <span class="fw-bold text-success fs-6">₦<?php echo e(number_format($step->basic_salary, 2)); ?></span>
                                                </td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-3 p-3 bg-light rounded">
                                <div class="row text-center">
                                    <div class="col-md-6">
                                        <strong>Total Steps:</strong> 
                                        <span class="badge bg-primary"><?php echo e($level->steps->count()); ?></span>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Salary Range:</strong> 
                                        <span class="text-success">
                                            ₦<?php echo e(number_format($level->steps->min('basic_salary'), 2)); ?> - 
                                            ₦<?php echo e(number_format($level->steps->max('basic_salary'), 2)); ?>

                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info border-0">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-info-circle fa-2x me-3"></i>
                                    <div>
                                        <h6 class="mb-1">No Steps Defined</h6>
                                        <p class="mb-0">This grade level doesn't have any salary steps configured yet.</p>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php endif; ?>

<?php $__env->startPush('styles'); ?>
<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #007bff, #0056b3);
}

.breadcrumb-dark .breadcrumb-item a {
    color: rgba(255, 255, 255, 0.7);
    text-decoration: none;
}

.breadcrumb-dark .breadcrumb-item a:hover {
    color: rgba(255, 255, 255, 0.9);
}

.card-hover:hover {
    transform: translateY(-2px);
    transition: all 0.3s ease;
}

.table-hover tbody tr:hover {
    background-color: rgba(0, 123, 255, 0.05);
}

.btn-group-sm .btn {
    transition: all 0.2s ease;
}

.btn-group-sm .btn:hover {
    transform: scale(1.05);
}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            if (alert.classList.contains('show')) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }
        }, 5000);
    });

    // Add loading state to filter form
    const filterForm = document.querySelector('form');
    if (filterForm) {
        filterForm.addEventListener('submit', function() {
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Loading...';
            }
        });
    }

    // Confirm delete with more details
    const deleteForms = document.querySelectorAll('form[onsubmit*="confirm"]');
    deleteForms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const gradeLevelName = this.closest('tr').querySelector('.fw-bold').textContent;
            if (confirm(`Are you sure you want to delete "${gradeLevelName}"?\n\nThis action cannot be undone and will remove all associated data.`)) {
                this.submit();
            }
        });
        // Remove the inline onsubmit to avoid double confirmation
        form.removeAttribute('onsubmit');
    });
});
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Rowwww\Herd\hrm_payroll_system_design\resources\views/salary-scales/grade-levels.blade.php ENDPATH**/ ?>