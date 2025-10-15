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
                                @foreach($filteredEmployees as $employee)
                                    <option value="{{ $employee->employee_id }}" data-appointment-type="{{ $employee->appointmentType->name ?? 'Permanent' }}">
                                        {{ $employee->first_name }} {{ $employee->last_name }} ({{ $employee->employee_number }})
                                        @if($employee->isContractEmployee())
                                            [Contract: {{ number_format($employee->amount ?? 0) }}]
                                        @endif
                                    </option>
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

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="principal_amount" class="form-label">Principal Amount</label>
                                <input type="number" step="0.01" name="principal_amount" id="principal_amount" class="form-control" value="{{ old('principal_amount') }}" required>
                                @error('principal_amount')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="interest_rate" class="form-label">Interest Rate (%)</label>
                                <input type="number" step="0.01" name="interest_rate" id="interest_rate" class="form-control" value="{{ old('interest_rate') }}">
                                @error('interest_rate')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="total_interest" class="form-label">Total Interest</label>
                                <input type="number" step="0.01" id="total_interest" class="form-control" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="total_repayment" class="form-label">Total Repayment</label>
                                <input type="number" step="0.01" id="total_repayment" class="form-control" readonly>
                            </div>
                        </div>

                                                            <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <label for="monthly_deduction" class="form-label">Monthly Deduction Amount</label>
                                                <input type="number" step="0.01" name="monthly_deduction" id="monthly_deduction" class="form-control" value="{{ old('monthly_deduction') }}">
                                                <small class="form-text text-muted">OR percentage below</small>
                                                @error('monthly_deduction')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-md-4 mb-3">
                                                <label for="monthly_percentage" class="form-label">Monthly Percentage of Salary (%)</label>
                                                <input type="number" step="0.01" name="monthly_percentage" id="monthly_percentage" class="form-control" value="{{ old('monthly_percentage') }}" max="100">
                                                <small class="form-text text-muted">OR number of months</small>
                                                @error('monthly_percentage')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            
                                            <div class="col-md-4 mb-3">
                                                <label for="loan_duration_months" class="form-label">Number of Months to Pay</label>
                                                <input type="number" name="loan_duration_months" id="loan_duration_months" class="form-control" value="{{ old('loan_duration_months') }}" min="1">
                                                <small class="form-text text-muted">Enter exact months (e.g., 29)</small>
                                                @error('loan_duration_months')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="auto_calculated_percentage" class="form-label">Auto-Calculated Percentage</label>
                                                <input type="number" step="0.01" name="auto_calculated_percentage" id="auto_calculated_percentage" class="form-control" readonly>
                                                <small class="form-text text-muted">Percentage based on salary and loan duration</small>
                                            </div>
                                            
                                            <div class="col-md-6 mb-3">
                                                <label for="display_monthly_deduction" class="form-label">Calculated Monthly Deduction</label>
                                                <input type="number" step="0.01" id="display_monthly_deduction" class="form-control" readonly>
                                                <small class="form-text text-muted">Amount to be deducted monthly</small>
                                            </div>
                                        </div>

