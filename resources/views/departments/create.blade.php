@extends('layouts.app')

@section('title', 'Create Department')

@section('content')
<div class="container mt-5">
    <h1 class="mb-4 text-primary">Create Department</h1>
    <div class="card border-info shadow">
        <div class="card-body">
            <form action="{{ route('departments.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="department_name" class="form-label fw-bold text-info">Department Name</label>
                    <input type="text" class="form-control border-info @error('department_name') is-invalid @enderror" id="department_name" name="department_name" value="{{ old('department_name') }}" required>
                    @error('department_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label fw-bold text-info">Description</label>
                    <textarea class="form-control border-info @error('description') is-invalid @enderror" id="description" name="description" rows="4">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <button type="submit" class="btn btn-primary me-2">Create</button>
                <a href="{{ route('departments.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection