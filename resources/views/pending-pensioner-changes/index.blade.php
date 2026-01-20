@extends('layouts.app')

@section('title', 'Pending Pensioner Changes')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Pending Pensioner Changes</h4>
                    
                    <!-- Filters -->
                    <div class="row mt-3">
                        <div class="col-md-4">
                            <select class="form-control" onchange="window.location='?status='+this.value+'&change_type='+getParam('change_type')+'&search='+getParam('search')">
                                <option value="">All Status</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <select class="form-control" onchange="window.location='?status='+getParam('status')+'&change_type='+this.value+'&search='+getParam('search')">
                                <option value="">All Types</option>
                                <option value="create" {{ request('change_type') == 'create' ? 'selected' : '' }}>Create</option>
                                <option value="update" {{ request('change_type') == 'update' ? 'selected' : '' }}>Update</option>
                                <option value="delete" {{ request('change_type') == 'delete' ? 'selected' : '' }}>Delete</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <form method="GET" class="d-flex">
                                <input type="text" class="form-control me-2" name="search" placeholder="Search pensioners..." value="{{ request('search') }}">
                                <button class="btn btn-outline-primary" type="submit">Search</button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Pensioner</th>
                                    <th>Change Type</th>
                                    <th>Requested By</th>
                                    <th>Changes</th>
                                    <th>Status</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pendingChanges as $change)
                                    <tr>
                                        <td>{{ $change->id }}</td>
                                        <td>{{ $change->pensioner_name }}</td>
                                        <td>
                                            <span class="badge bg-{{ $change->change_type == 'update' ? 'warning' : ($change->change_type == 'create' ? 'success' : 'danger') }} text-white">
                                                {{ ucfirst($change->change_type) }}
                                            </span>
                                        </td>
                                        <td>{{ $change->requestedBy->username ?? 'N/A' }}</td>
                                        <td>{{ $change->change_description }}</td>
                                        <td>
                                            <span class="badge bg-{{ $change->status == 'pending' ? 'secondary' : ($change->status == 'approved' ? 'success' : 'danger') }} text-white">
                                                {{ ucfirst($change->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $change->created_at->format('d M Y H:i') }}</td>
                                        <td>
                                            <a href="{{ route('pending-pensioner-changes.show', $change->id) }}" class="btn btn-info btn-sm">View</a>
                                            
                                            @if($change->status === 'pending')
                                                @can('approve_pensioner_changes')
                                                    <form action="{{ route('pending-pensioner-changes.approve', $change->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Are you sure you want to approve this change?')">Approve</button>
                                                    </form>
                                                    <form action="{{ route('pending-pensioner-changes.reject', $change->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to reject this change?')">Reject</button>
                                                    </form>
                                                @endcan
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">No pending changes found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{ $pendingChanges->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function getParam(param) {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get(param) || '';
}
</script>
@endsection