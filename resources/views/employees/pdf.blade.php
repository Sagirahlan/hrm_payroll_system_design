<!DOCTYPE html>
<html>
<head>
    <title>Retired Employees Report</title>
    <style>
        body { font-family: Arial, sans-serif; }
        table { width: 100%; border-collapse: collapse; font-size: 8px; margin: 0 auto; }
        th, td { border: 1px solid #000; padding: 1px 2px; text-align: left; }
        th { background-color: #f2f2f2; }
        th, td { max-width: 40px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    </style>
</head>
<body>
    <h1>Retired Employees Report</h1>
    <table>
        <thead>
            <tr>
                <th>Employee ID</th>
                <th>First Name</th>
                <th>Surname</th>
                <th>Gender</th>
                <th>Date of Birth</th>
                <th>Phone</th>
                <th>Email</th>
                <th>Department</th>
                <th>Cadre</th>
                <th>Salary Structure</th>
                <th>Date of First Appointment</th>
                <th>Expected Retirement Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($employees as $employee)
                <tr>
                    <td>{{ $employee->employee_id }}</td>
                    <td>{{ $employee->first_name }}</td>
                    <td>{{ $employee->surname }}</td>
                    <td>{{ $employee->gender }}</td>
                    <td>{{ $employee->date_of_birth }}</td>
                    <td>{{ $employee->mobile_no }}</td>
                    <td>{{ $employee->email }}</td>
                    <td>{{ $employee->department->department_name ?? 'N/A' }}</td>
                    <td>{{ $employee->cadre->cadre_name ?? 'N/A' }}</td>
                    <td>{{ $employee->salaryScale->scale_name ?? 'N/A' }}</td>
                    <td>{{ $employee->date_of_first_appointment }}</td>
                    <td>{{ $employee->expected_retirement_date }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>