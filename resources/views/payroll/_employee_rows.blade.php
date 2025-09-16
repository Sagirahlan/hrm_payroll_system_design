@foreach ($employees as $employee)
    <tr>
        <td class="text-center"><input type="checkbox" class="employee-checkbox" name="employee_ids[]" value="{{ $employee->employee_id }}" form="bulk-assignment-form"></td>
        <td>{{ $employee->employee_id }}</td>
        <td>{{ $employee->first_name }} {{ $employee->surname }}</td>
        <td>{{ $employee->department->department_name ?? 'N/A' }}</td>
        <td>{{ $employee->gradeLevel->name ?? 'N/A' }}</td>
    </tr>
@endforeach