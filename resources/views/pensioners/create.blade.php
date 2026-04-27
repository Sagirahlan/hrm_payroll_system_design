@extends('layouts.app')

@section('title', 'Add Pensioner')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Add Pensioner</h4>
                    <a href="{{ route('pensioners.index') }}" class="btn btn-secondary float-end">Cancel</a>
                </div>
                <div class="card-body">
                    <ul class="nav nav-tabs mb-3" id="pensionerTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="employee-tab" data-bs-toggle="tab" data-bs-target="#employee" type="button" role="tab">
                                From Retired Employees
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="retirement-tab" data-bs-toggle="tab" data-bs-target="#retirement" type="button" role="tab">
                                From Retirement Records
                            </button>
                        </li>
                    </ul>
                    
                    <div class="tab-content" id="pensionerTabsContent">
                        <!-- Tab 1: From Retired Employees with Pensioner appointment type -->
                        <div class="tab-pane fade show active" id="employee" role="tabpanel">
                            @if($pensionerEmployees->count() > 0)
                                <div class="alert alert-info">
                                    Found {{ $pensionerEmployees->count() }} retired employees with Pensioner appointment type.
                                </div>
                                
                                <form action="{{ route('pensioners.store') }}" method="POST">
                                    @csrf
                                    
                                    <div class="mb-3">
                                        <label for="employee_id" class="form-label">Select Employee</label>
                                        <select class="form-control" id="employee_id" name="employee_id" required>
                                            <option value="">Select Employee</option>
                                            @foreach($pensionerEmployees as $employee)
                                                <option value="{{ $employee->employee_id }}" 
                                                    data-first-name="{{ $employee->first_name }}"
                                                    data-surname="{{ $employee->surname }}"
                                                    data-middle-name="{{ $employee->middle_name }}"
                                                    data-bank-id="{{ $employee->bank->bank_id ?? '' }}"
                                                    data-bank-name="{{ $employee->bank->bank_name ?? '' }}"
                                                    data-account-name="{{ $employee->bank->account_name ?? '' }}"
                                                    data-account-no="{{ $employee->bank->account_no ?? '' }}">
                                                    {{ $employee->first_name }} {{ $employee->surname }} ({{ $employee->staff_no }}) - {{ $employee->bank->bank_name ?? 'No Bank' }} - {{ $employee->bank->account_no ?? '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="pension_amount" class="form-label">Pension Amount <span class="text-danger">*</span></label>
                                                <input type="number" step="0.01" class="form-control" id="pension_amount" name="pension_amount" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="gratuity_amount" class="form-label">Gratuity Amount <span class="text-danger">*</span></label>
                                                <input type="number" step="0.01" class="form-control" id="gratuity_amount" name="gratuity_amount" required>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <input type="hidden" id="bank_id" name="bank_id">
                                    <input type="hidden" id="account_number" name="account_number">
                                    <input type="hidden" id="account_name" name="account_name">
                                    <input type="hidden" id="first_name" name="first_name">
                                    <input type="hidden" id="surname" name="surname">
                                    <input type="hidden" id="status" name="status" value="Active">
                                    
                                    <button type="submit" class="btn btn-primary">Add Pensioner</button>
                                    <a href="{{ route('pensioners.index') }}" class="btn btn-secondary">Cancel</a>
                                </form>
                            @else
                                <div class="alert alert-info">
                                    No retired employees with Pensioner appointment type found.
                                </div>
                                <a href="{{ route('pensioners.index') }}" class="btn btn-primary">View Pensioners</a>
                            @endif
                        </div>
                        
                        <!-- Tab 2: From Retirement Records -->
                        <div class="tab-pane fade" id="retirement" role="tabpanel">
                            @if($retirements->count() > 0)
                                <div class="alert alert-info">
                                    Found {{ $retirements->count() }} retired employees without pensioner records. 
                                    <a href="#" onclick="moveAllRetiredToPensioners(); return false;" class="btn btn-sm btn-success">Process All</a>
                                </div>
                                
                                <form action="{{ route('pensioners.store') }}" method="POST">
                                    @csrf
                                    
                                    <div class="mb-3">
                                        <label for="retirement_id" class="form-label">Retired Employee</label>
                                        <select class="form-control" id="retirement_id" name="retirement_id" required>
                                            <option value="">Select Retired Employee</option>
                                            @foreach($retirements as $retirement)
                                                <option value="{{ $retirement->id }}">
                                                    {{ $retirement->employee->full_name }} ({{ $retirement->employee->employee_id }}) - {{ $retirement->retirement_date }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="pension_amount" class="form-label">Pension Amount</label>
                                                <input type="number" step="0.01" class="form-control" id="pension_amount" name="pension_amount" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="gratuity_amount" class="form-label">Gratuity Amount</label>
                                                <input type="number" step="0.01" class="form-control" id="gratuity_amount" name="gratuity_amount" required>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="bank_id" class="form-label">Bank</label>
                                                <select class="form-control" id="bank_id" name="bank_id">
                                                <option value="">Select Bank</option>
                                                @foreach($banks as $bank)
                                                    <option value="{{ $bank->id }}">{{ $bank->name }}</option>
                                                @endforeach
                                            </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="status" class="form-label">Status</label>
                                                <select class="form-control" id="status" name="status">
                                                    <option value="Active">Active</option>
                                                    <option value="Terminated">Terminated</option>
                                                    <option value="Deceased">Deceased</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="account_number" class="form-label">Account Number</label>
                                                <input type="text" class="form-control" id="account_number" name="account_number">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="account_name" class="form-label">Account Name</label>
                                                <input type="text" class="form-control" id="account_name" name="account_name">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary">Add Pensioner</button>
                                    <a href="{{ route('pensioners.index') }}" class="btn btn-secondary">Cancel</a>
                                </form>
                            @else
                                <div class="alert alert-info">
                                    No retired employees found without pensioner records. 
                                    All retired employees have been moved to the pensioners table.
                                </div>
                                <a href="{{ route('pensioners.index') }}" class="btn btn-primary">View Pensioners</a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const employeeSelect = document.getElementById('employee_id');
    const firstNameInput = document.getElementById('first_name');
    const surnameInput = document.getElementById('surname');
    const bankInput = document.getElementById('bank_id');
    const accountNumberInput = document.getElementById('account_number');
    const accountNameInput = document.getElementById('account_name');
    
    if (employeeSelect) {
        employeeSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            
            if (selectedOption.value) {
                // Auto-fill name fields
                firstNameInput.value = selectedOption.dataset.firstName || '';
                surnameInput.value = selectedOption.dataset.surname || '';
                
                // Auto-fill bank details directly from data attributes
                bankInput.value = selectedOption.dataset.bankId || '';
                accountNumberInput.value = selectedOption.dataset.accountNo || '';
                accountNameInput.value = selectedOption.dataset.accountName || '';
            } else {
                // Clear fields
                firstNameInput.value = '';
                surnameInput.value = '';
                bankInput.value = '';
                accountNumberInput.value = '';
                accountNameInput.value = '';
            }
        });
    }
});

function moveAllRetiredToPensioners() {
    if (confirm('Are you sure you want to process all retired employees to pensioners? This will move all eligible retired employees to the pensioners table.')) {
        fetch('{{ route("pensioners.move-retired") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while processing retired employees.');
        });
    }
}
</script>
@endsection
