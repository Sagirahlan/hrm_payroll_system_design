@extends('layouts.app')

@section('title', 'Audit Trail Logs')

@section('content')
<div class="container-fluid py-4">
    <div class="card border-primary shadow">
        <div class="card-header" style="background-color: skyblue; color: white;">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h5 class="mb-0">Audit Trail Logs</h5>
                </div>
                <div class="col-md-6">
                    <form method="GET" action="{{ route('audit-trails.index') }}" class="w-100">
                        <div class="row g-2 align-items-center">
                            <div class="col-md-2">
                                <input type="text" name="search" class="form-control form-control-sm" placeholder="Search logs..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-2">
                                <select name="action_filter" class="form-select form-select-sm">
                                    <option value="">All Actions</option>
                                    @php
                                        // Group actions by type for better organization
                                        $groupedActions = [];
                                        foreach($actions as $action) {
                                            $actionStr = (string)$action; // Ensure it's a string
                                            if (str_contains($actionStr, 'login')) {
                                                $groupedActions['Login/Logout'][] = $actionStr;
                                            } elseif (str_contains($actionStr, 'employee')) {
                                                $groupedActions['Employee'][] = $actionStr;
                                            } elseif (str_contains($actionStr, 'salary_scale')) {
                                                $groupedActions['Salary Scale'][] = $actionStr;
                                            } elseif (str_contains($actionStr, 'user')) {
                                                $groupedActions['User'][] = $actionStr;
                                            } elseif (str_contains($actionStr, 'payroll')) {
                                                $groupedActions['Payroll'][] = $actionStr;
                                            } elseif (str_contains($actionStr, 'addition')) {
                                                $groupedActions['Additions'][] = $actionStr;
                                            } elseif (str_contains($actionStr, 'deduction')) {
                                                $groupedActions['Deductions'][] = $actionStr;
                                            } elseif (str_contains($actionStr, 'promotion')) {
                                                $groupedActions['Promotions'][] = $actionStr;
                                            } elseif (str_contains($actionStr, 'leave')) {
                                                $groupedActions['Leaves'][] = $actionStr;
                                            } else {
                                                $groupedActions['Other'][] = $actionStr;
                                            }
                                        }
                                    @endphp
                                    @foreach($groupedActions as $group => $groupedActionList)
                                        <optgroup label="{{ $group }}">
                                            @foreach($groupedActionList as $action)
                                                <option value="{{ $action }}" @if(request('action_filter') == $action) selected @endif>
                                                    {{ ucfirst(str_replace('_', ' ', $action)) }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="text" name="start_date" class="form-control form-control-sm" placeholder="Start Date" onfocus="(this.type='date')" onblur="(this.type='text')" value="{{ request('start_date') }}">
                            </div>
                            <div class="col-md-2">
                                <input type="text" name="end_date" class="form-control form-control-sm" placeholder="End Date" onfocus="(this.type='date')" onblur="(this.type='text')" value="{{ request('end_date') }}">
                            </div>
                            <div class="col-md-2">
                                <select name="user_id" class="form-select form-select-sm">
                                    <option value="">All Users</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" @if(request('user_id') == $user->id) selected @endif>
                                            {{ $user->username }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-1">
                                <button type="submit" class="btn btn-primary btn-sm w-100"><i class="fas fa-filter me-1"></i>Filter</button>
                            </div>
                            <div class="col-md-1">
                                <a href="{{ route('audit-trails.index') }}" class="btn btn-outline-secondary btn-sm w-100"><i class="fas fa-sync-alt me-1"></i>Reset</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @elseif (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="table-responsive">
                <table class="table table-striped table-bordered align-items-center mb-0">
                    <thead class="table-primary">
                        <tr>
                            <th>#</th>
                            <th>User</th>
                            <th>Role</th>
                            <th>Action</th>
                            <th>Description</th>
                            <th>Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($auditLogs as $index => $log)
                            <tr>
                                <td>{{ $index + $auditLogs->firstItem() }}</td>
                                <td>
                                    <span class="badge bg-primary">
                                        {{ $log->user?->username ?? 'System' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-info text-dark">
                                        {{ $log->user?->roles->first()->name ?? 'N/A' }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $actionClass = 'bg-secondary'; // Default
                                        if (in_array($log->action, ['create', 'created_salary_scale', 'created_employee', 'login', 'created_user', 'created_payroll', 'created_addition'])) {
                                            $actionClass = 'bg-success';
                                        } elseif (in_array($log->action, ['update', 'updated_salary_scale', 'updated_employee', 'updated_user', 'updated_payroll'])) {
                                            $actionClass = 'bg-warning text-dark';
                                        } elseif (in_array($log->action, ['delete', 'deleted_salary_scale', 'deleted_employee', 'logout', 'deleted_user', 'deleted_payroll', 'deleted_deduction'])) {
                                            $actionClass = 'bg-danger';
                                        }
                                    @endphp
                                    <span class="badge {{ $actionClass }}">
                                        {{ ucfirst(str_replace('_', ' ', $log->action)) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="text-muted">
                                        {{ $log->description }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-dark">
                                        {{ \Carbon\Carbon::parse($log->action_timestamp)->format('Y-m-d H:i:s') }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">No audit logs found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-3">
                {{ $auditLogs->appends(request()->query())->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>
@endsection