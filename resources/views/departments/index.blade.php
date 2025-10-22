@extends('layouts.app')

@section('title', 'Departments')

@section('content')
<div class="container mt-4">
    <h1 class="mb-4 text-primary border-bottom border-3 border-primary pb-2">Departments</h1>
   
    @can('create_departments')
    <a href="{{ route('departments.create') }}" class="btn btn-primary mb-3">
        <i class="fas fa-plus"></i> Add Department
    </a>
    @endcan

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show border border-success" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show border border-danger" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-primary shadow-sm">
        <div class="card-body">
            <table class="table table-striped table-bordered align-middle">
                <thead class="table-primary">
                    <tr>
                        <th class="border-primary">Name</th>
                        <th class="border-primary">Description</th>
                        <th class="border-primary">Employees</th>
                        @canany(['edit_departments', 'delete_departments'])
                        <th class="border-primary">Actions</th>
                        @endcanany
                    </tr>
                </thead>
                <tbody>
                    @forelse ($departments as $department)
                        <tr>
                            <td class="border-primary">{{ $department->department_name }}</td>
                            <td class="border-primary">{{ $department->description ?? 'N/A' }}</td>
                            <td class="border-primary">
                                <button 
                                    type="button" 
                                    class="btn btn-link p-0 text-decoration-none" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#employeesModal{{ $department->id }}"
                                >
                                    {{ $department->employees()->count() }}
                                </button>

                                <!-- Employees Modal -->
                                <div class="modal fade" id="employeesModal{{ $department->id }}" tabindex="-1" aria-labelledby="employeesModalLabel{{ $department->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="employeesModalLabel{{ $department->id }}">
                                                    Employees in {{ $department->department_name }}
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                @php
                                                    $employees = $department->employees()->get();
                                                @endphp
                                                @if($employees->count())
                                                    <table class="table table-sm table-bordered align-middle">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th>#</th>
                                                                <th>Employee ID</th>
                                                                <th>Name</th>
                                                                <th>Status</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($employees as $index => $employee)
                                                                <tr>
                                                                    <td>{{ $index + 1 }}</td>
                                                                    <td>{{ $employee->employee_id }}</td>
                                                                    <td>{{ $employee->first_name }} {{ $employee->surname }}</td>
                                                                    <td>
                                                                        <span class="badge bg-{{ $employee->status === 'Active' ? 'success' : 'secondary' }}">
                                                                            {{ $employee->status }}
                                                                        </span>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                @else
                                                    <div class="text-muted text-center py-3">
                                                        No employees found in this department.
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            @canany(['edit_departments', 'delete_departments'])
                            <td class="border-primary">
                                <div class="dropdown">
                                    <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" id="actionsDropdown{{ $department->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                        Actions
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="actionsDropdown{{ $department->id }}">
                                        @can('edit_departments')
                                        <li>
                                            <a href="{{ route('departments.edit', $department) }}" class="dropdown-item">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                        </li>
                                        @endcan
                                        @can('delete_departments')
                                        <li>
                                            <form action="{{ route('departments.destroy', $department) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this department?')" style="display:inline;">
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
                            <td colspan="4" class="text-center text-muted">No departments found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="d-flex justify-content-center">
                {{ $departments->links() }}
            </div>
        </div>
    </div>
</div>
@endsection