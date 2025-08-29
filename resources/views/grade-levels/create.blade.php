@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="card border-primary shadow" style="max-width: 500px; margin: 0 auto;">
        <div class="card-header" style="background-color: skyblue; color: white; padding: 0.5rem 1rem;">
            <h6 class="mb-0">Add Grade Level</h6>
        </div>
        <div class="card-body p-3">
            <form action="{{ route('grade-levels.store') }}" method="POST">
                @csrf
                <div class="mb-2">
                    <label for="name" class="form-label small">Grade Level Name</label>
                    <input type="text" name="name" id="name" class="form-control form-control-sm" required>
                </div>
                <div class="mb-2">
                    <label for="basic_salary" class="form-label small">Basic Salary</label>
                    <input type="number" name="basic_salary" id="basic_salary" step="0.01" class="form-control form-control-sm" required>
                </div>
                <div class="mb-2">
                    <label for="grade_level" class="form-label small">Grade Level</label>
                    <input type="number" name="grade_level" id="grade_level" min="1" class="form-control form-control-sm" required>
                </div>
                <div class="mb-2">
                    <label for="step_level" class="form-label small">Step Level</label>
                    <input type="number" name="step_level" id="step_level" min="1" class="form-control form-control-sm" required>
                </div>
                <div class="mb-2">
                    <label for="description" class="form-label small">Description</label>
                    <textarea name="description" id="description" class="form-control form-control-sm" rows="2"></textarea>
                </div>
                <div class="d-flex gap-2 justify-content-end">
                    <button type="submit" class="btn btn-primary btn-sm">
                        Add
                    </button>
                    <a href="{{ route('grade-levels.index') }}" class="btn btn-secondary btn-sm">
                        Cancel
                    </a>
                </div>
            </form>
@endsection
