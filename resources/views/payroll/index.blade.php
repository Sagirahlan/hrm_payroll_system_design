@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="card border-primary shadow">
        <div class="card-header bg-primary text-white">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-file-invoice-dollar me-2"></i>Payroll Records
                </h5>
                <a href="{{ route('payroll.adjustments.manage') }}" class="btn btn-light">
                    <i class="fas fa-users-cog me-1"></i> Manage Adjustments
                </a>
            </div>
        </div>
        <div class="card-body">
            
            <!-- Payroll Generation Form -->
            <div class="card border-primary mb-4 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <strong><i class="fas fa-calculator me-2"></i>Generate Payroll</strong>
                </div>
                <div class="card-body">
                    <form action="{{ route('payroll.generate') }}" method="POST" class="row g-3 align-items-end">
                        @csrf
                        <div class="col-md-4">
                            <label for="month" class="form-label">Select Month</label>
                            <input type="month" name="month" id="month" value="{{ now()->format('Y-m') }}"
                                   class="form-control">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-cogs me-1"></i>Generate
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Search and Filter Section -->
            <div class="card border-info mb-4 shadow-sm">
                <div class="card-header bg-info text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <strong class="mb-0">
                            <i class="fas fa-search me-2"></i>Search & Filter
                        </strong>
                        <button class="btn btn-sm btn-outline-light" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse" aria-expanded="false">
                            <i class="fas fa-filter me-1"></i> Toggle Filters
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('payroll.index') }}" class="mb-3">
                        <!-- Quick Search -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-search"></i>
                                    </span>
                                    <input type="text" 
                                           name="search" 
                                           class="form-control" 
                                           placeholder="Search by employee name, payroll ID..." 
                                           value="{{ request('search') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex flex-wrap gap-2">
                                    <a href="{{ route('payroll.index') }}" class="btn btn-outline-secondary btn-sm">
                                        <i class="fas fa-refresh me-1"></i> Clear All
                                    </a>
                                    <div class="dropdown">
                                        <button class="btn btn-outline-info btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            <i class="fas fa-download me-1"></i> Export
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="{{ route('payroll.export', array_merge(request()->query(), ['detailed' => 0])) }}"><i class="fas fa-file-alt me-2"></i>Export Summary</a></li>
                                            <li><a class="dropdown-item" href="{{ route('payroll.export', array_merge(request()->query(), ['detailed' => 1])) }}"><i class="fas fa-file-contract me-2"></i>Export Detailed</a></li>
                                            <li><a class="dropdown-item" href="{{ route('payroll.export') }}"><i class="fas fa-file-export me-2"></i>Export All Records</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Advanced Filters (Collapsible) -->
                        <div class="collapse {{ request()->hasAny(['status', 'month_filter', 'salary_range', 'department']) ? 'show' : '' }}" id="filterCollapse">
                            <hr>
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
                                           value="{{ request('month_filter') }}">
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
                                <button type="submit" class="btn btn-primary btn-sm me-2">
                                    <i class="fas fa-filter me-1"></i> Apply Filters
                                </button>
                                <a href="{{ route('payroll.index') }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-times me-1"></i> Clear Filters
                                </a>
                            </div>
                        </div>
                    </form>

                    <!-- Active Filters Display -->
                    @if(request()->hasAny(['search', 'status', 'month_filter', 'salary_range', 'date_from', 'date_to']))
                        <div class="mt-3">
                            <small class="text-muted">Active filters:</small>
                            <div class="d-flex flex-wrap gap-2 mt-1">
                                @if(request('search'))
                                    <span class="badge bg-info">
                                        <i class="fas fa-search me-1"></i>Search: "{{ request('search') }}"
                                    </span>
                                @endif
                                @if(request('status'))
                                    <span class="badge bg-warning text-dark">
                                        <i class="fas fa-circle me-1"></i>Status: {{ request('status') }}
                                    </span>
                                @endif
                                @if(request('month_filter'))
                                    <span class="badge bg-success">
                                        <i class="fas fa-calendar me-1"></i>Month: {{ request('month_filter') }}
                                    </span>
                                @endif
                                @if(request('salary_range'))
                                    <span class="badge bg-primary">
                                        <i class="fas fa-money-bill-wave me-1"></i>Salary: {{ request('salary_range') }}
                                    </span>
                                @endif
                                @if(request('date_from') || request('date_to'))
                                    <span class="badge bg-secondary">
                                        <i class="fas fa-calendar-alt me-1"></i>Date: {{ request('date_from') ?: 'Any' }} to {{ request('date_to') ?: 'Any' }}
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
                        <i class="fas fa-list me-1"></i>
                        Showing {{ $payrolls->firstItem() ?? 0 }} to {{ $payrolls->lastItem() ?? 0 }} 
                        of {{ $payrolls->total() }} results
                        @if(request()->hasAny(['search', 'status', 'month_filter', 'salary_range', 'date_from', 'date_to']))
                            <span class="badge bg-info ms-1">filtered</span>
                        @endif
                    </p>
                </div>
                <div class="col-md-6 text-end">
                    @if($payrolls->count() > 0)
                        <p class="mb-0">
                            <i class="fas fa-calculator text-success me-1"></i>
                            <strong>Total Net Salary: ₦{{ number_format($payrolls->sum('net_salary'), 2) }}</strong>
                        </p>
                    @endif
                </div>
            </div>

            <!-- Payroll Table Section -->
            <section class="mb-4">
                <div class="card border-primary shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">
                                <i class="fas fa-table me-2"></i>Payroll Records
                            </h6>
                            <div class="d-flex gap-2">
                                <button class="btn btn-sm btn-warning" id="bulk-send-review">
                                    <i class="fas fa-paper-plane me-1"></i> Send for Review
                                </button>
                                <button class="btn btn-sm btn-info" id="bulk-mark-reviewed">
                                    <i class="fas fa-check-circle me-1"></i> Mark as Reviewed
                                </button>
                                <button class="btn btn-sm btn-info" id="bulk-send-approval">
                                    <i class="fas fa-paper-plane me-1"></i> Send for Approval
                                </button>
                                <button class="btn btn-sm btn-success" id="bulk-final-approve">
                                    <i class="fas fa-thumbs-up me-1"></i> Final Approve
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-middle mb-0">
                                <thead class="table-dark">
                                    <tr>
                                        <th class="align-middle text-center">
                                            <input type="checkbox" id="select-all-payrolls" class="form-check-input">
                                        </th>
                                        <th class="align-middle">Staff No</th>
                                        <th class="align-middle">Employee</th>
                                        <th class="align-middle">Expected Retirement</th>
                                        <th class="align-middle text-end">Basic Salary</th>
                                        <th class="text-center">Additions</th>
                                        <th class="text-center">Deductions</th>
                                        <th class="align-middle text-end">Net Salary</th>
                                        <th class="align-middle">Status</th>
                                        <th class="align-middle">Payment Date</th>
                                        <th class="align-middle">Month</th>
                                        <th class="align-middle">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($payrolls as $payroll)
                                        <tr>
                                            <td class="text-center">
                                                <input type="checkbox" name="payroll_ids[]" value="{{ $payroll->payroll_id }}" class="payroll-checkbox form-check-input">
                                            </td>
                                            <td>
                                                {{ $payroll->employee && $payroll->employee->reg_no ? $payroll->employee->reg_no : 'N/A' }}
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div>
                                                        @if($payroll->employee)
                                                            <strong>{{ $payroll->employee->first_name }} {{ $payroll->employee->surname }}</strong>
                                                            <br>
                                                            @if($payroll->employee->employee_id)
                                                                <small class="text-muted">ID: {{ $payroll->employee->employee_id }}</small>
                                                            @endif
                                                            <br>
                                                            @if($payroll->employee->gradeLevel)
                                                                <small class="text-muted">GL: {{ $payroll->employee->gradeLevel->name }}@if($payroll->employee->step) - Step {{ $payroll->employee->step->name }}@endif</small>
                                                            @endif
                                                            <br>
                                                            @if($payroll->employee->status === 'Suspended')
                                                                <span class="badge bg-warning text-dark">Suspended</span>
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
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                <strong>₦{{ number_format($payroll->basic_salary, 2) }}</strong>
                                            </td>
                                            
                                            <!-- Additions Details -->
                                            <td class="text-center">
                                                <div class="d-flex flex-column">
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
                                                    <div class="fw-bold text-success">₦{{ number_format($additions->sum('amount'), 2) }}</div>
                                                    <div class="small">
                                                        @if($additions->count() > 0)
                                                            <div class="dropdown">
                                                                <a class="btn btn-sm btn-outline-success dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                                                    {{ $additions->count() }} items
                                                                </a>
                                                                <div class="dropdown-menu p-2" style="max-width: 300px;">
                                                                    @foreach($additions as $addition)
                                                                        <div class="dropdown-item-text">
                                                                            <span class="badge bg-success text-white">{{ $addition->addition_type }}: ₦{{ number_format($addition->amount, 2) }}</span>
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        @else
                                                            <span class="text-muted">No additions</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            
                                            <!-- Deductions Details -->
                                            <td class="text-center">
                                                <div class="d-flex flex-column">
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
                                                    <div class="fw-bold text-danger">₦{{ number_format($deductions->sum('amount'), 2) }}</div>
                                                    <div class="small">
                                                        @if($deductions->count() > 0)
                                                            <div class="dropdown">
                                                                <a class="btn btn-sm btn-outline-danger dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                                                    {{ $deductions->count() }} items
                                                                </a>
                                                                <div class="dropdown-menu p-2" style="max-width: 300px;">
                                                                    @foreach($deductions as $deduction)
                                                                        <div class="dropdown-item-text">
                                                                            <span class="badge bg-danger text-white">{{ $deduction->deduction_type }}: ₦{{ number_format($deduction->amount, 2) }}</span>
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        @else
                                                            <span class="text-muted">No deductions</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            
                                            <td class="text-end">
                                                <strong class="text-primary">₦{{ number_format($payroll->net_salary, 2) }}</strong>
                                            </td>
                                            <td>
                                                <span class="badge 
                                                    @if($payroll->status === 'Approved') bg-success
                                                    @elseif($payroll->status === 'Paid') bg-success
                                                    @elseif($payroll->status === 'Pending Final Approval') bg-info
                                                    @elseif($payroll->status === 'Processed') bg-primary
                                                    @elseif($payroll->status === 'Under Review') bg-warning text-dark
                                                    @elseif($payroll->status === 'Reviewed') bg-info
                                                    @elseif($payroll->status === 'Pending Review') bg-secondary
                                                    @elseif($payroll->status === 'Rejected') bg-danger
                                                    @else bg-secondary @endif">
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
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" id="actionsDropdown{{ $payroll->payroll_id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="fas fa-cog me-1"></i>Actions
                                                    </button>
                                                    <ul class="dropdown-menu" aria-labelledby="actionsDropdown{{ $payroll->payroll_id }}">
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('payroll.show', $payroll->payroll_id) }}">
                                                                <i class="fas fa-eye me-2 text-info"></i> View Details
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('payroll.payslip', $payroll->payroll_id) }}">
                                                                <i class="fas fa-download me-2 text-success"></i> Download Pay Slip
                                                            </a>
                                                        </li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('payroll.deductions.show', $payroll->employee_id) }}">
                                                                <i class="fas fa-minus-circle me-2 text-danger"></i> Manage Deductions
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('payroll.additions.show', $payroll->employee_id) }}">
                                                                <i class="fas fa-plus-circle me-2 text-success"></i> Manage Additions
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('payroll.adjustments.manage') }}">
                                                                <i class="fas fa-users-cog me-2 text-primary"></i> Manage All Adjustments
                                                            </a>
                                                        </li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        @if($payroll->status === 'Pending Review')
                                                            <li>
                                                                <a class="dropdown-item" href="{{ route('payroll.send-for-review', $payroll->payroll_id) }}" 
                                                                   onclick="event.preventDefault(); document.getElementById('send-review-form-{{ $payroll->payroll_id }}').submit();">
                                                                    <i class="fas fa-paper-plane me-2 text-warning"></i> Send for Review
                                                                </a>
                                                            </li>
                                                        @elseif($payroll->status === 'Under Review')
                                                            <li>
                                                                <a class="dropdown-item" href="{{ route('payroll.mark-as-reviewed', $payroll->payroll_id) }}" 
                                                                   onclick="event.preventDefault(); document.getElementById('mark-reviewed-form-{{ $payroll->payroll_id }}').submit();">
                                                                    <i class="fas fa-check-circle me-2 text-info"></i> Mark as Reviewed
                                                                </a>
                                                            </li>
                                                        @elseif($payroll->status === 'Reviewed')
                                                            <li>
                                                                <a class="dropdown-item" href="{{ route('payroll.send-for-approval', $payroll->payroll_id) }}" 
                                                                   onclick="event.preventDefault(); document.getElementById('send-approval-form-{{ $payroll->payroll_id }}').submit();">
                                                                    <i class="fas fa-paper-plane me-2 text-info"></i> Send for Final Approval
                                                                </a>
                                                            </li>
                                                        @elseif($payroll->status === 'Pending Final Approval')
                                                            <li>
                                                                <a class="dropdown-item" href="{{ route('payroll.final-approve', $payroll->payroll_id) }}" 
                                                                   onclick="event.preventDefault(); document.getElementById('final-approve-form-{{ $payroll->payroll_id }}').submit();">
                                                                    <i class="fas fa-thumbs-up me-2 text-success"></i> Final Approve
                                                                </a>
                                                            </li>
                                                        @endif
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="12" class="text-center py-5">
                                                <div class="d-flex flex-column align-items-center justify-content-center">
                                                    <i class="fas fa-file-invoice-dollar fa-3x text-muted mb-3"></i>
                                                    <h5 class="text-muted">No payroll records found</h5>
                                                    <p class="text-muted mb-3">
                                                        @if(request()->hasAny(['search', 'status', 'month_filter', 'salary_range', 'date_from', 'date_to']))
                                                            No payroll records match your search criteria.
                                                        @else
                                                            No payroll records have been created yet.
                                                        @endif
                                                    </p>
                                                    @if(request()->hasAny(['search', 'status', 'month_filter', 'salary_range', 'date_from', 'date_to']))
                                                        <a href="{{ route('payroll.index') }}" class="btn btn-outline-primary">
                                                            <i class="fas fa-undo me-1"></i> Clear filters and view all records
                                                        </a>
                                                    @else
                                                        <a href="{{ route('payroll.generate') }}" class="btn btn-primary">
                                                            <i class="fas fa-calculator me-1"></i> Generate Payroll
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="card-footer bg-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-muted">
                                Showing {{ $payrolls->firstItem() ?? 0 }} to {{ $payrolls->lastItem() ?? 0 }} 
                                of {{ $payrolls->total() }} records
                            </div>
                            <div>
                                {{ $payrolls->appends(request()->query())->links('pagination::bootstrap-5') }}
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Bulk Operations Section -->
            <section>
                <div class="card border-info shadow-sm">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-layer-group me-2"></i>Bulk Operations
                        </h6>
                    </div>
                    <div class="card-body">
                        <form id="bulk-actions-form" method="POST" action="">
                            @csrf
                            <input type="hidden" name="select_all_pages" value="1">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-3">
                                    <label for="bulk_action" class="form-label">Select Action</label>
                                    <select name="bulk_action" id="bulk_action" class="form-select">
                                        <option value="">Choose an action...</option>
                                        <option value="send-for-review">Send All for Review</option>
                                        <option value="mark-as-reviewed">Mark All as Reviewed</option>
                                        <option value="send-for-approval">Send All for Final Approval</option>
                                        <option value="final-approve">Final Approve All</option>
                                        <option value="bulk-update-status">Update All Status</option>
                                    </select>
                                </div>
                                <div id="status-select-container" class="col-md-3" style="display: none;">
                                    <label for="new_status" class="form-label">New Status</label>
                                    <select name="new_status" id="new_status" class="form-select">
                                        <option value="Pending">Pending</option>
                                        <option value="Processed">Processed</option>
                                        <option value="Under Review">Under Review</option>
                                        <option value="Reviewed">Reviewed</option>
                                        <option value="Pending Final Approval">Pending Final Approval</option>
                                        <option value="Approved">Approved</option>
                                        <option value="Paid">Paid</option>
                                        <option value="Rejected">Rejected</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <button type="button" class="btn btn-primary w-100" id="execute-bulk-action">
                                        <i class="fas fa-bolt me-1"></i> Execute Action
                                    </button>
                                </div>
                            </div>
                            <div class="mt-3 p-3 bg-light rounded">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Note: This will apply the selected action to ALL payroll records that match the current filters ({{ $payrolls->total() }} total records).
                                </small>
                            </div>
                        </form>
                    </div>
                </div>
            </section>

            <!-- Hidden Bulk Action Forms -->
            <form id="bulk-send-review-form" action="{{ route('payroll.bulk_send_for_review') }}" method="POST" style="display: none;">
                @csrf
            </form>
            <form id="bulk-mark-reviewed-form" action="{{ route('payroll.bulk_mark_as_reviewed') }}" method="POST" style="display: none;">
                @csrf
            </form>
            <form id="bulk-send-approval-form" action="{{ route('payroll.bulk_send_for_approval') }}" method="POST" style="display: none;">
                @csrf
            </form>
            <form id="bulk-final-approve-form" action="{{ route('payroll.bulk_final_approve') }}" method="POST" style="display: none;">
                @csrf
            </form>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $payrolls->appends(request()->query())->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>