<!-- REMOVE the hidden calculated_monthly_deduction field entirely -->  
                            
                            <!-- Hidden field to store the calculated monthly deduction when using months input -->
                            <input type="hidden" name="calculated_monthly_deduction" id="calculated_monthly_deduction" value="">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="start_date" class="form-label">Start Date</label>
                                <input type="date" name="start_date" id="start_date" class="form-control" value="{{ old('start_date', date('Y-m-d')) }}" required>
                                @error('start_date')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea name="description" id="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
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
    const interestRateInput = document.getElementById('interest_rate');
    const totalInterestInput = document.getElementById('total_interest');
    const totalRepaymentInput = document.getElementById('total_repayment');
    const monthlyDeductionInput = document.getElementById('monthly_deduction');
    const monthlyPercentageInput = document.getElementById('monthly_percentage');
    const loanDurationMonthsInput = document.getElementById('loan_duration_months');
    const autoCalculatedPercentageInput = document.getElementById('auto_calculated_percentage');
    const displayMonthlyDeductionInput = document.getElementById('display_monthly_deduction');

    // Function to clear other fields when one is filled
    function clearOtherFields(exceptField) {
        if (exceptField !== 'monthly_deduction') {
            monthlyDeductionInput.value = '';
        }
        if (exceptField !== 'monthly_percentage') {
            monthlyPercentageInput.value = '';
        }
        if (exceptField !== 'loan_duration_months') {
            loanDurationMonthsInput.value = '';
            autoCalculatedPercentageInput.value = '';
            if (displayMonthlyDeductionInput) {
                displayMonthlyDeductionInput.value = '';
            }
        }
    }

    // Function to calculate percentage based on loan duration
    function calculatePercentage() {
        const principalAmount = parseFloat(principalAmountInput.value) || 0;
        const interestRate = parseFloat(interestRateInput.value) || 0;
        const loanDurationMonths = parseInt(loanDurationMonthsInput.value) || 0;
        
        const totalInterest = (principalAmount * interestRate) / 100;
        const totalRepayment = principalAmount + totalInterest;

        totalInterestInput.value = totalInterest.toFixed(2);
        totalRepaymentInput.value = totalRepayment.toFixed(2);

        console.log('Calculating with:', {
            principal: principalAmount,
            interestRate: interestRate,
            totalInterest: totalInterest,
            totalRepayment: totalRepayment,
            months: loanDurationMonths
        });
        
        if (totalRepayment > 0 && loanDurationMonths > 0) {
            const employeeId = employeeSelect.value;
            
            if (employeeId) {
                fetch(`/loans/employees/${employeeId}/salary`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.basic_salary) {
                            // Calculate exact monthly deduction: Total Repayment ÷ Months
                            const monthlyDeduction = totalRepayment / loanDurationMonths;
                            const percentage = (monthlyDeduction / data.basic_salary) * 100;
                            
                            console.log('Calculated:', {
                                monthlyDeduction: monthlyDeduction,
                                percentage: percentage,
                                verifyTotal: monthlyDeduction * loanDurationMonths
                            });
                            
                            autoCalculatedPercentageInput.value = percentage.toFixed(2);
                            if (displayMonthlyDeductionInput) {
                                displayMonthlyDeductionInput.value = monthlyDeduction.toFixed(2);
                            }
                        } else {
                            alert('Employee does not have a valid salary for percentage calculation.');
                            loanDurationMonthsInput.value = '';
                            autoCalculatedPercentageInput.value = '';
                            if (displayMonthlyDeductionInput) {
                                displayMonthlyDeductionInput.value = '';
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching employee salary:', error);
                        alert('Error fetching employee salary information.');
                    });
            } else {
                alert('Please select an employee first.');
                loanDurationMonthsInput.value = '';
                autoCalculatedPercentageInput.value = '';
                if (displayMonthlyDeductionInput) {
                    displayMonthlyDeductionInput.value = '';
                }
            }
        } else {
            autoCalculatedPercentageInput.value = '';
            if (displayMonthlyDeductionInput) {
                displayMonthlyDeductionInput.value = '';
            }
        }
    }

    // Event listeners to clear other fields when one is filled
    monthlyDeductionInput.addEventListener('input', function() {
        if (this.value) {
            clearOtherFields('monthly_deduction');
        }
    });
    
    monthlyPercentageInput.addEventListener('input', function() {
        if (this.value) {
            clearOtherFields('monthly_percentage');
        }
    });
    
    loanDurationMonthsInput.addEventListener('input', function() {
        if (this.value) {
            clearOtherFields('loan_duration_months');
            calculatePercentage();
        } else {
            autoCalculatedPercentageInput.value = '';
            if (displayMonthlyDeductionInput) {
                displayMonthlyDeductionInput.value = '';
            }
        }
    });

    // Recalculate when principal amount or interest rate changes
    principalAmountInput.addEventListener('input', calculatePercentage);
    interestRateInput.addEventListener('input', calculatePercentage);

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
                        if (addition.addition_type && addition.addition_type.name) {
                            option.textContent = addition.addition_type.name;
                        } else if (addition.additionType && addition.additionType.name) {
                            option.textContent = addition.additionType.name;
                        } else {
                            option.textContent = 'Unnamed Addition';
                        }
                        option.dataset.amount = addition.amount;
                        additionSelect.appendChild(option);
                    });
                });
        } else {
            additionSelect.innerHTML = '<option value="">Select Loan Type</option>';
            principalAmountInput.value = '';
            principalAmountInput.removeAttribute('readonly');
            autoCalculatedPercentageInput.value = '';
            if (displayMonthlyDeductionInput) {
                displayMonthlyDeductionInput.value = '';
            }
            loanDurationMonthsInput.value = '';
            monthlyPercentageInput.value = '';
            monthlyDeductionInput.value = '';
        }
    });

    // Make principal amount readonly after loan type is selected
    additionSelect.addEventListener('change', function () {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.value && selectedOption.dataset.amount) {
            principalAmountInput.value = selectedOption.dataset.amount;
            principalAmountInput.setAttribute('readonly', true);
            
            // Recalculate if months were already entered
            if (loanDurationMonthsInput.value) {
                calculatePercentage();
            }
        } else {
            principalAmountInput.value = '';
            principalAmountInput.removeAttribute('readonly');
        }
    });
});
</script>
@endpush
@endsection