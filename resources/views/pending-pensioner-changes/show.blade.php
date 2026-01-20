@extends('layouts.app')

@section('title', 'View Pending Pensioner Change')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Pending Pensioner Change #{{ $pendingChange->id }}</h4>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Pensioner:</strong>
                            <p>{{ $pendingChange->pensioner_name }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Change Type:</strong>
                            <span class="badge bg-{{ $pendingChange->change_type == 'update' ? 'warning' : ($pendingChange->change_type == 'create' ? 'success' : 'danger') }} text-white">
                                {{ ucfirst($pendingChange->change_type) }}
                            </span>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Requested By:</strong>
                            <p>{{ $pendingChange->requestedBy->username ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Status:</strong>
                            <span class="badge bg-{{ $pendingChange->status == 'pending' ? 'secondary' : ($pendingChange->status == 'approved' ? 'success' : 'danger') }} text-white">
                                {{ ucfirst($pendingChange->status) }}
                            </span>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Requested At:</strong>
                            <p>{{ $pendingChange->created_at->format('d M Y H:i:s') }}</p>
                        </div>
                        <div class="col-md-6">
                            @if($pendingChange->status !== 'pending')
                                <strong>{{ ucfirst($pendingChange->status) }} By:</strong>
                                <p>{{ $pendingChange->approvedBy->username ?? 'N/A' }}</p>
                                
                                <strong>{{ ucfirst($pendingChange->status) }} At:</strong>
                                <p>{{ $pendingChange->approved_at ? $pendingChange->approved_at->format('d M Y H:i:s') : 'N/A' }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="mb-3">
                        <strong>Reason:</strong>
                        <p>{{ $pendingChange->reason ?? 'N/A' }}</p>
                    </div>

                    @if($pendingChange->approval_notes)
                        <div class="mb-3">
                            <strong>Approval Notes:</strong>
                            <p>{{ $pendingChange->approval_notes }}</p>
                        </div>
                    @endif

                    @if($pendingChange->change_type === 'update')
                        <div class="card">
                            <div class="card-header">
                                <h5>Change Comparison</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6>Previous Values</h6>
                                        <dl class="row">
                                            @foreach($displayableOldData as $key => $value)
                                                <dt class="col-sm-5">{{ ucfirst(str_replace('_', ' ', $key)) }}:</dt>
                                                <dd class="col-sm-7">{{ $value ?: 'N/A' }}</dd>
                                            @endforeach
                                        </dl>
                                    </div>
                                    <div class="col-md-6">
                                        <h6>New Values</h6>
                                        <dl class="row">
                                            @foreach($displayableNewData as $key => $value)
                                                <dt class="col-sm-5">{{ ucfirst(str_replace('_', ' ', $key)) }}:</dt>
                                                <dd class="col-sm-7">{{ $value ?: 'N/A' }}</dd>
                                            @endforeach
                                        </dl>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="card">
                            <div class="card-header">
                                <h5>Data</h5>
                            </div>
                            <div class="card-body">
                                @if($pendingChange->change_type === 'create')
                                    <dl class="row">
                                        @foreach($displayableNewData as $key => $value)
                                            <dt class="col-sm-4">{{ ucfirst(str_replace('_', ' ', $key)) }}:</dt>
                                            <dd class="col-sm-8">{{ $value ?: 'N/A' }}</dd>
                                        @endforeach
                                    </dl>
                                @endif
                            </div>
                        </div>
                    @endif

                    @if($pendingChange->status === 'pending')
                        <div class="mt-4">
                            @can('approve_pensioner_changes')
                                <form action="{{ route('pending-pensioner-changes.approve', $pendingChange->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <div class="mb-3">
                                        <label for="approval_notes" class="form-label">Approval Notes (Optional)</label>
                                        <textarea class="form-control" id="approval_notes" name="approval_notes"></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-success" onclick="return confirm('Are you sure you want to approve this change?')">Approve Change</button>
                                </form>
                                
                                <form action="{{ route('pending-pensioner-changes.reject', $pendingChange->id) }}" method="POST" class="d-inline ms-2">
                                    @csrf
                                    <div class="mb-3">
                                        <label for="rejection_notes" class="form-label">Rejection Notes</label>
                                        <textarea class="form-control" id="rejection_notes" name="approval_notes" required></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to reject this change?')">Reject Change</button>
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