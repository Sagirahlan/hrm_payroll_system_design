@extends('layouts.app')

@section('title', 'Pension Payment History')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4>Pension Payment History for {{ $pensioner->employee->first_name }} {{ $pensioner->employee->surname }}</h4>
                        <a href="{{ route('pensioners.index') }}" class="btn btn-secondary">Back to Pensioners</a>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Pensioner Details -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5>Pensioner Information</h5>
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>Employee ID:</strong></td>
                                    <td>{{ $pensioner->employee->employee_id }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Pension Amount:</strong></td>
                                    <td>₦{{ number_format($pensioner->pension_amount, 2) }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Pension Type:</strong></td>
                                    <td>{{ $pensioner->pension_type }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        <span class="badge 
                                            {{ $pensioner->status === 'Active' ? 'bg-success' : 'bg-secondary' }}">
                                            {{ $pensioner->status }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5>Payment Tracking</h5>
                            <form method="POST" action="{{ route('pensioners.trackPayment', $pensioner->pensioner_id) }}">
                                @csrf
                                <div class="row">
                                    <div class="col-md-4">
                                        <label for="payment_date" class="form-label">Payment Date</label>
                                        <input type="date" name="payment_date" id="payment_date" class="form-control" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="amount" class="form-label">Amount</label>
                                        <input type="number" name="amount" id="amount" class="form-control" step="0.01" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="status" class="form-label">Status</label>
                                        <select name="status" id="status" class="form-control" required>
                                            <option value="Pending">Pending</option>
                                            <option value="Paid">Paid</option>
                                            <option value="Failed">Failed</option>
                                            <option value="Cancelled">Cancelled</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-4">
                                        <label for="payment_method" class="form-label">Payment Method</label>
                                        <input type="text" name="payment_method" id="payment_method" class="form-control">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="reference" class="form-label">Reference</label>
                                        <input type="text" name="reference" id="reference" class="form-control">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="account_number" class="form-label">Account</label>
                                        <input type="text" name="account_number" id="account_number" class="form-control">
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <button type="submit" class="btn btn-primary">Track Payment</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Payment History Table -->
                    <h5>Payment History</h5>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Month</th>
                                    <th>Amount</th>
                                    <th>Payment Date</th>
                                    <th>Status</th>
                                    <th>Method</th>
                                    <th>Reference</th>
                                    <th>Created At</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($paymentHistory as $payment)
                                    <tr>
                                        <td>{{ $payment->payroll_month ? \Carbon\Carbon::parse($payment->payroll_month)->format('M Y') : 'N/A' }}</td>
                                        <td>₦{{ number_format($payment->net_salary, 2) }}</td>
                                        <td>{{ $payment->payment_date ? \Carbon\Carbon::parse($payment->payment_date)->format('M d, Y') : 'N/A' }}</td>
                                        <td>
                                            <span class="badge 
                                                {{ $payment->status === 'Paid' ? 'bg-success' : 
                                                   ($payment->status === 'Pending' ? 'bg-warning' : 
                                                   ($payment->status === 'Failed' ? 'bg-danger' : 'bg-secondary')) }}">
                                                {{ $payment->status }}
                                            </span>
                                        </td>
                                        <td>{{ $payment->transaction ? $payment->transaction->method : 'N/A' }}</td>
                                        <td>{{ $payment->transaction ? $payment->transaction->reference : 'N/A' }}</td>
                                        <td>{{ $payment->created_at->format('M d, Y H:i') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">No payment history found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center">
                        {{ $paymentHistory->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection