@extends('layouts.app')

@section('title', 'Request Leave')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Request Leave</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('leaves.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="employee_id" class="form-label">Employee</label>
                                    <input type="text" id="employee_search" class="form-control @error('employee_id') is-invalid @enderror" placeholder="Search employee...">
                                    <input type="hidden" name="employee_id" id="employee_id" value="{{ old('employee_id') }}">
                                    <div id="employee_list" class="list-group mt-2" style="max-height: 200px; overflow-y: auto; display: none;">
                                        @foreach($employees as $employee)
                                            <a href="#" class="list-group-item list-group-item-action employee-item"
                                               data-id="{{ $employee->employee_id }}"
                                               data-name="{{ $employee->first_name }} {{ $employee->surname }} ({{ $employee->staff_no }})">
                                                {{ $employee->first_name }} {{ $employee->surname }} ({{ $employee->staff_no }})
                                            </a>
                                        @endforeach
                                    </div>
                                    @error('employee_id')
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
                                        <option value="Annual" {{ old('leave_type') == 'Annual' ? 'selected' : '' }}>Annual Leave</option>
                                        <option value="Sick" {{ old('leave_type') == 'Sick' ? 'selected' : '' }}>Sick Leave</option>
                                        <option value="Maternity" {{ old('leave_type') == 'Maternity' ? 'selected' : '' }}>Maternity Leave</option>
                                        <option value="Paternity" {{ old('leave_type') == 'Paternity' ? 'selected' : '' }}>Paternity Leave</option>
                                        <option value="Emergency" {{ old('leave_type') == 'Emergency' ? 'selected' : '' }}>Emergency Leave</option>
                                        <option value="Study" {{ old('leave_type') == 'Study' ? 'selected' : '' }}>Study Leave</option>
                                        <option value="Other" {{ old('leave_type') == 'Other' ? 'selected' : '' }}>Other</option>
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
                                    <input type="date" name="start_date" id="start_date" class="form-control @error('start_date') is-invalid @enderror" value="{{ old('start_date') }}" required>
                                    @error('start_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="end_date" class="form-label">End Date</label>
                                    <input type="date" name="end_date" id="end_date" class="form-control @error('end_date') is-invalid @enderror" value="{{ old('end_date') }}" required>
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
                                    <textarea name="reason" id="reason" class="form-control @error('reason') is-invalid @enderror" rows="4" placeholder="Enter reason for leave request">{{ old('reason') }}</textarea>
                                    @error('reason')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">Submit Leave Request</button>
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
    const employeeSearch = document.getElementById('employee_search');
    const employeeList = document.getElementById('employee_list');
    const employeeSelect = document.getElementById('employee_id');
    const employeeItems = document.querySelectorAll('.employee-item');
    const form = document.querySelector('form');

    startDateInput.addEventListener('change', function() {
        endDateInput.min = this.value;
    });

    // Set min date for start date to today
    startDateInput.min = new Date().toISOString().split('T')[0];

    // Employee search functionality
    employeeSearch.addEventListener('keyup', function() {
        const searchTerm = this.value.toLowerCase();
        let hasResults = false;

        employeeItems.forEach(item => {
            const text = item.textContent.toLowerCase();
            if (text.includes(searchTerm)) {
                item.style.display = 'block';
                hasResults = true;
            } else {
                item.style.display = 'none';
            }
        });

        if (searchTerm.length > 0 && hasResults) {
            employeeList.style.display = 'block';
        } else {
            employeeList.style.display = 'none';
        }
    });

    // Select employee from search results
    employeeItems.forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const employeeId = this.getAttribute('data-id');
            const employeeName = this.getAttribute('data-name');

            employeeSearch.value = employeeName;
            employeeSelect.value = employeeId;
            employeeList.style.display = 'none';
        });
    });

    // Hide list when clicking outside
    document.addEventListener('click', function(e) {
        if (!employeeSearch.contains(e.target) && !employeeList.contains(e.target)) {
            employeeList.style.display = 'none';
        }
    });

    // Form validation to ensure employee is selected
    form.addEventListener('submit', function(e) {
        if (!employeeSelect.value) {
            e.preventDefault();
            employeeSearch.classList.add('is-invalid');
            alert('Please select an employee from the search results.');
        } else {
            employeeSearch.classList.remove('is-invalid');
        }
    });
});
</script>
@endpush