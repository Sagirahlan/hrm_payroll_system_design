@extends('layouts.app')

@section('title', 'Edit Leave Request')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Edit Leave Request</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('leaves.update', $leave->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="employee_id" class="form-label">Employee</label>
                                    <select name="employee_id" id="employee_id" class="form-select @error('employee_id') is-invalid @enderror" required>
                                        <option value="">Select Employee</option>
                                        @foreach($employees as $employee)
                                            <option value="{{ $employee->employee_id }}" {{ old('employee_id', $leave->employee_id) == $employee->employee_id ? 'selected' : '' }}>
                                                {{ $employee->first_name }} {{ $employee->surname }} ({{ $employee->staff_no }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('employee_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                                        <option value="pending" {{ old('status', $leave->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="approved" {{ old('status', $leave->status) == 'approved' ? 'selected' : '' }}>Approved</option>
                                        <option value="rejected" {{ old('status', $leave->status) == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="leave_type" class="form-label">Leave Type</label>
                                    <select name="leave_type" id="leave_type" class="form-select @error('leave_type') is-invalid @enderror" required>
                                        <option value="">Select Leave Type</option>
                                        <option value="Annual" {{ old('leave_type', $leave->leave_type) == 'Annual' ? 'selected' : '' }}>Annual Leave</option>
                                        <option value="Sick" {{ old('leave_type', $leave->leave_type) == 'Sick' ? 'selected' : '' }}>Sick Leave</option>
                                        <option value="Maternity" {{ old('leave_type', $leave->leave_type) == 'Maternity' ? 'selected' : '' }}>Maternity Leave</option>
                                        <option value="Paternity" {{ old('leave_type', $leave->leave_type) == 'Paternity' ? 'selected' : '' }}>Paternity Leave</option>
                                        <option value="Emergency" {{ old('leave_type', $leave->leave_type) == 'Emergency' ? 'selected' : '' }}>Emergency Leave</option>
                                        <option value="Study" {{ old('leave_type', $leave->leave_type) == 'Study' ? 'selected' : '' }}>Study Leave</option>
                                        <option value="Other" {{ old('leave_type', $leave->leave_type) == 'Other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                    @error('leave_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="start_date" class="form-label">Start Date</label>
                                    <input type="date" name="start_date" id="start_date" class="form-control @error('start_date') is-invalid @enderror" value="{{ old('start_date', $leave->start_date) }}" required>
                                    @error('start_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="end_date" class="form-label">End Date</label>
                                    <input type="date" name="end_date" id="end_date" class="form-control @error('end_date') is-invalid @enderror" value="{{ old('end_date', $leave->end_date) }}" required>
                                    @error('end_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="reason" class="form-label">Reason</label>
                                    <textarea name="reason" id="reason" class="form-control @error('reason') is-invalid @enderror" rows="4" placeholder="Enter reason for leave request">{{ old('reason', $leave->reason) }}</textarea>
                                    @error('reason')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">Update Leave Request</button>
                            <a href="{{ route('leaves.index') }}" class="btn btn-secondary ms-2">Cancel</a>
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
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    
    startDateInput.addEventListener('change', function() {
        endDateInput.min = this.value;
    });
    
    // Set min date for start date to today
    startDateInput.min = new Date().toISOString().split('T')[0];
});
</script>
@endpush