@extends('layouts.app')

@section('title', 'Bulk Additions/Deductions')

@section('content')
<div class="container-fluid">
    <h1>Bulk Additions/Deductions Assignment</h1>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form action="{{ route('bulk-assignment.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="adjustment_type">Adjustment Type</label>
                            <select name="adjustment_type" id="adjustment_type" class="form-control" required>
                                <option value="">-- Select Type --</option>
                                <option value="addition">Addition</option>
                                <option value="deduction">Deduction</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="type_id">Select Item</label>
                            <select name="type_id" id="type_id" class="form-control" required disabled>
                                <!-- Options will be populated by JavaScript -->
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="amount">Amount</label>
                            <input type="number" name="amount" id="amount" class="form-control" required step="0.01">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="period">Frequency</label>
                            <select name="period" id="period" class="form-control" required>
                                <option value="OneTime">One Time</option>
                                <option value="Monthly">Monthly</option>
                                <option value="Perpetual">Perpetual</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="start_date">Start Date</label>
                            <input type="date" name="start_date" id="start_date" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="end_date">End Date (optional)</label>
                            <input type="date" name="end_date" id="end_date" class="form-control">
                        </div>
                    </div>
                </div>

                <hr>

                <h4>Select Employees</h4>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="Search by name or ID" value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <select name="department_id" class="form-control">
                            <option value="">-- Select Department --</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}" {{ request('department_id') == $department->id ? 'selected' : '' }}>{{ $department->department_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="grade_level_id" class="form-control">
                            <option value="">-- Select Grade Level --</option>
                            @foreach($gradeLevels as $gradeLevel)
                                <option value="{{ $gradeLevel->id }}" {{ request('grade_level_id') == $gradeLevel->id ? 'selected' : '' }}>{{ $gradeLevel->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" formaction="{{ route('bulk-assignment.create') }}" formmethod="GET" class="btn btn-secondary">Filter</button>
                        <a href="{{ route('bulk-assignment.create') }}" class="btn btn-outline-secondary">Clear</a>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="select-all"></th>
                                <th>Employee ID</th>
                                <th>Name</th>
                                <th>Department</th>
                                <th>Grade Level</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($employees as $employee)
                                <tr>
                                    <td><input type="checkbox" name="employee_ids[]" value="{{ $employee->employee_id }}"></td>
                                    <td>{{ $employee->employee_id }}</td>
                                    <td>{{ $employee->first_name }} {{ $employee->surname }}</td>
                                    <td>{{ $employee->department->department_name ?? 'N/A' }}</td>
                                    <td>{{ $employee->gradeLevel->name ?? 'N/A' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">No employees found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{ $employees->links() }}

                <button type="submit" class="btn btn-primary mt-3">Assign to Selected Employees</button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('adjustment_type').addEventListener('change', function() {
        var type = this.value;
        var typeSelect = document.getElementById('type_id');
        typeSelect.innerHTML = '<option value="">-- Select Item --</option>';
        typeSelect.disabled = true;

        if (type) {
            var items = (type === 'addition') ? @json($additionTypes) : @json($deductionTypes);
            
            items.forEach(function(item) {
                var option = document.createElement('option');
                option.value = item.id;
                option.textContent = item.name;
                typeSelect.appendChild(option);
            });

            typeSelect.disabled = false;
        }
    });

    document.getElementById('select-all').addEventListener('change', function() {
        var checkboxes = document.querySelectorAll('input[name="employee_ids[]"]');
        checkboxes.forEach(function(checkbox) {
            checkbox.checked = document.getElementById('select-all').checked;
        });
    });
</script>
@endpush
@endsection
