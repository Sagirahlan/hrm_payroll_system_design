@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">Add New Salary Scale</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('salary-scales.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="acronym" class="form-label">Acronym <span class="text-danger">*</span></label>
                        <input type="text" name="acronym" id="acronym" class="form-control" value="{{ old('acronym') }}" required maxlength="10">
                        @error('acronym')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="full_name" class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="full_name" id="full_name" class="form-control" value="{{ old('full_name') }}" required maxlength="100">
                        @error('full_name')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="sector_coverage" class="form-label">Sector Coverage <span class="text-danger">*</span></label>
                        <input type="text" name="sector_coverage" id="sector_coverage" class="form-control" value="{{ old('sector_coverage') }}" required maxlength="50">
                        @error('sector_coverage')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="max_retirement_age" class="form-label">Max Retirement Age <span class="text-danger">*</span></label>
                        <input type="number" name="max_retirement_age" id="max_retirement_age" class="form-control" value="{{ old('max_retirement_age') }}" required min="1">
                        @error('max_retirement_age')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="max_years_of_service" class="form-label">Max Years of Service <span class="text-danger">*</span></label>
                        <input type="number" name="max_years_of_service" id="max_years_of_service" class="form-control" value="{{ old('max_years_of_service') }}" required min="1">
                        @error('max_years_of_service')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="notes" class="form-label">Notes</label>
                    <textarea name="notes" id="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
                    @error('notes')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="d-flex justify-content-between">
                    <a href="{{ route('salary-scales.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Salary Scales
                    </a>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-1"></i> Save Salary Scale
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection