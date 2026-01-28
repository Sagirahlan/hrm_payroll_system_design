@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Loan Details</h4>
                    <a href="{{ route('loans.index') }}" class="btn btn-secondary">Back to Loans</a>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Loan Information</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Loan ID:</strong></td>
                                    <td>{{ $loan->loan_id }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Staff No:</strong></td>
                                    <td>{{ $loan->employee->staff_no }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Employee:</strong></td>
                                    <td>{{ $loan->employee->first_name }} {{ $loan->employee->last_name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Loan Type:</strong></td>
                                    <td>{{ $loan->deductionType->name ?? $loan->loan_type }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Description:</strong></td>
                                    <td>{{ $loan->description ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        <span class="badge bg-{{ $loan->status === 'active' ? 'primary' : ($loan->status === 'completed' ? 'success' : 'warning') }}">
                                            {{ ucfirst($loan->status) }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5>Financial Information</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Principal Amount:</strong></td>
                                    <td>{{ number_format($loan->principal_amount, 2) }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Interest Rate:</strong></td>
                                    <td>{{ $loan->interest_rate ? $loan->interest_rate . '%' : '0.00%' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Total Interest:</strong></td>
                                    <td>{{ number_format($loan->total_interest, 2) }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Total Repayment:</strong></td>
                                    <td>{{ number_format($loan->total_repayment, 2) }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Monthly Deduction:</strong></td>
                                    <td>{{ number_format($loan->monthly_deduction, 2) }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Monthly Percentage:</strong></td>
                                    <td>{{ $loan->monthly_percentage ? $loan->monthly_percentage . '%' : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Total Months:</strong></td>
                                    <td>{{ $loan->total_months }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Remaining Months:</strong></td>
                                    <td>{{ $loan->remaining_months }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Months Completed:</strong></td>
                                    <td>{{ $loan->months_completed }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Total Repaid:</strong></td>
                                    <td>{{ number_format($loan->total_repaid, 2) }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Remaining Balance:</strong></td>
                                    <td>{{ number_format($loan->remaining_balance, 2) }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <h5>Dates</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Deduction Start Month:</strong></td>
                                    <td>{{ $loan->deduction_start_month ? \Carbon\Carbon::parse($loan->deduction_start_month . '-01')->format('F Y') : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>End Date:</strong></td>
                                    <td>{{ $loan->end_date ? $loan->end_date->format('Y-m-d') : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Calculated End Date:</strong></td>
                                    <td>{{ $loan->calculateEndDate()->format('Y-m-d') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="mt-3">
                        <a href="{{ route('loans.index') }}" class="btn btn-secondary">Back to List</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection