@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Edit Role: {{ $role->role_name }}</h2>

    <form action="{{ route('roles.update', $role) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="name" class="form-label">Role Name</label>
            <input type="text" name="name" class="form-control" value="{{ $role->name }}" required>
        </div>


    <div class="mb-3">
            <label for="permissions" class="form-label">Permissions</label>
            <select name="permissions[]" class="form-control select2" multiple="multiple">
                @foreach ($permissions as $id => $name)
                    <option value="{{ $id }}" {{ in_array($id, $rolePermissions) ? 'selected' : '' }}>
                        {{ $name }}
                    </option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="btn btn-success">Update Role</button>
    </form>
</div>
@endsection

@section('scripts')
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- jQuery (required for Select2) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            $('.select2').select2({
                tags: true,
                placeholder: "Select or type to add permissions",
                allowClear: true,
                tokenSeparators: [',', ' ']
            });
        });
    </script>
@endsection

