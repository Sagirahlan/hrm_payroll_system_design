@extends('layouts.app')

@section('styles')
<!-- Additional styles for the dashboard -->
<style>
    .dashboard-card {
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        border: none;
        margin-bottom: 24px;
    }
    
    .dashboard-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 6px 25px rgba(0, 0, 0, 0.15);
    }
    
    .dashboard-card .card-header {
        border-radius: 12px 12px 0 0 !important;
        padding: 18px 24px;
        font-weight: 600;
        border: none;
    }
    
    .stat-card {
        border-radius: 10px;
        overflow: hidden;
        border: none;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }
    
    .stat-icon {
        width: 60px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        font-size: 24px;
    }
    
    .welcome-banner {
        background: linear-gradient(120deg, #17a2b8, #28a745);
        border-radius: 12px;
        color: white;
        padding: 25px;
        margin-bottom: 30px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }
    
    .welcome-banner h4 {
        font-weight: 700;
        margin-bottom: 10px;
    }
    
    .welcome-banner .lead {
        font-size: 1.1rem;
        opacity: 0.9;
    }
    
    .quick-action-card {
        height: 100%;
    }
    
    .quick-action-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 15px;
        border-radius: 10px;
        margin-bottom: 15px;
        transition: all 0.3s;
        text-decoration: none;
        font-weight: 500;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
    }
    
    .quick-action-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }
    
    .activity-table th {
        font-weight: 600;
        color: #495057;
    }
    
    .chart-container {
        position: relative;
        height: 300px;
    }
    
    .chart-card {
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        border: none;
        margin-bottom: 24px;
    }
    
    .chart-header {
        padding: 20px 24px 10px;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }
    
    .chart-body {
        padding: 20px;
    }
    
    .department-chart-container {
        height: 280px;
        position: relative;
    }
    
    .stat-number {
        font-size: 1.8rem;
        font-weight: 700;
        margin-bottom: 5px;
    }
    
    .stat-label {
        font-size: 0.9rem;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .bg-primary-light {
        background-color: rgba(23, 162, 184, 0.1);
        color: #17a2b8;
    }
    
    .bg-success-light {
        background-color: rgba(40, 167, 69, 0.1);
        color: #28a745;
    }
    
    .bg-warning-light {
        background-color: rgba(255, 193, 7, 0.1);
        color: #ffc107;
    }
    
    .bg-info-light {
        background-color: rgba(23, 162, 184, 0.1);
        color: #17a2b8;
    }
    
    .bg-purple-light {
        background-color: rgba(111, 66, 193, 0.1);
        color: #6f42c1;
    }
    
    .bg-danger-light {
        background-color: rgba(220, 53, 69, 0.1);
        color: #dc3545;
    }
    
    .bg-secondary-light {
        background-color: rgba(108, 117, 125, 0.1);
        color: #6c757d;
    }
    
    .stat-trend {
        font-size: 0.85rem;
        padding: 3px 8px;
        border-radius: 20px;
    }
    
    .status-badge {
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 500;
    }
    
    @media (max-width: 768px) {
        .stat-card {
            margin-bottom: 20px;
        }
        
        .welcome-banner {
            padding: 20px 15px;
        }
    }
</style>
@endsection

@section('content')
<div class="container-fluid py-4">
    @if(auth()->user()->hasRole('employee'))
        {{-- Employee View --}}
        <div class="dashboard-card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0 d-flex align-items-center">
                    <i class="fas fa-user-circle me-2"></i>
                    <span>My Employee Dashboard</span>
                </h5>
            </div>
            <div class="card-body">
                {{-- Welcome Section --}}
                <div class="welcome-banner">
                    <h4><i class="fas fa-hand-wave me-2"></i>Welcome back, {{ auth()->user()->username }}!</h4>
                    <p class="lead mb-0">Here's your personal overview and recent activities.</p>
                </div>

                {{-- Employee Stats Cards --}}
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stat-card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="stat-number">
                                            {{ auth()->user()->employee->department->department_name ?? 'N/A' }}
                                        </div>
                                        <div class="stat-label">My Department</div>
                                    </div>
                                    <div class="stat-icon bg-primary-light text-primary">
                                        <i class="fas fa-building"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stat-card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="stat-number">
                                            <span class="badge bg-success">{{ auth()->user()->employee->employment_status ?? 'Active' }}</span>
                                        </div>
                                        <div class="stat-label">Employment Status</div>
                                    </div>
                                    <div class="stat-icon bg-success-light text-success">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stat-card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="stat-number">
                                            {{ auth()->user()->employee->cadre->cadre_name ?? 'N/A' }}
                                        </div>
                                        <div class="stat-label">Position</div>
                                    </div>
                                    <div class="stat-icon bg-warning-light text-warning">
                                        <i class="fas fa-briefcase"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stat-card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="stat-number">
                                            {{ auth()->user()->employee->date_of_first_appointment ? \Carbon\Carbon::parse(auth()->user()->employee->date_of_first_appointment)->format('M Y') : 'N/A' }}
                                        </div>
                                        <div class="stat-label">Appointment Date</div>
                                    </div>
                                    <div class="stat-icon bg-info-light text-info">
                                        <i class="fas fa-calendar-alt"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    {{-- My Recent Activities --}}
                    <div class="col-lg-8 mb-4">
                        <div class="dashboard-card h-100">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0 d-flex align-items-center">
                                    <i class="fas fa-history me-2"></i>
                                    <span>My Recent Activities</span>
                                </h6>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover activity-table mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Action</th>
                                                <th>Description</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse (($myRecentActivities ?? collect())->where('user_id', auth()->id())->take(5) as $activity)
                                                <tr>
                                                    <td>
                                                        <span class="badge bg-info">{{ $activity->action }}</span>
                                                    </td>
                                                    <td class="text-muted">{{ Str::limit($activity->description, 50) }}</td>
                                                    <td>
                                                        <span class="badge bg-secondary">
                                                            {{ $activity->action_timestamp ? \Carbon\Carbon::parse($activity->action_timestamp)->diffForHumans() : 'N/A' }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="3" class="text-center py-5 text-muted">
                                                        <i class="fas fa-info-circle fa-2x mb-3"></i>
                                                        <p class="mb-0">No recent activities found</p>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Quick Actions --}}
                    <div class="col-lg-4 mb-4">
                        <div class="dashboard-card quick-action-card">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0 d-flex align-items-center">
                                    <i class="fas fa-bolt me-2"></i>
                                    <span>Quick Actions</span>
                                </h6>
                            </div>
                            <div class="card-body">
                                <a href="{{ route('profile') }}" class="quick-action-btn btn btn-outline-primary">
                                    <i class="fas fa-user me-2"></i> View My Profile
                                </a>
                                <a href="#" class="quick-action-btn btn btn-outline-info">
                                    <i class="fas fa-edit me-2"></i> Update Information
                                </a>
                                <a href="#" class="quick-action-btn btn btn-outline-warning">
                                    <i class="fas fa-calendar-plus me-2"></i> Request Leave
                                </a>
                                <a href="#" class="quick-action-btn btn btn-outline-success">
                                    <i class="fas fa-envelope me-2"></i> Contact HR
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    @else
        {{-- Admin/Manager View (Redesigned Dashboard) --}}
        <div class="dashboard-card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0 d-flex align-items-center">
                    <i class="fas fa-tachometer-alt me-2"></i>
                    <span>Management Dashboard Overview</span>
                </h5>
            </div>
            <div class="card-body">
                {{-- Stats Summary --}}
                <div class="row mb-4">
                    <div class="col-xl-2 col-md-4 col-6 mb-4">
                        <div class="stat-card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="stat-number">{{ $employeeCount ?? 0 }}</div>
                                        <div class="stat-label">Total Employees</div>
                                    </div>
                                    <div class="stat-icon bg-primary-light text-primary">
                                        <i class="fas fa-users"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-2 col-md-4 col-6 mb-4">
                        <div class="stat-card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="stat-number">{{ $activeEmployees ?? 0 }}</div>
                                        <div class="stat-label">Active</div>
                                    </div>
                                    <div class="stat-icon bg-success-light text-success">
                                        <i class="fas fa-user-check"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-2 col-md-4 col-6 mb-4">
                        <div class="stat-card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="stat-number">{{ $suspendedEmployees ?? 0 }}</div>
                                        <div class="stat-label">Suspended</div>
                                    </div>
                                    <div class="stat-icon bg-warning-light text-warning">
                                        <i class="fas fa-pause-circle"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-2 col-md-4 col-6 mb-4">
                        <div class="stat-card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="stat-number">{{ $terminatedEmployees ?? 0 }}</div>
                                        <div class="stat-label">Terminated</div>
                                    </div>
                                    <div class="stat-icon bg-danger-light text-danger">
                                        <i class="fas fa-user-slash"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-2 col-md-4 col-6 mb-4">
                        <div class="stat-card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="stat-number">{{ $deceasedEmployees ?? 0 }}</div>
                                        <div class="stat-label">Deceased</div>
                                    </div>
                                    <div class="stat-icon bg-secondary-light text-secondary">
                                        <i class="fas fa-cross"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-2 col-md-4 col-6 mb-4">
                        <div class="stat-card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="stat-number">{{ $retiredEmployees ?? 0 }}</div>
                                        <div class="stat-label">Retired</div>
                                    </div>
                                    <div class="stat-icon bg-info-light text-info">
                                        <i class="fas fa-user-clock"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    {{-- Employee Status Chart --}}
                    <div class="col-xl-8 mb-4">
                        <div class="chart-card">
                            <div class="chart-header">
                                <h6 class="mb-0 d-flex align-items-center">
                                    <i class="fas fa-chart-bar me-2 text-primary"></i>
                                    <span>Employee Status Overview</span>
                                </h6>
                            </div>
                            <div class="chart-body">
                                <div class="chart-container">
                                    <canvas id="employeeStatusChart"></canvas>
                                </div>
                            </div>
                        </div>
                        
                        {{-- Recent Audit Trail --}}
                        <div class="chart-card">
                            <div class="chart-header">
                                <h6 class="mb-0 d-flex align-items-center">
                                    <i class="fas fa-history me-2 text-success"></i>
                                    <span>Recent Audit Trail</span>
                                    <span id="audit-refresh-indicator" class="ms-auto" style="display:none;">
                                        <i class="fas fa-sync-alt fa-spin"></i>
                                    </span>
                                </h6>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover activity-table mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>User</th>
                                                <th>Action</th>
                                                <th>Description</th>
                                                <th>Timestamp</th>
                                            </tr>
                                        </thead>
                                        <tbody id="audit-table-body">
                                            @forelse (($recentAudits ?? collect())->take(5) as $audit)
                                                <tr>
                                                    <td>
                                                        <span class="badge bg-primary">
                                                            {{ $audit->user?->username ?? 'User ' . $audit->user_id }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-success">
                                                            {{ $audit->action }}
                                                        </span>
                                                    </td>
                                                    <td class="text-muted">{{ Str::limit($audit->description, 50) }}</td>
                                                    <td>
                                                        <span class="badge bg-secondary">
                                                            {{ $audit->action_timestamp ? \Carbon\Carbon::parse($audit->action_timestamp)->diffForHumans() : 'N/A' }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="text-center py-5 text-muted">
                                                        <i class="fas fa-info-circle fa-2x mb-3"></i>
                                                        <p class="mb-0">No recent audit records found</p>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Department Pie Chart --}}
                    <div class="col-xl-4 mb-4">
                        <div class="chart-card h-100">
                            <div class="chart-header">
                                <h6 class="mb-0 d-flex align-items-center">
                                    <i class="fas fa-building me-2 text-warning"></i>
                                    <span>Department Employee Counts</span>
                                </h6>
                            </div>
                            <div class="chart-body">
                                <div class="department-chart-container">
                                    <canvas id="departmentPieChart"></canvas>
                                </div>
                            </div>
                        </div>
                        
                        {{-- Quick Stats --}}
                        <div class="chart-card mt-4">
                            <div class="chart-header">
                                <h6 class="mb-0 d-flex align-items-center">
                                    <i class="fas fa-clipboard-list me-2 text-info"></i>
                                    <span>Quick Stats</span>
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-3">
                                    <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded">
                                        <span>Payroll Processed</span>
                                        <span class="badge bg-success">98%</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded">
                                        <span>Pending Leaves</span>
                                        <span class="badge bg-warning text-dark">12</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded">
                                        <span>Disciplinary Cases</span>
                                        <span class="badge bg-danger">3</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded">
                                        <span>New Hires (This Month)</span>
                                        <span class="badge bg-primary">7</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

