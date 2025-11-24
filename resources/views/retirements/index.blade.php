@extends('layouts.app')

@section('title', isset($retiredEmployees) ? 'Retired Employees' : 'Retirement Records')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        @can('create_retirements')
            <a href="{{ route('retirements.create') }}" class="btn btn-primary btn-lg rounded-3 fw-bold shadow">
                <i class="bi bi-plus-circle me-2"></i> Confirm Retirement
            </a>
        @endcan
        <div>
            @if(isset($retiredEmployees))
                <a href="{{ route('retirements.index') }}" class="btn btn-info">Approaching Retirement</a>
            @else
                <a href="{{ route('retirements.retired') }}" class="btn btn-info">View Retired Employees</a>
            @endif
        </div>
    </div>
    <div class="card shadow">
        <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">{{ isset($retiredEmployees) ? 'Retired Employees' : 'Employees Retiring Within 6 Months' }}</h5>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            
            <!-- Search and Filter Form -->
            <form method="GET" class="mb-3">
                <div class="row g-2">
                    <div class="col-md-5">
                        <input type="text" name="search" class="form-control" placeholder="Search employee ID or name" value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="Retired" {{ request('status') == 'Retired' ? 'selected' : '' }}>Retired</option>
                            <option value="Active" {{ request('status') == 'Active' ? 'selected' : '' }}>Active</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-outline-secondary w-100">Search</button>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('retirements.index') }}" class="btn btn-outline-secondary w-100">Clear</a>
                    </div>
                </div>
            </form>
            
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Employee ID</th>
                            <th>Name</th>
                            <th>{{ isset($retiredEmployees) ? 'Retirement Date' : 'Calculated Retirement Date' }}</th>
                            <th>Expected Date of Retirement</th>
                            <th>Years of Service</th>
                            <th>Age</th>
                            <th>Retirement Reason</th>
                            <th>Status</th>
                            @if(isset($retiredEmployees))
                                <th>Gratuity Amount</th>
                                <th>Actions</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $items = isset($retiredEmployees) ? $retiredEmployees : $retirements;
                        @endphp
                        @forelse($items as $item)
                            @php
                                $employee = isset($retiredEmployees) ? $item->employee : $item;
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $employee->employee_id }}</td>
                                <td>{{ $employee->first_name }} {{ $employee->surname }}</td>
                                <td>
                                    @if(isset($retiredEmployees))
                                        {{ \Carbon\Carbon::parse($item->retirement_date)->format('Y-m-d') }}
                                    @else
                                        {{ $employee->calculated_retirement_date ? $employee->calculated_retirement_date->format('Y-m-d') : 'N/A' }}
                                    @endif
                                </td>
                                <td>
                                    @if(!isset($retiredEmployees))
                                        {{ $employee->expected_retirement_date }}
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>
                                    @if(!isset($retiredEmployees))
                                        {{ $employee->years_of_service }} years
                                    @else
                                        {{ \Carbon\Carbon::parse($employee->date_of_first_appointment)->diffInYears(\Carbon\Carbon::now()) }} years
                                    @endif
                                </td>
                                <td>
                                    @if(!isset($retiredEmployees))
                                        {{ $employee->age }}
                                    @else
                                        {{ \Carbon\Carbon::parse($employee->date_of_birth)->age }}
                                    @endif
                                </td>
                                <td>
                                    @if(!isset($retiredEmployees))
                                        {{ $employee->retirement_reason }}
                                    @else
                                        {{ $item->status ?? 'N/A' }}
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $employee->status == 'Retired' ? 'success' : 'warning' }}">
                                        {{ $employee->status }}
                                    </span>
                                </td>
                                @if(isset($retiredEmployees))
                                    <td>₦{{ number_format($item->gratuity_amount, 2) }}</td>
                                    <td>
                                        <a href="{{ route('employees.show', $employee->employee_id) }}" class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ isset($retiredEmployees) ? 9 : 10 }}" class="text-center">No records found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-center">
                {{ $items->appends(request()->query())->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>
@endsection