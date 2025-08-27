@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    Roles
                    <a href="{{ route('roles.create') }}" class="btn btn-primary btn-sm float-right">Add Role</a>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Permissions</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($roles as $role)
                            <tr>
                                <td>{{ $role->name }}</td>
                                <td>
                                    @foreach ($role->permissions as $permission)
                                    <span class="badge badge-danger">{{ $permission->name }}</span>
                                    @endforeach
                                </td>
                                <td>
                                    <a href="{{ route('roles.edit', $role->id) }}" class="btn btn-primary btn-sm">Edit</a>
                                    <form action="{{ route('roles.destroy', $role->id) }}" method="POST" style="display: inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection