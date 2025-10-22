@extends('layouts.app')

@section('content')
<div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="card border-0 shadow-lg" style="max-width: 800px; width: 100%; background: linear-gradient(135deg, #e0f7fa 0%, #ffffff 100%);">
        <div class="card-header text-center" style="background: #00bcd4;">
            <h4 class="mb-0 text-white font-weight-bold">Pensioner Details</h4>
        </div>
        <div class="card-body p-4">
            <!-- Employee Photo if available -->
            @if ($pensioner->employee->photo_path)
                <div class="mb-4 text-center">
                    <img src="{{ asset('storage/' . $pensioner->employee->photo_path) }}" alt="Employee Photo" class="img-thumbnail border border-info shadow-sm" width="120">
                </div>
            @endif
            
            <!-- Personal Information -->
            <div class="mb-4">
                <h5 class="text-primary mb-3">Personal Information</h5>
                <div class="row">
                    <div class="col-md-6"><p><strong>Employee Name:</strong> {{ $pensioner->employee->first_name . ' ' . $pensioner->employee->surname }}</p></div>
                    <div class="col-md-6"><p><strong>Employee ID:</strong> {{ $pensioner->employee_id }}</p></div>
                    <div class="col-md-6"><p><strong>Gender:</strong> {{ $pensioner->employee->gender }}</p></div>
                    <div class="col-md-6"><p><strong>Date of Birth:</strong> {{ $pensioner->employee->date_of_birth ? \Carbon\Carbon::parse($pensioner->employee->date_of_birth)->format('Y-m-d') : 'N/A' }}</p></div>
                    <div class="col-md-6"><p><strong>Age:</strong> {{ $pensioner->employee->date_of_birth ? \Carbon\Carbon::parse($pensioner->employee->date_of_birth)->age : 'N/A' }} years</p></div>
                    <div class="col-md-6"><p><strong>Nationality:</strong> {{ $pensioner->employee->nationality }}</p></div>
                    <div class="col-md-6"><p><strong>State of Origin:</strong> {{ $pensioner->employee->state->name ?? 'N/A' }}</p></div>
                    <div class="col-md-6"><p><strong>Department:</strong> {{ $pensioner->employee->department->department_name ?? 'N/A' }}</p></div>
                </div>
            </div>
            
            <!-- Pension Information -->
            <div class="mb-4">
                <h5 class="text-primary mb-3">Pension Information</h5>
                <div class="row">
                    <div class="col-md-6"><p><strong>Pension Start Date:</strong> {{ $pensioner->pension_start_date ? $pensioner->pension_start_date->format('Y-m-d') : 'N/A' }}</p></div>
                    <div class="col-md-6"><p><strong>Pension Amount:</strong> ₦{{ number_format($pensioner->pension_amount, 2) }}</p></div>
                    <div class="col-md-6"><p><strong>Status:</strong> 
                        <span class="badge {{ $pensioner->status === 'Active' ? 'bg-success' : 'bg-secondary' }}">
                            {{ $pensioner->status }}
                        </span>
                    </p></div>
                    <div class="col-md-6"><p><strong>Pension Type:</strong> {{ $pensioner->pension_type ?? 'N/A' }}</p></div>
                    <div class="col-md-6"><p><strong>RSA Balance at Retirement:</strong> ₦{{ $pensioner->rsa_balance_at_retirement ? number_format($pensioner->rsa_balance_at_retirement, 2) : 'N/A' }}</p></div>
                    <div class="col-md-6"><p><strong>Lump Sum Amount:</strong> ₦{{ $pensioner->lump_sum_amount ? number_format($pensioner->lump_sum_amount, 2) : 'N/A' }}</p></div>
                    <div class="col-md-6"><p><strong>Expected Lifespan (Months):</strong> {{ $pensioner->expected_lifespan_months ?? 'N/A' }}</p></div>
                </div>
            </div>
            
            <!-- Retirement Information -->
            @if($pensioner->retirement)
            <div class="mb-4">
                <h5 class="text-primary mb-3">Retirement Information</h5>
                <div class="row">
                    <div class="col-md-6"><p><strong>Retirement Date:</strong> {{ $pensioner->retirement->retirement_date ? \Carbon\Carbon::parse($pensioner->retirement->retirement_date)->format('Y-m-d') : 'N/A' }}</p></div>
                    <div class="col-md-6"><p><strong>Reason for Retirement:</strong> {{ $pensioner->retirement->reason ?? 'N/A' }}</p></div>
                    <div class="col-md-6"><p><strong>Retirement Status:</strong> {{ $pensioner->retirement->status ?? 'N/A' }}</p></div>
                    <div class="col-md-6"><p><strong>Comments:</strong> {{ $pensioner->retirement->comments ?? 'N/A' }}</p></div>
                </div>
            </div>
            @endif
            
            <!-- Actions -->
            <div class="d-flex justify-content-between mt-4">
                <a href="{{ route('pensioners.index') }}" class="btn btn-secondary rounded-pill px-4">Back to List</a>
                <div>
                    @can('view_pensioners')
                    <a href="{{ route('pensioners.paymentHistory', $pensioner->pensioner_id) }}" class="btn btn-info rounded-pill px-4 mx-1">Payment History</a>
                    @endcan
                    @can('edit_pensioners')
                    <a href="{{ route('pensioners.edit', $pensioner->pensioner_id) }}" class="btn btn-warning rounded-pill px-4">Edit</a>
                    @endcan
                </div>
            </div>
        </div>
    </div>
</div>
@endsection