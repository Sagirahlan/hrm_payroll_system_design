@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Pending Change Details</h6>
                        <a href="{{ route('pending-changes.index') }}" class="btn btn-sm btn-outline-secondary">
                            ← Back to Pending Changes
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Change Information</h6>
                            <dl class="row">
                             
                                
                                <dt class="col-sm-4">Requested By:</dt>
                                <dd class="col-sm-8">{{ $pendingChange->requestedBy->username }}</dd>
                                
                                <dt class="col-sm-4">Requested At:</dt>
                                <dd class="col-sm-8">{{ $pendingChange->created_at->format('M d, Y H:i') }}</dd>
                                
                                @if($pendingChange->approvedBy)
                                <dt class="col-sm-4">Approved/Rejected By:</dt>
                                <dd class="col-sm-8">{{ $pendingChange->approvedBy->username }}</dd>
                                
                                <dt class="col-sm-4">Approved/Rejected At:</dt>
                                <dd class="col-sm-8">{{ $pendingChange->approved_at->format('M d, Y H:i') }}</dd>
                                @endif
                                
                                @if($pendingChange->approval_notes)
                                <dt class="col-sm-4">Notes:</dt>
                                <dd class="col-sm-8">{{ $pendingChange->approval_notes }}</dd>
                                @endif
                            </dl>
                        </div>
                        
                        <div class="col-md-6">
                            <h6>Employee Information</h6>
                            @if($pendingChange->employee)
                            <dl class="row">
                                <dt class="col-sm-4">Name:</dt>
                                <dd class="col-sm-8">{{ $pendingChange->employee->first_name }} {{ $pendingChange->employee->surname }}</dd>
                                
                                <dt class="col-sm-4">Staff No:</dt>
                                <dd class="col-sm-8">{{ $pendingChange->employee->staff_no }}</dd>
                                
                                <dt class="col-sm-4">Department:</dt>
                                <dd class="col-sm-8">{{ $pendingChange->employee->department->department_name ?? 'N/A' }}</dd>
                            </dl>
                            @else
                            <p>New employee pending creation</p>
                            @endif
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-12">
                            <h6>Change Details</h6>
                            @if($pendingChange->change_type === 'update')
                            <div class="table-responsive">
                                <table class="table align-items-center mb-0">
                                    <thead>
                                        <tr>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Field</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Previous Value</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">New Value</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($displayableNewData as $key => $newValue)
                                            @php
                                                $previousValue = $displayableOldData[$key] ?? null;
                                                $hasChanged = App\Helpers\ComparisonHelper::isDifferent($previousValue, $newValue);
                                            @endphp
                                            @if($hasChanged)
                                            <tr>
                                                <td>
                                                    <p class="text-xs font-weight-bold mb-0">{{ ucfirst(str_replace('_', ' ', $key)) }}</p>
                                                </td>
                                                <td>
                                                    <p class="text-xs text-secondary mb-0">{{ is_array($previousValue) ? json_encode($previousValue) : $previousValue }}</p>
                                                </td>
                                                <td>
                                                    <p class="text-xs {{ $hasChanged ? 'text-danger font-weight-bold' : 'text-secondary' }} mb-0">
                                                        {{ is_array($newValue) ? json_encode($newValue) : $newValue }}
                                                    </p>
                                                </td>
                                            </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @else
                            <div class="table-responsive">
                                <table class="table align-items-center mb-0">
                                    <thead>
                                        <tr>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Field</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Value</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($displayableNewData as $key => $value)
                                        <tr>
                                            <td>
                                                <p class="text-xs font-weight-bold mb-0">{{ ucfirst(str_replace('_', ' ', $key)) }}</p>
                                            </td>
                                            <td>
                                                <p class="text-xs text-secondary mb-0">{{ is_array($value) ? json_encode($value) : $value }}</p>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @endif
                        </div>
                    </div>
                    
                    @if($pendingChange->status === 'pending')
                    <div class="row mt-4">
                        <div class="col-12">
                            <h6>Actions</h6>
                            <form action="{{ route('pending-changes.approve', $pendingChange) }}" method="POST" class="d-inline">
                                @csrf
                                <div class="form-group">
                                    <label for="approval_notes" class="form-label">Approval Notes (Optional)</label>
                                    <textarea name="approval_notes" id="approval_notes" class="form-control" rows="2" placeholder="Add any notes for the approval..."></textarea>
                                </div>
                                <button type="submit" class="btn btn-success" onclick="return confirm('Are you sure you want to approve this change?')">
                                    Approve Change
                                </button>
                            </form>
                            
                            <form action="{{ route('pending-changes.reject', $pendingChange) }}" method="POST" class="d-inline ms-2">
                                @csrf
                                <div class="form-group">
                                    <label for="rejection_notes" class="form-label">Rejection Notes (Required)</label>
                                    <textarea name="approval_notes" id="rejection_notes" class="form-control" rows="2" placeholder="Reason for rejection..." required></textarea>
                                </div>
                                <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to reject this change?')">
                                    Reject Change
                                </button>
                            </form>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection