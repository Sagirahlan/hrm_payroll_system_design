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
