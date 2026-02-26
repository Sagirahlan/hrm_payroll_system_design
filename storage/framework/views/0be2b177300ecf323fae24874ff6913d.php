<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">
    <div class="card border-primary shadow">
        <div class="card-header bg-primary text-white">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-file-invoice-dollar me-2"></i>Payroll Records
                </h5>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage_payroll_adjustments')): ?>
                <a href="<?php echo e(route('payroll.adjustments.manage')); ?>" class="btn btn-light">
                    <i class="fas fa-users-cog me-1"></i> Manage Adjustments
                </a>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-body">
            
            <!-- Payroll Generation Form -->
             <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('generate_payroll')): ?>
            <div class="card border-primary mb-4 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <strong><i class="fas fa-calculator me-2"></i>Generate Payroll</strong>
                </div>
                <div class="card-body">
                    <form action="<?php echo e(route('payroll.generate')); ?>" method="POST" class="row g-3 align-items-end">
                        <?php echo csrf_field(); ?>
                        <div class="col-md-3">
                            <label for="month" class="form-label">Select Month</label>
                            <input type="month" name="month" id="month" value="<?php echo e(now()->format('Y-m')); ?>"
                                   class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label for="payroll_category" class="form-label">Payroll Category</label>
                            <select name="payroll_category" id="payroll_category" class="form-select">
                                <option value="staff">Staff (Active/Suspended)</option>
                                <option value="pensioners">Pensioners</option>
                                <option value="gratuity">Gratuity (Pensioners)</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="appointment_type_id" class="form-label">Appointment Type</label>
                            <select name="appointment_type_id" id="appointment_type_id" class="form-select">
                                <option value="">All Types</option>
                                <?php $__currentLoopData = $appointmentTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($type->id); ?>"><?php echo e($type->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100" id="generate-payroll-btn">
                                <i class="fas fa-cogs me-1"></i><span>Generate</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>

            <!-- Delete Monthly Payroll (Admin Only) -->
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete_payroll')): ?>
            <div class="card border-danger mb-4 shadow-sm">
                <div class="card-header bg-danger text-white">
                    <strong><i class="fas fa-trash-alt me-2"></i>Delete Monthly Payroll</strong>
                </div>
                <div class="card-body">
                    <form action="<?php echo e(route('payroll.delete_month')); ?>" method="POST" class="row g-3 align-items-end" onsubmit="return confirm('Are you sure you want to DELETE the entire payroll batch for this month? This action cannot be undone.');">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('DELETE'); ?>
                        <div class="col-md-3">
                            <label for="delete_month" class="form-label">Select Month to Delete</label>
                            <input type="month" name="month" id="delete_month" value="<?php echo e(now()->format('Y-m')); ?>"
                                   class="form-control">
                        </div>
                        <div class="col-md-9">
                            <button type="submit" class="btn btn-outline-danger">
                                <i class="fas fa-trash me-1"></i>Delete Batch
                            </button>
                            <small class="text-muted ms-2">
                                <i class="fas fa-info-circle"></i> Only unapproved/unpaid payrolls can be deleted. This will remove records and associated one-time additions/deductions.
                            </small>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>


            <!-- Search and Filter Section -->
            <div class="card border-info mb-4 shadow-sm">
                <div class="card-header bg-info text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <strong class="mb-0">
                            <i class="fas fa-search me-2"></i>Search & Filter
                        </strong>
                        <button class="btn btn-sm btn-outline-light" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse" aria-expanded="false">
                            <i class="fas fa-filter me-1"></i> Toggle Filters
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <form method="GET" action="<?php echo e(route('payroll.index')); ?>" class="mb-3">
                        <!-- Quick Search -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-search"></i>
                                    </span>
                                    <input type="text" 
                                           name="search" 
                                           class="form-control" 
                                           placeholder="Search by employee name, payroll ID..." 
                                           value="<?php echo e(request('search')); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex flex-wrap gap-2">
                                    <a href="<?php echo e(route('payroll.index')); ?>" class="btn btn-outline-secondary btn-sm">
                                        <i class="fas fa-refresh me-1"></i> Clear All
                                    </a>
                                    <div class="dropdown">
                                        <button class="btn btn-outline-info btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            <i class="fas fa-download me-1"></i> Export
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="<?php echo e(route('payroll.export', array_merge(request()->query(), ['detailed' => 0]))); ?>"><i class="fas fa-file-alt me-2"></i>Export Summary</a></li>
                                            <li><a class="dropdown-item" href="<?php echo e(route('payroll.export', array_merge(request()->query(), ['detailed' => 1]))); ?>"><i class="fas fa-file-contract me-2"></i>Export Detailed</a></li>
                                            <li><a class="dropdown-item" href="<?php echo e(route('payroll.export')); ?>"><i class="fas fa-file-export me-2"></i>Export All Records</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Advanced Filters (Collapsible) -->
                        <div class="collapse <?php echo e(request()->hasAny(['status', 'month_filter', 'salary_range', 'department']) ? 'show' : ''); ?>" id="filterCollapse">
                            <hr>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select name="status" id="status" class="form-select">
                                        <option value="">All Statuses</option>
                                        <option value="Pending" <?php echo e(request('status') == 'Pending' ? 'selected' : ''); ?>>Pending</option>
                                        <option value="Processed" <?php echo e(request('status') == 'Processed' ? 'selected' : ''); ?>>Processed</option>
                                        <option value="Approved" <?php echo e(request('status') == 'Approved' ? 'selected' : ''); ?>>Approved</option>
                                        <option value="Paid" <?php echo e(request('status') == 'Paid' ? 'selected' : ''); ?>>Paid</option>
                                        <option value="Pending Deletion" <?php echo e(request('status') == 'Pending Deletion' ? 'selected' : ''); ?>>Pending Deletion</option>
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label for="employee_status" class="form-label">Employee Status</label>
                                    <select name="employee_status" id="employee_status" class="form-select">
                                        <option value="">All Employee Statuses</option>
                                        <option value="Active" <?php echo e(request('employee_status') == 'Active' ? 'selected' : ''); ?>>Active</option>
                                        <option value="Suspended" <?php echo e(request('employee_status') == 'Suspended' ? 'selected' : ''); ?>>Suspended</option>
                                        <option value="Retired" <?php echo e(request('employee_status') == 'Retired' ? 'selected' : ''); ?>>Retired (Pensioners)</option>
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label for="appointment_type" class="form-label">Appointment Type</label>
                                    <select name="appointment_type" id="appointment_type" class="form-select">
                                        <option value="">All Appointment Types</option>
                                        <?php $__currentLoopData = $appointmentTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($type->id); ?>" <?php echo e(request('appointment_type') == $type->id ? 'selected' : ''); ?>>
                                                <?php echo e($type->name); ?>

                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label for="month_filter" class="form-label">Payroll Month</label>
                                    <input type="month"
                                           name="month_filter"
                                           id="month_filter"
                                           class="form-control"
                                           value="<?php echo e(request('month_filter')); ?>">
                                </div>

                                <div class="col-md-3">
                                    <label for="salary_range" class="form-label">Salary Range</label>
                                    <select name="salary_range" id="salary_range" class="form-select">
                                        <option value="">All Ranges</option>
                                        <option value="0-50000" <?php echo e(request('salary_range') == '0-50000' ? 'selected' : ''); ?>>₦0 - ₦50,000</option>
                                        <option value="50001-100000" <?php echo e(request('salary_range') == '50001-100000' ? 'selected' : ''); ?>>₦50,001 - ₦100,000</option>
                                        <option value="100001-200000" <?php echo e(request('salary_range') == '100001-200000' ? 'selected' : ''); ?>>₦100,001 - ₦200,000</option>
                                        <option value="200001-500000" <?php echo e(request('salary_range') == '200001-500000' ? 'selected' : ''); ?>>₦200,001 - ₦500,000</option>
                                        <option value="500001+" <?php echo e(request('salary_range') == '500001+' ? 'selected' : ''); ?>>₦500,001+</option>
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label for="sort_by" class="form-label">Sort By</label>
                                    <select name="sort_by" id="sort_by" class="form-select">
                                        <option value="created_at" <?php echo e(request('sort_by') == 'created_at' ? 'selected' : ''); ?>>Date Created</option>
                                        <option value="employee_name" <?php echo e(request('sort_by') == 'employee_name' ? 'selected' : ''); ?>>Employee Name</option>
                                        <option value="net_salary" <?php echo e(request('sort_by') == 'net_salary' ? 'selected' : ''); ?>>Net Salary</option>
                                        <option value="basic_salary" <?php echo e(request('sort_by') == 'basic_salary' ? 'selected' : ''); ?>>Basic Salary</option>
                                        <option value="payroll_month" <?php echo e(request('sort_by') == 'payroll_month' ? 'selected' : ''); ?>>Payroll Month</option>
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label for="sort_direction" class="form-label">Sort Direction</label>
                                    <select name="sort_direction" id="sort_direction" class="form-select">
                                        <option value="desc" <?php echo e(request('sort_direction') == 'desc' ? 'selected' : ''); ?>>Descending</option>
                                        <option value="asc" <?php echo e(request('sort_direction') == 'asc' ? 'selected' : ''); ?>>Ascending</option>
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label for="per_page" class="form-label">Records Per Page</label>
                                    <select name="per_page" id="per_page" class="form-select">
                                        <option value="10" <?php echo e(request('per_page') == '10' ? 'selected' : ''); ?>>10</option>
                                        <option value="20" <?php echo e(request('per_page', '20') == '20' ? 'selected' : ''); ?>>20</option>
                                        <option value="50" <?php echo e(request('per_page') == '50' ? 'selected' : ''); ?>>50</option>
                                        <option value="100" <?php echo e(request('per_page') == '100' ? 'selected' : ''); ?>>100</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Date Range</label>
                                    <div class="row g-2">
                                        <div class="col">
                                            <input type="date" 
                                                   name="date_from" 
                                                   class="form-control" 
                                                   placeholder="From" 
                                                   value="<?php echo e(request('date_from')); ?>">
                                        </div>
                                        <div class="col">
                                            <input type="date" 
                                                   name="date_to" 
                                                   class="form-control" 
                                                   placeholder="To" 
                                                   value="<?php echo e(request('date_to')); ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-3">
                                <button type="submit" class="btn btn-primary btn-sm me-2">
                                    <i class="fas fa-filter me-1"></i> Apply Filters
                                </button>
                                <a href="<?php echo e(route('payroll.index')); ?>" class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-times me-1"></i> Clear Filters
                                </a>
                            </div>
                        </div>
                    </form>

                    <!-- Active Filters Display -->
                    <?php if(request()->hasAny(['search', 'status', 'employee_status', 'appointment_type', 'month_filter', 'salary_range', 'date_from', 'date_to'])): ?>
                        <div class="mt-3">
                            <small class="text-muted">Active filters:</small>
                            <div class="d-flex flex-wrap gap-2 mt-1">
                                <?php if(request('search')): ?>
                                    <span class="badge bg-info">
                                        <i class="fas fa-search me-1"></i>Search: "<?php echo e(request('search')); ?>"
                                    </span>
                                <?php endif; ?>
                                <?php if(request('status')): ?>
                                    <span class="badge bg-warning text-dark">
                                        <i class="fas fa-circle me-1"></i>Status: <?php echo e(request('status')); ?>

                                    </span>
                                <?php endif; ?>
                                <?php if(request('employee_status')): ?>
                                    <span class="badge bg-info">
                                        <i class="fas fa-user me-1"></i>Employee Status: <?php echo e(request('employee_status')); ?>

                                    </span>
                                <?php endif; ?>
                                <?php if(request('appointment_type')): ?>
                                    <span class="badge bg-success">
                                        <i class="fas fa-briefcase me-1"></i>Appointment: <?php echo e($appointmentTypes->firstWhere('id', request('appointment_type'))?->name ?? 'Unknown'); ?>

                                    </span>
                                <?php endif; ?>
                                <?php if(request('month_filter')): ?>
                                    <span class="badge bg-success">
                                        <i class="fas fa-calendar me-1"></i>Month: <?php echo e(request('month_filter')); ?>

                                    </span>
                                <?php endif; ?>
                                <?php if(request('salary_range')): ?>
                                    <span class="badge bg-primary">
                                        <i class="fas fa-money-bill-wave me-1"></i>Salary: <?php echo e(request('salary_range')); ?>

                                    </span>
                                <?php endif; ?>
                                <?php if(request('date_from') || request('date_to')): ?>
                                    <span class="badge bg-secondary">
                                        <i class="fas fa-calendar-alt me-1"></i>Date: <?php echo e(request('date_from') ?: 'Any'); ?> to <?php echo e(request('date_to') ?: 'Any'); ?>

                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Results Summary -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <p class="text-muted mb-0">
                        <i class="fas fa-list me-1"></i>
                        Showing <?php echo e($payrolls->firstItem() ?? 0); ?> to <?php echo e($payrolls->lastItem() ?? 0); ?> 
                        of <?php echo e($payrolls->total()); ?> results
                        <?php if(request()->hasAny(['search', 'status', 'employee_status', 'appointment_type', 'month_filter', 'salary_range', 'date_from', 'date_to'])): ?>
                            <span class="badge bg-info ms-1">filtered</span>
                        <?php endif; ?>
                    </p>
                </div>
                <div class="col-md-6 text-end">
                    <?php if($payrolls->count() > 0): ?>
                        <p class="mb-0">
                            <i class="fas fa-calculator text-success me-1"></i>
                            <strong>Total Net Salary: ₦<?php echo e(number_format($payrolls->sum('net_salary'), 2)); ?></strong>
                        </p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Payroll Table Section -->
            <section class="mb-4">
                <div class="card border-primary shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">
                                <i class="fas fa-table me-2"></i>Payroll Records
                            </h6>
                            <div class="d-flex gap-2">
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('bulk_send_payroll_for_review')): ?>
                                <button class="btn btn-sm btn-warning" id="bulk-send-review">
                                    <i class="fas fa-paper-plane me-1"></i> Send for Review
                                </button>
                                <?php endif; ?>
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('bulk_mark_payroll_as_reviewed')): ?>
                                <button class="btn btn-sm btn-info" id="bulk-mark-reviewed">
                                    <i class="fas fa-check-circle me-1"></i> Mark as Reviewed
                                </button>
                                <?php endif; ?>
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('bulk_send_payroll_for_approval')): ?>
                                <button class="btn btn-sm btn-info" id="bulk-send-approval">
                                    <i class="fas fa-paper-plane me-1"></i> Send for Approval
                                </button>
                                <?php endif; ?>
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('bulk_final_approve_payroll')): ?>
                                <button class="btn btn-sm btn-success" id="bulk-final-approve">
                                    <i class="fas fa-thumbs-up me-1"></i> Final Approve
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-middle mb-0">
                                <thead class="table-dark">
                                    <tr>
                                        <th class="align-middle text-center">
                                            <input type="checkbox" id="select-all-payrolls" class="form-check-input">
                                        </th>
                                        <th class="align-middle">Staff No</th>
                                        <th class="align-middle">Employee</th>
                                        <th class="align-middle">Expected Retirement</th>
                                        <th class="align-middle text-end">Basic Salary</th>
                                        <th class="text-center">Additions</th>
                                        <th class="text-center">Deductions</th>
                                        <th class="align-middle text-end">Net Salary</th>
                                        <th class="align-middle">Status</th>
                                        <th class="align-middle">Payment Date</th>
                                        <th class="align-middle">Month</th>
                                        <th class="align-middle">Type</th>
                                        <th class="align-middle">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__empty_1 = true; $__currentLoopData = $payrolls; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payroll): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <tr>
                                            <td class="text-center">
                                                <input type="checkbox" name="payroll_ids[]" value="<?php echo e($payroll->payroll_id); ?>" class="payroll-checkbox form-check-input">
                                            </td>
                                            <td>
                                                <?php echo e($payroll->employee && $payroll->employee->staff_no ? $payroll->employee->staff_no : 'N/A'); ?>

                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div>
                                                        <?php if($payroll->employee): ?>
                                                            <strong><?php echo e($payroll->employee->first_name); ?> <?php echo e($payroll->employee->surname); ?></strong>
                                                            <br>
                                                            <?php if($payroll->employee->employee_id): ?>
                                                                <small class="text-muted">ID: <?php echo e($payroll->employee->employee_id); ?></small>
                                                            <?php endif; ?>
                                                            <br>
                                                            <?php if($payroll->gradeLevel || ($payroll->employee && $payroll->employee->gradeLevel)): ?>
                                                                <small class="text-muted">GL: <?php echo e(($payroll->gradeLevel ?? $payroll->employee->gradeLevel)->name); ?><?php if($payroll->step || ($payroll->employee && $payroll->employee->step)): ?> - Step <?php echo e(($payroll->step ?? $payroll->employee->step)->name); ?><?php endif; ?></small>
                                                            <?php endif; ?>
                                                            <br>
                                                            <?php if($payroll->employee->status === 'Suspended'): ?>
                                                                <span class="badge bg-warning text-dark">Suspended</span>
                                                            <?php endif; ?>
                                                        <?php else: ?>
                                                            <strong class="text-danger">Employee Not Found</strong>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if(isset($payroll->employee->expected_retirement_date)): ?>
                                                    <?php if(is_string($payroll->employee->expected_retirement_date)): ?>
                                                        <?php echo e(\Carbon\Carbon::parse($payroll->employee->expected_retirement_date)->format('M d, Y')); ?>

                                                    <?php else: ?>
                                                        <?php echo e($payroll->employee->expected_retirement_date->format('M d, Y')); ?>

                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="text-muted">N/A</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-end">
                                                <strong>₦<?php echo e(number_format($payroll->basic_salary, 2)); ?></strong>
                                            </td>
                                            
                                            <!-- Additions Details -->
                                            <td class="text-center">
                                                <div class="d-flex flex-column">
                                                    <?php
                                                        $payrollMonth = $payroll->payroll_month;
                                                        $additions = collect();
                                                        
                                                        // Show additions for Active/Standard payroll and Pension - only exclude Gratuity
                                                        if ($payroll->employee_id && $payroll->payment_type !== 'Gratuity') {
                                                            $additions = \App\Models\Addition::where('employee_id', $payroll->employee_id)
                                                                ->where(function($query) use ($payrollMonth) {
                                                                    $query->where('start_date', '<=', $payrollMonth)
                                                                          ->where(function($q) use ($payrollMonth) {
                                                                              $q->whereNull('end_date')
                                                                                ->orWhere('end_date', '>=', $payrollMonth);
                                                                          });
                                                                })
                                                                ->with('additionType')
                                                                ->get();
                                                        }
                                                    ?>
                                                    <div class="fw-bold text-success">₦<?php echo e(number_format($additions->sum('amount'), 2)); ?></div>
                                                    <div class="small">
                                                        <?php if($additions->count() > 0): ?>
                                                            <div class="dropdown">
                                                                <a class="btn btn-sm btn-outline-success dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                                                    <?php echo e($additions->count()); ?> items
                                                                </a>
                                                                <div class="dropdown-menu p-2" style="max-width: 300px;">
                                                                    <?php $__currentLoopData = $additions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $addition): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                        <div class="dropdown-item-text">
                                                                            <span class="badge bg-success text-white"><?php echo e($addition->additionType->name); ?>: ₦<?php echo e(number_format($addition->amount, 2)); ?></span>
                                                                        </div>
                                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                                </div>
                                                            </div>
                                                        <?php else: ?>
                                                            <span class="text-muted">No additions</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            
                                            <!-- Deductions Details -->
                                            <td class="text-center">
                                                <div class="d-flex flex-column">
                                                    <?php
                                                        $deductions = collect();
                                                        
                                                        // Show deductions for Active/Standard payroll and Pension - only exclude Gratuity
                                                        // Also: For Pension payrolls where the employee is Contract/Casual,
                                                        // deductions should NOT be shown (they only apply to the staff/contract payroll)
                                                        $skipDeductionsForPension = false;
                                                        if ($payroll->payment_type === 'Pension' && $payroll->employee && $payroll->employee->appointmentType) {
                                                            $skipDeductionsForPension = in_array($payroll->employee->appointmentType->name, ['Casual', 'Contract']);
                                                        }
                                                        
                                                        if ($payroll->employee_id && $payroll->payment_type !== 'Gratuity' && !$skipDeductionsForPension) {
                                                            $deductions = \App\Models\Deduction::where('employee_id', $payroll->employee_id)
                                                                ->where(function($query) use ($payrollMonth) {
                                                                    $query->where('start_date', '<=', $payrollMonth)
                                                                          ->where(function($q) use ($payrollMonth) {
                                                                              $q->whereNull('end_date')
                                                                                ->orWhere('end_date', '>=', $payrollMonth);
                                                                          });
                                                                })
                                                                ->get();
                                                        }
                                                    ?>
                                                    <div class="fw-bold text-danger">₦<?php echo e(number_format($deductions->sum('amount'), 2)); ?></div>
                                                    <div class="small">
                                                        <?php if($deductions->count() > 0): ?>
                                                            <div class="dropdown">
                                                                <a class="btn btn-sm btn-outline-danger dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                                                    <?php echo e($deductions->count()); ?> items
                                                                </a>
                                                                <div class="dropdown-menu p-2" style="max-width: 300px;">
                                                                    <?php $__currentLoopData = $deductions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $deduction): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                        <div class="dropdown-item-text">
                                                                            <span class="badge bg-danger text-white"><?php echo e($deduction->deduction_type); ?>: ₦<?php echo e(number_format($deduction->amount, 2)); ?></span>
                                                                        </div>
                                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                                </div>
                                                            </div>
                                                        <?php else: ?>
                                                            <span class="text-muted">No deductions</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            
                                            <td class="text-end">
                                                <strong class="text-primary">₦<?php echo e(number_format($payroll->net_salary, 2)); ?></strong>
                                            </td>
                                            <td>
                                                <span class="badge 
                                                    <?php if($payroll->status === 'Approved'): ?> bg-success
                                                    <?php elseif($payroll->status === 'Paid'): ?> bg-success
                                                    <?php elseif($payroll->status === 'Pending Final Approval'): ?> bg-info
                                                    <?php elseif($payroll->status === 'Processed'): ?> bg-primary
                                                    <?php elseif($payroll->status === 'Under Review'): ?> bg-warning text-dark
                                                    <?php elseif($payroll->status === 'Reviewed'): ?> bg-info
                                                    <?php elseif($payroll->status === 'Pending Review'): ?> bg-secondary
                                                    <?php elseif($payroll->status === 'Rejected'): ?> bg-danger
                                                    <?php else: ?> bg-secondary <?php endif; ?>">
                                                    <?php echo e($payroll->status); ?>

                                                </span>
                                            </td>
                                            <td><?php echo e($payroll->payment_date ? $payroll->payment_date->format('M d, Y') : 'Pending'); ?></td>
                                            <td>
                                                <?php if($payroll->payroll_month): ?>
                                                    <?php if(is_string($payroll->payroll_month)): ?>
                                                        <?php echo e(\Carbon\Carbon::parse($payroll->payroll_month)->format('M Y')); ?>

                                                    <?php else: ?>
                                                        <?php echo e($payroll->payroll_month->format('M Y')); ?>

                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="text-muted">N/A</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if($payroll->payment_type === 'Gratuity'): ?>
                                                    <span class="badge bg-purple text-white" style="background-color: #6f42c1;">Gratuity</span>
                                                <?php elseif($payroll->payment_type === 'Pension'): ?>
                                                    <span class="badge bg-info text-white">Pension</span>
                                                <?php elseif($payroll->payment_type === 'Casual'): ?>
                                                    <span class="badge bg-warning text-dark">Casual</span>
                                                <?php elseif($payroll->payment_type === 'Contract'): ?>
                                                    <span class="badge bg-secondary text-white">Contract</span>
                                                <?php elseif($payroll->payment_type === 'Permanent'): ?>
                                                    <span class="badge bg-success text-white">Permanent</span>
                                                <?php else: ?>
                                                    <span class="badge bg-light text-dark">Regular</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" id="actionsDropdown<?php echo e($payroll->payroll_id); ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="fas fa-cog me-1"></i>Actions
                                                    </button>
                                                    <ul class="dropdown-menu" aria-labelledby="actionsDropdown<?php echo e($payroll->payroll_id); ?>">
                                                        <li>
                                                            <a class="dropdown-item" href="<?php echo e(route('payroll.show', $payroll->payroll_id)); ?>">
                                                                <i class="fas fa-eye me-2 text-info"></i> View Details
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item" href="<?php echo e(route('payroll.payslip', $payroll->payroll_id)); ?>">
                                                                <i class="fas fa-download me-2 text-success"></i> Download Pay Slip
                                                            </a>
                                                        </li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li>
                                                            <a class="dropdown-item" href="<?php echo e(route('payroll.deductions.show', $payroll->employee_id)); ?>">
                                                                <i class="fas fa-minus-circle me-2 text-danger"></i> Manage Deductions
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item" href="<?php echo e(route('payroll.additions.show', $payroll->employee_id)); ?>">
                                                                <i class="fas fa-plus-circle me-2 text-success"></i> Manage Additions
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item" href="<?php echo e(route('payroll.adjustments.manage')); ?>">
                                                                <i class="fas fa-users-cog me-2 text-primary"></i> Manage All Adjustments
                                                            </a>
                                                        </li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <?php if($payroll->status === 'Pending Review'): ?>
                                                            <li>
                                                                <a class="dropdown-item" href="<?php echo e(route('payroll.send-for-review', $payroll->payroll_id)); ?>" 
                                                                   onclick="event.preventDefault(); document.getElementById('send-review-form-<?php echo e($payroll->payroll_id); ?>').submit();">
                                                                    <i class="fas fa-paper-plane me-2 text-warning"></i> Send for Review
                                                                </a>
                                                            </li>
                                                        <?php elseif($payroll->status === 'Under Review'): ?>
                                                            <li>
                                                                <a class="dropdown-item" href="<?php echo e(route('payroll.mark-as-reviewed', $payroll->payroll_id)); ?>" 
                                                                   onclick="event.preventDefault(); document.getElementById('mark-reviewed-form-<?php echo e($payroll->payroll_id); ?>').submit();">
                                                                    <i class="fas fa-check-circle me-2 text-info"></i> Mark as Reviewed
                                                                </a>
                                                            </li>
                                                        <?php elseif($payroll->status === 'Reviewed'): ?>
                                                            <li>
                                                                <a class="dropdown-item" href="<?php echo e(route('payroll.send-for-approval', $payroll->payroll_id)); ?>" 
                                                                   onclick="event.preventDefault(); document.getElementById('send-approval-form-<?php echo e($payroll->payroll_id); ?>').submit();">
                                                                    <i class="fas fa-paper-plane me-2 text-info"></i> Send for Final Approval
                                                                </a>
                                                            </li>
                                                        <?php elseif($payroll->status === 'Pending Final Approval'): ?>
                                                            <li>
                                                                <a class="dropdown-item" href="<?php echo e(route('payroll.final-approve', $payroll->payroll_id)); ?>" 
                                                                   onclick="event.preventDefault(); document.getElementById('final-approve-form-<?php echo e($payroll->payroll_id); ?>').submit();">
                                                                    <i class="fas fa-thumbs-up me-2 text-success"></i> Final Approve
                                                                </a>
                                                            </li>
                                                        <?php endif; ?>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <tr>
                                            <td colspan="12" class="text-center py-5">
                                                <div class="d-flex flex-column align-items-center justify-content-center">
                                                    <i class="fas fa-file-invoice-dollar fa-3x text-muted mb-3"></i>
                                                    <h5 class="text-muted">No payroll records found</h5>
                                                    <p class="text-muted mb-3">
                                                        <?php if(request()->hasAny(['search', 'status', 'month_filter', 'salary_range', 'date_from', 'date_to'])): ?>
                                                            No payroll records match your search criteria.
                                                        <?php else: ?>
                                                            No payroll records have been created yet.
                                                        <?php endif; ?>
                                                    </p>
                                                    <?php if(request()->hasAny(['search', 'status', 'month_filter', 'salary_range', 'date_from', 'date_to'])): ?>
                                                        <a href="<?php echo e(route('payroll.index')); ?>" class="btn btn-outline-primary">
                                                            <i class="fas fa-undo me-1"></i> Clear filters and view all records
                                                        </a>
                                                   
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="card-footer bg-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-muted">
                                Showing <?php echo e($payrolls->firstItem() ?? 0); ?> to <?php echo e($payrolls->lastItem() ?? 0); ?> 
                                of <?php echo e($payrolls->total()); ?> records
                            </div>
                            <div>
                                <?php echo e($payrolls->appends(request()->query())->links('pagination::bootstrap-5')); ?>

                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Bulk Operations Section -->
            <section>
                <div class="card border-info shadow-sm">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-layer-group me-2"></i>Bulk Operations
                        </h6>
                    </div>
                    <div class="card-body">
                        <form id="bulk-actions-form" method="POST" action="">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="select_all_pages" value="1">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-3">
                                    <label for="bulk_action" class="form-label">Select Action</label>
                                    <select name="bulk_action" id="bulk_action" class="form-select">
                                        <option value="">Choose an action...</option>
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('bulk_send_payroll_for_review')): ?>
                                        <option value="send-for-review">Send All for Review</option>
                                        <?php endif; ?>
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('bulk_mark_payroll_as_reviewed')): ?>
                                        <option value="mark-as-reviewed">Mark All as Reviewed</option>
                                        <?php endif; ?>
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('bulk_send_payroll_for_approval')): ?>
                                        <option value="send-for-approval">Send All for Final Approval</option>
                                        <?php endif; ?>
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('bulk_final_approve_payroll')): ?>
                                        <option value="final-approve">Final Approve All</option>
                                        <?php endif; ?>
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('bulk_update_payroll_status')): ?>
                                        <option value="bulk-update-status">Update All Status</option>
                                        <?php endif; ?>
                                    </select>
                                </div>
                                <div id="status-select-container" class="col-md-3" style="display: none;">
                                    <label for="new_status" class="form-label">New Status</label>
                                    <select name="new_status" id="new_status" class="form-select">
                                        <option value="Pending">Pending</option>
                                        <option value="Processed">Processed</option>
                                        <option value="Under Review">Under Review</option>
                                        <option value="Reviewed">Reviewed</option>
                                        <option value="Pending Final Approval">Pending Final Approval</option>
                                        <option value="Approved">Approved</option>
                                        <option value="Paid">Paid</option>
                                        <option value="Rejected">Rejected</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <button type="button" class="btn btn-primary w-100" id="execute-bulk-action">
                                        <i class="fas fa-bolt me-1"></i> Execute Action
                                    </button>
                                </div>
                            </div>
                            <div class="mt-3 p-3 bg-light rounded">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Note: This will apply the selected action to ALL payroll records that match the current filters (<?php echo e($payrolls->total()); ?> total records).
                                </small>
                            </div>
                        </form>
                    </div>
                </div>
            </section>

            <!-- Hidden Bulk Action Forms -->
            <form id="bulk-send-review-form" action="<?php echo e(route('payroll.bulk_send_for_review')); ?>" method="POST" style="display: none;">
                <?php echo csrf_field(); ?>
            </form>
            <form id="bulk-mark-reviewed-form" action="<?php echo e(route('payroll.bulk_mark_as_reviewed')); ?>" method="POST" style="display: none;">
                <?php echo csrf_field(); ?>
            </form>
            <form id="bulk-send-approval-form" action="<?php echo e(route('payroll.bulk_send_for_approval')); ?>" method="POST" style="display: none;">
                <?php echo csrf_field(); ?>
            </form>
            <form id="bulk-final-approve-form" action="<?php echo e(route('payroll.bulk_final_approve')); ?>" method="POST" style="display: none;">
                <?php echo csrf_field(); ?>
            </form>
            <form id="bulk-request-delete-form" action="<?php echo e(route('payroll.bulk_request_delete')); ?>" method="POST" style="display: none;">
                <?php echo csrf_field(); ?>
            </form>
            <form id="bulk-approve-delete-form" action="<?php echo e(route('payroll.bulk_approve_delete')); ?>" method="POST" style="display: none;">
                <?php echo csrf_field(); ?>
            </form>

          
        </div>
    </div>
