@extends('layouts.app')

@section('styles')
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
@endsection

@section('content')
<div class="dashboard-wrapper">
    @if(auth()->user()->hasRole('employee'))
        {{-- Employee View --}}
        <div class="page-header">
            <h1 class="page-title">Welcome, {{ auth()->user()->username }}</h1>
            <p class="page-subtitle">Employee Dashboard - Personal Overview</p>
        </div>

        {{-- Employee Information --}}
        <div class="info-section">
            <h2 class="section-heading">
                <i class="fas fa-id-card"></i> Employment Information
            </h2>
            <div class="info-details-grid">
                <div class="info-detail-item primary">
                    <div class="info-detail-label">Department</div>
                    <div class="info-detail-value">
                        {{ auth()->user()->employee->department->department_name ?? 'N/A' }}
                    </div>
                </div>

                <div class="info-detail-item success">
                    <div class="info-detail-label">Employment Status</div>
                    <div class="info-detail-value">
                        {{ auth()->user()->employee->employment_status ?? 'Active' }}
                    </div>
                </div>

                <div class="info-detail-item warning">
                    <div class="info-detail-label">Position/Cadre</div>
                    <div class="info-detail-value">
                        {{ auth()->user()->employee->cadre->name ?? 'N/A' }}
                    </div>
                </div>

                <div class="info-detail-item info">
                    <div class="info-detail-label">Date of Appointment</div>
                    <div class="info-detail-value">
                        {{ auth()->user()->employee->date_of_first_appointment ? \Carbon\Carbon::parse(auth()->user()->employee->date_of_first_appointment)->format('M d, Y') : 'N/A' }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Leave Section for Employees --}}
        <div class="info-section">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="section-heading">
                    <i class="fas fa-calendar-check"></i> Leave Management
                </h2>
                <a href="{{ route('leaves.create.my') }}" class="btn btn-primary">
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
                            @php
                                $employeeId = auth()->user()->employee->employee_id ?? null;
                                $recentLeaves = $employeeId ? \App\Models\Models\Leave::where('employee_id', $employeeId)->latest()->take(5)->get() : collect();
                            @endphp
                            @forelse ($recentLeaves as $leave)
                                <tr>
                                    <td>{{ $leave->leave_type }}</td>
                                    <td>
                                        {{ \Carbon\Carbon::parse($leave->start_date)->format('M d') }} - {{ \Carbon\Carbon::parse($leave->end_date)->format('M d, Y') }}
                                    </td>
                                    <td>{{ $leave->days_requested }}</td>
                                    <td>
                                        @if($leave->status === 'pending')
                                            <span class="badge-pro warning">Pending</span>
                                        @elseif($leave->status === 'approved')
                                            <span class="badge-pro success">Approved</span>
                                        @else
                                            <span class="badge-pro danger">Rejected</span>
                                        @endif
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($leave->created_at)->format('M d, Y') }}</td>
                                    <td>
                                        <a href="{{ route('leaves.show', $leave->id) }}" class="btn btn-sm btn-info">View</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">
                                        <div class="empty-state-container">
                                            <i class="fas fa-calendar-plus"></i>
                                            <p>No leave requests found</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    @if($recentLeaves->count() > 0)
                        <div class="p-3 text-center">
                            <a href="{{ route('leaves.my') }}" class="btn btn-outline-primary">View All Leave Requests</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Recent Activities --}}
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
                        @forelse (($myRecentActivities ?? collect())->where('user_id', auth()->id())->take(10) as $activity)
                            <tr>
                                <td>
                                    <span class="badge-pro info">{{ $activity->action }}</span>
                                </td>
                                <td>{{ Str::limit($activity->description, 65) }}</td>
                                <td>
                                    <span class="badge-pro secondary">
                                        {{ $activity->action_timestamp ? \Carbon\Carbon::parse($activity->action_timestamp)->format('M d, Y g:i A') : 'N/A' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3">
                                    <div class="empty-state-container">
                                        <i class="fas fa-folder-open"></i>
                                        <p>No recent activities recorded</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    @else
        {{-- Admin/Manager View --}}
        <div class="page-header">
            <h1 class="page-title">Management Dashboard</h1>
            <p class="page-subtitle">Workforce Overview & Analytics</p>
        </div>

        {{-- Statistics Summary --}}
        <div class="stats-container">
            <div class="stat-box primary">
                <div class="stat-header">
                    <div>
                        <div class="stat-number">{{ $employeeCount ?? 0 }}</div>
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
                        <div class="stat-number">{{ $activeEmployees ?? 0 }}</div>
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
                        <div class="stat-number">{{ $suspendedEmployees ?? 0 }}</div>
                        <div class="stat-title">Suspended</div>
                    </div>
                    <div class="stat-icon warning">
                        <i class="fas fa-user-minus"></i>
                    </div>
                </div>
            </div>

            <div class="stat-box primary">
                <div class="stat-header">
                    <div>
                        <div class="stat-number">{{ $permanentEmployees ?? 0 }}</div>
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
                        <div class="stat-number">{{ $contractEmployees ?? 0 }}</div>
                        <div class="stat-title">Contract Staff</div>
                    </div>
                    <div class="stat-icon secondary">
                        <i class="fas fa-file-signature"></i>
                    </div>
                </div>
            </div>

            <div class="stat-box secondary">
                <div class="stat-header">
                    <div>
                        <div class="stat-number">{{ $retiredEmployees ?? 0 }}</div>
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
                        <div class="stat-number">{{ $deceasedEmployees ?? 0 }}</div>
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
                        <div class="stat-number">{{ $totalLeaveRequests ?? 0 }}</div>
                        <div class="stat-title">Total Leave Requests</div>
                    </div>
                    <div class="stat-icon info">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                </div>
            </div>

            <div class="stat-box warning">
                <div class="stat-header">
                    <div>
                        <div class="stat-number">{{ $pendingLeaveRequests ?? 0 }}</div>
                        <div class="stat-title">Pending Leaves</div>
                    </div>
                    <div class="stat-icon warning">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>

            <div class="stat-box success">
                <div class="stat-header">
                    <div>
                        <div class="stat-number">{{ $approvedLeaveRequests ?? 0 }}</div>
                        <div class="stat-title">Approved Leaves</div>
                    </div>
                    <div class="stat-icon success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>

            <div class="stat-box danger">
                <div class="stat-header">
                    <div>
                        <div class="stat-number">{{ $rejectedLeaveRequests ?? 0 }}</div>
                        <div class="stat-title">Rejected Leaves</div>
                    </div>
                    <div class="stat-icon danger">
                        <i class="fas fa-times-circle"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Main Content Grid --}}
        <div class="content-layout">
            {{-- Department Distribution --}}
            <div class="card-professional">
                <div class="card-professional-header">
                    <h3 class="card-professional-title">
                        <i class="fas fa-sitemap"></i> Department Distribution
                    </h3>
                </div>
                <div class="card-professional-body">
                    <div class="department-list">
                        @if(isset($departments) && $departments->count() > 0)
                            @foreach ($departments as $department)
                                <div class="department-row">
                                    <span class="department-title">{{ $department->department_name }}</span>
                                    <span class="department-value">{{ $department->employees_count ?? 0 }}</span>
                                </div>
                            @endforeach
                        @else
                            <div class="empty-state-container">
                                <i class="fas fa-building"></i>
                                <p>No departments available</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Audit Trail --}}
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
                            @forelse (($recentAudits ?? collect())->take(8) as $audit)
                                <tr>
                                    <td>
                                        <span class="badge-pro primary">
                                            {{ $audit->user?->username ?? 'User ' . $audit->user_id }}
                                        </span>
                                    </td>
                                    <td class="text-truncate" style="max-width: 250px;">
                                        {{ Str::limit($audit->description, 45) }}
                                    </td>
                                    <td>
                                        <span class="badge-pro secondary">
                                            {{ $audit->action_timestamp ? \Carbon\Carbon::parse($audit->action_timestamp)->format('M d, g:i A') : 'N/A' }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3">
                                        <div class="empty-state-container">
                                            <i class="fas fa-clipboard"></i>
                                            <p>No audit records available</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@section('scripts')
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
@endsection