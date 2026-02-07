@extends('layouts.app')

@section('content')
<div class="container">
    <div class="mb-3">
        <a href="{{ route('loans.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Loans
        </a>
    </div>

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
                                        {{ $employee->first_name }} {{ $employee->middle_name ?? '' }} {{ $employee->surname }} ({{ $employee->employee_id ?? $employee->employee_number }})
                                        @if($employee->isCasualEmployee())
                                            [Casual: {{ number_format($employee->amount ?? 0) }}]
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
                                <label for="addition_month" class="form-label">Addition Month</label>
                                <input type="text" id="addition_month" class="form-control" readonly placeholder="Select loan type to see addition month">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="deduction_start_month_display" class="form-label">Deduction Start Month</label>
                                <input type="text" id="deduction_start_month_display" class="form-control" readonly placeholder="Auto-calculated from addition month">
                                <input type="hidden" name="deduction_start_month" id="deduction_start_month" value="">
                            </div>
                        </div>

                        <div class="row">
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
                                <small class="form-text text-muted" id="percentage_help_text">OR number of months</small>
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
                                <label for="display_monthly_deduction" class="form-label" id="monthly_deduction_label">Calculated Monthly Deduction</label>
                                <input type="number" step="0.01" id="display_monthly_deduction" class="form-control" readonly>
                                <small class="form-text text-muted">Amount to be deducted monthly</small>
                            </div>
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
    // Force clear any old values after DOM loads completely
    document.addEventListener('DOMContentLoaded', function () {
        const employeeSelect = document.getElementById('employee_id');
        const additionSelect = document.getElementById('addition_id');
        const principalAmountInput = document.getElementById('principal_amount');
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
            const loanDurationMonths = parseInt(loanDurationMonthsInput.value) || 0;

            // For now, we'll calculate total repayment as equal to principal (no interest)
            const totalRepayment = principalAmount;

            totalRepaymentInput.value = totalRepayment.toFixed(2);

            console.log('Calculating with:', {
                principal: principalAmount,
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
                                    verifyTotal: monthlyDeduction * loanDurationMonths,
                                    isRetired: data.is_retired,
                                    salaryType: data.salary_type
                                });

                                autoCalculatedPercentageInput.value = percentage.toFixed(2);
                                if (displayMonthlyDeductionInput) {
                                    displayMonthlyDeductionInput.value = monthlyDeduction.toFixed(2);
                                }
                                
                                // Update help text based on employee type
                                const percentageHelpText = document.getElementById('percentage_help_text');
                                const autoCalcLabel = document.querySelector('label[for="auto_calculated_percentage"]');
                                const monthlyDeductionLabel = document.getElementById('monthly_deduction_label');
                                
                                if (data.is_retired) {
                                    if (percentageHelpText) {
                                        percentageHelpText.textContent = 'OR number of months (based on pension amount)';
                                        percentageHelpText.classList.add('text-success');
                                    }
                                    if (autoCalcLabel) {
                                        autoCalcLabel.innerHTML = 'Auto-Calculated Percentage <span class="badge bg-success">Pension-based</span>';
                                    }
                                    if (monthlyDeductionLabel) {
                                        monthlyDeductionLabel.innerHTML = 'Calculated Monthly Deduction <span class="badge bg-success">From Pension</span>';
                                    }
                                } else {
                                    if (percentageHelpText) {
                                        percentageHelpText.textContent = 'OR number of months';
                                        percentageHelpText.classList.remove('text-success');
                                    }
                                    if (autoCalcLabel) {
                                        autoCalcLabel.textContent = 'Auto-Calculated Percentage';
                                    }
                                    if (monthlyDeductionLabel) {
                                        monthlyDeductionLabel.textContent = 'Calculated Monthly Deduction';
                                    }
                                }
                            } else {
                                const errorMsg = data.is_retired 
                                    ? 'Retired employee does not have a valid pension amount for percentage calculation.'
                                    : 'Employee does not have a valid salary for percentage calculation.';
                                alert(errorMsg);
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

        // Recalculate when principal amount changes
        principalAmountInput.addEventListener('input', calculatePercentage);

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
                            // Store the addition date and month so we can use them later
                            option.dataset.additionDate = addition.addition_date;
                            option.dataset.additionMonth = addition.addition_month;
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
                document.getElementById('addition_month').value = '';
                document.getElementById('deduction_start_month_display').value = '';
                document.getElementById('deduction_start_month').value = '';
            }
        });

        // Function to automatically select matching deduction type when loan type is selected
        function selectMatchingDeductionType() {
            const selectedOption = additionSelect.options[additionSelect.selectedIndex];
            if (selectedOption.value) {
                // Get the loan type name from the selected option
                const loanTypeName = selectedOption.text;

                // Try to find a matching deduction type in the deduction type dropdown
                let matchingDeductionOption = null;

                for (let i = 0; i < deductionTypeSelect.options.length; i++) {
                    if (deductionTypeSelect.options[i].text === loanTypeName) {
                        matchingDeductionOption = deductionTypeSelect.options[i];
                        break;
                    }
                }

                // If a matching deduction type is found, select it
                if (matchingDeductionOption) {
                    matchingDeductionOption.selected = true;
                } else {
                    // If no match found, reset to default option
                    deductionTypeSelect.selectedIndex = 0;
                }
            }
        }

        // Function to set start date to first day of next month after today
        function setStartDateToNextMonth() {
            const today = new Date();
            // Calculate the first day of the next month after today
            const nextMonth = new Date(today.getFullYear(), today.getMonth() + 1, 1);
            // Format as YYYY-MM-DD for input type=date
            const formattedDate = nextMonth.toISOString().split('T')[0];
            document.getElementById('start_date').value = formattedDate;
        }

        // Function to calculate next month from a given date string
        // Returns both formatted display and Y-m value
        function calculateNextMonth(dateString) {
            if (!dateString) return null;
            
            const date = new Date(dateString);
            // Add one month
            const nextMonth = new Date(date.getFullYear(), date.getMonth() + 1, 1);
            
            const monthNames = ["January", "February", "March", "April", "May", "June",
                "July", "August", "September", "October", "November", "December"
            ];
            
            return {
                display: `${monthNames[nextMonth.getMonth()]} ${nextMonth.getFullYear()}`,
                value: `${nextMonth.getFullYear()}-${String(nextMonth.getMonth() + 1).padStart(2, '0')}`
            };
        }

        // Make principal amount readonly after loan type is selected
        additionSelect.addEventListener('change', function () {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption.value && selectedOption.dataset.amount) {
                principalAmountInput.value = selectedOption.dataset.amount;
                principalAmountInput.setAttribute('readonly', true);

                // Automatically select the matching deduction type
                selectMatchingDeductionType();

                // Populate addition month field if addition date is available
                if (selectedOption.dataset.additionDate) {
                    console.log('Raw addition date:', selectedOption.dataset.additionDate);
                    const additionDate = new Date(selectedOption.dataset.additionDate);
                    console.log('Parsed addition date:', additionDate);
                    const monthNames = ["January", "February", "March", "April", "May", "June",
                        "July", "August", "September", "October", "November", "December"
                    ];
                    const additionMonth = `${monthNames[additionDate.getMonth()]} ${additionDate.getFullYear()}`;
                    console.log('Formatted addition month:', additionMonth);
                    document.getElementById('addition_month').value = additionMonth;
                    
                    // Calculate and set deduction start month (next month after addition month)
                    const deductionStartMonth = calculateNextMonth(selectedOption.dataset.additionDate);
                    if (deductionStartMonth) {
                        document.getElementById('deduction_start_month_display').value = deductionStartMonth.display;
                        document.getElementById('deduction_start_month').value = deductionStartMonth.value;
                        console.log('Deduction start month:', deductionStartMonth);
                    }
                } else {
                    document.getElementById('addition_month').value = 'Month not available';
                    document.getElementById('deduction_start_month_display').value = '';
                    document.getElementById('deduction_start_month').value = '';
                }

                // Recalculate if months were already entered
                if (loanDurationMonthsInput.value) {
                    calculatePercentage();
                }
            } else {
                principalAmountInput.value = '';
                principalAmountInput.removeAttribute('readonly');
                document.getElementById('addition_month').value = '';
                document.getElementById('deduction_start_month_display').value = '';
                document.getElementById('deduction_start_month').value = '';
            }
        });

        // Create reference to deduction type dropdown
        const deductionTypeSelect = document.getElementById('deduction_type_id');
    });
</script>
@endpush
@endsection

