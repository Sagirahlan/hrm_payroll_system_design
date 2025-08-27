<!-- resources/views/retirements/index.blade.php -->
@extends('layouts.app')

@section('title', 'Retirement Records')

@section('content')
<a href="{{ route('retirements.create') }}" class="btn btn-primary btn-lg rounded-3 fw-bold shadow">
    <i class="bi bi-plus-circle me-2"></i> Confirm Retirement
</a>
<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Employees Retiring Within 3 Months</h5>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            <form method="GET" class="mb-3">
                <div class="row g-2">
                    <div class="col-md-6">
                        <input type="text" name="search" class="form-control" placeholder="Search employee ID or name" value="{{ request('search') }}">
                    </div>
                    <div class="col-md-4">
                        <select name="status" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="Retired" {{ request('status') == 'Retired' ? 'selected' : '' }}>Retired</option>
                            <option value="Active" {{ request('status') == 'Active' ? 'selected' : '' }}>Active</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-outline-secondary w-100">Search</button>
                    </div>
                </div>
            </form>
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Employee ID</th>
                        <th>Name</th>
                        <th>Expected Retirement Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $cutoff = \Carbon\Carbon::now()->addMonths(3);
                        $today = \Carbon\Carbon::now()->startOfDay();
                    @endphp
                    @forelse($retirements as $index => $employee)
                        @php
                            $expected = \Carbon\Carbon::parse($employee->expected_retirement_date)->startOfDay();
                            if ($expected->lessThanOrEqualTo($today) && $employee->status !== 'Retired') {
                                $employee->status = 'Retired';
                                $employee->save();
                            }
                        @endphp
                        @if($expected >= $today && $expected <= $cutoff)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $employee->employee_id }}</td>
                                <td>{{ $employee->first_name }} {{ $employee->surname }}</td>
                                <td>{{ $expected->format('Y-m-d') }}</td>
                                <td>
                                    <span class="badge bg-{{ $employee->status == 'Retired' ? 'success' : 'warning' }}">
                                        {{ $employee->status }}
                                    </span>
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr><td colspan="5" class="text-center">No employees retiring within the next 3 months.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="d-flex justify-content-center">
                {{ $retirements->appends(request()->query())->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>
@endsection
