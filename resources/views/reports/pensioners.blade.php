@extends('layouts.app')

@section('title', 'Pensioners Report')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4>Pensioners Report</h4>
                        <div>
                            <a href="{{ route('reports.index') }}" class="btn btn-secondary">Back to Reports</a>
                            <button onclick="window.print()" class="btn btn-primary">Print Report</button>
                        </div>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Report Header -->
                    <div class="row mb-4">
                        <div class="col-md-12 text-center">
                            <h2>{{ $reportData['report_title'] ?? 'Pensioners Report' }}</h2>
                            <p><strong>Generated on:</strong> {{ $reportData['generated_date'] ?? 'N/A' }}</p>
                            <p><strong>Total Pensioners:</strong> {{ $reportData['total_pensioners'] ?? 0 }}</p>
                        </div>
                    </div>
                    
                    <!-- Pensioners Table -->
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Employee ID</th>
                                    <th>Full Name</th>
                                    <th>Department</th>
                                    <th>Cadre</th>
                                    <th>Grade Level</th>
                                    <th>Retirement Date</th>
                                    <th>Pension Start Date</th>
                                    <th>Pension Type</th>
                                    <th>Pension Amount</th>
                                    <th>RSA Balance at Retirement</th>
                                    <th>Lump Sum Amount</th>
                                    <th>Expected Lifespan (Months)</th>
                                    <th>Status</th>
                                    <th>Bank Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($reportData['pensioners'] ?? [] as $index => $pensioner)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $pensioner['employee_id'] ?? 'N/A' }}</td>
                                        <td>{{ $pensioner['full_name'] ?? 'N/A' }}</td>
                                        <td>{{ $pensioner['department'] ?? 'N/A' }}</td>
                                        <td>{{ $pensioner['cadre'] ?? 'N/A' }}</td>
                                        <td>{{ $pensioner['grade_level'] ?? 'N/A' }}</td>
                                        <td>{{ $pensioner['retirement_date'] ?? 'N/A' }}</td>
                                        <td>{{ $pensioner['pension_start_date'] ?? 'N/A' }}</td>
                                        <td>{{ $pensioner['pension_type'] ?? 'N/A' }}</td>
                                        <td>{{ $pensioner['pension_amount'] ?? '₦0.00' }}</td>
                                        <td>{{ $pensioner['rsa_balance_at_retirement'] ?? '₦0.00' }}</td>
                                        <td>{{ $pensioner['lump_sum_amount'] ?? '₦0.00' }}</td>
                                        <td>{{ $pensioner['expected_lifespan_months'] ?? 'N/A' }}</td>
                                        <td>
                                            @if(isset($pensioner['status']))
                                                <span class="badge 
                                                    @if($pensioner['status'] === 'Active')
                                                        bg-success
                                                    @elseif($pensioner['status'] === 'Deceased')
                                                        bg-danger
                                                    @else
                                                        bg-secondary
                                                    @endif">
                                                    {{ $pensioner['status'] }}
                                                </span>
                                            @else
                                                <span class="badge bg-secondary">N/A</span>
                                            @endif
                                        </td>
                                        <td>{{ $pensioner['bank_details'] ?? 'N/A' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="15" class="text-center">No pensioners found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="card-footer">
                    <div class="d-flex justify-content-between">
                        <small class="text-muted">
                            Report generated on {{ $reportData['generated_date'] ?? 'N/A' }}
                        </small>
                        <small class="text-muted">
                            Total pensioners: {{ $reportData['total_pensioners'] ?? 0 }}
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    body * {
        visibility: hidden;
    }
    .card, .card * {
        visibility: visible;
    }
    .card {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }
}
</style>
@endsection