</div>

<!-- JavaScript for Payroll Page -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // Initialize all components
    initializeAutoSubmitFilters();
    initializeBulkActions();
    initializeBulkButtons();
    
    /* Auto-submit filters when changed */
    function initializeAutoSubmitFilters() {
        const autoSubmitFields = ['status', 'month_filter', 'salary_range', 'sort_by', 'sort_direction', 'per_page'];
        
        autoSubmitFields.forEach(fieldName => {
            const field = document.getElementById(fieldName);
            if (field) {
                field.addEventListener('change', function() {
                    this.form.submit();
                });
            }
        });
    }
    
    /* Initialize bulk action controls */
    function initializeBulkActions() {
        // Handle status select visibility based on selected action
        const bulkActionSelect = document.getElementById('bulk_action');
        const statusSelectContainer = document.getElementById('status-select-container');
        
        if (bulkActionSelect && statusSelectContainer) {
            bulkActionSelect.addEventListener('change', function() {
                statusSelectContainer.style.display = (this.value === 'bulk-update-status') ? 'block' : 'none';
            });
        }
        
        // Handle bulk action execution for all records
        const executeBulkActionBtn = document.getElementById('execute-bulk-action');
        if (executeBulkActionBtn && bulkActionSelect) {
            executeBulkActionBtn.addEventListener('click', function() {
                executeBulkAction(bulkActionSelect.value);
            });
        }
        
        // Select all payroll checkboxes
        const selectAllCheckboxes = document.getElementById('select-all-payrolls');
        if (selectAllCheckboxes) {
            selectAllCheckboxes.addEventListener('change', function() {
                const checkboxes = document.querySelectorAll('input[name="payroll_ids[]"]');
                checkboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
            });
        }

        /* Check Payroll Status for Regeneration */
        const monthInput = document.getElementById('month');
        const categorySelect = document.getElementById('payroll_category');
        const generateBtn = document.getElementById('generate-payroll-btn');
        const generateBtnText = generateBtn ? generateBtn.querySelector('span') : null;
        const generateBtnIcon = generateBtn ? generateBtn.querySelector('i') : null;
        
        // Data passed from controller
        const payrollStatusMap = <?php echo json_encode($payrollStatusMap ?? [], 15, 512) ?>;

        function checkPayrollStatus() {
            if (!monthInput || !categorySelect || !generateBtn) return;

            const month = monthInput.value;
            const category = categorySelect.value;
            
            // Get status from local data
            const statusData = payrollStatusMap[month] ? payrollStatusMap[month][category] : null;

            if (statusData && statusData.exists) {
                if (statusData.approved) {
                    generateBtn.className = 'btn btn-secondary w-100';
                    if(generateBtnText) generateBtnText.textContent = 'Already Approved';
                    if(generateBtnIcon) generateBtnIcon.className = 'fas fa-lock me-1';
                    generateBtn.disabled = true;
                    generateBtn.title = 'Payroll for this month is already approved/paid and cannot be regenerated.';
                } else {
                    generateBtn.className = 'btn btn-warning w-100';
                    if(generateBtnText) generateBtnText.textContent = 'Regenerate';
                    if(generateBtnIcon) generateBtnIcon.className = 'fas fa-sync-alt me-1';
                    generateBtn.disabled = false;
                    generateBtn.title = 'Payroll exists but is not approved. Clicking this will DELETE existing records and regenerate them. WARNING: Any manual edits to payroll records will be lost!';
                }
            } else {
                generateBtn.className = 'btn btn-primary w-100';
                if(generateBtnText) generateBtnText.textContent = 'Generate';
                if(generateBtnIcon) generateBtnIcon.className = 'fas fa-cogs me-1';
                generateBtn.title = '';
                generateBtn.disabled = false;
            }
        }

        if (monthInput && categorySelect) {
            monthInput.addEventListener('change', checkPayrollStatus);
            categorySelect.addEventListener('change', checkPayrollStatus);
            // Check status on load
            checkPayrollStatus();
        }
    }
    
    // Execute the selected bulk action
    function executeBulkAction(action) {
        if (!action) {
            alert('Please select an action.');
            return;
        }
        
        // Safety Check for Delete Request
        if (action === 'request-delete') {
            const monthFilter = document.getElementById('month_filter');
            if (monthFilter && !monthFilter.value) {
                alert('Action Aborted: Please select a specific Payroll Month in the filters before checking "Select All" to request deletion. This prevents accidental deletion of all records.');
                return;
            }
        }
        
        // Confirm action before proceeding
        const recordCount = <?php echo e($payrolls->total()); ?>;
        const confirmationMessage = getConfirmationMessage(action, recordCount);
        
        if (!confirm(confirmationMessage)) {
            return;
        }
        
        // Get action URL based on selected action
        const actionUrl = getActionUrl(action);
        if (!actionUrl) {
            alert('Invalid action selected.');
            return;
        }
        
        // Set the form action
        const form = document.getElementById('bulk-actions-form');
        if (form) {
            form.action = actionUrl;
            
            // Append current filter values to the form so they are respected by the bulk action
            const filters = ['search', 'status', 'employee_status', 'appointment_type', 'month_filter', 'salary_range', 'date_from', 'date_to', 'sort_by', 'sort_direction', 'per_page'];
            
            filters.forEach(filterName => {
                const input = document.getElementById(filterName);
                // Remove existing hidden input if any to avoid duplicates
                const existing = form.querySelector(`input[name="${filterName}"]`);
                if (existing) {
                    existing.remove();
                }
                
                if (input && input.value) {
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = filterName;
                    hiddenInput.value = input.value;
                    form.appendChild(hiddenInput);
                }
            });

            form.submit();
        }
    }
    
    // Get confirmation message based on action
    function getConfirmationMessage(action, recordCount) {
        switch (action) {
            case 'request-delete':
                return `Are you sure you want to REQUEST DELETION for ${recordCount} payroll records? This will marking them for deletion.`;
            case 'approve-delete':
                return `Are you sure you want to PERMANENTLY DELETE ${recordCount} payroll records? This action cannot be undone!`;
            case 'send-for-review':
                return `Are you sure you want to send ALL matching records for review? This will affect ${recordCount} records.`;
            case 'mark-as-reviewed':
                return `Are you sure you want to mark ALL matching records as reviewed? This will affect ${recordCount} records.`;
            case 'send-for-approval':
                return `Are you sure you want to send ALL matching records for final approval? This will affect ${recordCount} records.`;
            case 'final-approve':
                return `Are you sure you want to final approve ALL matching records? This will affect ${recordCount} records.`;
            case 'bulk-update-status':
                return `Are you sure you want to update the status of ALL matching records? This will affect ${recordCount} records.`;
            default:
                return 'Invalid action selected.';
        }
    }
    
    // Get action URL based on the selected action
    function getActionUrl(action) {
        switch (action) {
            case 'send-for-review':
                return '<?php echo e(route("payroll.bulk_send_for_review")); ?>';
            case 'mark-as-reviewed':
                return '<?php echo e(route("payroll.bulk_mark_as_reviewed")); ?>';
            case 'send-for-approval':
                return '<?php echo e(route("payroll.bulk_send_for_approval")); ?>';
            case 'final-approve':
                return '<?php echo e(route("payroll.bulk_final_approve")); ?>';
            case 'request-delete':
                return '<?php echo e(route("payroll.bulk_request_delete")); ?>';
            case 'approve-delete':
                return '<?php echo e(route("payroll.bulk_approve_delete")); ?>';
            case 'bulk-update-status':
                return '<?php echo e(route("payroll.bulk_update_status")); ?>';
            default:
                return null;
        }
    }
    
    // Initialize bulk operation buttons
    function initializeBulkButtons() {
        const bulkSendReviewBtn = document.getElementById('bulk-send-review');
        const bulkMarkReviewedBtn = document.getElementById('bulk-mark-reviewed');
        const bulkSendApprovalBtn = document.getElementById('bulk-send-approval');
        const bulkFinalApproveBtn = document.getElementById('bulk-final-approve');
        const bulkActionSelect = document.getElementById('bulk_action');
        const executeBulkActionBtn = document.getElementById('execute-bulk-action');
        
        // Hide buttons if user doesn't have permission
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->denies('bulk_send_payroll_for_review')): ?>
        if (bulkSendReviewBtn) {
            bulkSendReviewBtn.style.display = 'none';
        }
        <?php endif; ?>
        
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->denies('bulk_mark_payroll_as_reviewed')): ?>
        if (bulkMarkReviewedBtn) {
            bulkMarkReviewedBtn.style.display = 'none';
        }
        <?php endif; ?>
        
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->denies('bulk_send_payroll_for_approval')): ?>
        if (bulkSendApprovalBtn) {
            bulkSendApprovalBtn.style.display = 'none';
        }
        <?php endif; ?>
        
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->denies('bulk_final_approve_payroll')): ?>
        if (bulkFinalApproveBtn) {
            bulkFinalApproveBtn.style.display = 'none';
        }
        <?php endif; ?>
        
        if (bulkSendReviewBtn) {
            bulkSendReviewBtn.addEventListener('click', function() {
                if (bulkActionSelect && executeBulkActionBtn) {
                    bulkActionSelect.value = 'send-for-review';
                    executeBulkActionBtn.click();
                }
            });
        }
        
        if (bulkMarkReviewedBtn) {
            bulkMarkReviewedBtn.addEventListener('click', function() {
                if (bulkActionSelect && executeBulkActionBtn) {
                    bulkActionSelect.value = 'mark-as-reviewed';
                    executeBulkActionBtn.click();
                }
            });
        }
        
        if (bulkSendApprovalBtn) {
            bulkSendApprovalBtn.addEventListener('click', function() {
                if (bulkActionSelect && executeBulkActionBtn) {
                    bulkActionSelect.value = 'send-for-approval';
                    executeBulkActionBtn.click();
                }
            });
        }
        
        if (bulkFinalApproveBtn) {
            bulkFinalApproveBtn.addEventListener('click', function() {
                if (bulkActionSelect && executeBulkActionBtn) {
                    bulkActionSelect.value = 'final-approve';
                    executeBulkActionBtn.click();
                }
            });
        }
    }
    
});
</script>

