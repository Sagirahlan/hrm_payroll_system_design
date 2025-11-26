@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card border-primary shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Manage Additions for {{ $employee->first_name }} {{ $employee->surname }}</h5>
                </div>
                <div class="card-body">
                    <div class="row">
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
                                            <label for="end_date" class="form-label">End Date (Optional)</label>
                                            <input type="date" name="end_date" id="end_date" class="form-control">
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

        function toggleEndDate() {
            if (periodSelect.value === 'OneTime') {
                endDateContainer.style.display = 'none';
                endDateInput.required = false;
                endDateInput.value = ''; // Clear value
            } else {
                endDateContainer.style.display = 'block';
                // End date is optional for Perpetual, but let's keep it consistent with request
                // Request says: "if montly use the start date and end date"
                // It implies end date is important for Monthly.
                // However, existing backend validation might treat it as optional.
                // Let's just control visibility for now.
            }
        }

        periodSelect.addEventListener('change', function () {
            toggleEndDate();
            
            if (this.value === 'OneTime' && startDateInput.value) {
                // Optional: logic to set start date to first of month if needed, 
                // but user request didn't explicitly ask for this auto-correction here,
                // just "use that start date".
            }
        });

        // Initial check
        toggleEndDate();
    });
</script>
@endpush
