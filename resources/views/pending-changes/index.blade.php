@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Pending Employee Changes</h6>
                    </div>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
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
                                        <p class="text-sm text-muted text-black">No pending changes found.</p>
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
        </div>
    </div>
</div>
@endsection