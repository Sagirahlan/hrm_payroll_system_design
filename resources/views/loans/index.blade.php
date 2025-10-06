@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4>Loans Management</h4>
                        <a href="{{ route('loans.create') }}" class="btn btn-primary">Add New Loan</a>
                    </div>
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Employee</th>
                                    <th>Loan Type</th>
                                    <th>Principal Amount</th>
                                    <th>Monthly Deduction</th>
                                    <th>Monthly Percentage</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Remaining Balance</th>
                                    <th>Remaining Months</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($loans as $loan)
                                    <tr>
                                        <td>{{ $loan->loan_id }}</td>
                                        <td>{{ $loan->employee->first_name }} {{ $loan->employee->last_name }}</td>
                                        <td>{{ $loan->deductionType->name ?? $loan->loan_type }}</td>
                                        <td>{{ number_format($loan->principal_amount, 2) }}</td>
                                        <td>{{ number_format($loan->monthly_deduction, 2) }}</td>
                                        <td>{{ $loan->monthly_percentage ? $loan->monthly_percentage . '%' : 'N/A' }}</td>
                                        <td>{{ $loan->start_date->format('Y-m-d') }}</td>
                                        <td>{{ $loan->end_date ? $loan->end_date->format('Y-m-d') : 'N/A' }}</td>
                                        <td>{{ number_format($loan->remaining_balance, 2) }}</td>
                                        <td>{{ $loan->remaining_months }}</td>
                                        <td>
                                            <span class="badge bg-{{ $loan->status === 'active' ? 'primary' : ($loan->status === 'completed' ? 'success' : 'warning') }}">
                                                {{ ucfirst($loan->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('loans.show', $loan->loan_id) }}" class="btn btn-sm btn-info">View</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="12" class="text-center">No loans found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center">
                        {{ $loans->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection