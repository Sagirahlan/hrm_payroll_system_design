@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="card border-primary shadow">
        <div class="card-header" style="background-color: skyblue; color: white;">
            <h5 class="mb-0">Bulk Manage Deductions / Additions</h5>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <form action="{{ route('payroll.adjustments.submit') }}" method="POST">
                @csrf

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="adjustment_type" class="form-label">Adjustment Type</label>
                        <select name="adjustment_type" id="adjustment_type" class="form-select" required>
                            <option value="">-- Select --</option>
                            <option value="addition">Addition</option>
                            <option value="deduction">Deduction</option>
                        </select>
                    </div>

                    <div class="col-md-8">
                        <label for="type_name" class="form-label">Name of Adjustment</label>
                        <input type="text" name="name_type" id="name_type" class="form-control" required placeholder="e.g., Loan Deduction, Housing Allowance">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for="amount" class="form-label">Amount</label>
                        <input type="number" name="amount" id="amount" class="form-control" required step="0.01">
                    </div>

                    <div class="col-md-3">
                        <label for="period" class="form-label">Period</label>
                        <select name="period" id="period" class="form-select" required>
                            <option value="OneTime">One-Time</option>
                            <option value="Monthly">Monthly</option>
                            <option value="Perpetual">Perpetual</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" name="start_date" id="start_date" class="form-control" required>
                    </div>

                    <div class="col-md-3">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" name="end_date" id="end_date" class="form-control">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="employee_ids" class="form-label">Select Employees</label>
                    <div class="d-flex justify-content-between mb-2">
                        <small class="text-muted">Hold Ctrl (or Cmd) to select multiple employees</small>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectAll()">Select All</button>
                    </div>
                    <input type="text" id="employeeSearch" class="form-control mb-2" placeholder="Search employees...">
                    <select name="employee_ids[]" id="employee_ids" class="form-select" multiple size="10" required style="border: 2px solid #0d6efd; background-color: #f8f9fa;">
                        @foreach($employees as $employee)
                            <option value="{{ $employee->employee_id }}" style="padding: 8px;">
                                {{ $employee->first_name }} {{ $employee->surname }} ({{ $employee->employee_id }})
                            </option>
                        @endforeach
                    </select>
                    <script>
                        document.getElementById('employeeSearch').addEventListener('input', function() {
                            const search = this.value.toLowerCase();
                            const select = document.getElementById('employee_ids');
                            for (let option of select.options) {
                                const text = option.text.toLowerCase();
                                option.style.display = text.includes(search) ? '' : 'none';
                            }
                        });
                    </script>
                </div>
                </div>

                <button type="submit" class="btn btn-primary">Apply</button>
            </form>
        </div>
    </div>
</div>

<script>
    function selectAll() {
        const select = document.getElementById('employee_ids');
        for (let i = 0; i < select.options.length; i++) {
            select.options[i].selected = true;
        }
    }
</script>
@endsection