@endsection

@section('scripts')
<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Employee Status Chart
    var statusCtx = document.getElementById('employeeStatusChart').getContext('2d');
    var employeeStatusChart = new Chart(statusCtx, {
        type: 'bar',
        data: {
            labels: ['Total', 'Active', 'Suspended', 'Terminated', 'Deceased', 'Retired'],
            datasets: [{
                label: 'Count',
                data: [
                    {{ $employeeCount ?? 0 }},
                    {{ $activeEmployees ?? 0 }},
                    {{ $suspendedEmployees ?? 0 }},
                    {{ $terminatedEmployees ?? 0 }},
                    {{ $deceasedEmployees ?? 0 }},
                    {{ $retiredEmployees ?? 0 }}
                ],
                backgroundColor: [
                    'rgba(23, 162, 184, 0.7)',
                    'rgba(40, 167, 69, 0.7)',
                    'rgba(255, 193, 7, 0.7)',
                    'rgba(220, 53, 69, 0.7)',
                    'rgba(108, 117, 125, 0.7)',
                    'rgba(23, 162, 184, 0.7)'
                ],
                borderColor: [
                    'rgba(23, 162, 184, 1)',
                    'rgba(40, 167, 69, 1)',
                    'rgba(255, 193, 7, 1)',
                    'rgba(220, 53, 69, 1)',
                    'rgba(108, 117, 125, 1)',
                    'rgba(23, 162, 184, 1)'
                ],
                borderWidth: 1,
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    titleFont: {
                        size: 14
                    },
                    bodyFont: {
                        size: 13
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    },
                    ticks: {
                        stepSize: 1
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });

    // Department Pie Chart
    var deptCtx = document.getElementById('departmentPieChart').getContext('2d');
    var departmentLabels = [
        @if(isset($departments))
            @foreach ($departments as $department)
                "{{ $department->department_name }}",
            @endforeach
        @endif
    ];
    var departmentCounts = [
        @if(isset($departments))
            @foreach ($departments as $department)
                {{ $department->employees_count }},
            @endforeach
        @endif
    ];
    
    var backgroundColors = [
        'rgba(23, 162, 184, 0.7)',
        'rgba(220, 53, 69, 0.7)',
        'rgba(255, 193, 7, 0.7)',
        'rgba(40, 167, 69, 0.7)',
        'rgba(108, 117, 125, 0.7)',
        'rgba(111, 66, 193, 0.7)',
        'rgba(232, 62, 140, 0.7)'
    ];
    
    var borderColors = [
        'rgba(23, 162, 184, 1)',
        'rgba(220, 53, 69, 1)',
        'rgba(255, 193, 7, 1)',
        'rgba(40, 167, 69, 1)',
        'rgba(108, 117, 125, 1)',
        'rgba(111, 66, 193, 1)',
        'rgba(232, 62, 140, 1)'
    ];

    function getColor(arr, i) {
        return arr[i % arr.length];
    }

    if (departmentLabels.length > 0) {
        var pieChart = new Chart(deptCtx, {
            type: 'doughnut',
            data: {
                labels: departmentLabels,
                datasets: [{
                    data: departmentCounts,
                    backgroundColor: departmentLabels.map((_, i) => getColor(backgroundColors, i)),
                    borderColor: departmentLabels.map((_, i) => getColor(borderColors, i)),
                    borderWidth: 2,
                    hoverOffset: 15
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '60%',
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom',
                        labels: {
                            padding: 15,
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                let value = context.parsed || 0;
                                let total = context.dataset.data.reduce((a, b) => a + b, 0);
                                let percentage = Math.round((value / total) * 100);
                                return `${label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                },
                animation: {
                    animateRotate: true,
                    animateScale: true,
                    duration: 1000,
                    easing: 'easeInOutCubic'
                }
            }
        });
    }
    
    // Live update for audit trail
    function fetchAuditTrail() {
        document.getElementById('audit-refresh-indicator').style.display = 'inline';
        // In a real implementation, this would fetch actual data
        // For now, we'll just simulate the refresh indicator
        setTimeout(() => {
            document.getElementById('audit-refresh-indicator').style.display = 'none';
        }, 1000);
    }
    
    // Refresh every 30 seconds
    setInterval(fetchAuditTrail, 30000);
});
</script>
@endsection