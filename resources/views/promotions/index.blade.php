@extends('layouts.app')

@section('title', 'Promotions & Demotions')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Promotions & Demotions</h3>
                    @can('create_promotions')
                    <a href="{{ route('promotions.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> New Promotion/Demotion
                    </a>
                    @endcan
                </div>
                <div class="card-body">
                    <!-- Search and Filter Form -->
                    <form method="GET" action="{{ route('promotions.index') }}" class="mb-4">
                        <div class="row">
                            <div class="col-md-2">
                                <input type="text" name="search" class="form-control" placeholder="Search employees..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-2">
                                <select name="type" class="form-control">
                                    <option value="">All Types</option>
                                    <option value="promotion" {{ request('type') == 'promotion' ? 'selected' : '' }}>Promotion</option>
                                    <option value="demotion" {{ request('type') == 'demotion' ? 'selected' : '' }}>Demotion</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="status" class="form-control">
                                    <option value="">All Statuses</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="employee_id" class="form-control select2">
                                    <option value="">All Employees</option>
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->employee_id }}" {{ request('employee_id') == $employee->employee_id ? 'selected' : '' }}>
                                            {{ trim($employee->first_name . ' ' . $employee->middle_name . ' ' . $employee->surname) }} ({{ $employee->employee_id }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="date" name="promotion_date" class="form-control" placeholder="Promotion Date" value="{{ request('promotion_date') }}">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">Filter</button>
                            </div>
                        </div>
                    </form>

                    <!-- Promotions Table -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Type</th>
                                    <th>Previous Grade</th>
                                    <th>New Grade</th>
                                    <th>Promotion Date</th>
                                    <th>Effective Date</th>
                                    <th>Status</th>
                                    <th>Created By</th>
                                    @can('view_promotions')
                                    <th>Actions</th>
                                    @endcan
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($promotions as $promotion)
                                    <tr>
                                        <td>
                                            {{ trim($promotion->employee->first_name . ' ' . $promotion->employee->middle_name . ' ' . $promotion->employee->surname) ?? 'N/A' }}<br>
                                            <small class="text-muted">{{ $promotion->employee->employee_id ?? 'N/A' }}</small>
                                        </td>
                                        <td>
                                            <span class="badge badge-{{ $promotion->promotion_type === 'promotion' ? 'success' : 'warning' }}">
                                                {{ ucfirst($promotion->promotion_type) }}
                                            </span>
                                        </td>
                                        <td>{{ $promotion->previous_grade_level }}</td>
                                        <td>{{ $promotion->new_grade_level }}</td>
                                        <td>{{ \Carbon\Carbon::parse($promotion->promotion_date)->format('Y-m-d') }}</td>
                                        <td>{{ \Carbon\Carbon::parse($promotion->effective_date)->format('Y-m-d') }}</td>
                                        <td>
                                            <span class="badge badge-{{ $promotion->status === 'approved' ? 'success' : ($promotion->status === 'rejected' ? 'danger' : 'warning') }}">
                                                {{ ucfirst($promotion->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $promotion->creator->name ?? 'System' }}</td>
                                        @can('view_promotions')
                                        <td>
                                            <a href="{{ route('promotions.show', $promotion->id) }}" class="btn btn-sm btn-info">View</a>
                                        </td>
                                        @endcan
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center">No promotions or demotions found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center">
                        {{ $promotions->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    $('.select2').select2({
        placeholder: 'Select an employee',
        allowClear: true
    });
});
</script>
@endsection