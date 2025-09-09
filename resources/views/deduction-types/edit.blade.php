@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Edit Deduction Type</h1>
    <form action="{{ route('deduction-types.update', $deductionType) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="name">Name</label>
            <input type="text" name="name" id="name" class="form-control" value="{{ $deductionType->name }}">
        </div>
        <div class="form-group">
            <label for="code">Code</label>
            <input type="text" name="code" id="code" class="form-control" value="{{ $deductionType->code }}">
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea name="description" id="description" class="form-control">{{ $deductionType->description }}</textarea>
        </div>
        <div class="form-group">
            <label for="is_statutory">Is Statutory</label>
            <select name="is_statutory" id="is_statutory" class="form-control">
                <option value="1" {{ $deductionType->is_statutory ? 'selected' : '' }}>Yes</option>
                <option value="0" {{ !$deductionType->is_statutory ? 'selected' : '' }}>No</option>
            </select>
        </div>
        <div class="form-group">
            <label for="calculation_type">Calculation Type</label>
            <select name="calculation_type" id="calculation_type" class="form-control">
                <option value="percentage" {{ $deductionType->calculation_type === 'percentage' ? 'selected' : '' }}>Percentage</option>
                <option value="fixed_amount" {{ $deductionType->calculation_type === 'fixed_amount' ? 'selected' : '' }}>Fixed Amount</option>
            </select>
        </div>
        <div class="form-group">
            <label for="rate_or_amount">Rate/Amount</label>
            <input type="text" name="rate_or_amount" id="rate_or_amount" class="form-control" value="{{ $deductionType->rate_or_amount }}">
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
    </form>
</div>
@endsection
