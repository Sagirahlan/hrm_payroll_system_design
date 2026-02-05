@foreach ($employees as $employee)
    <tr>
        <td class="text-center"><input type="checkbox" class="employee-checkbox" name="employee_ids[]" value="{{ $employee->employee_id }}" form="bulk-assignment-form"></td>
        <td>{{ $employee->staff_no }}</td>
        <td>
            {{ $employee->first_name }} {{ $employee->surname }}
            @if($employee->status === 'Retired')
                <span class="badge bg-warning text-dark ms-2">Retiring this Month</span>
            @endif
        </td>
        <td>{{ $employee->department->department_name ?? 'N/A' }}</td>
        <td>{{ $employee->gradeLevel->name ?? 'N/A' }}</td>
        <td>{{ $employee->appointmentType->name ?? 'N/A' }}</td>
    </tr>
@endforeach