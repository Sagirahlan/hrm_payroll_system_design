@extends('layouts.app')

@section('title', 'Update Employee Bank Details - ' . $employee->first_name . ' ' . $employee->surname)

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <a href="{{ route('bank-details.index') }}" class="btn btn-outline-primary">
                <i class="fa fa-chevron-left"></i> Back to List
            </a>
        </div>
    </div>
    
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Update Bank Details for {{ $employee->first_name }} {{ $employee->surname }}</h4>
                    <p class="card-category">Employee ID: {{ $employee->employee_id }}</p>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Current Bank Details</h5>
                            <div class="border p-3 rounded mb-4">
                                @if($currentBankDetails)
                                    <p><strong>Bank Name:</strong> {{ $currentBankDetails->bank_name }}</p>
                                    <p><strong>Account Number:</strong> {{ $currentBankDetails->account_no }}</p>
                                    <p><strong>Account Name:</strong> {{ $currentBankDetails->account_name }}</p>
                                    <p><strong>Bank Code:</strong> {{ $currentBankDetails->bank_code }}</p>
                                @else
                                    <p>No bank details found for this employee.</p>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-6">
                            <h5>Employee Information</h5>
                            <div class="border p-3 rounded mb-4">
                                <p><strong>Name:</strong> {{ $employee->first_name }} {{ $employee->middle_name }} {{ $employee->surname }}</p>
                                <p><strong>Staff Number:</strong> {{ $employee->staff_no }}</p>
                                <p><strong>Department:</strong> {{ $employee->department->department_name ?? 'N/A' }}</p>
                                <p><strong>Status:</strong> <span class="badge bg-{{ $employee->status == 'Active' ? 'success' : 'warning' }}">{{ $employee->status }}</span></p>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('bank-details.update', $employee->employee_id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="bank_name" class="form-label">Bank Name *</label>
                                    <select name="bank_name" id="bank_name" class="form-control @error('bank_name') is-invalid @enderror" required>
                                        <option value="">Select Bank</option>
                                        @foreach($banks as $bank)
                                            <option value="{{ $bank->bank_name }}"
                                                    data-code="{{ $bank->bank_code }}"
                                                    {{ (old('bank_name', $currentBankDetails->bank_name ?? '') == $bank->bank_name) ? 'selected' : '' }}>
                                                {{ $bank->bank_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('bank_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="bank_code" class="form-label">Bank Code *</label>
                                    <input type="text" name="bank_code" id="bank_code" class="form-control @error('bank_code') is-invalid @enderror"
                                           value="{{ old('bank_code', $currentBankDetails->bank_code ?? '') }}"
                                           required readonly>
                                    @error('bank_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="account_name" class="form-label">Account Name *</label>
                                    <input type="text" name="account_name" id="account_name" class="form-control @error('account_name') is-invalid @enderror"
                                           value="{{ old('account_name', $currentBankDetails->account_name ?? '') }}" required>
                                    @error('account_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="account_no" class="form-label">Account Number *</label>
                                    <input type="text" name="account_no" id="account_no" class="form-control @error('account_no') is-invalid @enderror"
                                           value="{{ old('account_no', $currentBankDetails->account_no ?? '') }}" required>
                                    @error('account_no')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Bank Details
                            </button>
                            <a href="{{ route('bank-details.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to List
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const bankNameSelect = document.getElementById('bank_name');
    const bankCodeInput = document.getElementById('bank_code');

    if (bankNameSelect && bankCodeInput) {
        // Set the bank code field to readonly initially
        bankCodeInput.readOnly = true;

        // Set initial value if a bank is already selected
        if (bankNameSelect.value) {
            const selectedOption = bankNameSelect.options[bankNameSelect.selectedIndex];
            if (selectedOption) {
                const bankCode = selectedOption.getAttribute('data-code');
                bankCodeInput.value = bankCode || '';
            }
        }

        // Add event listener for bank name change
        bankNameSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption) {
                const bankCode = selectedOption.getAttribute('data-code');
                bankCodeInput.value = bankCode || '';
            }
        });
    }
});
</script>
@endpush