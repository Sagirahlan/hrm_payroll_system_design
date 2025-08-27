@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="card border-primary shadow">
        <div class="card-header" style="background-color: skyblue; color: white;">
            <h5 class="mb-0">Manage Deductions and Additions for {{ $employee->name }}</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <!-- Deduction Form -->
                <div class="col-lg-6 mb-4">
                    <div class="card border-primary shadow">
                        <div class="card-header" style="background-color: skyblue; color: white;">
                            <strong>Add Deduction</strong>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('payroll.deductions.store', $employee->employee_id) }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label for="deduction_type" class="form-label">Deduction Type</label>
                                    <input type="text" name="deduction_type" id="deduction_type" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label for="amount" class="form-label">Amount</label>
                                    <input type="number" name="amount" id="amount" step="0.01" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label for="deduction_period" class="form-label">Period</label>
                                    <select name="deduction_period" id="deduction_period" class="form-select" required>
                                        <option value="OneTime">One-Time</option>
                                        <option value="Monthly">Monthly</option>
                                        <option value="Perpetual">Perpetual</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="start_date" class="form-label">Start Date</label>
                                    <input type="date" name="start_date" id="start_date" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label for="end_date" class="form-label">End Date (Optional)</label>
                                    <input type="date" name="end_date" id="end_date" class="form-control">
                                </div>
                                <button type="submit" class="btn btn-primary">Add Deduction</button>
                            </form>
                        </div>
                    </div>
                </div>
                <!-- Addition Form -->
                <div class="col-lg-6 mb-4">
                    <div class="card border-primary shadow">
                        <div class="card-header" style="background-color: skyblue; color: white;">
                            <strong>Add Addition</strong>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('payroll.additions.store', $employee->employee_id) }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label for="addition_type" class="form-label">Addition Type</label>
                                    <input type="text" name="addition_type" id="addition_type" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label for="amount" class="form-label">Amount</label>
                                    <input type="number" name="amount" id="amount" step="0.01" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label for="addition_period" class="form-label">Period</label>
                                    <select name="addition_period" id="addition_period" class="form-select" required>
                                        <option value="OneTime">One-Time</option>
                                        <option value="Monthly">Monthly</option>
                                        <option value="Perpetual">Perpetual</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="start_date" class="form-label">Start Date</label>
                                    <input type="date" name="start_date" id="start_date" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label for="end_date" class="form-label">End Date (Optional)</label>
                                    <input type="date" name="end_date" id="end_date" class="form-control">
                                </div>
                                <button type="submit" class="btn btn-primary">Add Addition</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Deductions Table -->
            <div class="container-fluid py-4">
    <div class="card shadow">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0">Payroll Adjustments for {{ $employee->first_name }} {{ $employee->surname }}</h5>
        </div>
        <div class="card-body">


            <!-- Deductions Table -->
            <div class="card mb-4">
                <div class="card-header bg-danger text-white">Deductions</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Period</th>
                                    <th>Start</th>
                                    <th>End</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($deductions as $deduction)
                                    <tr>
                                        <td>{{ $deduction->deduction_type }}</td>
                                        <td>₦{{ number_format($deduction->amount, 2) }}</td>
                                        <td>{{ $deduction->deduction_period }}</td>
                                        <td>{{ $deduction->start_date }}</td>
                                        <td>{{ $deduction->end_date ?? 'N/A' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">No deductions found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Additions Table -->
            <div class="card">
                <div class="card-header bg-success text-white">Additions</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Period</th>
                                    <th>Start</th>
                                    <th>End</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($additions as $addition)
                                    <tr>
                                        <td>{{ $addition->addition_type }}</td>
                                        <td>₦{{ number_format($addition->amount, 2) }}</td>
                                        <td>{{ $addition->addition_period }}</td>
                                        <td>{{ $addition->start_date }}</td>
                                        <td>{{ $addition->end_date ?? 'N/A' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">No additions found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection