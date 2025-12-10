@extends('layouts.app')

@section('title', 'Manage Employee Bank Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Manage Employee Bank Details</h4>
                    <p class="card-category">Update employee bank details</p>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <form action="{{ route('bank-details.search') }}" method="POST" class="d-flex">
                            @csrf
                            <input type="text" name="query" class="form-control me-2" placeholder="Search employees by name, ID, or staff number..." value="{{ request('query', '') }}">
                            <button class="btn btn-outline-primary" type="submit">Search</button>
                        </form>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead>
                                <tr>
                                    <th>Employee ID</th>
                                    <th>Full Name</th>
                                    <th>Department</th>
                                    <th>Bank Name</th>
                                    <th>Bank Code</th>
                                    <th>Account Number</th>
                                    <th>Account Name</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($employees as $employee)
                                    <tr>
                                        <td>{{ $employee->employee_id }}</td>
                                        <td>{{ $employee->first_name }} {{ $employee->middle_name }} {{ $employee->surname }}</td>
                                        <td>{{ $employee->department->department_name ?? 'N/A' }}</td>
                                        <td>{{ $employee->bank->bank_name ?? 'Not Set' }}</td>
                                        <td>{{ $employee->bank->bank_code ?? 'Not Set' }}</td>
                                        <td>{{ $employee->bank->account_no ?? 'Not Set' }}</td>
                                        <td>{{ $employee->bank->account_name ?? 'Not Set' }}</td>
                                        <td>
                                            <a href="{{ route('bank-details.show', $employee->employee_id) }}" class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i> Update
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">No employees found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection