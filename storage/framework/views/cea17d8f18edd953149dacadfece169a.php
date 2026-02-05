<?php $__env->startSection('title', 'Generate Reports'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('generate_reports')): ?>
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="bg-light p-4 rounded mb-4">
                <form method="GET" action="<?php echo e(route('reports.create')); ?>" id="filter-form">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="search" class="form-label">Search Employees</label>
                            <input type="text" id="search" name="search" class="form-control" placeholder="Search by name or ID..." value="<?php echo e(request('search')); ?>">
                        </div>
                        <div class="col-md-2 mb-3">
                            <label for="department" class="form-label">Department</label>
                            <select id="department" name="department" class="form-select">
                                <option value="">All Departments</option>
                                <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($dept->department_id); ?>" <?php echo e(request('department') == $dept->department_id ? 'selected' : ''); ?>>
                                        <?php echo e($dept->department_name); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select id="status" name="status" class="form-select">
                                <option value="">All Statuses</option>
                                <option value="Active" <?php echo e(request('status') == 'Active' ? 'selected' : ''); ?>>Active</option>
                                <option value="Suspended" <?php echo e(request('status') == 'Suspended' ? 'selected' : ''); ?>>Suspended</option>
                                <option value="Retired" <?php echo e(request('status') == 'Retired' ? 'selected' : ''); ?>>Retired</option>
                                <option value="Deceased" <?php echo e(request('status') == 'Deceased' ? 'selected' : ''); ?>>Deceased</option>
                            </select>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label for="appointment_type" class="form-label">Appointment Type</label>
                            <select id="appointment_type" name="appointment_type" class="form-select">
                                <option value="">All Appointment Types</option>
                                <?php
                                    $appointmentTypes = \App\Models\AppointmentType::all();
                                ?>
                                <?php $__currentLoopData = $appointmentTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($type->id); ?>" <?php echo e(request('appointment_type') == $type->id ? 'selected' : ''); ?>>
                                        <?php echo e($type->name); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="col-md-2 mb-3 d-flex align-items-end">
                            <div>
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="fas fa-search"></i> Search
                                </button>
                                <a href="<?php echo e(route('reports.create')); ?>" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Reset
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="mb-4">
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view_comprehensive_reports')): ?>
                <a href="<?php echo e(route('reports.comprehensive.index')); ?>" class="btn btn-primary btn-lg">
                    <i class="fas fa-chart-bar"></i> Go to Comprehensive Reports System
                </a>
                <?php endif; ?>
            </div>




            <div class="row">
                <?php $__empty_1 = true; $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="col-md-6">
                        <div class="card mb-4 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <img src="<?php echo e(asset('storage/' . $employee->photo_path)); ?>" alt="" class="rounded-circle me-3" style="width: 60px; height: 60px; object-fit: cover;">
                                    <div>
                                        <h5 class="mb-1"><?php echo e($employee->first_name); ?> <?php echo e($employee->surname); ?></h5>
                                        <p class="mb-0 text-muted"><strong><?php echo e($employee->staff_no); ?></strong> | <?php echo e($employee->department->department_name ?? 'N/A'); ?></p>
                                    </div>
                                </div>
                                <form action="<?php echo e(route('reports.generate')); ?>" method="POST" class="report-form">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="employee_id" value="<?php echo e($employee->employee_id); ?>">
                                    <div class="mb-3">
                                        <label for="report_type_<?php echo e($employee->employee_id); ?>" class="form-label">Report Type</label>
                                        <select name="report_type" id="report_type_<?php echo e($employee->employee_id); ?>" class="form-select report-type-select">
                                            <option value="">-- Select Report Type --</option>
                                            <option value="comprehensive">Comprehensive Report</option>
                                            <option value="basic">Basic Information</option>
                                            <option value="disciplinary">Disciplinary Records</option>
                                            <option value="payroll">Payroll Information</option>
                                            <option value="retirement">Retirement Planning</option>
                                            <option value="deduction">Deduction Report</option>
                                            <option value="addition">Addition Report</option>
                                        </select>
                                    </div>

                                    <!-- Dynamic Deduction Type Select -->
                                    <div class="mb-3 deduction-type-section" id="deduction_type_section_<?php echo e($employee->employee_id); ?>" style="display: none;">
                                        <label for="deduction_type_<?php echo e($employee->employee_id); ?>" class="form-label">Deduction Type</label>
                                        <select name="deduction_type_id" id="deduction_type_<?php echo e($employee->employee_id); ?>" class="form-select deduction-type-select">
                                            <option value="">-- Select Deduction Type --</option>
                                            <?php $__currentLoopData = App\Models\DeductionType::all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $deductionType): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($deductionType->id); ?>"><?php echo e($deductionType->name); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                    </div>

                                    <!-- Dynamic Addition Type Select -->
                                    <div class="mb-3 addition-type-section" id="addition_type_section_<?php echo e($employee->employee_id); ?>" style="display: none;">
                                        <label for="addition_type_<?php echo e($employee->employee_id); ?>" class="form-label">Addition Type</label>
                                        <select name="addition_type_id" id="addition_type_<?php echo e($employee->employee_id); ?>" class="form-select addition-type-select">
                                            <option value="">-- Select Addition Type --</option>
                                            <?php $__currentLoopData = App\Models\AdditionType::all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $additionType): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($additionType->id); ?>"><?php echo e($additionType->name); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                    </div>

                                    <!-- Date Range Selection (for deduction/addition reports) -->
                                    <div class="mb-3 date-range-section" id="date_range_section_<?php echo e($employee->employee_id); ?>" style="display: none;">
                                        <div class="card bg-light">
                                            <div class="card-body">
                                                <h6>Select Date Range for Report</h6>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <label for="start_date_<?php echo e($employee->employee_id); ?>" class="form-label">Start Date</label>
                                                        <input type="date" name="start_date" id="start_date_<?php echo e($employee->employee_id); ?>" class="form-control">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label for="end_date_<?php echo e($employee->employee_id); ?>" class="form-label">End Date</label>
                                                        <input type="date" name="end_date" id="end_date_<?php echo e($employee->employee_id); ?>" class="form-control">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="export_format_<?php echo e($employee->employee_id); ?>" class="form-label">Export Format</label>
                                        <select name="export_format" id="export_format_<?php echo e($employee->employee_id); ?>" class="form-select">
                                            <option value="PDF">PDF</option>
                                            <option value="Excel">Excel</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-file-export"></i> Generate Report
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="col-12">
                        <div class="alert alert-info text-center">
                            No employees found matching your criteria.
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <?php if($employees->hasPages()): ?>
            <div class="d-flex justify-content-center mt-4">
                <?php echo e($employees->appends(request()->query())->links('pagination::bootstrap-4')); ?>

            </div>
            <?php endif; ?>

            <div class="mt-3">
                <a href="<?php echo e(route('reports.index')); ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Reports
                </a>
            </div>
        </div>
    </div>
