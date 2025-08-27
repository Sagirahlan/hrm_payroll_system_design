@extends('layouts.app')

@section('title', 'Edit Disciplinary Action')

@section('content')
<div class="container">
    <h1>Edit Disciplinary Action</h1>
    <div class="card">
        <div class="card-body">
            <form action="{{ route('disciplinary.update', $action->action_id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label for="employee_id" class="form-label">Employee</label>
                    <select class="form-select @error('employee_id') is-invalid @enderror" id="employee_id" name="employee_id" required>
                        <option value="">Select Employee</option>
                        @foreach ($employees as $employee)
                            <option value="{{ $employee->employee_id }}" {{ old('employee_id', $action->employee_id) == $employee->employee_id ? 'selected' : '' }} {{ old('employee_id', $action->employee_id) == $employee->employee_id ? 'readonly disabled' : 'disabled' }}>
                                {{ $employee->first_name }} {{ $employee->surname }}
                            </option>
                        @endforeach
                    </select>
                    @error('employee_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="action_type" class="form-label">Action Type</label>
                    <input type="text" class="form-control @error('action_type') is-invalid @enderror" id="action_type" name="action_type" value="{{ old('action_type', $action->action_type) }}" required readonly>
                    @error('action_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="4">{{ old('description', $action->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="action_date" class="form-label">Action Date</label>
                    <input type="date" class="form-control @error('action_date') is-invalid @enderror" id="action_date" name="action_date" value="{{ old('action_date', $action->action_date) }}" required>
                    @error('action_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="resolution_date" class="form-label">Resolution Date</label>
                    <input type="date" class="form-control @error('resolution_date') is-invalid @enderror" id="resolution_date" name="resolution_date" value="{{ old('resolution_date', $action->resolution_date) }}">
                    @error('resolution_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                        <option value="Open" {{ old('status', $action->status) == 'Open' ? 'selected' : '' }}>Open</option>
                        <option value="Resolved" {{ old('status', $action->status) == 'Resolved' ? 'selected' : '' }} 
                            @if(old('status', $action->status) == 'Resolved') 
                                data-action-type-update="active" 
                            @endif
                        >Resolved</option>
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                const statusSelect = document.getElementById('status');
                                const actionTypeInput = document.getElementById('action_type');
                                statusSelect.addEventListener('change', function() {
                                    const selectedOption = statusSelect.options[statusSelect.selectedIndex];
                                    if (selectedOption.value === 'Resolved') {
                                        // Update action_type to 'active'
                                        if(actionTypeInput) actionTypeInput.value = 'active';
                                    }
                                });
                            });
                        </script>
                        <option value="Pending" {{ old('status', $action->status) == 'Pending' ? 'selected' : '' }}>Pending</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{ route('disciplinary.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection