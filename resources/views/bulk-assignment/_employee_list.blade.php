<div class="table-responsive" style="max-height: 450px;">
    <table class="table table-bordered table-striped table-hover">
        <thead class="table-light sticky-top">
            <tr>
                <th class="text-center"><input type="checkbox" id="select-all"></th>
                <th>Employee ID</th>
                <th>Name</th>
                <th>Department</th>
            </tr>
        </thead>
        <tbody>
            @forelse($employees as $employee)
                <tr>
                    <td class="text-center"><input type="checkbox" name="employee_ids[]" value="{{ $employee->employee_id }}" class="employee-checkbox"></td>
                    <td>{{ $employee->employee_id }}</td>
                    <td>{{ $employee->first_name }} {{ $employee->surname }}</td>
                    <td>{{ $employee->department->department_name ?? 'N/A' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center">No employees found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-3">
    {{ $employees->links('pagination::bootstrap-4') }}
</div>
