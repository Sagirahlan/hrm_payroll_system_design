<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="card border-0 shadow-lg" style="background: linear-gradient(135deg, #e0f7fa 0%, #ffffff 100%);">
        <!-- Header Section -->
        <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-center gap-3" style="background: #00bcd4;">
            <h4 class="mb-0 text-white font-weight-bold text-center text-md-start">
                <i class="fas fa-users me-2"></i>Employees Management
            </h4>
            <div class="d-flex flex-wrap gap-2 justify-content-center">
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create_employees')): ?>
                <a href="<?php echo e(route('employees.create')); ?>" class="btn btn-light btn-sm rounded-pill me-2 font-weight-bold shadow-sm">
                    <i class="fas fa-plus me-1"></i>Add Employee
                </a>
                <?php endif; ?>
                <a href="<?php echo e(route('probation.index')); ?>" class="btn btn-info btn-sm rounded-pill me-2 font-weight-bold shadow-sm">
                    <i class="fas fa-clock me-1"></i>Probation Employees
                </a>
            </div>
        </div>

        <div class="card-body p-4">
            <!-- Success Message -->
            <?php if(session('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i><?php echo e(session('success')); ?>

                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Import Section -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm" style="background: #f8f9fa;">
                        <div class="card-body">
                            <form action="<?php echo e(route('employees.import')); ?>" method="POST" enctype="multipart/form-data" class="row g-3 align-items-center">
                                <?php echo csrf_field(); ?>
                                <div class="col-12 col-md-auto">
                                    <label for="import_file" class="form-label mb-0 fw-bold">
                                        <i class="fas fa-upload me-2"></i>Import Employees
                                    </label>
                                </div>
                                <div class="col-12 col-md-6">
                                    <input type="file" class="form-control" name="import_file" id="import_file" accept=".xlsx,.xls" required>
                                </div>
                                <div class="col-12 col-md-auto">
                                    <button type="submit" class="btn btn-primary btn-sm rounded-pill fw-bold shadow-sm w-100">
                                        <i class="fas fa-cloud-upload-alt me-1"></i>Import
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Advanced Search and Filter Section -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header d-flex justify-content-between align-items-center" style="background: #f1f8ff;">
                            <h6 class="mb-0 fw-bold text-primary">
                                <div class="d-flex align-items-center">

                                    <span class="me-3"></span>
                                    <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#advancedFilters"
                                        aria-expanded="<?php echo e(request()->hasAny(['department', 'cadre', 'status', 'gender', 'appointment_type_id', 'state_of_origin', 'age_from', 'age_to', 'appointment_from', 'appointment_to']) ? 'true' : 'false'); ?>"
                                        aria-controls="advancedFilters">
                                        <i class="fas fa-filter me-1"></i>Search &amp; Filter Options
                                </div>
                            </h6>
                            <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#advancedFilters">
                                <i class="fas fa-chevron-down"></i>
                            </button>
                        </div>
                        <div class="card-body">
                            <form method="GET" action="<?php echo e(route('employees.index')); ?>" id="filterForm">
                                <!-- Quick Search Row -->
                                <div class="row mb-3">
                                    <div class="col-12 col-md-6 mb-3 mb-md-0">
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                                            <input type="text" name="search" class="form-control"
                                                   placeholder="Search by name, ID, email, phone..."
                                                   value="<?php echo e(request('search')); ?>">
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <div class="d-flex flex-wrap gap-2">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-search me-1"></i>Search
                                            </button>
                                            <a href="<?php echo e(route('employees.index')); ?>" class="btn btn-outline-secondary">
                                                <i class="fas fa-times me-1"></i>Clear
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Advanced Filters (Collapsible) -->
                                <div class="collapse <?php echo e(request()->hasAny(['department', 'cadre', 'status', 'gender', 'appointment_type_id', 'state_of_origin', 'age_from', 'age_to']) ? 'show' : ''); ?>" id="advancedFilters">
                                    <div class="row g-3">
                                        <!-- Row 1 -->
                                        <div class="col-md-3 col-12">
                                            <label class="form-label fw-bold">Department</label>
                                            <select name="department" class="form-select">
                                                <option value="">All Departments</option>
                                                <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <option value="<?php echo e($dept->department_id); ?>"
                                                            <?php echo e(request('department') == $dept->department_id ? 'selected' : ''); ?>>
                                                        <?php echo e($dept->department_name); ?>

                                                    </option>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </select>
                                        </div>
                                        <div class="col-md-3 col-12">
                                            <label class="form-label fw-bold">Cadre</label>
                                            <select name="cadre" class="form-select">
                                                <option value="">All Cadres</option>
                                                <?php $__currentLoopData = $cadres; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cadre): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <option value="<?php echo e($cadre->cadre_id); ?>"
                                                            <?php echo e(request('cadre') == $cadre->cadre_id ? 'selected' : ''); ?>>
                                                        <?php echo e($cadre->name); ?>

                                                    </option>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </select>
                                        </div>
                                        <div class="col-md-3 col-12">
                                            <label class="form-label fw-bold">Status</label>
                                            <select name="status" class="form-select">
                                                <option value="">All Statuses</option>
                                                <?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <option value="<?php echo e($status); ?>"
                                                            <?php echo e(request('status') == $status ? 'selected' : ''); ?>>
                                                        <?php echo e($status); ?>

                                                    </option>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </select>
                                        </div>
                                        <div class="col-md-3 col-12">
                                            <label class="form-label fw-bold">Gender</label>
                                            <select name="gender" class="form-select">
                                                <option value="">All Genders</option>
                                                <?php $__currentLoopData = $genders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $gender): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <option value="<?php echo e($gender); ?>"
                                                            <?php echo e(request('gender') == $gender ? 'selected' : ''); ?>>
                                                        <?php echo e($gender); ?>

                                                    </option>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row g-3 mt-2">
                                        <!-- Row 2 -->
                                        <div class="col-md-3 col-12">
                                            <label class="form-label fw-bold">Appointment Type</label>
                                            <select name="appointment_type_id" class="form-select">
                                                <option value="">All Types</option>
                                                <?php $__currentLoopData = $appointmentTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <option value="<?php echo e($type->id); ?>"
                                                            <?php echo e(request('appointment_type_id') == $type->id ? 'selected' : ''); ?>>
                                                        <?php echo e($type->name); ?>

                                                    </option>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </select>
                                        </div>
                                        <div class="col-md-3 col-12">
                                            <label class="form-label fw-bold">State of Origin</label>
                                            <select name="state_of_origin" class="form-select">
                                                <option value="">All States</option>
                                                <?php $__currentLoopData = $states; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $state): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <option value="<?php echo e($state->name); ?>"
                                                            <?php echo e(request('state_of_origin') == $state->name ? 'selected' : ''); ?>>
                                                        <?php echo e($state->name); ?>

                                                    </option>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </select>
                                        </div>
                                        <div class="col-md-3 col-12">
                                            <label class="form-label fw-bold">Grade Level</label>
                                            <select name="grade_level_id" class="form-select">
                                                <option value="">All Grade Levels</option>
                                                <?php $__currentLoopData = $gradeLevels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $level): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <option value="<?php echo e($level->id); ?>"
                                                            <?php echo e(request('grade_level_id') == $level->id ? 'selected' : ''); ?>>
                                                        <?php echo e($level->name); ?>

                                                    </option>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </select>
                                        </div>
                                        <div class="col-md-3 col-12">
                                            <label class="form-label fw-bold">Results Per Page</label>
                                            <select name="per_page" class="form-select">
                                                <option value="10" <?php echo e(request('per_page') == '10' ? 'selected' : ''); ?>>10</option>
                                                <option value="25" <?php echo e(request('per_page') == '25' ? 'selected' : ''); ?>>25</option>
                                                <option value="50" <?php echo e(request('per_page') == '50' ? 'selected' : ''); ?>>50</option>
                                                <option value="100" <?php echo e(request('per_page') == '100' ? 'selected' : ''); ?>>100</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row g-3 mt-2">
                                        <!-- Row 3 - Date Ranges -->
                                        <div class="col-md-6 col-12">
                                            <label class="form-label fw-bold">Age Range</label>
                                            <div class="row">
                                                <div class="col-6">
                                                    <input type="number" name="age_from" class="form-control"
                                                           placeholder="From" min="18" max="70"
                                                           value="<?php echo e(request('age_from')); ?>">
                                                </div>
                                                <div class="col-6">
                                                    <input type="number" name="age_to" class="form-control"
                                                           placeholder="To" min="18" max="70"
                                                           value="<?php echo e(request('age_to')); ?>">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-12">
                                            <label class="form-label fw-bold">Appointment Date Range</label>
                                            <div class="row">
                                                <div class="col-6">
                                                    <input type="date" name="appointment_from" class="form-control"
                                                           value="<?php echo e(request('appointment_from')); ?>">
                                                </div>
                                                <div class="col-6">
                                                    <input type="date" name="appointment_to" class="form-control"
                                                           value="<?php echo e(request('appointment_to')); ?>">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row g-3 mt-2">
                                        <!-- Row 4 - Probation Status -->
                                        <div class="col-md-6 col-12">
                                            <label class="form-label fw-bold">Probation Status</label>
                                            <select name="probation_status" class="form-select">
                                                <option value="">All Probation Statuses</option>
                                                <option value="pending" <?php echo e(request('probation_status') == 'pending' ? 'selected' : ''); ?>>On Probation</option>
                                                <option value="approved" <?php echo e(request('probation_status') == 'approved' ? 'selected' : ''); ?>>Approved</option>
                                                <option value="rejected" <?php echo e(request('probation_status') == 'rejected' ? 'selected' : ''); ?>>Rejected</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row mt-3">
                                        <div class="col-12 d-flex flex-wrap gap-2">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-filter me-1"></i>Apply Filters
                                            </button>
                                            <a href="<?php echo e(route('employees.index')); ?>" class="btn btn-outline-secondary">
                                                <i class="fas fa-undo me-1"></i>Reset All
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Results Summary and Sorting -->
            <div class="row mb-3">
                <div class="col-12 col-md-6 mb-3 mb-md-0">
                    <p class="mb-0 text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        Showing <?php echo e($employees->firstItem() ?? 0); ?> to <?php echo e($employees->lastItem() ?? 0); ?>

                        of <?php echo e($employees->total()); ?> results
                        <?php if(request()->hasAny(['search', 'department', 'cadre', 'status', 'probation_status'])): ?>
                            <span class="badge bg-info ms-2">Filtered</span>
                        <?php endif; ?>
                    </p>
                </div>
                <div class="col-12 col-md-6">
                    <div class="d-flex flex-wrap justify-content-md-end align-items-center gap-2">
                        <label class="form-label mb-0 fw-bold">Sort by:</label>
                        <select class="form-select" style="width: auto;" onchange="changeSorting(this.value)">
                            <option value="created_at|desc" <?php echo e(request('sort_by') == 'created_at' && request('sort_order') == 'desc' ? 'selected' : ''); ?>>
                                Newest First
                            </option>
                            <option value="first_name|asc" <?php echo e(request('sort_by') == 'first_name' && request('sort_order') == 'asc' ? 'selected' : ''); ?>>
                                Name A-Z
                            </option>
                            <option value="first_name|desc" <?php echo e(request('sort_by') == 'first_name' && request('sort_order') == 'desc' ? 'selected' : ''); ?>>
                                Name Z-A
                            </option>
                            <option value="date_of_first_appointment|desc" <?php echo e(request('sort_by') == 'date_of_first_appointment' && request('sort_order') == 'desc' ? 'selected' : ''); ?>>
                                Latest Appointment
                            </option>
                            <option value="expected_retirement_date|asc" <?php echo e(request('sort_by') == 'expected_retirement_date' && request('sort_order') == 'asc' ? 'selected' : ''); ?>>
                                Earliest Retirement
                            </option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Employee Table -->
            <div class="table-responsive">
                <table class="table table-striped table-bordered align-middle shadow-sm" style="background: #fff; border-radius: 12px;">
                    <thead style="background: #b2ebf2;">
                        <tr>
                            <th>#</th>
                            <th>Staff no</th>
                            <th>Photo</th>
                            <th>Name</th>
                            <th>Department</th>
                            <th>Cadre</th>
                            <th>Pay Point</th>
                            <th>Appointment Type</th>
                            <th>Status</th>
                            <th>Probation</th>
                            <th>Contact</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e($loop->iteration + ($employees->firstItem() ? $employees->firstItem() - 1 : 0)); ?></td>
                                <td>
                                    <span class="badge bg-secondary"><?php echo e($employee->staff_no); ?></span>
                                </td>
                                <td>
                                    <?php if($employee->photo_path): ?>
                                        <img src="<?php echo e(asset('storage/' . $employee->photo_path)); ?>"
                                             alt="<?php echo e($employee->first_name); ?>"
                                             class="rounded-circle"
                                             style="width: 40px; height: 40px; object-fit: cover;">
                                    <?php else: ?>
                                        <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center text-white"
                                             style="width: 40px; height: 40px;">
                                            <?php echo e(substr($employee->first_name, 0, 1)); ?><?php echo e(substr($employee->surname, 0, 1)); ?>

                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div>
                                        <strong><?php echo e($employee->first_name); ?> <?php echo e($employee->surname); ?></strong>
                                        <?php if($employee->middle_name): ?>
                                            <br><small class="text-muted"><?php echo e($employee->middle_name); ?></small>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td><?php echo e($employee->department->department_name ?? 'N/A'); ?></td>
                                <td><?php echo e($employee->cadre->name ?? 'N/A'); ?></td>
                                <td><?php echo e($employee->pay_point ?? 'N/A'); ?></td>
                                <td><?php echo e($employee->appointmentType->name ?? 'N/A'); ?></td>
                                <td>
                                    <span class="badge <?php echo e($employee->status == 'Active' ? 'bg-success' :
                                        ($employee->status == 'Suspended' ? 'bg-warning' :
                                        ($employee->status == 'Retired' ? 'bg-info' : 'bg-dark'))); ?>">
                                        <?php echo e($employee->status); ?>

                                    </span>
                                </td>
                                <td>
                                    <?php if($employee->on_probation): ?>
                                        <span class="badge bg-warning">
                                            <?php if($employee->probation_status == 'pending'): ?>
                                                <?php echo e('On Probation'); ?>

                                                <?php if($employee->getRemainingProbationDays() > 0): ?>
                                                    <br><small><?php echo e($employee->getRemainingProbationDays()); ?> days left</small>
                                                <?php endif; ?>
                                            <?php elseif($employee->probation_status == 'approved'): ?>
                                                Approved
                                            <?php elseif($employee->probation_status == 'rejected'): ?>
                                                Rejected
                                            <?php endif; ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Not on Probation</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small>
                                        <i class="fas fa-phone me-1"></i><?php echo e($employee->mobile_no); ?><br>
                                        <?php if($employee->email): ?>
                                            <i class="fas fa-envelope me-1"></i><?php echo e($employee->email); ?>

                                        <?php endif; ?>
                                    </small>
                                </td>
                                <td>
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage_employees')): ?>
                                        <div class="dropdown">
                                            <button class="btn btn-secondary btn-sm rounded-pill dropdown-toggle"
                                                    type="button" data-bs-toggle="dropdown">
                                                Actions
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <a class="dropdown-item" href="<?php echo e(route('employees.show', $employee)); ?>">
                                                        <i class="fas fa-eye me-2"></i>View
                                                    </a>
                                                </li>
                                                <?php if($employee->on_probation): ?>
                                                <li>
                                                    <a class="dropdown-item" href="<?php echo e(route('probation.show', $employee)); ?>">
                                                        <i class="fas fa-clock me-2"></i>Probation Details
                                                    </a>
                                                </li>
                                                <?php endif; ?>
                                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('edit_employees')): ?>
                                                <li>
                                                    <a class="dropdown-item" href="<?php echo e(route('employees.edit', $employee)); ?>">
                                                        <i class="fas fa-edit me-2"></i>Edit
                                                    </a>
                                                </li>
                                                <?php endif; ?>
                                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete_employees')): ?>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <a href="#" class="dropdown-item text-danger" onclick="deleteEmployee(<?php echo e($employee->employee_id); ?>, '<?php echo e($employee->first_name); ?> <?php echo e($employee->surname); ?>')">
                                                        <i class="fas fa-trash me-2"></i>Delete
                                                    </a>
                                                </li>
                                                <?php endif; ?>
                                            </ul>
                                        </div>
                                    <?php else: ?>
                                        <a class="btn btn-outline-secondary btn-sm rounded-pill" href="<?php echo e(route('employees.show', $employee)); ?>">
                                            <i class="fas fa-eye me-1"></i>View
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="12" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-users fa-3x mb-3"></i>
                                        <h5>No employees found</h5>
                                        <p>Try adjusting your search criteria or add new employees.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4 d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                <div>
                    <?php echo e($employees->withQueryString()->links('pagination::bootstrap-5')); ?>

                </div>
                <div class="text-muted text-center text-md-end">
                    <small>
                        Total: <?php echo e($employees->total()); ?> employees
                        <?php if(request()->hasAny(['search', 'department', 'cadre', 'status', 'probation_status'])): ?>
                            | <span class="text-info">Filtered results</span>
                        <?php endif; ?>
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function changeSorting(value) {
    const [sortBy, sortOrder] = value.split('|');
    const url = new URL(window.location);
    url.searchParams.set('sort_by', sortBy);
    url.searchParams.set('sort_order', sortOrder);
    window.location.href = url.toString();
}

