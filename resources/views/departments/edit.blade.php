@extends('layouts.app')

@section('title', 'Edit Department')

@section('content')
<div class="container">
    <h1>Edit Department</h1>
    <div class="card">
        <div class="card-body">
            <form action="{{ route('departments.update', $department) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label for="department_name" class="form-label">Department Name</label>
                    <input type="text" class="form-control @error('department_name') is-invalid @enderror" id="department_name" name="department_name" value="{{ old('department_name', $department->department_name) }}" required>
                    @error('department_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="4">{{ old('description', $department->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{ route('departments.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection