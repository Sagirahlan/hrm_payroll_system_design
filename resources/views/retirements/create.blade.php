@extends('layouts.app')

@section('title', 'Process Retired Employees')

@section('content')
<body>
    <div class="container mt-4">
        <div class="card shadow">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">Process Retired Employees</h5>
            </div>
            <div class="card-body">
                <div id="successAlert" class="alert alert-success d-none"></div>

                <!-- Search and Filter Form -->
                <form method="GET" action="{{ route('retirements.create') }}" class="mb-4">
                    <div class="row">
                        <div class="col-md-4">
                            <input type="text" name="search" class="form-control" placeholder="Search by Reg No, Name..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3">
                            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                        </div>
                        <div class="col-md-3">
                            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <a href="{{ route('retirements.create') }}" class="btn btn-secondary">Clear</a>
                        </div>
                    </div>
                </form>

                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Reg No</th>
                            <th>Name</th>
                            <th>Retirement Date</th>
                            <th>Grade Level</th>
                            <th>Gratuity</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="employeeTableBody">
                        @foreach($retiredEmployees as $employee)
                            @php
                                $lastPayroll = $employee->payrollRecords->sortByDesc('created_at')->first();
                                $basic_salary = $lastPayroll?->basic_salary ?? 0;
                                $alreadyProcessed = \App\Models\Retirement::where('employee_id', $employee->employee_id)->exists();
                                $years = 0;
                                if ($employee->date_of_first_appointment && $employee->expected_retirement_date) {
                                    $start = \Carbon\Carbon::parse($employee->date_of_first_appointment);
                                    $end = \Carbon\Carbon::parse($employee->expected_retirement_date);
                                    $years = $end->diffInYears($start);
                                    if ($start->copy()->addYears($years)->lt($end)) {
                                        $years += 1;
                                    }
                                }
                                $gratuity = $basic_salary * 0.1 * $years;
                            @endphp
                            <tr data-employee-id="{{ $employee->employee_id }}">
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    {{ $employee->reg_no }}
                                    <input type="hidden" name="employee_id" value="{{ $employee->employee_id }}">
                                </td>
                                <td>{{ $employee->first_name }} {{ $employee->surname }}</td>
                                <td>
                                    {{ \Carbon\Carbon::parse($employee->expected_retirement_date)->format('Y-m-d') }}
                                    <input type="hidden" name="retirement_date" value="{{ $employee->expected_retirement_date }}">
                                    <input type="hidden" name="appointment_date" value="{{ $employee->date_of_first_appointment }}">
                                </td>
                                <td>
                                    {{ $employee->gradeLevel->name ?? 'N/A' }}
                                    <input type="hidden" name="basic_salary" value="{{ $basic_salary }}">
                                </td>
                                <td>
                                    <div class="input-group">
                                        <input type="number" name="gratuity_amount" class="form-control form-control-sm gratuity-input" 
                                            value="{{ number_format($gratuity, 2, '.', '') }}" step="0.01" min="0" readonly>
                                        <span class="input-group-text calculated-gratuity">
                                            ₦{{ number_format($gratuity, 2) }}
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    @if($alreadyProcessed)
                                        <span class="badge bg-success status-badge">Completed</span>
                                        <input type="hidden" name="status" value="complete">
                                    @else
                                        <span class="badge bg-secondary status-badge">Pending</span>
                                        <input type="hidden" name="status" value="pending">
                                    @endif
                                </td>
                                <td>
                                    @if($alreadyProcessed)
                                        <button type="button" class="btn btn-secondary btn-sm process-btn" disabled>Processed</button>
                                    @else
                                        <button type="button" class="btn btn-success btn-sm process-btn" onclick="calculateAndProcess(this)">Calculate & Process</button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="mt-4">
                    {{ $retiredEmployees->appends(request()->query())->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>

    <!-- Processing Modal -->
    <div class="modal fade" id="processModal" tabindex="-1" aria-labelledby="processModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="processModalLabel">Confirm Retirement Processing</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="modalEmployeeInfo"></div>
                    <div class="mt-3">
                        <strong>Calculated Gratuity: <span id="modalGratuityAmount" class="text-success"></span></strong>
                    </div>
                    <div class="mt-2 text-muted">
                        <small>Years of Service: <span id="modalYearsOfService"></span></small><br>
                        <small>Basic Salary: <span id="modalBasicSalary"></span></small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" onclick="savePensioner()">Process Retirement</button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentEmployeeData = null;
        const processModal = new bootstrap.Modal(document.getElementById('processModal'));

        function calculateAndProcess(button) {
            const row = button.closest('tr');
            const employeeId = row.querySelector('input[name="employee_id"]').value;
            const retirementDate = row.querySelector('input[name="retirement_date"]').value;
            const appointmentDate = row.querySelector('input[name="appointment_date"]').value;
            const basicSalary = parseFloat(row.querySelector('input[name="basic_salary"]').value);
            const employeeName = row.cells[2].textContent.trim();

            // Calculate gratuity using the same logic as your controller
            const gratuity = calculateGratuity(appointmentDate, retirementDate, basicSalary);
            const yearsOfService = calculateYearsOfService(appointmentDate, retirementDate);

            // Update the input field and display
            const gratuityInput = row.querySelector('.gratuity-input');
            const calculatedGratuitySpan = row.querySelector('.calculated-gratuity');

            gratuityInput.value = gratuity.toFixed(2);
            calculatedGratuitySpan.textContent = `₦${formatNumber(gratuity)}`;

            // Store current employee data for modal
            currentEmployeeData = {
                employeeId: employeeId,
                employeeName: employeeName,
                retirementDate: retirementDate,
                appointmentDate: appointmentDate,
                basicSalary: basicSalary,
                gratuity: gratuity,
                yearsOfService: yearsOfService,
                row: row
            };


            // Update modal content
            document.getElementById('modalEmployeeInfo').innerHTML = `
                <p><strong>Employee:</strong> ${employeeName} (${employeeId})</p>
                <p><strong>Retirement Date:</strong> ${formatDate(retirementDate)}</p>
            `;
            document.getElementById('modalGratuityAmount').textContent = `₦${formatNumber(gratuity)}`;
            document.getElementById('modalYearsOfService').textContent = `${yearsOfService} years`;
            document.getElementById('modalBasicSalary').textContent = `₦${formatNumber(basicSalary)}`;

            // Show modal
            processModal.show();
        }

        function calculateGratuity(appointmentDate, retirementDate, basicSalary) {
            if (!appointmentDate || !retirementDate || !basicSalary) {
                return 0;
            }

            const appointment = new Date(appointmentDate);
            const retirement = new Date(retirementDate);

            // If retirement date is before appointment, return 0
            if (retirement <= appointment) {
                return 0;
            }

            // Calculate years of service (count partial years as full year)
            const yearsOfService = calculateYearsOfService(appointmentDate, retirementDate);

            if (yearsOfService < 1) {
                return 0;
            }

            // Gratuity formula: 10% of last salary per year of service
            const gratuity = basicSalary * 0.1 * yearsOfService;

            return Math.round(gratuity * 100) / 100; // Round to 2 decimal places
        }

        function calculateYearsOfService(appointmentDate, retirementDate) {
            const appointment = new Date(appointmentDate);
            const retirement = new Date(retirementDate);

            let years = retirement.getFullYear() - appointment.getFullYear();

            // Check if we need to add an extra year (partial year counts as full year)
            const appointmentThisYear = new Date(retirement.getFullYear(), appointment.getMonth(), appointment.getDate());
            if (appointmentThisYear < retirement) {
                years += 1;
            }

            return Math.max(0, years);
        }

        // Store in the pensioners table via AJAX
        // Store in the pensioners table via AJAX
function savePensioner() {
    if (!currentEmployeeData) return;

    // Use the retirement processing endpoint instead of pensioners
    fetch('/retirements', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            employee_id: currentEmployeeData.employeeId,
            retirement_date: currentEmployeeData.retirementDate,
            notification_date: currentEmployeeData.retirementDate, // or current date
            gratuity_amount: currentEmployeeData.gratuity,
            status: 'complete'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update the row to show as processed
            const row = currentEmployeeData.row;
            const statusBadge = row.querySelector('.status-badge');
            const processBtn = row.querySelector('.process-btn');

            statusBadge.textContent = 'Completed';
            statusBadge.className = 'badge bg-success status-badge';

            processBtn.textContent = 'Processed';
            processBtn.className = 'btn btn-secondary btn-sm';
            processBtn.disabled = true;

            // Show success message
            const successAlert = document.getElementById('successAlert');
            successAlert.textContent = `Retirement processed successfully for ${currentEmployeeData.employeeName} (${currentEmployeeData.employeeId})`;
            successAlert.classList.remove('d-none');

            // Hide modal
            processModal.hide();

            // Reset current employee data
            currentEmployeeData = null;

            // Hide success message after 5 seconds
            setTimeout(() => {
                successAlert.classList.add('d-none');
            }, 5000);
        } else {
            alert(data.message || 'An error occurred while processing retirement');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while processing retirement');
    });
}

        function formatNumber(num) {
            return new Intl.NumberFormat('en-NG', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(num);
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-NG', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        }
    </script>
</body>

@endsection