</div>
<?php else: ?>
<div class="alert alert-warning">
    You don't have permission to generate reports.
</div>
<?php endif; ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle bulk report generation
        const bulkGenerateBtn = document.getElementById('bulk-generate-btn');
        const bulkReportType = document.getElementById('bulk_report_type');
        const bulkExportFormat = document.getElementById('bulk_export_format');
        const bulkForm = document.getElementById('bulk-report-form');
        const employeeIdsInput = document.getElementById('employee_ids');
        const dateRangeSectionBulk = document.getElementById('date_range_section_bulk');
        const startDateBulk = document.getElementById('start_date_bulk');
        const endDateBulk = document.getElementById('end_date_bulk');

        // Enable/disable bulk generate button based on selection
        bulkReportType.addEventListener('change', function() {
            bulkGenerateBtn.disabled = !this.value;

            // Show/hide date range section for deduction/addition reports
            if (this.value.startsWith('deduction_') || this.value.startsWith('addition_')) {
                if (dateRangeSectionBulk) dateRangeSectionBulk.style.display = 'block';
            } else {
                if (dateRangeSectionBulk) dateRangeSectionBulk.style.display = 'none';
            }
        });

        // Handle form submission for special report types
        bulkForm.addEventListener('submit', function(e) {
            const reportType = bulkReportType.value;

            // For individual deduction and addition reports, we don't need employee_ids
            if (reportType.startsWith('deduction_') || reportType.startsWith('addition_')) {
                // Remove employee_ids input for these report types
                employeeIdsInput.removeAttribute('name');

                // Add hidden inputs for date range if provided
                if (startDateBulk && startDateBulk.value) {
                    const startDateInput = document.createElement('input');
                    startDateInput.type = 'hidden';
                    startDateInput.name = 'start_date';
                    startDateInput.value = startDateBulk.value;
                    bulkForm.appendChild(startDateInput);
                }

                if (endDateBulk && endDateBulk.value) {
                    const endDateInput = document.createElement('input');
                    endDateInput.type = 'hidden';
                    endDateInput.name = 'end_date';
                    endDateInput.value = endDateBulk.value;
                    bulkForm.appendChild(endDateInput);
                }
            } else {
                // For other report types, ensure employee_ids input has a name
                employeeIdsInput.setAttribute('name', 'employee_ids');

                // Validate that at least one employee is selected
                if (!employeeIdsInput.value) {
                    e.preventDefault();
                    alert('Please select at least one employee for this report type.');
                    return false;
                }
            }
        });

        // Handle individual employee report forms
        const reportTypeSelects = document.querySelectorAll('.report-type-select');

        reportTypeSelects.forEach(function(select) {
            select.addEventListener('change', function() {
                const employeeId = this.id.replace('report_type_', '');
                const deductionSection = document.getElementById(`deduction_type_section_${employeeId}`);
                const additionSection = document.getElementById(`addition_type_section_${employeeId}`);
                const dateRangeSection = document.getElementById(`date_range_section_${employeeId}`);
                const deductionSelect = document.getElementById(`deduction_type_${employeeId}`);
                const additionSelect = document.getElementById(`addition_type_${employeeId}`);

                // Hide all sections initially
                if (deductionSection) deductionSection.style.display = 'none';
                if (additionSection) additionSection.style.display = 'none';
                if (dateRangeSection) dateRangeSection.style.display = 'none';

                // Show the appropriate section based on selection
                if (this.value === 'deduction') {
                    if (deductionSection) deductionSection.style.display = 'block';
                    if (additionSection) additionSection.style.display = 'none';
                    if (dateRangeSection) dateRangeSection.style.display = 'block';
                } else if (this.value === 'addition') {
                    if (additionSection) additionSection.style.display = 'block';
                    if (deductionSection) deductionSection.style.display = 'none';
                    if (dateRangeSection) dateRangeSection.style.display = 'block';
                } else {
                    // For all other options, hide all sections
                    if (deductionSection) deductionSection.style.display = 'none';
                    if (additionSection) additionSection.style.display = 'none';
                    if (dateRangeSection) dateRangeSection.style.display = 'none';
                }
            });
        });

        // Update form submission to handle dynamic report types
        const reportForms = document.querySelectorAll('.report-form');

        reportForms.forEach(function(form) {
            form.addEventListener('submit', function(e) {
                const reportTypeSelect = form.querySelector('.report-type-select');
                const deductionSection = form.querySelector('.deduction-type-section');
                const additionSection = form.querySelector('.addition-type-section');

                let finalReportType = reportTypeSelect.value;

                if (reportTypeSelect.value === 'deduction' && deductionSection && deductionSection.style.display !== 'none') {
                    const deductionTypeSelect = form.querySelector('.deduction-type-select');
                    if (deductionTypeSelect && deductionTypeSelect.value) {
                        finalReportType = `deduction_${deductionTypeSelect.value}`;
                    }
                } else if (reportTypeSelect.value === 'addition' && additionSection && additionSection.style.display !== 'none') {
                    const additionTypeSelect = form.querySelector('.addition-type-select');
                    if (additionTypeSelect && additionTypeSelect.value) {
                        finalReportType = `addition_${additionTypeSelect.value}`;
                    }
                }

                // Set the value of the original select to the final value
                reportTypeSelect.value = finalReportType;
            });
        });
    });
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Rowwww\Herd\hrm_payroll_system_design\resources\views/reports/create.blade.php ENDPATH**/ ?>