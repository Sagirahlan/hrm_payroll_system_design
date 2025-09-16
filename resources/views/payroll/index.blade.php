@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="card border-primary shadow">
        <div class="card-header" style="background-color: skyblue; color: white;">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Payroll Records</h5>
                <a href="{{ route('payroll.adjustments.manage') }}" class="btn btn-light">
                    <i class="fas fa-users-cog"></i> Manage All Deductions/Additions
                </a>
            </div>
        </div>
        <div class="card-body">
            
            <!-- Payroll Generation Form -->
            <div class="card border-primary mb-4 shadow">
                <div class="card-header" style="background-color: skyblue; color: white;">
                    <strong>Generate Payroll</strong>
                </div>
                <div class="card-body">
                    <form action="{{ route('payroll.generate') }}" method="POST" class="row g-3 align-items-end">
                        @csrf
                        <div class="col-auto">
                            <label for="month" class="form-label mb-0">Select Month</label>
                            <input type="month" name="month" id="month" value="{{ now()->format('Y-m') }}"
                                   class="form-control">
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary mt-2">
                                Generate Payroll
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Search and Filter Section -->
            <div class="card border-info mb-4 shadow">
                <div class="card-header" style="background-color: #17a2b8; color: white;">
                    <strong>Search & Filter</strong>
                    <button class="btn btn-sm btn-outline-light float-end" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse" aria-expanded="false">
                        <i class="fas fa-filter"></i> Toggle Filters
                    </button>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('payroll.index') }}" class="mb-3">
                        <!-- Quick Search -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <input type="text" 
                                           name="search" 
                                           class="form-control" 
                                           placeholder="Search by employee name, payroll ID..." 
                                           value="{{ request('search') }}">
                                    <button class="btn btn-outline-primary" type="submit">
                                        <i class="fas fa-search"></i> Search
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex gap-2">
                                    <a href="{{ route('payroll.index') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-refresh"></i> Clear All
                                    </a>
                                    <button class="btn btn-outline-info dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        <i class="fas fa-download"></i> Export
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="{{ route('payroll.export', array_merge(request()->query(), ['detailed' => 0])) }}">Export Summary</a></li>
                                        <li><a class="dropdown-item" href="{{ route('payroll.export', array_merge(request()->query(), ['detailed' => 1])) }}">Export Detailed</a></li>
                                        <li><a class="dropdown-item" href="{{ route('payroll.export') }}">Export All Records</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Advanced Filters (Collapsible) -->
                        <div class="collapse {{ request()->hasAny(['status', 'month_filter', 'salary_range', 'department']) ? 'show' : '' }}" id="filterCollapse">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select name="status" id="status" class="form-select">
                                        <option value="">All Statuses</option>
                                        <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="Processed" {{ request('status') == 'Processed' ? 'selected' : '' }}>Processed</option>
                                        <option value="Approved" {{ request('status') == 'Approved' ? 'selected' : '' }}>Approved</option>
                                        <option value="Paid" {{ request('status') == 'Paid' ? 'selected' : '' }}>Paid</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-3">
                                    <label for="month_filter" class="form-label">Payroll Month</label>
                                    <input type="month" 
                                           name="month_filter" 
                                           id="month_filter" 
                                           class="form-control" 
                                           value="{{ request('month_filter') }}"
                                           placeholder="e.g. 2024-06">
                                    <small class="form-text text-muted">
                                        Remarks: Generated for {{ request('month_filter') ? \Carbon\Carbon::parse(request('month_filter'))->format('F Y') : 'N/A' }}
                                    </small>
                                </div>

                                <div class="col-md-3">
                                    <label for="salary_range" class="form-label">Salary Range</label>
                                    <select name="salary_range" id="salary_range" class="form-select">
                                        <option value="">All Ranges</option>
                                        <option value="0-50000" {{ request('salary_range') == '0-50000' ? 'selected' : '' }}>₦0 - ₦50,000</option>
                                        <option value="50001-100000" {{ request('salary_range') == '50001-100000' ? 'selected' : '' }}>₦50,001 - ₦100,000</option>
                                        <option value="100001-200000" {{ request('salary_range') == '100001-200000' ? 'selected' : '' }}>₦100,001 - ₦200,000</option>
                                        <option value="200001-500000" {{ request('salary_range') == '200001-500000' ? 'selected' : '' }}>₦200,001 - ₦500,000</option>
                                        <option value="500001+" {{ request('salary_range') == '500001+' ? 'selected' : '' }}>₦500,001+</option>
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label for="sort_by" class="form-label">Sort By</label>
                                    <select name="sort_by" id="sort_by" class="form-select">
                                        <option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>Date Created</option>
                                        <option value="employee_name" {{ request('sort_by') == 'employee_name' ? 'selected' : '' }}>Employee Name</option>
                                        <option value="net_salary" {{ request('sort_by') == 'net_salary' ? 'selected' : '' }}>Net Salary</option>
                                        <option value="basic_salary" {{ request('sort_by') == 'basic_salary' ? 'selected' : '' }}>Basic Salary</option>
                                        <option value="payroll_month" {{ request('sort_by') == 'payroll_month' ? 'selected' : '' }}>Payroll Month</option>
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label for="sort_direction" class="form-label">Sort Direction</label>
                                    <select name="sort_direction" id="sort_direction" class="form-select">
                                        <option value="desc" {{ request('sort_direction') == 'desc' ? 'selected' : '' }}>Descending</option>
                                        <option value="asc" {{ request('sort_direction') == 'asc' ? 'selected' : '' }}>Ascending</option>
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label for="per_page" class="form-label">Records Per Page</label>
                                    <select name="per_page" id="per_page" class="form-select">
                                        <option value="10" {{ request('per_page') == '10' ? 'selected' : '' }}>10</option>
                                        <option value="20" {{ request('per_page', '20') == '20' ? 'selected' : '' }}>20</option>
                                        <option value="50" {{ request('per_page') == '50' ? 'selected' : '' }}>50</option>
                                        <option value="100" {{ request('per_page') == '100' ? 'selected' : '' }}>100</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Date Range</label>
                                    <div class="row g-2">
                                        <div class="col">
                                            <input type="date" 
                                                   name="date_from" 
                                                   class="form-control" 
                                                   placeholder="From" 
                                                   value="{{ request('date_from') }}">
                                        </div>
                                        <div class="col">
                                            <input type="date" 
                                                   name="date_to" 
                                                   class="form-control" 
                                                   placeholder="To" 
                                                   value="{{ request('date_to') }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter"></i> Apply Filters
                                </button>
                                <a href="{{ route('payroll.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times"></i> Clear Filters
                                </a>
                            </div>
                        </div>
                    </form>

                    <!-- Active Filters Display -->
                    @if(request()->hasAny(['search', 'status', 'month_filter', 'salary_range', 'date_from', 'date_to']))
                        <div class="active-filters">
                            <small class="text-muted">Active filters:</small>
                            <div class="d-flex flex-wrap gap-1 mt-1">
                                @if(request('search'))
                                    <span class="badge bg-info">Search: "{{ request('search') }}"</span>
                                @endif
                                @if(request('status'))
                                    <span class="badge bg-warning">Status: {{ request('status') }}</span>
                                @endif
                                @if(request('month_filter'))
                                    <span class="badge bg-success">Month: {{ request('month_filter') }}</span>
                                @endif
                                @if(request('salary_range'))
                                    <span class="badge bg-primary">Salary: {{ request('salary_range') }}</span>
                                @endif
                                @if(request('date_from') || request('date_to'))
                                    <span class="badge bg-secondary">
                                        Date: {{ request('date_from') ?: 'Any' }} to {{ request('date_to') ?: 'Any' }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Results Summary -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <p class="text-muted mb-0">
                        Showing {{ $payrolls->firstItem() ?? 0 }} to {{ $payrolls->lastItem() ?? 0 }} 
                        of {{ $payrolls->total() }} results
                        @if(request()->hasAny(['search', 'status', 'month_filter', 'salary_range', 'date_from', 'date_to']))
                            (filtered)
                        @endif
                    </p>
                </div>
                <div class="col-md-6 text-end">
                    @if($payrolls->count() > 0)
                        <small class="text-muted">
                            Total Net Salary: ₦{{ number_format($payrolls->sum('net_salary'), 2) }}
                        </small>
                    @endif
                </div>
            </div>

            <!-- Enhanced Payroll Table -->
            <div class="card border-primary shadow">
                <div class="card-header" style="background-color: skyblue; color: white;">
                    <strong>Payroll List with Detailed Adjustments</strong>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered align-items-center mb-0">
                            <thead class="table-primary">
                                <tr>
                                    <th rowspan="2" class="align-middle">Staff No</th>
                                    <th rowspan="2" class="align-middle">Employee</th>
                                    <th rowspan="2" class="align-middle">Expected Retirement</th>
                                    <th rowspan="2" class="align-middle">Basic Salary</th>
                                    <th colspan="2" class="text-center">Additions</th>
                                    <th colspan="2" class="text-center">Deductions</th>
                                    <th rowspan="2" class="align-middle">Net Salary</th>
                                    <th rowspan="2" class="align-middle">Status</th>
                                    <th rowspan="2" class="align-middle">Payment Date</th>
                                    <th rowspan="2" class="align-middle">Month</th>
                                    <th rowspan="2" class="align-middle">Actions</th>
                                </tr>
                                <tr>
                                    <th class="text-success">Details</th>
                                    <th class="text-success">Total</th>
                                    <th class="text-danger">Details</th>
                                    <th class="text-danger">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($payrolls as $payroll)
                                    <tr>
                                        <td>
                                            {{ $payroll->employee && $payroll->employee->reg_no ? $payroll->employee->reg_no : 'N/A' }}
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div>
                                                    @if($payroll->employee)
                                                        <strong>{{ $payroll->employee->first_name }} {{ $payroll->employee->surname }}</strong>
                                                        @if($payroll->employee->employee_id)
                                                            <br><small class="text-muted">ID: {{ $payroll->employee->employee_id }}</small>
                                                        @endif
                                                    @else
                                                        <strong class="text-danger">Employee Not Found</strong>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if(isset($payroll->employee->expected_retirement_date))
                                                @if(is_string($payroll->employee->expected_retirement_date))
                                                    {{ \Carbon\Carbon::parse($payroll->employee->expected_retirement_date)->format('M d, Y') }}
                                                @else
                                                    {{ $payroll->employee->expected_retirement_date->format('M d, Y') }}
                                                @endif
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td>₦{{ number_format($payroll->basic_salary, 2) }}</td>
                                        
                                        <!-- Additions Details -->
                                        <td class="text-success" style="min-width: 200px;">
                                            @php
                                                $payrollMonth = $payroll->payroll_month;
                                                $additions = collect();
                                                
                                                if ($payroll->employee_id) {
                                                    $additions = \App\Models\Addition::where('employee_id', $payroll->employee_id)
                                                        ->where(function($query) use ($payrollMonth) {
                                                            $query->where('start_date', '<=', $payrollMonth)
                                                                  ->where(function($q) use ($payrollMonth) {
                                                                      $q->whereNull('end_date')
                                                                        ->orWhere('end_date', '>=', $payrollMonth);
                                                                  });
                                                        })
                                                        ->get();
                                                }
                                            @endphp
                                            
                                            @if($additions->count() > 0)
                                                <div class="small">
                                                    @foreach($additions as $addition)
                                                        <div class="mb-1">
                                                            <span class="badge bg-success bg-opacity-25 text-success">
                                                                {{ $addition->addition_type }}: ₦{{ number_format($addition->amount, 2) }}
                                                            </span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <small class="text-muted">No additions</small>
                                            @endif
                                        </td>
                                        <td class="text-success">
                                            <strong>₦{{ number_format($additions->sum('amount'), 2) }}</strong>
                                        </td>
                                        
                                        <!-- Deductions Details -->
                                        <td class="text-danger" style="min-width: 200px;">
                                            @php
                                                $deductions = collect();
                                                
                                                if ($payroll->employee_id) {
                                                    $deductions = \App\Models\Deduction::where('employee_id', $payroll->employee_id)
                                                        ->where(function($query) use ($payrollMonth) {
                                                            $query->where('start_date', '<=', $payrollMonth)
                                                                  ->where(function($q) use ($payrollMonth) {
                                                                      $q->whereNull('end_date')
                                                                        ->orWhere('end_date', '>=', $payrollMonth);
                                                                  });
                                                        })
                                                        ->get();
                                                }
                                            @endphp
                                            
                                            @if($deductions->count() > 0)
                                                <div class="small">
                                                    @foreach($deductions as $deduction)
                                                        <div class="mb-1">
                                                            <span class="badge bg-danger bg-opacity-25 text-danger">
                                                                {{ $deduction->deduction_type }}: ₦{{ number_format($deduction->amount, 2) }}
                                                            </span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <small class="text-muted">No deductions</small>
                                            @endif
                                        </td>
                                        <td class="text-danger">
                                            <strong>₦{{ number_format($deductions->sum('amount'), 2) }}</strong>
                                        </td>
                                        
                                        <td>
                                            <strong class="text-primary">₦{{ number_format($payroll->net_salary, 2) }}</strong>
                                        </td>
                                        <td>
                                            <span class="badge 
                                                @if($payroll->status === 'Approved') bg-success
                                                @elseif($payroll->status === 'Processed') bg-primary
                                                @elseif($payroll->status === 'Paid') bg-success
                                                @else bg-warning text-dark @endif">
                                                {{ $payroll->status }}
                                            </span>
                                        </td>
                                        <td>{{ $payroll->payment_date ? $payroll->payment_date->format('M d, Y') : 'Pending' }}</td>
                                        <td>
                                            @if($payroll->payroll_month)
                                                @if(is_string($payroll->payroll_month))
                                                    {{ \Carbon\Carbon::parse($payroll->payroll_month)->format('M Y') }}
                                                @else
                                                    {{ $payroll->payroll_month->format('M Y') }}
                                                @endif
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" id="actionsDropdown{{ $payroll->payroll_id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                                    Actions
                                                </button>
                                                <ul class="dropdown-menu" aria-labelledby="actionsDropdown{{ $payroll->payroll_id }}">
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('payroll.show', $payroll->payroll_id) }}">
                                                            <i class="fas fa-eye"></i> View Details
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('payroll.payslip', $payroll->payroll_id) }}">
                                                            <i class="fas fa-download"></i> Download Pay Slip
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('payroll.deductions.show', $payroll->employee_id) }}">
                                                            <i class="fas fa-minus-circle"></i> Manage Deductions
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('payroll.additions.show', $payroll->employee_id) }}">
                                                            <i class="fas fa-plus-circle"></i> Manage Additions
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('payroll.adjustments.manage') }}">
                                                            <i class="fas fa-users-cog"></i> Manage All Adjustments
                                                        </a>
                                                    </li>
                                                    @php $tx = optional($payroll->transaction); @endphp
                                                    @if($tx && $tx->status === 'pending')
                                                        <li>
                                                            <form action="{{ route('payments.nabroll.initiate', $tx->transaction_id) }}" method="POST" class="dropdown-item m-0 p-0">
                                                                @csrf
                                                                <button type="submit" class="btn btn-link dropdown-item">
                                                                    <i class="fas fa-credit-card"></i> Proceed to NABRoll
                                                                </button>
                                                            </form>
                                                        </li>
                                                    @elseif($tx && $tx->payment_url)
                                                        <li>
                                                            <a class="dropdown-item" href="{{ $tx->payment_url }}" target="_blank">
                                                                <i class="fas fa-external-link-alt"></i> Pay Now
                                                            </a>
                                                        </li>
                                                    @elseif(!$tx)
                                                        <li>
                                                            <span class="dropdown-item text-muted">
                                                                <i class="fas fa-exclamation-circle"></i> No transaction
                                                            </span>
                                                        </li>
                                                    @endif
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="13" class="text-center text-muted py-4">
                                            <i class="fas fa-search fa-2x mb-3 text-muted"></i>
                                            <br>
                                            @if(request()->hasAny(['search', 'status', 'month_filter', 'salary_range', 'date_from', 'date_to']))
                                                No payroll records found matching your search criteria.
                                                <br>
                                                <a href="{{ route('payroll.index') }}" class="btn btn-outline-primary mt-2">
                                                    Clear filters and view all records
                                                </a>
                                            @else
                                                No payroll records found.
                                            @endif
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $payrolls->appends(request()->query())->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>

<!-- Auto-submit filters on change -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-submit form when certain filters change
    const autoSubmitFields = ['status', 'month_filter', 'salary_range', 'sort_by', 'sort_direction', 'per_page'];
    
    autoSubmitFields.forEach(fieldName => {
        const field = document.getElementById(fieldName);
        if (field) {
            field.addEventListener('change', function() {
                this.form.submit();
            });
        }
    });
});
</script>

<style>
/* Additional styling for better presentation */
.table th, .table td {
    vertical-align: middle;
}

.badge {
    font-size: 0.75em;
    margin-bottom: 2px;
    display: inline-block;
}

.table-responsive {
    max-height: 80vh;
    overflow-y: auto;
}

/* Sticky header for better UX */
.table thead th {
    position: sticky;
    top: 0;
    background-color: var(--bs-primary);
    z-index: 10;
}

/* Better spacing for adjustment details */
.small .badge {
    white-space: nowrap;
}
</style>
@endsection