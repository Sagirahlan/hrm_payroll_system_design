@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>Create New Loan</h4>
                </div>

                <div class="card-body">
                    <form action="{{ route('loans.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="employee_id" class="form-label">Employee</label>
                            <select name="employee_id" id="employee_id" class="form-control" required>
                                <option value="">Select Employee</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->employee_id }}">{{ $employee->first_name }} {{ $employee->last_name }} ({{ $employee->employee_number }})</option>
                                @endforeach
                            </select>
                            @error('employee_id')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="addition_id" class="form-label">Loan Type</label>
                            <select name="addition_id" id="addition_id" class="form-control" required>
                                <option value="">Select Loan Type</option>
                            </select>
                            @error('addition_id')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="deduction_type_id" class="form-label">Deduction Type</label>
                            <select name="deduction_type_id" id="deduction_type_id" class="form-control" required>
                                <option value="">Select Deduction Type</option>
                                @foreach($deductionTypes as $deductionType)
                                    <option value="{{ $deductionType->id }}">{{ $deductionType->name }}</option>
                                @endforeach
                            </select>
                            @error('deduction_type_id')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="principal_amount" class="form-label">Principal Amount</label>
                            <input type="number" step="0.01" name="principal_amount" id="principal_amount" class="form-control" value="{{ old('principal_amount') }}" required readonly>
                            @error('principal_amount')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="monthly_deduction" class="form-label">Monthly Deduction Amount</label>
                                <input type="number" step="0.01" name="monthly_deduction" id="monthly_deduction" class="form-control" value="{{ old('monthly_deduction') }}">
                                <small class="form-text text-muted">Enter either this OR percentage below</small>
                                @error('monthly_deduction')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                                @if ($errors->has('monthly_percentage') && !$errors->has('monthly_deduction'))
                                    <div class="text-danger">Please provide a monthly deduction amount or a percentage.</div>
                                @endif
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="monthly_percentage" class="form-label">Monthly Percentage of Salary (%)</label>
                                <input type="number" step="0.01" name="monthly_percentage" id="monthly_percentage" class="form-control" value="{{ old('monthly_percentage') }}" max="100">
                                <small class="form-text text-muted">Enter either this OR fixed amount above</small>
                                @error('monthly_percentage')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                                @if ($errors->has('monthly_deduction') && !$errors->has('monthly_percentage'))
                                    <div class="text-danger">Please provide a monthly deduction amount or a percentage.</div>
                                @endif
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" name="start_date" id="start_date" class="form-control" value="{{ old('start_date', date('Y-m-d')) }}" required>
                            @error('start_date')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea name="description" id="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary">Create Loan</button>
                            <a href="{{ route('loans.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const employeeSelect = document.getElementById('employee_id');
        const additionSelect = document.getElementById('addition_id');
        const principalAmountInput = document.getElementById('principal_amount');

        employeeSelect.addEventListener('change', function () {
            const employeeId = this.value;
            if (employeeId) {
                fetch(`/loans/employees/${employeeId}/additions`)
                    .then(response => response.json())
                    .then(data => {
                        additionSelect.innerHTML = '<option value="">Select Loan Type</option>';
                        data.forEach(addition => {
                            const option = document.createElement('option');
                            option.value = addition.addition_id;
                            if (addition.addition_type) {
                                option.textContent = addition.addition_type.name;
                            } else {
                                option.textContent = 'Unnamed Addition'; // Or some other default value
                            }
                            option.dataset.amount = addition.amount;
                            additionSelect.appendChild(option);
                        });
                    });
            } else {
                additionSelect.innerHTML = '<option value="">Select Loan Type</option>';
                principalAmountInput.value = '';
            }
        });

        additionSelect.addEventListener('change', function () {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption.value && selectedOption.dataset.amount) {
                principalAmountInput.value = selectedOption.dataset.amount;
            } else {
                principalAmountInput.value = '';
            }
        });
    });
</script>
@endpush
@endsection