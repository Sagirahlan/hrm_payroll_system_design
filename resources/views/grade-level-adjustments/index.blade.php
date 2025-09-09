@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Adjustments for Grade Level: {{ $gradeLevel->name }}</h1>

    <h2>Deductions</h2>
    <table class="table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Percentage</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($gradeLevel->deductionTypes as $deductionType)
                <tr>
                    <td>{{ $deductionType->name }}</td>
                    <td>{{ $deductionType->pivot->percentage }}%</td>
                    <td>
                        <form action="{{ route('grade-levels.adjustments.destroy', [$gradeLevel, $deductionType->id]) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Remove</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2>Additions</h2>
    <table class="table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Percentage</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($gradeLevel->additionTypes as $additionType)
                <tr>
                    <td>{{ $additionType->name }}</td>
                    <td>{{ $additionType->pivot->percentage }}%</td>
                    <td>
                        <form action="{{ route('grade-levels.adjustments.destroy', [$gradeLevel, $additionType->id]) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Remove</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <hr>

    <h2>Add New Adjustment</h2>
    <form action="{{ route('grade-levels.adjustments.store', $gradeLevel) }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="adjustment_type">Adjustment Type</label>
            <select name="adjustment_type" id="adjustment_type" class="form-control">
                <option value="deduction">Deduction</option>
                <option value="addition">Addition</option>
            </select>
        </div>
        <div class="form-group">
            <label for="adjustment_id">Adjustment</label>
            <select name="adjustment_id" id="adjustment_id" class="form-control">
                <optgroup label="Deductions">
                    @foreach ($deductionTypes as $deductionType)
                        <option value="{{ $deductionType->id }}">{{ $deductionType->name }}</option>
                    @endforeach
                </optgroup>
                <optgroup label="Additions">
                    @foreach ($additionTypes as $additionType)
                        <option value="{{ $additionType->id }}">{{ $additionType->name }}</option>
                    @endforeach
                </optgroup>
            </select>
        </div>
        <div class="form-group">
            <label for="percentage">Percentage</label>
            <input type="text" name="percentage" id="percentage" class="form-control">
        </div>
        <button type="submit" class="btn btn-primary">Add Adjustment</button>
    </form>
</div>
@endsection