function exportFiltered(format) {
    // Build the export URL with all current filter parameters
    const exportUrl = new URL('<?php echo e(route("employees.export.filtered")); ?>', window.location.origin);

    // Get all form parameters and add them to the URL
    const form = document.getElementById('filterForm');
    const formData = new FormData(form);

    for (let [key, value] of formData.entries()) {
        if (value) {
            exportUrl.searchParams.append(key, value);
        }
    }

    // Add the format parameter
    exportUrl.searchParams.set('format', format);

    // Open the export URL
    window.open(exportUrl.toString(), '_blank');
}

// Auto-submit form when filters change (optional)
document.addEventListener('DOMContentLoaded', function() {
    console.log('Employee index page loaded');
    const filterInputs = document.querySelectorAll('#filterForm select:not([name="per_page"]), #filterForm input[type="date"], #filterForm input[type="number"]');

    filterInputs.forEach(input => {
        input.addEventListener('change', function() {
            // Optional: Auto-submit on change
            // document.getElementById('filterForm').submit();
        });
    });
});

// Clear individual filters
function clearFilter(filterName) {
    const input = document.querySelector(`[name="${filterName}"]`);
    if (input) {
        input.value = '';
        document.getElementById('filterForm').submit();
    }
}

// Delete employee function
function deleteEmployee(employeeId, employeeName) {
    if (confirm(`Are you sure you want to delete employee ${employeeName}?`)) {
        const reason = prompt('Please provide a reason for deleting this employee:');
        if (reason !== null && reason.trim() !== '') {
            // Create form dynamically
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/employees/${employeeId}`;

            // Add CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = csrfToken;
            form.appendChild(csrfInput);

            // Add method field
            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';
            form.appendChild(methodInput);

            // Add delete reason
            const reasonInput = document.createElement('input');
            reasonInput.type = 'hidden';
            reasonInput.name = 'delete_reason';
            reasonInput.value = reason.trim();
            form.appendChild(reasonInput);

            // Submit form
            document.body.appendChild(form);
            form.submit();
        }
    }
}
</script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Rowwww\Herd\hrm_payroll_system_design\resources\views/employees/index.blade.php ENDPATH**/ ?>