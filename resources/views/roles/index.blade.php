@extends('layouts.app')

@section('content')
<div class="container">
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header" style="color: black;">
                    Roles
                    <a href="{{ route('roles.create') }}" class="btn btn-primary btn-sm float-right">Add Role</a>
                </div>
                <div class="card-body" style="color: black;">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th style="color: black;">Name</th>
                                <th style="color: black;">Permissions</th>
                                <th style="color: black;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($roles as $role)
                            <tr>
                                <td style="color: black;">{{ $role->name }}</td>
                                <td>
                                    @foreach ($role->permissions as $permission)
                                    <span class="badge badge-success" style="color: black;">{{ $permission->name }}</span>
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