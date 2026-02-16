@extends('layouts.app')

@section('title', 'Payment Transactions Report')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Payment Transactions Report</h3>
                    <div class="card-tools d-flex align-items-center">
                        <a href="{{ route('reports.index') }}" class="btn btn-sm btn-secondary mr-2">
                            <i class="fas fa-arrow-left"></i> Back to Reports
                        </a>
                        <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('reports.payment-transactions') }}" method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="payment_month">Select Month</label>
                                    <input type="month" name="payment_month" id="payment_month" class="form-control" value="{{ request('payment_month', now()->format('Y-m')) }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="status">Status</label>
                                    <select name="status" id="status" class="form-control">
                                        <option value="">All Statuses</option>
                                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="successful" {{ request('status') == 'successful' ? 'selected' : '' }}>Successful</option>
                                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="bank_code">Bank</label>
                                    <select name="bank_code" id="bank_code" class="form-control">
                                        <option value="">All Banks</option>
                                        @foreach($banks as $bank)
                                            <option value="{{ $bank->bank_code }}" {{ request('bank_code') == $bank->bank_code ? 'selected' : '' }}>
                                                {{ $bank->bank_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="department_id">Department</label>
                                    <select name="department_id" id="department_id" class="form-control">
                                        <option value="">All Departments</option>
                                        @foreach($departments as $dept)
                                            <option value="{{ $dept->department_id }}" {{ request('department_id') == $dept->department_id ? 'selected' : '' }}>
                                                {{ $dept->department_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="appointment_type_id">Appointment Type</label>
                                    <select name="appointment_type_id" id="appointment_type_id" class="form-control">
                                        <option value="">All Types</option>
                                        <option value="pensioner" {{ request('appointment_type_id') == 'pensioner' ? 'selected' : '' }}>Pensioners</option>
                                        @foreach($appointmentTypes as $type)
                                            <option value="{{ $type->id }}" {{ request('appointment_type_id') == $type->id ? 'selected' : '' }}>
                                                {{ $type->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="search">Search Employee</label>
                                    <input type="text" name="search" id="search" class="form-control" placeholder="Name or Staff ID" value="{{ request('search') }}">
                                </div>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-12 text-right">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter"></i> Filter
                                </button>
                                <a href="{{ route('reports.payment-transactions') }}" class="btn btn-secondary">
                                    <i class="fas fa-undo"></i> Reset
                                </a>
                                <a href="{{ route('reports.payment-transactions.export', request()->all()) }}" class="btn btn-success">
                                    <i class="fas fa-file-csv"></i> Export CSV
                                </a>
                                <button type="button" class="btn btn-info" onclick="window.print()">
                                    <i class="fas fa-print"></i> Print
                                </button>
                            </div>
                        </div>
                    </form>

                    <div class="text-center mb-4">
                        <img src="{{ asset('images/WhatsApp Image 2026-01-22 at 10.28.01 AM.jpeg') }}" alt="Logo" style="width: 80px; height: 80px;" class="mb-2">
                        <h3 class="font-weight-bold text-uppercase mb-1" style="color: #000;">KATSINA STATE WATER BOARD</h3>
                        <h5 class="font-weight-bold text-uppercase mb-1" style="color: #000;">
                            {{ \Carbon\Carbon::parse(request('payment_month', now()))->format('F Y') }} SALARY
                        </h5>
                        <h5 class="font-weight-bold text-uppercase" style="color: #000;">
                            @php
                                $typeId = request('appointment_type_id');
                                if ($typeId === 'pensioner') {
                                    $typeName = 'PENSIONER';
                                } else {
                                    $typeName = $typeId ? $appointmentTypes->firstWhere('id', $typeId)->name : 'ALL STAFF';
                                }
                            @endphp
                            {{ $typeName }} PAYMENT SCHEDULE
                        </h5>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Employee</th>
                                    <th>Staff ID</th>
                                    <th>Payroll Month</th>
                                    <th>Amount</th>
                                    <th>Bank</th>
                                    <th>Account Name</th>
                                    <th>Account Number</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($transactions as $transaction)
                                    <tr>
                                        <td>{{ $transaction->payment_date ? \Carbon\Carbon::parse($transaction->payment_date)->format('Y-m-d') : 'N/A' }}</td>
                                        <td>
                                            @if($transaction->employee)
                                                {{ $transaction->employee->first_name }} {{ $transaction->employee->surname }}
                                            @else
                                                <span class="text-muted">Unknown Employee</span>
                                            @endif
                                        </td>
                                        <td>{{ $transaction->employee->staff_no ?? $transaction->employee_id }}</td>
                                        <td>
                                            @if($transaction->payroll && $transaction->payroll->payroll_month)
                                                {{ $transaction->payroll->payroll_month->format('M Y') }}
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td>₦{{ number_format($transaction->amount, 2) }}</td>
                                        <td>{{ $transaction->bank_code }}</td>
                                        <td>{{ $transaction->account_name }}</td>
                                        <td>{{ $transaction->account_number }}</td>
                                        <td>
                                            @if($transaction->status == 'successful')
                                                <span class="badge badge-success">Successful</span>
                                            @elseif($transaction->status == 'pending')
                                                <span class="badge badge-warning">Pending</span>
                                            @elseif($transaction->status == 'failed')
                                                <span class="badge badge-danger">Failed</span>
                                            @else
                                                <span class="badge badge-secondary">{{ ucfirst($transaction->status) }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">No payment transactions found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $transactions->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
