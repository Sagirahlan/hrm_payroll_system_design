@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0">Edit Step for {{ $gradeLevel->name }}</h5>
            <small>{{ $salaryScale->acronym }} - {{ $salaryScale->full_name }}</small>
        </div>
        <div class="card-body">
            <form action="{{ route('salary-scales.grade-levels.steps.update', [$salaryScale->id, $gradeLevel->id, $step->id]) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">Step Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $step->name) }}" required maxlength="50" placeholder="e.g., Step 1, Step 2, etc.">
                        @error('name')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="basic_salary" class="form-label">Basic Salary <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">₦</span>
                            <input type="number" name="basic_salary" id="basic_salary" step="0.01" class="form-control" value="{{ old('basic_salary', $step->basic_salary) }}" required min="0">
                        </div>
                        @error('basic_salary')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="d-flex justify-content-between">
                    <a href="{{ route('salary-scales.grade-levels.edit', [$salaryScale->id, $gradeLevel->id]) }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Grade Level
                    </a>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save me-1"></i> Update Step
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection