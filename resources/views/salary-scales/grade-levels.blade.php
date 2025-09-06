@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Grade Levels for {{ $salaryScale->acronym }} - {{ $salaryScale->full_name }}</h5>
            <a href="{{ route('salary-scales.index') }}" class="btn btn-light btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Back to Salary Scales
            </a>
        </div>
        <div class="card-body">
            <div class="d-flex justify-content-between mb-3">
                <a href="{{ route('salary-scales.grade-levels.create', $salaryScale->id) }}" class="btn btn-success">
                    <i class="fas fa-plus me-1"></i> Add New Grade Level
                </a>
            </div>

            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            @if($gradeLevels->count() > 0)
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Basic Salary</th>
                            <th>Grade Level</th>
                            <th>Step Level</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($gradeLevels as $level)
                            <tr>
                                <td>{{ $level->name }}</td>
                                <td>₦{{ number_format($level->basic_salary, 2) }}</td>
                                <td>{{ $level->grade_level }}</td>
                                <td>{{ $level->step_level }}</td>
                                <td>{{ $level->description ?? 'N/A' }}</td>
                                <td>
                                    <a href="{{ route('salary-scales.grade-levels.edit', [$salaryScale->id, $level->id]) }}" class="btn btn-sm btn-primary">Edit</a>
                                    <form action="{{ route('salary-scales.grade-levels.destroy', [$salaryScale->id, $level->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this grade level?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="alert alert-info">
                    No grade levels found for this salary scale. 
                    <a href="{{ route('salary-scales.grade-levels.create', $salaryScale->id) }}">Add a new grade level</a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection