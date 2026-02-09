@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="mb-3">
        <a href="{{ route('payroll.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Payroll
        </a>
    </div>
    
    <div class="row">
        <div class="col-12">
            <div class="card border-primary shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Manage Additions for {{ $employee->first_name }} {{ $employee->surname }}</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                         <div class="col-12 mb-3">
                            <a href="{{ url()->previous() }}" class="btn btn-outline-primary">
                                &larr; Back
                            </a>
                        </div>
                        @if(count($approvedPayrollMonths) > 0)
                        <div class="col-12 mb-3">
                            <div class="alert alert-info d-flex align-items-center" role="alert">
                                <i class="fas fa-info-circle me-2"></i>
                                <div>
                                    <strong>Note:</strong> Some payroll months are approved. Additions for those months cannot be deleted. You can still add additions for unapproved months.
                                </div>
                            </div>
                        </div>
                        @endif
                        <!-- Addition Form -->
                        <div class="col-lg-6 mb-4">
                            <div class="card border-success shadow">
                                <div class="card-header bg-success text-white">
                                    <strong>Add Addition</strong>
                                </div>
                                <div class="card-body">
                                    <form action="{{ route('payroll.additions.store', $employee->employee_id) }}" method="POST">
                                        @csrf
                                        <div class="mb-3">
                                            <label for="addition_type_id" class="form-label">Addition Type</label>
                                            <select name="addition_type_id" id="addition_type_id" class="form-select" required>
                                                <option value="">-- Select --</option>
                                                @foreach($additionTypes as $type)
                                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="amount_type" class="form-label">Amount Type</label>
                                            <select name="amount_type" id="amount_type" class="form-select" required>
                                                <option value="fixed">Fixed</option>
                                                <option value="percentage">Percentage</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="amount" class="form-label">Amount</label>
                                            <input type="number" name="amount" id="amount" step="0.01" class="form-control" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="period" class="form-label">Period</label>
                                            <select name="period" id="period" class="form-select" required>
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
                                            <label for="end_date" class="form-label">End Date</label>
                                            <input type="date" name="end_date" id="end_date" class="form-control" required>
                                        </div>
                                        <button type="submit" class="btn btn-success">Add Addition</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <!-- Additions History -->
                        <div class="col-lg-6 mb-4">
                            <div class="card border-info shadow">
                                <div class="card-header bg-info text-white">
                                    <strong>Additions History</strong>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Type</th>
                                                    <th>Amount</th>
                                                    <th>Period</th>
                                                    <th>Start Date</th>
                                                    <th>Start Date</th>
                                                    <th>End Date</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($additions as $addition)
                                                    <tr>
                                                        <td>{{ $addition->addition_type }}</td>
                                                        <td>₦{{ number_format($addition->amount, 2) }}</td>
                                                        <td>{{ $addition->addition_period }}</td>
                                                        <td>{{ \Carbon\Carbon::parse($addition->start_date)->format('M d, Y') }}</td>
                                                        <td>{{ $addition->end_date ? \Carbon\Carbon::parse($addition->end_date)->format('M d, Y') : 'N/A' }}</td>
                                                        <td>
                                                            @can('delete_additions')
                                                                @php
                                                                    $additionMonth = $addition->start_date ? \Carbon\Carbon::parse($addition->start_date)->format('Y-m') : null;
                                                                    $isMonthLocked = $additionMonth && in_array($additionMonth, $approvedPayrollMonths);
                                                                @endphp
                                                                @if($isMonthLocked)
                                                                    <button type="button" class="btn btn-sm btn-secondary" disabled title="Cannot delete: Payroll for {{ \Carbon\Carbon::parse($addition->start_date)->format('F Y') }} has been approved">
                                                                        <i class="fas fa-lock"></i>
                                                                    </button>
                                                                @else
                                                                    <form action="{{ route('payroll.additions.destroy', ['employeeId' => $employee->employee_id, 'additionId' => $addition->addition_id]) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this addition?');">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button type="submit" class="btn btn-sm btn-danger">
                                                                            <i class="fas fa-trash"></i>
                                                                        </button>
                                                                    </form>
                                                                @endif
                                                            @endcan
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="5" class="text-center text-muted">No additions found.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                    <!-- Pagination -->
                                    <div class="card-footer bg-white">
                                        {{ $additions->withQueryString()->links() }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const periodSelect = document.getElementById('period');
        const startDateInput = document.getElementById('start_date');
        const endDateInput = document.getElementById('end_date');
        const endDateContainer = endDateInput.closest('.mb-3');

        // Set default dates (start of month and end of month)
        function setDefaultDates() {
            const today = new Date();
            const year = today.getFullYear();
            const month = String(today.getMonth() + 1).padStart(2, '0'); // Month is 0-indexed

            // Set start date to first day of current month
            const startDate = `${year}-${month}-01`;
            startDateInput.value = startDate;

            // Calculate last day of current month
            const lastDay = new Date(year, today.getMonth() + 1, 0).getDate();
            const endDate = `${year}-${month}-${String(lastDay).padStart(2, '0')}`;
            endDateInput.value = endDate;
        }

        function toggleEndDate() {
            // End date is always required and visible now
            endDateContainer.style.display = 'block';
            endDateInput.required = true;
        }

        periodSelect.addEventListener('change', function () {
            toggleEndDate();

            if (this.value === 'OneTime' && startDateInput.value) {
                // For OneTime, use current date as start date
                const today = new Date().toISOString().split('T')[0];
                startDateInput.value = today;
            }
        });

        // Initialize default dates and visibility
        setDefaultDates();
        toggleEndDate();
    });
</script>
@endpush
