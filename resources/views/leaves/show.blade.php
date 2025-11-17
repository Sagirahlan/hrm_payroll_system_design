@extends('layouts.app')

@section('title', 'Leave Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title">Leave Details</h4>
                    <a href="{{ route('leaves.index') }}" class="btn btn-secondary">Back to List</a>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Employee</label>
                                <p class="form-control-plaintext">
                                    @if($leave->employee)
                                        {{ $leave->employee->first_name }} {{ $leave->employee->surname }} ({{ $leave->employee->staff_no }})
                                        <br>
                                        <small class="text-muted">
                                            {{ $leave->employee->department->department_name ?? 'N/A' }} |
                                            {{ $leave->employee->appointmentType->name ?? 'N/A' }} |
                                            {{ $leave->employee->status ?? 'N/A' }}
                                        </small>
                                    @else
                                        <span class="text-danger">Employee Record Not Found</span>
                                        <br>
                                        <small class="text-muted">
                                            Employee ID: {{ $leave->employee_id }} (Record Deleted)
                                        </small>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Leave Type</label>
                                <p class="form-control-plaintext">{{ $leave->leave_type }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Start Date</label>
                                <p class="form-control-plaintext">{{ \Carbon\Carbon::parse($leave->start_date)->format('d M Y') }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">End Date</label>
                                <p class="form-control-plaintext">{{ \Carbon\Carbon::parse($leave->end_date)->format('d M Y') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Days Requested</label>
                                <p class="form-control-plaintext">{{ $leave->days_requested }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <p class="form-control-plaintext">
                                    @if($leave->status === 'pending')
                                        <span class="badge bg-warning">Pending</span>
                                    @elseif($leave->status === 'approved')
                                        <span class="badge bg-success">Approved</span>
                                    @else
                                        <span class="badge bg-danger">Rejected</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">Reason</label>
                                <p class="form-control-plaintext">{{ $leave->reason }}</p>
                            </div>
                        </div>
                    </div>

                    @if($leave->approval_remarks)
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">Approval Remarks</label>
                                <p class="form-control-plaintext">{{ $leave->approval_remarks }}</p>
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Requested On</label>
                                <p class="form-control-plaintext">{{ \Carbon\Carbon::parse($leave->created_at)->format('d M Y h:i A') }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            @if($leave->approved_at)
                            <div class="mb-3">
                                <label class="form-label">Approved On</label>
                                <p class="form-control-plaintext">{{ \Carbon\Carbon::parse($leave->approved_at)->format('d M Y h:i A') }}</p>
                            </div>
                            @endif
                        </div>
                    </div>

                    @if($leave->status === 'pending')
                    <div class="d-flex justify-content-end">
                        @can('manage_leaves')
                            <a href="{{ route('leaves.edit', $leave->id) }}" class="btn btn-warning me-2">Edit</a>
                        @endcan
                        @can('approve_leaves')
                            <form action="{{ route('leaves.approve', $leave->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('POST')
                                <input type="hidden" name="status" value="approved">
                                <button type="submit" class="btn btn-success me-2" onclick="return confirm('Are you sure you want to approve this leave?')">Approve</button>
                            </form>
                            <form action="{{ route('leaves.approve', $leave->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('POST')
                                <input type="hidden" name="status" value="rejected">
                                <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to reject this leave?')">Reject</button>
                            </form>
                        @endcan
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection