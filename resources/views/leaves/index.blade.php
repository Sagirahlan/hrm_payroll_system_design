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
                                            <small class="text-muted">{{ $leave->employee->department->department_name ?? 'N/A' }}</small>
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
                </div>
            </div>
        </div>
    </div>
</div>
@endsection