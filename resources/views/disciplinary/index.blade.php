@extends('layouts.app')

@section('title', 'Disciplinary Actions')

@section('content')
<div class="container-fluid py-4">
    @can('manage_disciplinary')
    <div class="card border-primary shadow">
        <div class="card-header" style="background-color: skyblue; color: white;">
        </div>
        <div class="card-body">
            <div class="d-flex justify-content-between mb-3">
                @can('create_disciplinary')
                <a href="{{ route('disciplinary.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Disciplinary Action
                </a>
                @endcan
                <form action="{{ route('disciplinary.index') }}" method="GET" class="d-flex">
                    <input type="text" name="search" class="form-control me-2" placeholder="Search by employee, action type, or status" value="{{ request('search') }}">
                    <button type="submit" class="btn btn-outline-primary me-2">
                        <i class="fas fa-search"></i> Search
                    </button>
                    @if (request('search'))
                        <a href="{{ route('disciplinary.index') }}" class="btn btn-outline-secondary">Reset</a>
                    @endif
                </form>
            </div>
            <div class="mb-3">
                <form action="{{ route('disciplinary.index') }}" method="GET" class="d-flex">
                    <select name="department" class="form-select me-2">
                        <option value="">All Departments</option>
                        @foreach ($departments as $department)
                            <option value="{{ $department->department_id }}" {{ request('department') == $department->department_id ? 'selected' : '' }}>{{ $department->department_name }}</option>
                        @endforeach
                    </select>
                    <select name="status" class="form-select me-2">
                        <option value="">All Statuses</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>{{ $status }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-outline-primary">Filter</button>
                    @if (request('department') || request('status'))
                        <a href="{{ route('disciplinary.index') }}" class="btn btn-outline-secondary ms-2">Clear Filters</a>
                    @endif
                </form>
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card border-primary mb-3">
                <div class="card-header" style="background-color: skyblue; color: white;">
                    <strong>All Disciplinary Actions</strong>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered align-items-center mb-0">
                            <thead class="table-primary">
                                <tr>
                                    <th>Employee</th>
                                    <th>Department</th>
                                    <th>Action Type</th>
                                    <th>Description</th>
                                    <th>Action Date</th>
                                    <th>Resolution Date</th>
                                    <th>Status</th>
                                    @canany(['view_disciplinary', 'edit_disciplinary', 'delete_disciplinary'])
                                    <th>Actions</th>
                                    @endcanany
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($actions as $action)
                                    <tr>
                                        <td>{{ $action->employee ? $action->employee->first_name . ' ' . $action->employee->surname : 'N/A' }}</td>
                                        <td>{{ $action->employee ? $action->employee->department->department_name : 'N/A' }}</td>
                                        <td>{{ $action->action_type }}</td>
                                        <td>{{ $action->description ?? 'N/A' }}</td>
                                        <td>{{ $action->action_date }}</td>
                                        <td>{{ $action->resolution_date ?? 'N/A' }}</td>
                                        <td>
                                            <span class="badge bg-info text-dark">{{ $action->status }}</span>
                                        </td>
                                        @canany(['view_disciplinary', 'edit_disciplinary', 'delete_disciplinary'])
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" id="actionDropdown{{ $action->action_id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                                    Actions
                                                </button>
                                                <ul class="dropdown-menu" aria-labelledby="actionDropdown{{ $action->action_id }}">
                                                    @can('view_disciplinary')
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('disciplinary.show', $action->action_id) }}">
                                                            <i class="fas fa-eye"></i> View
                                                        </a>
                                                    </li>
                                                    @endcan
                                                    @can('edit_disciplinary')
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('disciplinary.edit', $action->action_id) }}">
                                                            <i class="fas fa-edit"></i> Edit
                                                        </a>
                                                    </li>
                                                    @endcan
                                                    @can('delete_disciplinary')
                                                    <li>
                                                        <form action="{{ route('disciplinary.destroy', $action->action_id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this disciplinary action?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="dropdown-item text-danger">
                                                                <i class="fas fa-trash"></i> Delete
                                                            </button>
                                                        </form>
                                                    </li>
                                                    @endcan
                                                </ul>
                                            </div>
                                        </td>
                                        @endcanany
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8">No disciplinary actions found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-center mt-3">
                        {{ $actions->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="alert alert-warning">
        You don't have permission to manage disciplinary actions.
    </div>
    @endcan
</div>
@endsection