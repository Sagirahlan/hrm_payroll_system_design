@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    @can('create_disciplinary')
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">New Disciplinary Action</h6>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <form action="{{ route('disciplinary.store') }}" method="POST">
                                @csrf
                                <div class="form-group mb-3">
                                    <label for="employee_id" class="form-label">Select Employee *</label>
                                    <select name="employee_id" id="employee_id" class="form-select" required>
                                        <option value="">Select an employee</option>
                                    </select>
                                    @error('employee_id') <div class="text-danger">{{ $message }}</div> @enderror
                                </div>

                                <div class="form-group mb-3">
                                    <label for="action_type" class="form-label">Action Type *</label>
                                    <select name="action_type" class="form-select" required>
                                        <option value="">Select action type</option>
                                        <option value="suspended">Suspended</option>
                                        <option value="hold">Hold</option>
                                        <option value="warning">Warning</option>
                                        <option value="terminated">Terminated</option>
                                    </select>
                                    @error('action_type') <div class="text-danger">{{ $message }}</div> @enderror
                                </div>

                                <div class="form-group mb-3">
                                    <label for="description" class="form-label">Description *</label>
                                    <textarea name="description" class="form-control" rows="3" required></textarea>
                                    @error('description') <div class="text-danger">{{ $message }}</div> @enderror
                                </div>

                                <div class="form-group mb-3">
                                    <label for="action_date" class="form-label">Action Date *</label>
                                    <input type="date" name="action_date" class="form-control" required>
                                    @error('action_date') <div class="text-danger">{{ $message }}</div> @enderror
                                </div>

                                <div class="form-group mb-3">
                                    <label for="status" class="form-label">Status *</label>
                                    <select name="status" class="form-select" required>
                                        <option value="Open">Open</option>
                                        <option value="Resolved">Resolved</option>
                                        <option value="Pending">Pending</option>
                                    </select>
                                    @error('status') <div class="text-danger">{{ $message }}</div> @enderror
                                </div>

                                <div class="d-flex justify-content-start mt-4">
                                    <button type="submit" class="btn btn-primary">Save Disciplinary Action</button>
                                    <a href="{{ route('disciplinary.index') }}" class="btn btn-secondary ms-2">Cancel</a>
                                </div>
                            </form>
                        </div>

                        <div class="col-md-6">
                            <!-- Search and Filter Form -->
                            <form action="{{ route('disciplinary.create') }}" method="GET" class="mb-4">
                                <div class="row">
                                    <div class="col-md-6">
                                        <input type="text" name="search" id="employeeSearch" class="form-control" placeholder="Search by name or staff ID" value="{{ request('search') }}">
                                    </div>
                                    <div class="col-md-4">
                                        <select name="department" class="form-select">
                                            <option value="">All Departments</option>
                                            @foreach ($departments as $department)
                                                <option value="{{ $department->department_id }}" {{ request('department') == $department->department_id ? 'selected' : '' }}>
                                                    {{ $department->department_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="submit" class="btn btn-primary">Filter</button>
                                        <a href="{{ route('disciplinary.create') }}" class="btn btn-secondary">Clear</a>
                                    </div>
                                </div>
                            </form>

                            <h6>Active Employees</h6>
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Staff ID</th>
                                            <th>Department</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="employeesTable">
                                        @foreach ($employees as $employee)
                                        <tr>
                                            <td>{{ $employee->first_name }} {{ $employee->surname }}</td>
                                            <td>{{ $employee->staff_no }}</td>
                                            <td>{{ $employee->department->department_name ?? 'N/A' }}</td>
                                            <td>
                                                <span class="badge bg-success">{{ $employee->status }}</span>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-primary select-employee"
                                                        data-id="{{ $employee->employee_id }}"
                                                        data-name="{{ $employee->first_name }} {{ $employee->surname }}">
                                                    Select
                                                </button>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <div class="d-flex justify-content-center mt-3">
                                {{ $employees->appends(request()->query())->links('pagination::bootstrap-4') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="alert alert-warning">
        You don't have permission to create disciplinary actions.
    </div>
    @endcan
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Select employee functionality
        document.querySelectorAll('.select-employee').forEach(button => {
            button.addEventListener('click', function() {
                const employeeId = this.getAttribute('data-id');
                const employeeName = this.getAttribute('data-name');

                // Set the selected employee in the dropdown
                const select = document.getElementById('employee_id');

                // Check if option already exists, if not create it
                let option = select.querySelector(`option[value="${employeeId}"]`);
                if (!option) {
                    option = document.createElement('option');
                    option.value = employeeId;
                    option.textContent = employeeName;
                    select.appendChild(option);
                }

                // Select the option
                option.selected = true;

                // Scroll to the form section
                document.querySelector('#employee_id').scrollIntoView({ behavior: 'smooth' });
            });
        });
    });
</script>
@endsection