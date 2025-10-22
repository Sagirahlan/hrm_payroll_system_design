@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Addition Types</h1>
     @can('create_addition_types')
    <a href="{{ route('addition-types.create') }}" class="btn btn-primary">Create Addition Type</a>
    @endcan
    <table class="table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Code</th>
                <th>Statutory</th>
                <th>Calculation Type</th>
                <th>Rate/Amount</th>
                 @can('edit_addition_types')
                <th>Actions</th>
                @endcan
            </tr>
        </thead>
        <tbody>
            @foreach ($additionTypes as $additionType)
                <tr>
                    <td>{{ $additionType->name }}</td>
                    <td>{{ $additionType->code }}</td>
                    <td>{{ $additionType->is_statutory ? 'Yes' : 'No' }}</td>
                    <td>{{ $additionType->calculation_type }}</td>
                    <td>{{ $additionType->rate_or_amount }}</td>
                    @can('edit_addition_types')
                    <td>
                        <a href="{{ route('addition-types.edit', $additionType) }}" class="btn btn-sm btn-primary">Edit</a>
                        <form action="{{ route('addition-types.destroy', $additionType) }}" method="POST" style="display: inline-block;">
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
