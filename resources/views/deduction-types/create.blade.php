@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Create Deduction Type</h1>
    <form action="{{ route('deduction-types.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="name">Name</label>
            <input type="text" name="name" id="name" class="form-control">
        </div>
        <div class="form-group">
            <label for="code">Code</label>
            <input type="text" name="code" id="code" class="form-control">
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea name="description" id="description" class="form-control"></textarea>
        </div>
        <div class="form-group">
            <label for="is_statutory">Is Statutory</label>
            <select name="is_statutory" id="is_statutory" class="form-control">
                <option value="1">Yes</option>
                <option value="0">No</option>
            </select>
        </div>
        <div class="form-group" id="calculation_type_group">
            <label for="calculation_type">Calculation Type</label>
            <select name="calculation_type" id="calculation_type" class="form-control">
                <option value="percentage">Percentage</option>
                <option value="fixed_amount">Fixed Amount</option>
            </select>
        </div>
        <div class="form-group" id="rate_or_amount_group">
            <label for="rate_or_amount">Rate/Amount</label>
            <input type="text" name="rate_or_amount" id="rate_or_amount" class="form-control">
        </div>
        <button type="submit" class="btn btn-primary">Create</button>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const isStatutorySelect = document.getElementById('is_statutory');
        const calculationTypeGroup = document.getElementById('calculation_type_group');
        const rateOrAmountGroup = document.getElementById('rate_or_amount_group');

        function toggleCalculationFields() {
            const isStatutory = isStatutorySelect.value;
            if (isStatutory === '0') {
                // Hide calculation fields when "No" is selected
                calculationTypeGroup.style.display = 'none';
                rateOrAmountGroup.style.display = 'none';
            } else {
                // Show calculation fields when "Yes" is selected
                calculationTypeGroup.style.display = 'block';
                rateOrAmountGroup.style.display = 'block';
            }
        }

        // Initialize on page load
        toggleCalculationFields();

        // Add event listener for changes
        isStatutorySelect.addEventListener('change', toggleCalculationFields);
    });
</script>
@endsection