<style>
/* Additional styling for better presentation */
.table th, .table td {
    vertical-align: middle;
}

.badge {
    font-size: 0.75em;
    margin-bottom: 2px;
    display: inline-block;
}

.table-responsive {
    max-height: 80vh;
    overflow-y: auto;
}

/* Sticky header for better UX */
.table thead th {
    position: sticky;
    top: 0;
    background-color: var(--bs-primary);
    z-index: 10;
}

/* Better spacing for adjustment details */
.small .badge {
    white-space: nowrap;
}

/* Card header styling */
.card-header {
    border-radius: 0.375rem 0.375rem 0 0;
}

/* Improved styling for filter section */
.card.border-info .card-header {
    background-color: #17a2b8 !important;
}

/* Improved styling for primary card */
.card.border-primary .card-header {
    background-color: #0d6efd !important;
}

/* Button styling */
.btn {
    border-radius: 0.375rem;
}

/* Input styling */
.form-control:focus,
.form-select:focus {
    border-color: #86b7fe;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

/* Improved dropdown styling */
.dropdown-item {
    padding: 0.5rem 1rem;
}

.dropdown-item i {
    width: 1.5em;
}

/* Consistent card spacing */
.card {
    margin-bottom: 1.5rem;
}

/* Improved spacing in forms */
.form-label {
    font-weight: 500;
}

/* Consistent button sizing */
.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

/* Better styling for table actions */
.table .dropdown-toggle {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

/* Improved pagination styling */
.page-item.active .page-link {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

/* Consistent border styling */
.border-primary, .border-info {
    border-width: 2px;
}

/* Background color for form sections */
.active-filters {
    background-color: rgba(0, 0, 0, 0.03);
    padding: 0.75rem;
    border-radius: 0.375rem;
}
</style>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Rowwww\Herd\hrm_payroll_system_design\resources\views/payroll/index.blade.php ENDPATH**/ ?>