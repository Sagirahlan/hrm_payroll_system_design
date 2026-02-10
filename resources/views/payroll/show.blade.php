@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="card border-primary shadow">
        <div class="card-header" style="background-color: skyblue; color: white;">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Payroll Details</h5>
                <a href="{{ route('payroll.index') }}" class="btn btn-light">
                    <i class="fas fa-arrow-left"></i> Back to Payroll List
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            Employee Information
                        </div>
                        <div class="card-body">
                            <p><strong>Name:</strong> {{ $payroll->employee->first_name }} {{ $payroll->employee->surname }}</p>
                            <p><strong>Staff No:</strong> {{ $payroll->employee->staff_no }}</p>
                            <p><strong>Department:</strong> {{ ($payroll->department ?? ($payroll->employee->department ?? null))->department_name ?? 'N/A' }}</p>
                            <p><strong>Grade Level:</strong> {{ $payroll->gradeLevel->name ?? 'N/A' }}</p>
                            <p><strong>Step:</strong> {{ $payroll->step->name ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            Payroll Information
                        </div>
                        <div class="card-body">
                            <p><strong>Payroll Month:</strong> {{ \Carbon\Carbon::parse($payroll->payroll_month)->format('F Y') }}</p>
                            <p><strong>Basic Salary:</strong> ₦{{ number_format($payroll->basic_salary, 2) }}</p>
                            <p><strong>Total Additions:</strong> <span class="text-success">₦{{ number_format($payroll->total_additions, 2) }}</span></p>
                            <p><strong>Total Deductions:</strong> <span class="text-danger">₦{{ number_format($payroll->total_deductions, 2) }}</span></p>
                            <p><strong>Net Salary:</strong> <strong>₦{{ number_format($payroll->net_salary, 2) }}</strong></p>
                            <p><strong>Status:</strong> <span class="badge bg-{{ $payroll->status === 'Approved' ? 'success' : 'warning text-dark' }}">{{ $payroll->status }}</span></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            Additions for {{ \Carbon\Carbon::parse($payroll->payroll_month)->format('F Y') }}
                        </div>
                        <div class="card-body">
                            @if($additions->count() > 0)
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Type</th>
                                            <th>Amount</th>
                                            <th>Calculation</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($additions as $addition)
                                            <tr>
                                                <td>{{ $addition->additionType->name }}</td>
                                                <td class="text-success">₦{{ number_format($addition->amount, 2) }}</td>
                                                <td>{{ $addition->calculation_type_description }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @else
                                <p class="text-muted">No additions for this period.</p>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            Deductions for {{ \Carbon\Carbon::parse($payroll->payroll_month)->format('F Y') }}
                        </div>
                        <div class="card-body">
                            @if($deductions->count() > 0)
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Type</th>
                                            <th>Amount</th>
                                            <th>Calculation</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($deductions as $deduction)
                                            <tr>
                                                <td>{{ $deduction->deduction_type }}</td>
                                                <td class="text-danger">₦{{ number_format($deduction->amount, 2) }}</td>
                                                <td>{{ $deduction->calculation_type_description }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @else
                                <p class="text-muted">No deductions for this period.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            Payroll Actions
                        </div>
                        <div class="card-body">
                            <div class="d-flex gap-2">
                                @can('view_payslips')
                                <a href="{{ route('payroll.payslip', $payroll->payroll_id) }}" class="btn btn-primary">
                                    <i class="fas fa-download"></i> Download Pay Slip
                                </a>
                                @endcan
                                
                                @if($payroll->status !== 'Approved')
                                    @can('approve_payroll')
                                    <form action="{{ route('payroll.approve', $payroll->payroll_id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-success" onclick="return confirm('Are you sure you want to approve this payroll?')">
                                            <i class="fas fa-check"></i> Approve Payroll
                                        </button>
                                    </form>
                                    @endcan
                                @endif
                                
                                @can('manage_payroll_adjustments')
                                <a href="{{ route('payroll.deductions.show', $payroll->employee_id) }}" class="btn btn-warning">
                                    <i class="fas fa-minus-circle"></i> Manage Deductions
                                </a>
                                
                                <a href="{{ route('payroll.additions.show', $payroll->employee_id) }}" class="btn btn-info">
                                    <i class="fas fa-plus-circle"></i> Manage Additions
                                </a>
                                @endcan
                                
                                <button type="button" class="btn btn-secondary" onclick="window.print()">
                                    <i class="fas fa-print"></i> Print
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
