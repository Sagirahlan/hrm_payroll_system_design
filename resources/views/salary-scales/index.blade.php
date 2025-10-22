@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Salary Scales Management</h5>
            @can('create_salary_scales')
            <a href="{{ route('salary-scales.create') }}" class="btn btn-light btn-sm">
                <i class="fas fa-plus me-1"></i> Add New Salary Scale
            </a>
            @endcan
        </div>
        <div class="card-body">
            <!-- Search and Filter Form -->
            <form method="GET" action="{{ route('salary-scales.index') }}" class="mb-4">
                <div class="row g-3 align-items-end">
                    <div class="col-md-6">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" name="search" id="search" class="form-control" placeholder="Search by acronym or full name..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <label for="sort_by" class="form-label">Sort By</label>
                        <select name="sort_by" id="sort_by" class="form-select">
                            <option value="acronym" {{ request('sort_by') == 'acronym' ? 'selected' : '' }}>Acronym</option>
                            <option value="full_name" {{ request('sort_by') == 'full_name' ? 'selected' : '' }}>Full Name</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="sort_order" class="form-label">Order</label>
                        <select name="sort_order" id="sort_order" class="form-select">
                            <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>Ascending</option>
                            <option value="desc" {{ request('sort_order') == 'desc' ? 'selected' : '' }}>Descending</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Apply Filters</button>
                        <a href="{{ route('salary-scales.index') }}" class="btn btn-secondary">Clear Filters</a>
                    </div>
                </div>
            </form>

            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Acronym</th>
                        <th>Full Name</th>
                        <th>Sector Coverage</th>
                        <th>Max Retirement Age</th>
                        <th>Max Years of Service</th>
                        <th>Grade Levels</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($salaryScales as $scale)
                        <tr>
                            <td>{{ $scale->acronym }}</td>
                            <td>{{ $scale->full_name }}</td>
                            <td>{{ $scale->sector_coverage }}</td>
                            <td>{{ $scale->max_retirement_age }}</td>
                            <td>{{ $scale->max_years_of_service }}</td>
                            <td>{{ $scale->gradeLevels->count() }}</td>
                            
                            <td>
                                @can('view_grade_levels')
                                <a href="{{ route('salary-scales.grade-levels', $scale->id) }}" class="btn btn-sm btn-info">View Grade Levels</a>
                                @endcan
                                @can('edit_salary_scales')
                                <a href="{{ route('salary-scales.edit', $scale->id) }}" class="btn btn-sm btn-primary">Edit</a>
                                <form action="{{ route('salary-scales.destroy', $scale->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this salary scale?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </td>
                            @endcan
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">No salary scales found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="d-flex justify-content-center">
                {{ $salaryScales->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>
@endsection