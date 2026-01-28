@extends('layouts.app')

@section('title', 'Leave Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title">Leave Management</h4>
                    <a href="{{ route('leaves.create') }}" class="btn btn-primary">Request Leave</a>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <!-- Search and Filter Form -->
                    <div class="mb-4 p-3 bg-light rounded">
                        <form method="GET" action="{{ route('leaves.index') }}" class="row g-3">
                            <div class="col-md-3">
                                <label for="search" class="form-label">Search</label>
                                <input type="text" class="form-control" id="search" name="search" value="{{ request('search') }}" placeholder="Name or staff no...">
                            </div>
                            <div class="col-md-2">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="">All Statuses</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="leave_type" class="form-label">Leave Type</label>
                                <input type="text" class="form-control" id="leave_type" name="leave_type" value="{{ request('leave_type') }}" placeholder="e.g., Annual">
                            </div>
                            <div class="col-md-2">
                                <label for="date_from" class="form-label">From Date</label>
                                <input type="date" class="form-control" id="date_from" name="date_from" value="{{ request('date_from') }}">
                            </div>
                            <div class="col-md-2">
                                <label for="date_to" class="form-label">To Date</label>
                                <input type="date" class="form-control" id="date_to" name="date_to" value="{{ request('date_to') }}">
                            </div>
                            <div class="col-md-1 d-flex align-items-end">
                                <div>
                                    <button type="submit" class="btn btn-primary w-100 mb-1">Filter</button>
                                    <a href="{{ route('leaves.index') }}" class="btn btn-secondary w-100">Clear</a>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Employee</th>
                                    <th>Leave Type</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Days</th>
                                    <th>Reason</th>
                                    <th>Status</th>
                                    <th>Requested On</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($leaves as $leave)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        @if($leave->employee)
                                            <div>{{ $leave->employee->first_name }} {{ $leave->employee->surname }}</div>
                                            <small class="text-muted">{{ $leave->employee->staff_no ?? 'N/A' }}</small>
                                        @else
                                            <div class="text-danger">Employee Record Not Found</div>
                                            <small class="text-muted">ID: {{ $leave->employee_id }} (Deleted)</small>
                                        @endif
                                    </td>
                                    <td>{{ $leave->leave_type }}</td>
                                    <td>{{ \Carbon\Carbon::parse($leave->start_date)->format('d M Y') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($leave->end_date)->format('d M Y') }}</td>
                                    <td>{{ $leave->days_requested }}</td>
                                    <td>{{ Str::limit($leave->reason, 50) }}</td>
                                    <td>
                                        @if($leave->status === 'pending')
                                            <span class="badge bg-warning">Pending</span>
                                        @elseif($leave->status === 'approved')
                                            <span class="badge bg-success">Approved</span>
                                        @else
                                            <span class="badge bg-danger">Rejected</span>
                                        @endif
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($leave->created_at)->format('d M Y') }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('leaves.show', $leave->id) }}" class="btn btn-sm btn-info">View</a>
                                            
                                            @if($leave->status === 'pending')
                                                @can('manage_leaves')
                                                    <a href="{{ route('leaves.edit', $leave->id) }}" class="btn btn-sm btn-warning">Edit</a>
                                                @endcan
                                                @can('approve_leaves')
                                                    <form action="{{ route('leaves.approve', $leave->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('POST')
                                                        <input type="hidden" name="status" value="approved">
                                                        <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Are you sure you want to approve this leave?')">Approve</button>
                                                    </form>
                                                    <form action="{{ route('leaves.approve', $leave->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('POST')
                                                        <input type="hidden" name="status" value="rejected">
                                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to reject this leave?')">Reject</button>
                                                    </form>
                                                @endcan
                                            @endif

                                            @can('manage_leaves')
                                                <form action="{{ route('leaves.destroy', $leave->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this leave request?')">Delete</button>
                                                </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="10" class="text-center">No leave requests found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-3">
                        {{ $leaves->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection