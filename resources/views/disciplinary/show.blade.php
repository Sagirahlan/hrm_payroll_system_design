@extends('layouts.app')

@section('title', 'Disciplinary Action Details')

@section('content')
<div class="container py-4">
    @can('view_disciplinary')
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card border-primary shadow">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Disciplinary Action Details</h5>
                        <a href="{{ route('disciplinary.index') }}" class="btn btn-light">
                            <i class="fas fa-arrow-left me-1"></i> Back to List
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Main Disciplinary Action Details -->
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0">Action Details</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th>Employee:</th>
                                            <td>{{ $action->employee ? $action->employee->first_name . ' ' . $action->employee->surname : 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Employee ID:</th>
                                            <td>{{ $action->employee ? $action->employee->employee_id : 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Action Type:</th>
                                            <td>{{ $action->action_type }}</td>
                                        </tr>
                                        <tr>
                                            <th>Description:</th>
                                            <td>{{ $action->description ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Action Date:</th>
                                            <td>{{ $action->action_date ? \Carbon\Carbon::parse($action->action_date)->format('M d, Y') : 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Resolution Date:</th>
                                            <td>{{ $action->resolution_date ? \Carbon\Carbon::parse($action->resolution_date)->format('M d, Y') : 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Status:</th>
                                            <td>
                                                <span class="badge bg-{{ $action->status === 'Open' ? 'warning' : ($action->status === 'Resolved' ? 'success' : 'secondary') }}">
                                                    {{ $action->status }}
                                                </span>
                                            </td>
                                        </tr>
                                    </table>
                                    
                                    @canany(['edit_disciplinary', 'delete_disciplinary'])
                                    <div class="d-flex gap-2 mt-3">
                                        @can('edit_disciplinary')
                                        <a href="{{ route('disciplinary.edit', $action->action_id) }}" class="btn btn-warning">
                                            <i class="fas fa-edit me-1"></i> Edit
                                        </a>
                                        @endcan
                                        
                                        @can('delete_disciplinary')
                                        <form action="{{ route('disciplinary.destroy', $action->action_id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this disciplinary action?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger">
                                                <i class="fas fa-trash me-1"></i> Delete
                                            </button>
                                        </form>
                                        @endcan
                                    </div>
                                    @endcanany
                                </div>
                            </div>
                        </div>
                        
                        <!-- Employee Information -->
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0">Employee Information</h6>
                                </div>
                                <div class="card-body">
                                    @if($action->employee)
                                        <table class="table table-borderless">
                                            <tr>
                                                <th>Full Name:</th>
                                                <td>{{ $action->employee->first_name }} {{ $action->employee->middle_name }} {{ $action->employee->surname }}</td>
                                            </tr>
                                            <tr>
                                                <th>Department:</th>
                                                <td>{{ $action->employee->department->department_name ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Grade Level:</th>
                                                <td>{{ $action->employee->gradeLevel->name ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Step:</th>
                                                <td>{{ $action->employee->step->name ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Status:</th>
                                                <td>
                                                    <span class="badge bg-{{ $action->employee->status === 'Active' ? 'success' : ($action->employee->status === 'Suspended' ? 'warning' : 'secondary') }}">
                                                        {{ $action->employee->status }}
                                                    </span>
                                                </td>
                                            </tr>
                                        </table>
                                    @else
                                        <p class="text-muted">Employee information not available.</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Disciplinary History Section -->
                    <div class="card">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0">Disciplinary History for {{ $action->employee ? $action->employee->first_name . ' ' . $action->employee->surname : 'N/A' }}</h6>
                        </div>
                        <div class="card-body">
                            @if ($disciplinaryHistory->isEmpty())
                                <p class="text-center">No other disciplinary actions found for this employee.</p>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-striped align-middle">
                                        <thead class="table-primary">
                                            <tr>
                                                <th>Action Type</th>
                                                <th>Description</th>
                                                <th>Action Date</th>
                                                <th>Resolution Date</th>
                                                <th>Status</th>
                                                @can('view_disciplinary')
                                                <th>Actions</th>
                                                @endcan
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($disciplinaryHistory as $history)
                                                <tr>
                                                    <td>{{ $history->action_type }}</td>
                                                    <td>{{ $history->description ?? 'N/A' }}</td>
                                                    <td>{{ $history->action_date ? \Carbon\Carbon::parse($history->action_date)->format('M d, Y') : 'N/A' }}</td>
                                                    <td>{{ $history->resolution_date ? \Carbon\Carbon::parse($history->resolution_date)->format('M d, Y') : 'N/A' }}</td>
                                                    <td>
                                                        <span class="badge bg-{{ $history->status === 'Open' ? 'warning' : ($history->status === 'Resolved' ? 'success' : 'secondary') }}">
                                                            {{ $history->status }}
                                                        </span>
                                                    </td>
                                                    @can('view_disciplinary')
                                                    <td>
                                                        <a href="{{ route('disciplinary.show', $history->action_id) }}" class="btn btn-sm btn-primary">
                                                            <i class="fas fa-eye me-1"></i> View
                                                        </a>
                                                    </td>
                                                    @endcan
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="alert alert-warning">
        You don't have permission to view disciplinary actions.
    </div>
    @endcan
</div>
@endsection