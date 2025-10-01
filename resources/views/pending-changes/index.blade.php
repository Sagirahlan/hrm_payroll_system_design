@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <!-- Pending Employee Data Changes Table -->
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Pending Employee Data Changes</h6>
                    </div>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    <!-- Search and Filter Form -->
                    <div class="px-4 py-3 bg-light">
                        <form method="GET" action="{{ route('pending-changes.index') }}" class="row g-3">
                            <div class="col-md-4">
                                <label for="search" class="form-label">Search</label>
                                <input type="text" class="form-control" id="search" name="search" value="{{ request('search') }}" placeholder="Search by employee name or ID">
                            </div>
                            <div class="col-md-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="">All Statuses</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="change_type" class="form-label">Change Type</label>
                                <select class="form-select" id="change_type" name="change_type">
                                    <option value="">All Types</option>
                                    <option value="create" {{ request('change_type') == 'create' ? 'selected' : '' }}>Create</option>
                                    <option value="update" {{ request('change_type') == 'update' ? 'selected' : '' }}>Update</option>
                                    <option value="delete" {{ request('change_type') == 'delete' ? 'selected' : '' }}>Delete</option>
                                </select>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <div>
                                    <button type="submit" class="btn btn-primary me-2">Filter</button>
                                    <a href="{{ route('pending-changes.index') }}" class="btn btn-secondary">Clear</a>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-black">Employee</th>
                                    <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-black">Change Type</th>
                                    <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-black">Description</th>
                                    <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-black">Requested By</th>
                                    <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-black">Requested At</th>
                                    <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-black">Status</th>
                                    <th class="text-secondary opacity-7 text-black"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pendingChanges as $change)
                                <tr>
                                    <td>
                                        <div class="d-flex px-2 py-1">
                                            <div class="d-flex flex-column justify-content-center">
                                                <h6 class="mb-0 text-sm text-black">
                                                    {{ $change->employee_name }}
                                                </h6>
                                                @if($change->employee)
                                                    <p class="text-xs text-secondary mb-0 text-black">
                                                        {{ $change->employee->employee_id }}
                                                    </p>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-sm bg-gradient-{{ $change->change_type == 'create' ? 'success' : ($change->change_type == 'update' ? 'warning' : 'danger') }} text-black">
                                            {{ ucfirst($change->change_type) }}
                                        </span>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0 text-black">{{ $change->change_description }}</p>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0 text-black">{{ $change->requestedBy->username }}</p>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0 text-black">{{ $change->created_at->format('M d, Y H:i') }}</p>
                                    </td>
                                    <td>
                                        <span class="badge badge-sm bg-gradient-{{ $change->status == 'pending' ? 'secondary' : ($change->status == 'approved' ? 'success' : 'danger') }} text-black">
                                            {{ ucfirst($change->status) }}
                                        </span>
                                    </td>
                                    <td class="align-middle">
                                        <a href="{{ route('pending-changes.show', $change) }}" class="text-secondary font-weight-bold text-xs text-black" data-toggle="tooltip" data-original-title="View">
                                            View
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center">
                                        <p class="text-sm text-muted text-black">No pending employee data changes found.</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="d-flex justify-content-center mt-4">
                        {{ $pendingChanges->links() }}
                    </div>
                </div>
            </div>

            <!-- Pending Promotions/Demotions Table -->
            @if($pendingPromotions && $pendingPromotions->count() > 0)
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Pending Promotions/Demotions</h6>
                    </div>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-black">Employee</th>
                                    <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-black">Change Type</th>
                                    <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-black">Details</th>
                                    <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-black">Requested By</th>
                                    <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-black">Requested At</th>
                                    <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-black">Status</th>
                                    <th class="text-secondary opacity-7 text-black">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pendingPromotions as $promotion)
                                <tr>
                                    <td>
                                        <div class="d-flex px-2 py-1">
                                            <div class="d-flex flex-column justify-content-center">
                                                <h6 class="mb-0 text-sm text-black">
                                                    {{ $promotion->employee->first_name }} {{ $promotion->employee->surname }}
                                                </h6>
                                                <p class="text-xs text-secondary mb-0 text-black">
                                                    {{ $promotion->employee->employee_id }}
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-sm bg-gradient-{{ $promotion->promotion_type == 'promotion' ? 'success' : 'danger' }} text-black">
                                            {{ ucfirst($promotion->promotion_type) }}
                                        </span>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0 text-black">
                                            Grade: {{ $promotion->previous_grade_level }} → {{ $promotion->new_grade_level }}
                                        </p>
                                        <p class="text-xs text-secondary mb-0 text-black">
                                            Reason: {{ $promotion->reason }}
                                        </p>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0 text-black">
                                            @if($promotion->creator)
                                                {{ $promotion->creator->username }}
                                            @else
                                                N/A
                                            @endif
                                        </p>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0 text-black">{{ $promotion->created_at->format('M d, Y H:i') }}</p>
                                    </td>
                                    <td>
                                        <span class="badge badge-sm bg-gradient-secondary text-black">
                                            {{ ucfirst($promotion->status) }}
                                        </span>
                                    </td>
                                    <td class="align-middle">
                                        <a href="{{ route('promotions.show', $promotion->id) }}" class="text-secondary font-weight-bold text-xs text-black me-2" data-toggle="tooltip" data-original-title="View">
                                            View
                                        </a>
                                        <div class="btn-group btn-group-xs">
                                            <form action="{{ route('promotions.approve', $promotion) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('POST')
                                                <button type="submit" class="btn btn-xs btn-success" onclick="return confirm('Are you sure you want to approve this promotion/demotion?')">Approve</button>
                                            </form>
                                            <form action="{{ route('promotions.reject', $promotion) }}" method="POST" class="d-inline ms-1">
                                                @csrf
                                                @method('POST')
                                                <button type="submit" class="btn btn-xs btn-danger" onclick="return confirm('Are you sure you want to reject this promotion/demotion?')">Reject</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection