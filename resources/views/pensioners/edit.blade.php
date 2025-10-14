@extends('layouts.app')

@section('title', 'Edit Pensioner')

@section('content')
<div class="container-fluid py-4">
    <div class="card border-primary shadow">
        <div class="card-header" style="background-color: skyblue; color: white;">
            <h5 class="mb-0">Edit Pensioner</h5>
        </div>
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <form method="POST" action="{{ route('pensioners.update', $pensioner->pensioner_id) }}">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="employee_id" class="form-label">Employee</label>
                            <select name="employee_id" class="form-select" disabled>
                                <option value="{{ $pensioner->employee_id }}" selected>
                                    {{ $pensioner->employee ? $pensioner->employee->first_name . ' ' . $pensioner->employee->surname . ' (' . $pensioner->employee_id . ')' : 'N/A' }}
                                </option>
                            </select>
                            <div class="form-text">Employee cannot be changed once pensioner record is created</div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="pension_start_date" class="form-label">Pension Start Date</label>
                            <input type="date" class="form-control" id="pension_start_date" name="pension_start_date" 
                                   value="{{ old('pension_start_date', $pensioner->pension_start_date) }}" required>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="pension_amount" class="form-label">Pension Amount</label>
                            <input type="number" class="form-control" id="pension_amount" name="pension_amount" 
                                   value="{{ old('pension_amount', $pensioner->pension_amount) }}" step="0.01" min="0" required>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" class="form-select" id="status" required>
                                <option value="Active" {{ old('status', $pensioner->status) === 'Active' ? 'selected' : '' }}>Active</option>
                                <option value="Deceased" {{ old('status', $pensioner->status) === 'Deceased' ? 'selected' : '' }}>Deceased</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="pension_type" class="form-label">Pension Type</label>
                            <select name="pension_type" class="form-select" id="pension_type">
                                <option value="">Select Pension Type</option>
                                <option value="PW" {{ old('pension_type', $pensioner->pension_type) === 'PW' ? 'selected' : '' }}>Programmed Withdrawal (PW)</option>
                                <option value="Annuity" {{ old('pension_type', $pensioner->pension_type) === 'Annuity' ? 'selected' : '' }}>Annuity</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="rsa_balance_at_retirement" class="form-label">RSA Balance at Retirement</label>
                            <input type="number" class="form-control" id="rsa_balance_at_retirement" name="rsa_balance_at_retirement" 
                                   value="{{ old('rsa_balance_at_retirement', $pensioner->rsa_balance_at_retirement) }}" step="0.01" min="0">
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="lump_sum_amount" class="form-label">Lump Sum Amount</label>
                            <input type="number" class="form-control" id="lump_sum_amount" name="lump_sum_amount" 
                                   value="{{ old('lump_sum_amount', $pensioner->lump_sum_amount) }}" step="0.01" min="0">
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="expected_lifespan_months" class="form-label">Expected Lifespan (Months)</label>
                            <input type="number" class="form-control" id="expected_lifespan_months" name="expected_lifespan_months" 
                                   value="{{ old('expected_lifespan_months', $pensioner->expected_lifespan_months) }}" min="1">
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <a href="{{ route('pensioners.index') }}" class="btn btn-secondary rounded-pill px-4">Cancel</a>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Update Pensioner</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection