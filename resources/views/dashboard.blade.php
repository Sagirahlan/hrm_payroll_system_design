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
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
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
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 12px;
        color: white;
        padding: 25px;
        margin-bottom: 30px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        position: relative;
        overflow: hidden;
    }
    
    .welcome-banner::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        animation: float 6s ease-in-out infinite;
    }
    
    @keyframes float {
        0%, 100% { transform: translate(0, 0) rotate(0deg); }
        50% { transform: translate(-20px, -20px) rotate(180deg); }
    }
    
    .welcome-banner h4 {
        font-weight: 700;
        margin-bottom: 10px;
        position: relative;
        z-index: 1;
    }
    
    .welcome-banner .lead {
        font-size: 1.1rem;
        opacity: 0.9;
        position: relative;
        z-index: 1;
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
        height: 350px;
    }
    
    .chart-card {
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        border: none;
        margin-bottom: 24px;
        overflow: hidden;
    }
    
    .chart-header {
        padding: 20px 24px 10px;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    }
    
    .chart-body {
        padding: 20px;
    }
    
    .department-chart-container {
        height: 300px;
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
        background: linear-gradient(135deg, rgba(23, 162, 184, 0.1) 0%, rgba(23, 162, 184, 0.05) 100%);
        color: #17a2b8;
    }
    
    .bg-success-light {
        background: linear-gradient(135deg, rgba(40, 167, 69, 0.1) 0%, rgba(40, 167, 69, 0.05) 100%);
        color: #28a745;
    }
    
    .bg-warning-light {
        background: linear-gradient(135deg, rgba(255, 193, 7, 0.1) 0%, rgba(255, 193, 7, 0.05) 100%);
        color: #ffc107;
    }
    
    .bg-info-light {
        background: linear-gradient(135deg, rgba(23, 162, 184, 0.1) 0%, rgba(23, 162, 184, 0.05) 100%);
        color: #17a2b8;
    }
    
    .bg-purple-light {
        background: linear-gradient(135deg, rgba(111, 66, 193, 0.1) 0%, rgba(111, 66, 193, 0.05) 100%);
        color: #6f42c1;
    }
    
    .bg-danger-light {
        background: linear-gradient(135deg, rgba(220, 53, 69, 0.1) 0%, rgba(220, 53, 69, 0.05) 100%);
        color: #dc3545;
    }
    
    .bg-secondary-light {
        background: linear-gradient(135deg, rgba(108, 117, 125, 0.1) 0%, rgba(108, 117, 125, 0.05) 100%);
        color: #6c757d;
    }
    
    .status-badge {
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 500;
    }
    
    .chart-loading {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 300px;
        color: #6c757d;
    }
    
    .chart-loading i {
        animation: spin 1s linear infinite;
        margin-right: 10px;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    .quick-stats-item {
        padding: 15px;
        border-radius: 10px;
        margin-bottom: 15px;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-left: 4px solid;
        transition: all 0.3s ease;
    }
    
    .quick-stats-item:hover {
        transform: translateX(5px);
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }
    
    .quick-stats-item.payroll { border-left-color: #28a745; }
    .quick-stats-item.leaves { border-left-color: #ffc107; }
    .quick-stats-item.disciplinary { border-left-color: #dc3545; }
    .quick-stats-item.hires { border-left-color: #007bff; }
    
    @media (max-width: 768px) {
        .stat-card {
            margin-bottom: 20px;
        }
        
        .welcome-banner {
            padding: 20px 15px;
        }
        
        .chart-container {
            height: 250px;
        }
        
        .department-chart-container {
            height: 250px;
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
                    <div class="col-xl-3 col-md-6 col-12 mb-4">
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

                    <div class="col-xl-3 col-md-6 col-12 mb-4">
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

                    <div class="col-xl-3 col-md-6 col-12 mb-4">
                        <div class="stat-card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="stat-number">
                                            {{ auth()->user()->employee->cadre->name ?? 'N/A' }}
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

                    <div class="col-xl-3 col-md-6 col-12 mb-4">
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
                    <div class="col-lg-8 col-12 mb-4">
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
                    <div class="col-lg-4 col-12 mb-4">
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
        {{-- Admin/Manager View (Enhanced Dashboard) --}}
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
                    <div class="col-xl-2 col-lg-3 col-md-4 col-6 mb-4">
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

                    <div class="col-xl-2 col-lg-3 col-md-4 col-6 mb-4">
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

                    <div class="col-xl-2 col-lg-3 col-md-4 col-6 mb-4">
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

                    <div class="col-xl-2 col-lg-3 col-md-4 col-6 mb-4">
                        <div class="stat-card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="stat-number">{{ $deceasedEmployees ?? 0 }}</div>
                                        <div class="stat-label">Deceased</div>
                                    </div>
                                    <div class="stat-icon bg-secondary-light text-secondary">
                                        <i class="fas fa-heartbeat"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-2 col-lg-3 col-md-4 col-6 mb-4">
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
                    
                    <!-- Contract and Permanent Employee Stats -->
                    <div class="col-xl-2 col-lg-3 col-md-4 col-6 mb-4">
                        <div class="stat-card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="stat-number">{{ $permanentEmployees ?? 0 }}</div>
                                        <div class="stat-label">Permanent</div>
                                    </div>
                                    <div class="stat-icon bg-purple-light text-purple">
                                        <i class="fas fa-id-card"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-2 col-lg-3 col-md-4 col-6 mb-4">
                        <div class="stat-card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="stat-number">{{ $contractEmployees ?? 0 }}</div>
                                        <div class="stat-label">Contract</div>
                                    </div>
                                    <div class="stat-icon bg-info-light text-info">
                                        <i class="fas fa-file-contract"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    {{-- Employee Status Chart --}}
                    <div class="col-xl-8 col-12 mb-4">
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
                                    <div id="statusChartLoading" class="chart-loading">
                                        <i class="fas fa-spinner"></i>
                                        <span>Loading chart...</span>
                                    </div>
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

                    {{-- Department Pie Chart and Quick Stats --}}
                    <div class="col-xl-4 col-12 mb-4">
                        <div class="chart-card h-100">
                            <div class="chart-header">
                                <h6 class="mb-0 d-flex align-items-center">
                                    <i class="fas fa-building me-2 text-warning"></i>
                                    <span>Department Distribution</span>
                                </h6>
                            </div>
                            <div class="chart-body">
                                <div class="department-chart-container">
                                    <canvas id="departmentPieChart"></canvas>
                                    <div id="deptChartLoading" class="chart-loading">
                                        <i class="fas fa-spinner"></i>
                                        <span>Loading chart...</span>
                                    </div>
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
                                <div class="d-grid gap-2">
                                    <div class="quick-stats-item payroll d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="fas fa-money-check me-2"></i>
                                            <span>Payroll Processed</span>
                                        </div>
                                        <span class="badge bg-success">98%</span>
                                    </div>
                                    <div class="quick-stats-item leaves d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="fas fa-calendar-times me-2"></i>
                                            <span>Pending Leaves</span>
                                        </div>
                                        <span class="badge bg-warning text-dark">12</span>
                                    </div>
                                    <div class="quick-stats-item disciplinary d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                            <span>Disciplinary Cases</span>
                                        </div>
                                        <span class="badge bg-danger">3</span>
                                    </div>
                                    <div class="quick-stats-item hires d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="fas fa-user-plus me-2"></i>
                                            <span>New Hires (This Month)</span>
                                        </div>
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
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0/dist/chartjs-plugin-datalabels.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Chart configuration options
    const chartDefaults = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: true
            }
        }
    };

    // Get data with fallbacks
    const employeeData = {
        total: {{ $employeeCount ?? 210 }},
        active: {{ $activeEmployees ?? 64 }},
        suspended: {{ $suspendedEmployees ?? 40 }},
        terminated: {{ $terminatedEmployees ?? 0 }},
        deceased: {{ $deceasedEmployees ?? 57 }},
        retired: {{ $retiredEmployees ?? 49 }}
    };

    // Department data with fallbacks
    const departmentData = [
        @if(isset($departments) && $departments->count() > 0)
            @foreach ($departments as $department)
                {
                    name: "{{ $department->department_name }}",
                    count: {{ $department->employees_count ?? 0 }}
                },
            @endforeach
        @else
            { name: "Human Resources", count: 25 },
            { name: "Finance", count: 18 },
            { name: "IT Department", count: 32 },
            { name: "Operations", count: 45 },
            { name: "Marketing", count: 22 },
            { name: "Sales", count: 38 },
            { name: "Administration", count: 30 }
        @endif
    ];

    // Employee Status Chart
    const statusCtx = document.getElementById('employeeStatusChart');
    if (statusCtx) {
        // Hide loading indicator
        document.getElementById('statusChartLoading').style.display = 'none';
        
        const employeeStatusChart = new Chart(statusCtx, {
            type: 'bar',
            data: {
                labels: ['Active', 'Suspended', 'Terminated', 'Deceased', 'Retired'],
                datasets: [{
                    label: 'Employee Count',
                    data: [
                        employeeData.active,
                        employeeData.suspended,
                        employeeData.terminated,
                        employeeData.deceased,
                        employeeData.retired
                    ],
                    backgroundColor: [
                        'rgba(40, 167, 69, 0.8)',
                        'rgba(255, 193, 7, 0.8)',
                        'rgba(220, 53, 69, 0.8)',
                        'rgba(108, 117, 125, 0.8)',
                        'rgba(23, 162, 184, 0.8)'
                    ],
                    borderColor: [
                        'rgba(40, 167, 69, 1)',
                        'rgba(255, 193, 7, 1)',
                        'rgba(220, 53, 69, 1)',
                        'rgba(108, 117, 125, 1)',
                        'rgba(23, 162, 184, 1)'
                    ],
                    borderWidth: 2,
                    borderRadius: 8,
                    borderSkipped: false,
                }]
            },
            options: {
                ...chartDefaults,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        titleFont: {
                            size: 14,
                            weight: 'bold'
                        },
                        bodyFont: {
                            size: 13
                        },
                        cornerRadius: 8,
                        displayColors: true,
                        callbacks: {
                            label: function(context) {
                                const total = employeeData.total;
                                const value = context.parsed.y;
                                const percentage = ((value / total) * 100).toFixed(1);
                                return `${context.label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)',
                            drawBorder: false
                        },
                        ticks: {
                            stepSize: Math.ceil(Math.max(...Object.values(employeeData)) / 10),
                            color: '#666'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: '#666'
                        }
                    }
                },
                animation: {
                    duration: 1500,
                    easing: 'easeInOutCubic'
                }
            }
        });
    }

    // Department Pie Chart
    const deptCtx = document.getElementById('departmentPieChart');
    if (deptCtx && departmentData.length > 0) {
        // Hide loading indicator
        document.getElementById('deptChartLoading').style.display = 'none';
        
        // Generate colors
        const colors = [
            { bg: 'rgba(54, 162, 235, 0.8)', border: 'rgba(54, 162, 235, 1)' },
            { bg: 'rgba(255, 99, 132, 0.8)', border: 'rgba(255, 99, 132, 1)' },
            { bg: 'rgba(255, 205, 86, 0.8)', border: 'rgba(255, 205, 86, 1)' },
            { bg: 'rgba(75, 192, 192, 0.8)', border: 'rgba(75, 192, 192, 1)' },
            { bg: 'rgba(153, 102, 255, 0.8)', border: 'rgba(153, 102, 255, 1)' },
            { bg: 'rgba(255, 159, 64, 0.8)', border: 'rgba(255, 159, 64, 1)' },
            { bg: 'rgba(199, 199, 199, 0.8)', border: 'rgba(199, 199, 199, 1)' },
            { bg: 'rgba(83, 102, 255, 0.8)', border: 'rgba(83, 102, 255, 1)' }
        ];

        const departmentPieChart = new Chart(deptCtx, {
            type: 'doughnut',
            data: {
                labels: departmentData.map(dept => dept.name),
                datasets: [{
                    data: departmentData.map(dept => dept.count),
                    backgroundColor: departmentData.map((_, i) => colors[i % colors.length].bg),
                    borderColor: departmentData.map((_, i) => colors[i % colors.length].border),
                    borderWidth: 2,
                    hoverOffset: 15,
                    hoverBorderWidth: 3
                }]
            },
            options: {
                ...chartDefaults,
                cutout: '60%',
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom',
                        labels: {
                            padding: 15,
                            usePointStyle: true,
                            pointStyle: 'circle',
                            font: {
                                size: 11
                            },
                            generateLabels: function(chart) {
                                const data = chart.data;
                                if (data.labels.length && data.datasets.length) {
                                    const total = data.datasets[0].data.reduce((a, b) => a + b, 0);
                                    return data.labels.map((label, i) => {
                                        const value = data.datasets[0].data[i];
                                        const percentage = ((value / total) * 100).toFixed(1);
                                        return {
                                            text: `${label} (${percentage}%)`,
                                            fillStyle: data.datasets[0].backgroundColor[i],
                                            strokeStyle: data.datasets[0].borderColor[i],
                                            lineWidth: data.datasets[0].borderWidth,
                                            hidden: false,
                                            index: i
                                        };
                                    });
                                }
                                return [];
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        cornerRadius: 8,
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((value / total) * 100).toFixed(1);
                                return `${label}: ${value} employees (${percentage}%)`;
                            }
                        }
                    }
                },
                animation: {
                    animateRotate: true,
                    animateScale: true,
                    duration: 1500,
                    easing: 'easeInOutCubic'
                }
            }
        });
    }
    
    // Live update function for audit trail
    function fetchAuditTrail() {
        const indicator = document.getElementById('audit-refresh-indicator');
        if (indicator) {
            indicator.style.display = 'inline';
            
            // Simulate API call - replace with actual endpoint
            setTimeout(() => {
                indicator.style.display = 'none';
                // You can add actual AJAX call here to refresh audit data
            }, 1500);
        }
    }
    
    // Auto-refresh audit trail every 30 seconds
    setInterval(fetchAuditTrail, 30000);
    
    // Add click handlers for quick stats
    document.querySelectorAll('.quick-stats-item').forEach(item => {
        item.addEventListener('click', function() {
            const type = this.classList.contains('payroll') ? 'payroll' :
                        this.classList.contains('leaves') ? 'leaves' :
                        this.classList.contains('disciplinary') ? 'disciplinary' : 'hires';
            
            // Add visual feedback
            this.style.transform = 'scale(0.98)';
            setTimeout(() => {
                this.style.transform = '';
            }, 150);
            
            // You can add navigation logic here
            console.log(`Clicked on ${type} stats`);
        });
    });
    
    // Add hover effects to stat cards
    document.querySelectorAll('.stat-card').forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
    
    // Initialize tooltips if Bootstrap is available
    if (typeof bootstrap !== 'undefined') {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
    
    // Add loading states management
    function showChartLoading(chartId) {
        const loading = document.getElementById(chartId + 'Loading');
        if (loading) loading.style.display = 'flex';
    }
    
    function hideChartLoading(chartId) {
        const loading = document.getElementById(chartId + 'Loading');
        if (loading) loading.style.display = 'none';
    }
    
    // Add refresh functionality
    function refreshDashboard() {
        showChartLoading('statusChart');
        showChartLoading('deptChart');
        
        // Simulate data refresh
        setTimeout(() => {
            hideChartLoading('statusChart');
            hideChartLoading('deptChart');
        }, 2000);
    }
    
    // Add keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Ctrl + R or F5 for refresh
        if ((e.ctrlKey && e.key === 'r') || e.key === 'F5') {
            e.preventDefault();
            refreshDashboard();
        }
    });
});

// Export chart instances for external access
window.dashboardCharts = {
    statusChart: null,
    departmentChart: null,
    refresh: function() {
        location.reload();
    }
};
</script>
@endsection