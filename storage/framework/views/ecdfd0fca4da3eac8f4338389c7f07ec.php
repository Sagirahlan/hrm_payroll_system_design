<?php $__env->startSection('styles'); ?>
<style>
    :root {
        --primary-color: #2c3e50;
        --secondary-color: #34495e;
        --accent-blue: #3498db;
        --accent-green: #27ae60;
        --accent-orange: #e67e22;
        --accent-red: #e74c3c;
        --text-primary: #2c3e50;
        --text-secondary: #7f8c8d;
        --border-color: #ecf0f1;
        --bg-light: #f8f9fa;
        --white: #ffffff;
        --shadow-sm: 0 2px 4px rgba(0, 0, 0, 0.08);
        --shadow-md: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    [data-bs-theme="dark"] {
        --primary-color: #c7d0db;
        --secondary-color: #a0aec0;
        --accent-blue: #60a5fa;
        --accent-green: #34d399;
        --accent-orange: #fbbf24;
        --accent-red: #f87171;
        --text-primary: #e2e8f0;
        --text-secondary: #94a3b8;
        --border-color: #374151;
        --bg-light: #1e293b;
        --white: #1e293b;
        --shadow-sm: 0 2px 4px rgba(0, 0, 0, 0.25);
        --shadow-md: 0 4px 12px rgba(0, 0, 0, 0.3);
    }

    [data-bs-theme="dark"] .page-header {
        background: #1e293b;
        color: var(--text-primary);
    }

    [data-bs-theme="dark"] .stat-box {
        background: #1e293b;
        color: var(--text-primary);
        border: 1px solid #374151;
    }

    [data-bs-theme="dark"] .stat-number {
        color: var(--text-primary);
    }

    [data-bs-theme="dark"] .card-professional {
        background: #1e293b;
        border: 1px solid #374151;
    }

    [data-bs-theme="dark"] .card-professional-header {
        background: #1a222e;
        border-bottom: 2px solid #374151;
        color: var(--text-primary);
    }

    [data-bs-theme="dark"] .card-professional-title {
        color: var(--text-primary);
    }

    [data-bs-theme="dark"] .data-table thead th {
        background: #1a222e;
        color: var(--text-primary);
        border-bottom: 2px solid #374151;
    }

    [data-bs-theme="dark"] .data-table tbody td {
        color: var(--text-primary);
        border-bottom: 1px solid #374151;
    }

    [data-bs-theme="dark"] .data-table tbody tr:hover {
        background: #2d3748;
    }

    [data-bs-theme="dark"] .info-section {
        background: #1e293b;
        border: 1px solid #374151;
    }

    [data-bs-theme="dark"] .section-heading {
        color: var(--text-primary);
        border-bottom: 2px solid #374151;
    }

    [data-bs-theme="dark"] .info-detail-item {
        background: #1a222e;
        border: 1px solid #374151;
    }

    [data-bs-theme="dark"] .department-row {
        background: #1a222e;
        border: 1px solid #374151;
    }

    [data-bs-theme="dark"] .department-row:hover {
        background: #2d3748;
        border-color: #4b5563;
    }

    [data-bs-theme="dark"] .department-title {
        color: var(--text-primary);
    }

    [data-bs-theme="dark"] .department-value {
        color: var(--accent-blue);
    }

    [data-bs-theme="dark"] .badge-pro {
        color: #e2e8f0;
    }

    [data-bs-theme="dark"] .badge-pro.primary {
        background: #1e3a8a;
        color: #e3f2fd;
        border: 1px solid #1e40af;
    }

    [data-bs-theme="dark"] .badge-pro.success {
        background: #166534;
        color: #e8f5e9;
        border: 1px solid #166534;
    }

    [data-bs-theme="dark"] .badge-pro.warning {
        background: #92400e;
        color: #fff3e0;
        border: 1px solid #c2410c;
    }

    [data-bs-theme="dark"] .badge-pro.danger {
        background: #7f1d1d;
        color: #ffebee;
        border: 1px solid #991b1b;
    }

    [data-bs-theme="dark"] .badge-pro.secondary {
        background: #374151;
        color: #f3f4f6;
        border: 1px solid #4b5563;
    }

    [data-bs-theme="dark"] .badge-pro.info {
        background: #0369a1;
        color: #e0f7fa;
        border: 1px solid #0284c7;
    }

    [data-bs-theme="dark"] .empty-state-container {
        color: #94a3b8;
    }

    [data-bs-theme="dark"] .empty-state-container i {
        color: #94a3b8;
    }

    body {
        background: #f4f6f8;
        font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        color: var(--text-primary);
    }

    .dashboard-wrapper {
        max-width: 1400px;
        margin: 0 auto;
        padding: 2rem 1.5rem;
    }

    /* Header Section */
    .page-header {
        background: var(--white);
        border-radius: 8px;
        padding: 2rem 2.5rem;
        margin-bottom: 2rem;
        box-shadow: var(--shadow-sm);
        border-left: 4px solid var(--accent-blue);
    }

    .page-title {
        font-size: 1.75rem;
        font-weight: 600;
        color: var(--text-primary);
        margin: 0 0 0.5rem 0;
        letter-spacing: -0.5px;
    }

    .page-subtitle {
        font-size: 0.95rem;
        color: var(--text-secondary);
        margin: 0;
        font-weight: 400;
    }

    /* Statistics Grid */
    .stats-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stat-box {
        background: var(--white);
        border-radius: 8px;
        padding: 1.75rem;
        box-shadow: var(--shadow-sm);
        border: 1px solid var(--border-color);
        transition: all 0.2s ease;
        position: relative;
    }

    .stat-box:hover {
        box-shadow: var(--shadow-md);
        transform: translateY(-2px);
    }

    .stat-box::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
        border-radius: 8px 0 0 8px;
    }

    .stat-box.primary::before { background: var(--accent-blue); }
    .stat-box.success::before { background: var(--accent-green); }
    .stat-box.warning::before { background: var(--accent-orange); }
    .stat-box.danger::before { background: var(--accent-red); }
    .stat-box.secondary::before { background: var(--secondary-color); }

    .stat-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 0.75rem;
    }

    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        color: var(--white);
    }

    .stat-icon.primary { background: var(--accent-blue); }
    .stat-icon.success { background: var(--accent-green); }
    .stat-icon.warning { background: var(--accent-orange); }
    .stat-icon.danger { background: var(--accent-red); }
    .stat-icon.secondary { background: var(--secondary-color); }

    .stat-number {
        font-size: 2rem;
        font-weight: 700;
        color: var(--text-primary);
        line-height: 1;
        margin-bottom: 0.5rem;
    }

    .stat-title {
        font-size: 0.875rem;
        color: var(--text-secondary);
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Content Cards */
    .content-layout {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 2rem;
    }

    .card-professional {
        background: var(--white);
        border-radius: 8px;
        box-shadow: var(--shadow-sm);
        border: 1px solid var(--border-color);
        overflow: hidden;
    }

    .card-professional-header {
        padding: 1.5rem 2rem;
        border-bottom: 2px solid var(--border-color);
        background: var(--bg-light);
    }

    .card-professional-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: var(--text-primary);
        margin: 0;
        display: flex;
        align-items: center;
    }

    .card-professional-title i {
        margin-right: 0.75rem;
        color: var(--accent-blue);
        font-size: 1rem;
    }

    .card-professional-body {
        padding: 0;
    }

    /* Tables */
    .data-table {
        width: 100%;
        border-collapse: collapse;
    }

    .data-table thead th {
        background: var(--bg-light);
        padding: 1rem 1.5rem;
        text-align: left;
        font-weight: 600;
        color: var(--text-primary);
        font-size: 0.875rem;
        border-bottom: 2px solid var(--border-color);
    }

    .data-table tbody td {
        padding: 1rem 1.5rem;
        border-bottom: 1px solid var(--border-color);
        color: var(--text-primary);
        font-size: 0.9rem;
    }

    .data-table tbody tr:hover {
        background: #fafbfc;
    }

    .data-table tbody tr:last-child td {
        border-bottom: none;
    }

    /* Badges */
    .badge-pro {
        display: inline-block;
        padding: 0.35rem 0.75rem;
        border-radius: 4px;
        font-size: 0.8rem;
        font-weight: 500;
        line-height: 1;
    }

    .badge-pro.primary {
        background: #e3f2fd;
        color: #1976d2;
        border: 1px solid #bbdefb;
    }

    .badge-pro.success {
        background: #e8f5e9;
        color: #388e3c;
        border: 1px solid #c8e6c9;
    }

    .badge-pro.warning {
        background: #fff3e0;
        color: #e65100;
        border: 1px solid #ffe0b2;
    }

    .badge-pro.danger {
        background: #ffebee;
        color: #c62828;
        border: 1px solid #ffcdd2;
    }

    .badge-pro.secondary {
        background: #f5f5f5;
        color: #616161;
        border: 1px solid #e0e0e0;
    }

    .badge-pro.info {
        background: #e0f7fa;
        color: #00838f;
        border: 1px solid #b2ebf2;
    }

    /* Department List */
    .department-list {
        padding: 1.5rem;
    }

    .department-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem 1.25rem;
        background: var(--bg-light);
        border-radius: 6px;
        margin-bottom: 0.75rem;
        border: 1px solid var(--border-color);
        transition: all 0.2s ease;
    }

    .department-row:last-child {
        margin-bottom: 0;
    }

    .department-row:hover {
        background: #f0f3f5;
        border-color: #d5dce3;
    }

    .department-title {
        font-weight: 600;
        color: var(--text-primary);
        font-size: 0.95rem;
    }

    .department-value {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--accent-blue);
    }

    /* Employee Info Section */
    .info-section {
        background: var(--white);
        border-radius: 8px;
        padding: 2rem;
        box-shadow: var(--shadow-sm);
        border: 1px solid var(--border-color);
        margin-bottom: 2rem;
    }

    .section-heading {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--text-primary);
        margin: 0 0 1.5rem 0;
        padding-bottom: 1rem;
        border-bottom: 2px solid var(--border-color);
    }

    .info-details-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 1.5rem;
    }

    .info-detail-item {
        padding: 1.25rem;
        background: var(--bg-light);
        border-radius: 6px;
        border: 1px solid var(--border-color);
        border-left: 3px solid;
    }

    .info-detail-item.primary { border-left-color: var(--accent-blue); }
    .info-detail-item.success { border-left-color: var(--accent-green); }
    .info-detail-item.warning { border-left-color: var(--accent-orange); }
    .info-detail-item.info { border-left-color: #00bcd4; }

    .info-detail-label {
        font-size: 0.8rem;
        color: var(--text-secondary);
        margin-bottom: 0.5rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 500;
    }

    .info-detail-value {
        font-size: 1.125rem;
        font-weight: 600;
        color: var(--text-primary);
    }

    /* Empty State */
    .empty-state-container {
        text-align: center;
        padding: 3rem 2rem;
        color: var(--text-secondary);
    }

    .empty-state-container i {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.3;
        color: var(--text-secondary);
    }

    .empty-state-container p {
        margin: 0;
        font-size: 0.95rem;
    }

    /* Responsive Design */
    @media (max-width: 1024px) {
        .content-layout {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .dashboard-wrapper {
            padding: 1rem;
        }

        .page-header {
            padding: 1.5rem;
        }

        .page-title {
            font-size: 1.5rem;
        }

        .stats-container {
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 1rem;
        }

        .stat-box {
            padding: 1.25rem;
        }

        .stat-number {
            font-size: 1.75rem;
        }

        .info-details-grid {
            grid-template-columns: 1fr;
        }

        .data-table thead th,
        .data-table tbody td {
            padding: 0.75rem 1rem;
            font-size: 0.85rem;
        }
    }

    /* Utility Classes */
    .mb-0 { margin-bottom: 0; }
    .mb-2 { margin-bottom: 1rem; }
    .mb-3 { margin-bottom: 1.5rem; }
    .text-truncate {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="dashboard-wrapper">
    <?php if(auth()->user()->hasRole('employee')): ?>
        
        <div class="page-header">
            <h1 class="page-title">Welcome, <?php echo e(auth()->user()->username); ?></h1>
            <p class="page-subtitle">Employee Dashboard - Personal Overview</p>
        </div>

        
        <div class="info-section">
            <h2 class="section-heading">
                <i class="fas fa-id-card"></i> Employment Information
            </h2>
            <div class="info-details-grid">
                <div class="info-detail-item primary">
                    <div class="info-detail-label">Department</div>
                    <div class="info-detail-value">
                        <?php echo e(auth()->user()->employee->department->department_name ?? 'N/A'); ?>

                    </div>
                </div>

                <div class="info-detail-item success">
                    <div class="info-detail-label">Employment Status</div>
                    <div class="info-detail-value">
                        <?php echo e(auth()->user()->employee->employment_status ?? 'Active'); ?>

                    </div>
                </div>

                <div class="info-detail-item warning">
                    <div class="info-detail-label">Position/Cadre</div>
                    <div class="info-detail-value">
                        <?php echo e(auth()->user()->employee->cadre->name ?? 'N/A'); ?>

                    </div>
                </div>

                <div class="info-detail-item info">
                    <div class="info-detail-label">Date of Appointment</div>
                    <div class="info-detail-value">
                        <?php echo e(auth()->user()->employee->date_of_first_appointment ? \Carbon\Carbon::parse(auth()->user()->employee->date_of_first_appointment)->format('M d, Y') : 'N/A'); ?>

                    </div>
                </div>
            </div>
        </div>

        
        <div class="info-section">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="section-heading">
                    <i class="fas fa-calendar-check"></i> Leave Management
                </h2>
                <a href="<?php echo e(route('leaves.create.my')); ?>" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Request Leave
                </a>
            </div>

            <!-- Recent Leave Requests -->
            <div class="card-professional">
                <div class="card-professional-header">
                    <h3 class="card-professional-title">
                        <i class="fas fa-list"></i> My Recent Leave Requests
                    </h3>
                </div>
                <div class="card-professional-body">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Period</th>
                                <th>Days</th>
                                <th>Status</th>
                                <th>Date Requested</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                $employeeId = auth()->user()->employee->employee_id ?? null;
                                $recentLeaves = $employeeId ? \App\Models\Models\Leave::where('employee_id', $employeeId)->latest()->take(5)->get() : collect();
                            ?>
                            <?php $__empty_1 = true; $__currentLoopData = $recentLeaves; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $leave): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td><?php echo e($leave->leave_type); ?></td>
                                    <td>
                                        <?php echo e(\Carbon\Carbon::parse($leave->start_date)->format('M d')); ?> - <?php echo e(\Carbon\Carbon::parse($leave->end_date)->format('M d, Y')); ?>

                                    </td>
                                    <td><?php echo e($leave->days_requested); ?></td>
                                    <td>
                                        <?php if($leave->status === 'pending'): ?>
                                            <span class="badge-pro warning">Pending</span>
                                        <?php elseif($leave->status === 'approved'): ?>
                                            <span class="badge-pro success">Approved</span>
                                        <?php else: ?>
                                            <span class="badge-pro danger">Rejected</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo e(\Carbon\Carbon::parse($leave->created_at)->format('M d, Y')); ?></td>
                                    <td>
                                        <a href="<?php echo e(route('leaves.show', $leave->id)); ?>" class="btn btn-sm btn-info">View</a>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="6">
                                        <div class="empty-state-container">
                                            <i class="fas fa-calendar-plus"></i>
                                            <p>No leave requests found</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    <?php if($recentLeaves->count() > 0): ?>
                        <div class="p-3 text-center">
                            <a href="<?php echo e(route('leaves.my')); ?>" class="btn btn-outline-primary">View All Leave Requests</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        
        <div class="card-professional">
            <div class="card-professional-header">
                <h3 class="card-professional-title">
                    <i class="fas fa-clock"></i> Recent Activities
                </h3>
            </div>
            <div class="card-professional-body">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Action</th>
                            <th>Description</th>
                            <th>Date & Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = ($myRecentActivities ?? collect())->where('user_id', auth()->id())->take(10); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $activity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td>
                                    <span class="badge-pro info"><?php echo e($activity->action); ?></span>
                                </td>
                                <td><?php echo e(Str::limit($activity->description, 65)); ?></td>
                                <td>
                                    <span class="badge-pro secondary">
                                        <?php echo e($activity->action_timestamp ? \Carbon\Carbon::parse($activity->action_timestamp)->format('M d, Y g:i A') : 'N/A'); ?>

                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="3">
                                    <div class="empty-state-container">
                                        <i class="fas fa-folder-open"></i>
                                        <p>No recent activities recorded</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    <?php else: ?>
        
        <div class="page-header">
            <h1 class="page-title">Management Dashboard</h1>
            <p class="page-subtitle">Workforce Overview & Analytics</p>
        </div>

        
        <div class="stats-container">
            <div class="stat-box primary">
                <div class="stat-header">
                    <div>
                        <div class="stat-number"><?php echo e($employeeCount ?? 0); ?></div>
                        <div class="stat-title">Total Employees</div>
                    </div>
                    <div class="stat-icon primary">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>

            <div class="stat-box success">
                <div class="stat-header">
                    <div>
                        <div class="stat-number"><?php echo e($activeEmployees ?? 0); ?></div>
                        <div class="stat-title">Active Employees</div>
                    </div>
                    <div class="stat-icon success">
                        <i class="fas fa-user-check"></i>
                    </div>
                </div>
            </div>

            <div class="stat-box warning">
                <div class="stat-header">
                    <div>
                        <div class="stat-number"><?php echo e($suspendedEmployees ?? 0); ?></div>
                        <div class="stat-title">Suspended</div>
                    </div>
                    <div class="stat-icon warning">
                        <i class="fas fa-user-minus"></i>
                    </div>
                </div>
            </div>

            <div class="stat-box danger">
                <div class="stat-header">
                    <div>
                        <div class="stat-number"><?php echo e($holdEmployees ?? 0); ?></div>
                        <div class="stat-title">Hold Status</div>
                    </div>
                    <div class="stat-icon danger">
                        <i class="fas fa-user-lock"></i>
                    </div>
                </div>
            </div>

            <div class="stat-box primary">
                <div class="stat-header">
                    <div>
                        <div class="stat-number"><?php echo e($permanentEmployees ?? 0); ?></div>
                        <div class="stat-title">Permanent Staff</div>
                    </div>
                    <div class="stat-icon primary">
                        <i class="fas fa-id-badge"></i>
                    </div>
                </div>
            </div>

            <div class="stat-box secondary">
                <div class="stat-header">
                    <div>
                        <div class="stat-number"><?php echo e($contractEmployees ?? 0); ?></div>
                        <div class="stat-title">Casual Staff</div>
                    </div>
                    <div class="stat-icon secondary">
                        <i class="fas fa-file-signature"></i>
                    </div>
                </div>
            </div>

            <div class="stat-box secondary">
                <div class="stat-header">
                    <div>
                        <div class="stat-number"><?php echo e($retiredEmployees ?? 0); ?></div>
                        <div class="stat-title">Retired</div>
                    </div>
                    <div class="stat-icon secondary">
                        <i class="fas fa-user-clock"></i>
                    </div>
                </div>
            </div>

            <div class="stat-box danger">
                <div class="stat-header">
                    <div>
                        <div class="stat-number"><?php echo e($deceasedEmployees ?? 0); ?></div>
                        <div class="stat-title">Deceased</div>
                    </div>
                    <div class="stat-icon danger">
                        <i class="fas fa-heart-broken"></i>
                    </div>
                </div>
            </div>

            <div class="stat-box info">
                <div class="stat-header">
                    <div>
                        <div class="stat-number"><?php echo e($maleEmployees ?? 0); ?></div>
                        <div class="stat-title">Male Employees</div>
                    </div>
                    <div class="stat-icon info">
                        <i class="fas fa-mars"></i>
                    </div>
                </div>
            </div>

            <div class="stat-box warning">
                <div class="stat-header">
                    <div>
                        <div class="stat-number"><?php echo e($femaleEmployees ?? 0); ?></div>
                        <div class="stat-title">Female Employees</div>
                    </div>
                    <div class="stat-icon warning">
                        <i class="fas fa-venus"></i>
                    </div>
                </div>
            </div>

            <div class="stat-box success">
                <div class="stat-header">
                    <div>
                        <div class="stat-number"><?php echo e($totalDepartments ?? 0); ?></div>
                        <div class="stat-title">Departments</div>
                    </div>
                    <div class="stat-icon success">
                        <i class="fas fa-building"></i>
                    </div>
                </div>
            </div>

            <div class="stat-box danger">
                <div class="stat-header">
                    <div>
                        <div class="stat-number"><?php echo e($openDisciplinaryActions ?? 0); ?></div>
                        <div class="stat-title">Open Disciplinary Actions</div>
                    </div>
                    <div class="stat-icon danger">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
            </div>

            <div class="stat-box primary">
                <div class="stat-header">
                    <div>
                        <div class="stat-number"><?php echo e($resolvedDisciplinaryActions ?? 0); ?></div>
                        <div class="stat-title">Resolved Disciplinary Actions</div>
                    </div>
                    <div class="stat-icon primary">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>

            <div class="stat-box secondary">
                <div class="stat-header">
                    <div>
                        <div class="stat-number"><?php echo e($payrollRecords ?? 0); ?></div>
                        <div class="stat-title">Current Month Payrolls</div>
                    </div>
                    <div class="stat-icon secondary">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                </div>
            </div>

            <div class="stat-box warning">
                <div class="stat-header">
                    <div>
                        <div class="stat-number"><?php echo e($employeesRetiringWithin6Months->count() ?? 0); ?></div>
                        <div class="stat-title">Retiring Within 6 Months</div>
                    </div>
                    <div class="stat-icon warning">
                        <i class="fas fa-user-clock"></i>
                    </div>
                </div>
            </div>

            <div class="stat-box info">
                <div class="stat-header">
                    <div>
                        <div class="stat-number"><?php echo e($pendingRetirementConfirmations->count() ?? 0); ?></div>
                        <div class="stat-title">Pending Retirement Confirmations</div>
                    </div>
                    <div class="stat-icon info">
                        <i class="fas fa-user-clock"></i>
                    </div>
                </div>
            </div>

            
            <div class="stat-box info">
                <div class="stat-header">
                    <div>
                        <div class="stat-number"><?php echo e($totalPendingApprovals ?? 0); ?></div>
                        <div class="stat-title">Total Pending Approvals</div>
                    </div>
                    <div class="stat-icon info">
                        <i class="fas fa-tasks"></i>
                    </div>
                </div>
            </div>

            <div class="stat-box warning">
                <div class="stat-header">
                    <div>
                        <div class="stat-number"><?php echo e($pendingLeaveRequests ?? 0); ?></div>
                        <div class="stat-title">Pending Leave Requests</div>
                    </div>
                    <div class="stat-icon warning">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                </div>
            </div>

            <div class="stat-box primary">
                <div class="stat-header">
                    <div>
                        <div class="stat-number"><?php echo e($pendingPayrollApprovals ?? 0); ?></div>
                        <div class="stat-title">Pending Payroll Approvals</div>
                    </div>
                    <div class="stat-icon primary">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                </div>
            </div>

            <div class="stat-box danger">
                <div class="stat-header">
                    <div>
                        <div class="stat-number"><?php echo e($pendingProbations ?? 0); ?></div>
                        <div class="stat-title">Pending Probation Reviews</div>
                    </div>
                    <div class="stat-icon danger">
                        <i class="fas fa-clipboard-check"></i>
                    </div>
                </div>
            </div>

            <div class="stat-box success">
                <div class="stat-header">
                    <div>
                        <div class="stat-number"><?php echo e($pendingPromotions ?? 0); ?></div>
                        <div class="stat-title">Pending Promotions</div>
                    </div>
                    <div class="stat-icon success">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
            </div>

            <div class="stat-box secondary">
                <div class="stat-header">
                    <div>
                        <div class="stat-number"><?php echo e($pendingEmployeeChanges ?? 0); ?></div>
                        <div class="stat-title">Pending Employee Changes</div>
                    </div>
                    <div class="stat-icon secondary">
                        <i class="fas fa-user-edit"></i>
                    </div>
                </div>
            </div>

            <div class="stat-box warning">
                <div class="stat-header">
                    <div>
                        <div class="stat-number"><?php echo e($pendingDisciplinaryActions ?? 0); ?></div>
                        <div class="stat-title">Pending Disciplinary Actions</div>
                    </div>
                    <div class="stat-icon warning">
                        <i class="fas fa-gavel"></i>
                    </div>
                </div>
            </div>

        </div>

        
        <?php if($employeesRetiringWithin6Months && $employeesRetiringWithin6Months->count() > 0): ?>
        <div class="card-professional mb-3">
            <div class="card-professional-header">
                <h3 class="card-professional-title">
                    <i class="fas fa-user-clock"></i> Employees Retiring Within 6 Months
                </h3>
            </div>
            <div class="card-professional-body">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Employee Name</th>
                            <th>Department</th>
                            <th>Grade Level</th>
                            <th>Estimated Retirement Date</th>
                            <th>Reason</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            $retirementData = [];
                            foreach ($employeesRetiringWithin6Months as $employee) {
                                $retirementAge = (int) $employee->gradeLevel->salaryScale->max_retirement_age;
                                $yearsOfService = (int) $employee->gradeLevel->salaryScale->max_years_of_service;

                                // Calculate retirement date based on age
                                $retirementDateByAge = \Carbon\Carbon::parse($employee->date_of_birth)->addYears($retirementAge);

                                // Calculate retirement date based on service
                                $retirementDateByService = \Carbon\Carbon::parse($employee->date_of_first_appointment)->addYears($yearsOfService);

                                // The actual retirement date is the earlier of the two
                                $actualRetirementDate = $retirementDateByAge->min($retirementDateByService);

                                // Determine retirement reason
                                $age = \Carbon\Carbon::parse($employee->date_of_birth)->age;
                                $serviceYears = \Carbon\Carbon::parse($employee->date_of_first_appointment)->floatDiffInYears(now());

                                $retirementReason = 'By Years of Service';
                                if ($age >= $retirementAge && $serviceYears < $yearsOfService) {
                                    $retirementReason = 'By Old Age';
                                } elseif ($actualRetirementDate->eq($retirementDateByAge)) {
                                    $retirementReason = 'By Old Age';
                                } else {
                                    $retirementReason = 'By Years of Service';
                                }

                                $retirementData[] = [
                                    'employee' => $employee,
                                    'date' => $actualRetirementDate,
                                    'reason' => $retirementReason
                                ];
                            }

                            // Sort by retirement date (earliest first)
                            usort($retirementData, function($a, $b) {
                                return $a['date']->timestamp - $b['date']->timestamp;
                            });
                        ?>

                        <?php $__currentLoopData = $retirementData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $employee = $item['employee'];
                                $retirementDate = $item['date'];
                                $retirementReason = $item['reason'];

                                $fullName = trim($employee->first_name . ' ' . ($employee->middle_name ?? '') . ' ' . $employee->surname);
                                $retirementReasonDisplay = $retirementReason;
                            ?>
                            <tr>
                                <td><?php echo e($fullName); ?></td>
                                <td><?php echo e($employee->department->department_name ?? 'N/A'); ?></td>
                                <td><?php echo e($employee->gradeLevel->name ?? 'N/A'); ?></td>
                                <td><?php echo e($retirementDate->format('M d, Y')); ?></td>
                                <td>
                                    <span class="badge-pro <?php echo e($retirementReason === 'By Old Age' ? 'warning' : 'info'); ?>">
                                        <?php echo e($retirementReasonDisplay); ?>

                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        
        <?php if($pendingRetirementConfirmations && $pendingRetirementConfirmations->count() > 0): ?>
        <div class="card-professional mb-3">
            <div class="card-professional-header">
                <h3 class="card-professional-title">
                    <i class="fas fa-user-clock"></i> Pending Retirement Confirmations
                </h3>
            </div>
            <div class="card-professional-body">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Employee Name</th>
                            <th>Department</th>
                            <th>Grade Level</th>
                            <th>Age</th>
                            <th>Service Years</th>
                            <th>Eligibility</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $pendingRetirementConfirmations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $age = \Carbon\Carbon::parse($employee->date_of_birth)->age;
                                $serviceDuration = \Carbon\Carbon::parse($employee->date_of_first_appointment)->diffInYears(\Carbon\Carbon::now());
                                $retirementAge = $employee->gradeLevel->salaryScale->max_retirement_age;
                                $maxServiceYears = $employee->gradeLevel->salaryScale->max_years_of_service;
                                $fullName = trim($employee->first_name . ' ' . ($employee->middle_name ?? '') . ' ' . $employee->surname);

                                // Determine eligibility
                                $eligibility = [];
                                if ($age >= $retirementAge) {
                                    $eligibility[] = 'Age (' . $age . ' of ' . $retirementAge . ')';
                                }
                                if ($serviceDuration >= $maxServiceYears) {
                                    $eligibility[] = 'Service (' . $serviceDuration . ' of ' . $maxServiceYears . ' years)';
                                }
                                $eligibilityText = implode(', ', $eligibility);
                            ?>
                            <tr>
                                <td><?php echo e($fullName); ?></td>
                                <td><?php echo e($employee->department->department_name ?? 'N/A'); ?></td>
                                <td><?php echo e($employee->gradeLevel->name ?? 'N/A'); ?></td>
                                <td><?php echo e($age); ?></td>
                                <td><?php echo e($serviceDuration); ?> years</td>
                                <td>
                                    <span class="badge-pro warning">
                                        <?php echo e($eligibilityText); ?>

                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        
        <div class="content-layout">
            
            <div class="card-professional">
                <div class="card-professional-header">
                    <h3 class="card-professional-title">
                        <i class="fas fa-sitemap"></i> Department Distribution
                    </h3>
                </div>
                <div class="card-professional-body">
                    <div class="department-list">
                        <?php if(isset($departments) && $departments->count() > 0): ?>
                            <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $department): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="department-row">
                                    <span class="department-title"><?php echo e($department->department_name); ?></span>
                                    <span class="department-value"><?php echo e($department->employees_count ?? 0); ?></span>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php else: ?>
                            <div class="empty-state-container">
                                <i class="fas fa-building"></i>
                                <p>No departments available</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            
            <div class="card-professional">
                <div class="card-professional-header">
                    <h3 class="card-professional-title">
                        <i class="fas fa-clipboard-list"></i> Recent Audit Trail
                    </h3>
                </div>
                <div class="card-professional-body">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Activity</th>
                                <th>Timestamp</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = ($recentAudits ?? collect())->take(8); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $audit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td>
                                        <span class="badge-pro primary">
                                            <?php echo e($audit->user?->username ?? 'User ' . $audit->user_id); ?>

                                        </span>
                                    </td>
                                    <td class="text-truncate" style="max-width: 250px;">
                                        <?php echo e(Str::limit($audit->description, 45)); ?>

                                    </td>
                                    <td>
                                        <span class="badge-pro secondary">
                                            <?php echo e($audit->action_timestamp ? \Carbon\Carbon::parse($audit->action_timestamp)->format('M d, g:i A') : 'N/A'); ?>

                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="3">
                                        <div class="empty-state-container">
                                            <i class="fas fa-clipboard"></i>
                                            <p>No audit records available</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Smooth hover effects
    const statBoxes = document.querySelectorAll('.stat-box');
    statBoxes.forEach(box => {
        box.addEventListener('mouseenter', function() {
            this.style.borderColor = '#d5dce3';
        });
        box.addEventListener('mouseleave', function() {
            this.style.borderColor = 'var(--border-color)';
        });
    });

    // Table row highlighting
    const tableRows = document.querySelectorAll('.data-table tbody tr');
    tableRows.forEach(row => {
        if (row.querySelector('td:not([colspan])')) {
            row.style.cursor = 'pointer';
        }
    });

    // Department row interactions
    const deptRows = document.querySelectorAll('.department-row');
    deptRows.forEach(row => {
        row.addEventListener('click', function() {
            // Add any click handler logic here
            console.log('Department clicked:', this.querySelector('.department-title').textContent);
        });
    });
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Rowwww\Herd\hrm_payroll_system_design\resources\views/dashboard.blade.php ENDPATH**/ ?>