<!-- JavaScript for Payroll Page -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // Initialize all components
    initializeAutoSubmitFilters();
    initializeBulkActions();
    initializeBulkButtons();
    
    /* Auto-submit filters when changed */
    function initializeAutoSubmitFilters() {
        const autoSubmitFields = ['status', 'month_filter', 'salary_range', 'sort_by', 'sort_direction', 'per_page'];
        
        autoSubmitFields.forEach(fieldName => {
            const field = document.getElementById(fieldName);
            if (field) {
                field.addEventListener('change', function() {
                    this.form.submit();
                });
            }
        });
    }
    
    /* Initialize bulk action controls */
    function initializeBulkActions() {
        // Handle status select visibility based on selected action
        const bulkActionSelect = document.getElementById('bulk_action');
        const statusSelectContainer = document.getElementById('status-select-container');
        
        if (bulkActionSelect && statusSelectContainer) {
            bulkActionSelect.addEventListener('change', function() {
                statusSelectContainer.style.display = (this.value === 'bulk-update-status') ? 'block' : 'none';
            });
        }
        
        // Handle bulk action execution for all records
        const executeBulkActionBtn = document.getElementById('execute-bulk-action');
        if (executeBulkActionBtn && bulkActionSelect) {
            executeBulkActionBtn.addEventListener('click', function() {
                executeBulkAction(bulkActionSelect.value);
            });
        }
        
        // Select all payroll checkboxes
        const selectAllCheckboxes = document.getElementById('select-all-payrolls');
        if (selectAllCheckboxes) {
            selectAllCheckboxes.addEventListener('change', function() {
                const checkboxes = document.querySelectorAll('input[name="payroll_ids[]"]');
                checkboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
            });
        }
    }
    
    // Execute the selected bulk action
    function executeBulkAction(action) {
        if (!action) {
            alert('Please select an action.');
            return;
        }
        
        // Confirm action before proceeding
        const recordCount = {{ $payrolls->total() }};
        const confirmationMessage = getConfirmationMessage(action, recordCount);
        
        if (!confirm(confirmationMessage)) {
            return;
        }
        
        // Get action URL based on selected action
        const actionUrl = getActionUrl(action);
        if (!actionUrl) {
            alert('Invalid action selected.');
            return;
        }
        
        // Set the form action and submit
        const form = document.getElementById('bulk-actions-form');
        if (form) {
            form.action = actionUrl;
            form.submit();
        }
    }
    
    // Get confirmation message based on action
    function getConfirmationMessage(action, recordCount) {
        switch (action) {
            case 'send-for-review':
                return `Are you sure you want to send ALL matching records for review? This will affect ${recordCount} records.`;
            case 'mark-as-reviewed':
                return `Are you sure you want to mark ALL matching records as reviewed? This will affect ${recordCount} records.`;
            case 'send-for-approval':
                return `Are you sure you want to send ALL matching records for final approval? This will affect ${recordCount} records.`;
            case 'final-approve':
                return `Are you sure you want to final approve ALL matching records? This will affect ${recordCount} records.`;
            case 'bulk-update-status':
                return `Are you sure you want to update the status of ALL matching records? This will affect ${recordCount} records.`;
            default:
                return 'Invalid action selected.';
        }
    }
    
    // Get action URL based on the selected action
    function getActionUrl(action) {
        switch (action) {
            case 'send-for-review':
                return '{{ route("payroll.bulk_send_for_review") }}';
            case 'mark-as-reviewed':
                return '{{ route("payroll.bulk_mark_as_reviewed") }}';
            case 'send-for-approval':
                return '{{ route("payroll.bulk_send_for_approval") }}';
            case 'final-approve':
                return '{{ route("payroll.bulk_final_approve") }}';
            case 'bulk-update-status':
                return '{{ route("payroll.bulk_update_status") }}';
            default:
                return null;
        }
    }
    
    // Initialize bulk operation buttons
    function initializeBulkButtons() {
        const bulkSendReviewBtn = document.getElementById('bulk-send-review');
        const bulkMarkReviewedBtn = document.getElementById('bulk-mark-reviewed');
        const bulkSendApprovalBtn = document.getElementById('bulk-send-approval');
        const bulkFinalApproveBtn = document.getElementById('bulk-final-approve');
        const bulkActionSelect = document.getElementById('bulk_action');
        const executeBulkActionBtn = document.getElementById('execute-bulk-action');
        
        if (bulkSendReviewBtn) {
            bulkSendReviewBtn.addEventListener('click', function() {
                if (bulkActionSelect && executeBulkActionBtn) {
                    bulkActionSelect.value = 'send-for-review';
                    executeBulkActionBtn.click();
                }
            });
        }
        
        if (bulkMarkReviewedBtn) {
            bulkMarkReviewedBtn.addEventListener('click', function() {
                if (bulkActionSelect && executeBulkActionBtn) {
                    bulkActionSelect.value = 'mark-as-reviewed';
                    executeBulkActionBtn.click();
                }
            });
        }
        
        if (bulkSendApprovalBtn) {
            bulkSendApprovalBtn.addEventListener('click', function() {
                if (bulkActionSelect && executeBulkActionBtn) {
                    bulkActionSelect.value = 'send-for-approval';
                    executeBulkActionBtn.click();
                }
            });
        }
        
        if (bulkFinalApproveBtn) {
            bulkFinalApproveBtn.addEventListener('click', function() {
                if (bulkActionSelect && executeBulkActionBtn) {
                    bulkActionSelect.value = 'final-approve';
                    executeBulkActionBtn.click();
                }
            });
        }
    }
    
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

/* Card header styling */
.card-header {
    border-radius: 0.375rem 0.375rem 0 0;
}

/* Improved styling for filter section */
.card.border-info .card-header {
    background-color: #17a2b8 !important;
}

/* Improved styling for primary card */
.card.border-primary .card-header {
    background-color: #0d6efd !important;
}

/* Button styling */
.btn {
    border-radius: 0.375rem;
}

/* Input styling */
.form-control:focus,
.form-select:focus {
    border-color: #86b7fe;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

/* Improved dropdown styling */
.dropdown-item {
    padding: 0.5rem 1rem;
}

.dropdown-item i {
    width: 1.5em;
}

/* Consistent card spacing */
.card {
    margin-bottom: 1.5rem;
}

/* Improved spacing in forms */
.form-label {
    font-weight: 500;
}

/* Consistent button sizing */
.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

/* Better styling for table actions */
.table .dropdown-toggle {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

/* Improved pagination styling */
.page-item.active .page-link {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

/* Consistent border styling */
.border-primary, .border-info {
    border-width: 2px;
}

/* Background color for form sections */
.active-filters {
    background-color: rgba(0, 0, 0, 0.03);
    padding: 0.75rem;
    border-radius: 0.375rem;
}
</style>
@endsection