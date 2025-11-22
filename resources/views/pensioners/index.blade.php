@extends('layouts.app')

@section('title', 'Pensioners')

@section('content')
<div class="container-fluid py-4">
    <div class="card border-primary shadow">
        <div class="card-header" style="background-color: skyblue; color: white;">
            <h5 class="mb-0">Pensioners</h5>
        </div>
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Search and Filter Form -->
            <form method="GET" action="{{ route('pensioners.index') }}" class="row g-3 mb-4">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="Search by name or employee ID" value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="filter" class="form-select">
                        <option value="">All Status</option>
                        <option value="Active" {{ request('filter') == 'Active' ? 'selected' : '' }}>Active</option>
                        <option value="Deceased" {{ request('filter') == 'Deceased' ? 'selected' : '' }}>Deceased</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" name="start_date" class="form-control" placeholder="Start Date" value="{{ request('start_date') }}">
                </div>
                <div class="col-md-2">
                    <input type="date" name="end_date" class="form-control" placeholder="End Date" value="{{ request('end_date') }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
                <div class="col-md-1">
                    <a href="{{ route('pensioners.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
                </div>
            </form>
            <!-- End Search and Filter Form -->

            <div class="card border-primary mb-3 shadow">
                <div class="card-header" style="background-color: skyblue; color: white;">
                    <strong>Pensioners List</strong>
                </div>
                <div class="card-body p-0">
                    @if ($pensioners->isEmpty())
                        <p class="p-3">No pensioners found for the given search or filter criteria.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered align-items-center mb-0">
                                <thead class="table-primary">
                                    <tr>
                                        <th>#</th>
                                        <th>Employee</th>
                                        <th>Pension Start Date</th>
                                        <th>Pension Amount</th>
                                        <th>Status</th>
                                        @canany(['view_pensioners', 'manage_pensioners'])
                                        <th>Actions</th>
                                        @endcanany
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($pensioners as $index => $pensioner)
                                        <tr>
                                            <td>{{ ($pensioners->currentPage() - 1) * $pensioners->perPage() + $index + 1 }}</td>
                                            <td>
                                                <span class="fw-bold">
                                                    {{ $pensioner->employee ? $pensioner->employee->first_name . ' ' . $pensioner->employee->surname . ' (' . $pensioner->employee_id . ')' : 'N/A' }}
                                                </span>
                                            </td>
                                            <td>{{ $pensioner->pension_start_date ? $pensioner->pension_start_date->format('Y-m-d') : 'N/A' }}</td>
                                            <td>
                                                <span class="badge bg-info text-dark">
                                                    {{ number_format($pensioner->pension_amount, 2) }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge {{ $pensioner->status === 'Active' ? 'bg-success' : 'bg-secondary' }}">
                                                    {{ $pensioner->status }}
                                                </span>
                                            </td>
                                            @canany(['view_pensioners', 'manage_pensioners'])
                                            <td>
                                                <div class="d-flex flex-column flex-sm-row gap-1">
                                                    @can('view_pensioners')
                                                    <a href="{{ route('pensioners.show', $pensioner->pensioner_id) }}" class="btn btn-sm btn-primary mb-1 mb-sm-0 me-sm-1">View</a>
                                                    @endcan
                                                    @can('manage_pensioners')
                                                    <form action="{{ route('pensioners.updateStatus', $pensioner->pensioner_id) }}" method="POST" style="display:inline;">
                                                        @csrf
                                                        <select name="status" class="form-select form-select-sm d-inline w-auto" onchange="this.form.submit()">
                                                            <option value="Active" {{ $pensioner->status === 'Active' ? 'selected' : '' }}>Active</option>
                                                            <option value="Deceased" {{ $pensioner->status === 'Deceased' ? 'selected' : '' }}>Deceased</option>
                                                        </select>
                                                    </form>
                                                    @endcan
                                                </div>
                                            </td>
                                            @endcanany
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-center mt-3">
                            {{ $pensioners->appends(request()->query())->links('pagination::bootstrap-5') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection