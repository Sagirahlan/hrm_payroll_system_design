@extends('layouts.app')

@section('title', 'Create User')

@section('content')
<div class="container">
    <h1>Create User</h1>
    <div class="card" style="max-width: 500px; margin: 0 auto;">
        <div class="card-body">
            <form action="{{ route('users.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="employee_id" class="form-label">Employee</label>
                    <input type="text" id="employeeSearch" class="form-control mb-2" placeholder="Search employees...">
                    <select name="employee_id" id="employee_ids" class="form-select @error('employee_id') is-invalid @enderror">
                        <option value="">Select Employee</option>
                        @foreach ($employees as $employee)
                            <option value="{{ $employee->employee_id }}">{{ $employee->first_name }} {{ $employee->surname }} ({{ $employee->employee_id }})</option>
                        @endforeach
                    </select>
                    @error('employee_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" name="username" id="username" class="form-control @error('username') is-invalid @enderror" value="{{ old('username') }}">
                    @error('username')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror">
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="password_confirmation" class="form-label">Confirm Password</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control">
                </div>
                <div class="mb-3">
                    <label for="role" class="form-label">Role</label>
                    <select name="role" id="role" class="form-select @error('role') is-invalid @enderror">
                        <option value="">Select Role</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role }}">{{ $role }}</option>
                        @endforeach
                    </select>
                    @error('role')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Create User</button>
                <a href="{{ route('users.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
            </form>
        </div>
    </div>
</div>
<script>
                        document.getElementById('employeeSearch').addEventListener('input', function() {
                            const search = this.value.toLowerCase();
                            const select = document.getElementById('employee_ids');
                            for (let option of select.options) {
                                const text = option.text.toLowerCase();
                                option.style.display = text.includes(search) ? '' : 'none';
                            }
                        });
                    </script>
@endsection