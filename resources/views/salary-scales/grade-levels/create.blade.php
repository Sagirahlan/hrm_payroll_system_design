@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">Add New Grade Level for {{ $salaryScale->acronym }} - {{ $salaryScale->full_name }}</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('salary-scales.grade-levels.store', $salaryScale->id) }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">Grade Level Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" required maxlength="50">
                        @error('name')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="basic_salary" class="form-label">Basic Salary <span class="text-danger">*</span></label>
                        <input type="number" name="basic_salary" id="basic_salary" step="0.01" class="form-control" value="{{ old('basic_salary') }}" required min="0">
                        @error('basic_salary')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="grade_level" class="form-label">Grade Level <span class="text-danger">*</span></label>
                        <input type="number" name="grade_level" id="grade_level" class="form-control" value="{{ old('grade_level') }}" required min="1">
                        @error('grade_level')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="step_level" class="form-label">Step Level <span class="text-danger">*</span></label>
                        <input type="number" name="step_level" id="step_level" class="form-control" value="{{ old('step_level') }}" required min="1">
                        @error('step_level')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea name="description" id="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="d-flex justify-content-between">
                    <a href="{{ route('salary-scales.grade-levels', $salaryScale->id) }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Grade Levels
                    </a>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-1"></i> Save Grade Level
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection