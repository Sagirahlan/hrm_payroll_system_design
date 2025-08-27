@extends('layouts.app')

@section('title', 'Audit Trail Logs')

@section('content')
<div class="container-fluid py-4">
    <div class="card border-primary shadow">
        <div class="card-header d-flex justify-content-between align-items-center" style="background-color: skyblue; color: white;">
            <h5 class="mb-0">Audit Trail Logs</h5>
            <form method="GET" action="{{ route('audit-trails.index') }}" class="w-100">
                <div class="row g-3 align-items-center">
                    <div class="col-md-3">
                        <input type="text" name="search" class="form-control" placeholder="Search logs..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <select name="action" class="form-select">
                            <option value="">All Actions</option>
                            <option value="create" @if(request('action') == 'create') selected @endif>Create</option>
                            <option value="update" @if(request('action') == 'update') selected @endif>Update</option>
                            <option value="delete" @if(request('action') == 'delete') selected @endif>Delete</option>
                            <option value="login" @if(request('action') == 'login') selected @endif>Login</option>
                            <option value="logout" @if(request('action') == 'logout') selected @endif>Logout</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="text" name="start_date" class="form-control" placeholder="Start Date" onfocus="(this.type='date')" onblur="(this.type='text')" value="{{ request('start_date') }}">
                    </div>
                    <div class="col-md-2">
                        <input type="text" name="end_date" class="form-control" placeholder="End Date" onfocus="(this.type='date')" onblur="(this.type='text')" value="{{ request('end_date') }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </div>
            </form>
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
                                        if (in_array($log->action, ['create', 'login'])) {
                                            $actionClass = 'bg-success';
                                        } elseif (in_array($log->action, ['update'])) {
                                            $actionClass = 'bg-warning text-dark';
                                        } elseif (in_array($log->action, ['delete', 'logout'])) {
                                            $actionClass = 'bg-danger';
                                        }
                                    @endphp
                                    <span class="badge {{ $actionClass }}">
                                        {{ ucfirst($log->action) }}
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