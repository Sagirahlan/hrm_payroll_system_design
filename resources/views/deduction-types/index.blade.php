@extends('layouts.app')

@section('content')
<div class="container">
     @can('create_deduction_types')
    <a href="{{ route('deduction-types.create') }}" class="btn btn-primary">Create Deduction Type</a>
    @endcan
    <table class="table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Code</th>
                <th>Statutory</th>
                <th>Calculation Type</th>
                <th>Rate/Amount</th>
                 @can('edit_deduction_types')
                <th>Actions</th>
                @endcan
            </tr>
        </thead>
        <tbody>
            @foreach ($deductionTypes as $deductionType)
                <tr>
                    <td>{{ $deductionType->name }}</td>
                    <td>{{ $deductionType->code }}</td>
                    <td>{{ $deductionType->is_statutory ? 'Yes' : 'No' }}</td>
                    <td>{{ $deductionType->calculation_type }}</td>
                    <td>{{ $deductionType->rate_or_amount }}</td>
                     @can('edit_deduction_types')
                    <td>
                        <a href="{{ route('deduction-types.edit', $deductionType) }}" class="btn btn-sm btn-primary">Edit</a>
                        <form action="{{ route('deduction-types.destroy', $deductionType) }}" method="POST" style="display: inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </td>
                    @endcan
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
