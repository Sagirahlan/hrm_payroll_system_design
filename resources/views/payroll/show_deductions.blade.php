@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card border-primary shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Manage Deductions for {{ $employee->first_name }} {{ $employee->surname }}</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Deduction Form -->
                        <div class="col-lg-6 mb-4">
                            <div class="card border-danger shadow">
                                <div class="card-header bg-danger text-white">
                                    <strong>Add Deduction</strong>
                                </div>
                                <div class="card-body">
                                    <form action="{{ route('payroll.deductions.store', $employee->employee_id) }}" method="POST">
                                        @csrf
                                        <div class="mb-3">
                                            <label for="deduction_type_id" class="form-label">Deduction Type</label>
                                            <select name="deduction_type_id" id="deduction_type_id" class="form-select" required>
                                                <option value="">-- Select Non-Statutory Deduction --</option>
                                                @foreach($deductionTypes as $type)
                                                    @if(!$type->is_statutory)
                                                        <option value="{{ $type->id }}" 
                                                                data-calculation-type="{{ $type->calculation_type }}"
                                                                data-rate-or-amount="{{ $type->rate_or_amount }}">
                                                            {{ $type->name }}
                                                        </option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mb-3" id="amount_type_section" style="display: none;">
                                            <label for="amount_type" class="form-label">Amount Type</label>
                                            <select name="amount_type" id="amount_type" class="form-select">
                                                <option value="fixed">Fixed</option>
                                                <option value="percentage">Percentage</option>
                                            </select>
                                        </div>
                                        <div class="mb-3" id="amount_section" style="display: none;">
                                            <label for="amount" class="form-label">Amount/Percentage</label>
                                            <input type="number" name="amount" id="amount" step="0.01" class="form-control">
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
                                        <button type="submit" class="btn btn-danger">Add Deduction</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <!-- Deductions History -->
                        <div class="col-lg-6 mb-4">
                            <div class="card border-info shadow">
                                <div class="card-header bg-info text-white">
                                    <strong>Deductions History</strong>
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
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const deductionTypeSelect = document.getElementById('deduction_type_id');
    const amountTypeSection = document.getElementById('amount_type_section');
    const amountSection = document.getElementById('amount_section');
    const amountTypeSelect = document.getElementById('amount_type');
    const amountInput = document.getElementById('amount');
    
    deductionTypeSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const isStatutory = selectedOption.getAttribute('data-is-statutory') === '1';
        const calculationType = selectedOption.getAttribute('data-calculation-type');
        const rateOrAmount = selectedOption.getAttribute('data-rate-or-amount');
        
        if (isStatutory) {
            // Hide amount fields for statutory deductions
            amountTypeSection.style.display = 'none';
            amountSection.style.display = 'none';
        } else {
            // Show amount fields for non-statutory deductions
            amountTypeSection.style.display = 'block';
            amountSection.style.display = 'block';
            
            // Set default values based on deduction type
            if (calculationType === 'percentage') {
                amountTypeSelect.value = 'percentage';
                amountInput.placeholder = 'Enter percentage (e.g., 2.5 for 2.5%)';
            } else {
                amountTypeSelect.value = 'fixed';
                amountInput.placeholder = 'Enter fixed amount';
            }
            
            // Pre-fill with rate_or_amount if available
            if (rateOrAmount) {
                amountInput.value = rateOrAmount;
            }
        }
    });
});
</script>
@endsection
