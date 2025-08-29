@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0">Grade Levels Management</h5>
        </div>
        <div class="card-body">
            <!-- Search and Filter Form -->
            <form method="GET" action="{{ route('grade-levels.index') }}" class="mb-4">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" name="search" id="search" class="form-control" placeholder="Search by name or description..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <label for="grade_level" class="form-label">Grade Level</label>
                        <select name="grade_level" id="grade_level" class="form-select">
                            <option value="">All</option>
                            @foreach($grades as $level)
                                <option value="{{ $level }}" {{ request('grade_level') == $level ? 'selected' : '' }}>{{ $level }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="step_level" class="form-label">Step Level</label>
                        <select name="step_level" id="step_level" class="form-select">
                            <option value="">All</option>
                            @foreach($steps as $step)
                                <option value="{{ $step }}" {{ request('step_level') == $step ? 'selected' : '' }}>{{ $step }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="min_salary" class="form-label">Min Basic Salary</label>
                        <input type="number" name="min_salary" id="min_salary" class="form-control" value="{{ request('min_salary') }}" step="0.01" min="0">
                    </div>
                    <div class="col-md-2">
                        <label for="max_salary" class="form-label">Max Basic Salary</label>
                        <input type="number" name="max_salary" id="max_salary" class="form-control" value="{{ request('max_salary') }}" step="0.01" min="0">
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Apply Filters</button>
                        <a href="{{ route('grade-levels.index') }}" class="btn btn-secondary">Clear Filters</a>
                    </div>
                </div>
            </form>

            <div class="d-flex justify-content-between mb-3">
                <a href="{{ route('grade-levels.create') }}" class="btn btn-success">Add New Grade Level</a>
            </div>

            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Basic Salary</th>
                        <th>Grade Level</th>
                        <th>Step Level</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($gradeLevels as $level)
                        <tr>
                            <td>{{ $level->id }}</td>
                            <td>{{ $level->name }}</td>
                            <td>₦{{ number_format($level->basic_salary, 2) }}</td>
                            <td>{{ $level->grade_level }}</td>
                            <td>{{ $level->step_level }}</td>
                            <td>{{ $level->description ?? 'N/A' }}</td>
                            <td>
                                <a href="{{ route('grade-levels.edit', $level->id) }}" class="btn btn-sm btn-primary">Edit</a>
                                <form action="{{ route('grade-levels.destroy', $level->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this grade level?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">No grade levels found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="d-flex justify-content-center">
                {{ $gradeLevels->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>
@endsection