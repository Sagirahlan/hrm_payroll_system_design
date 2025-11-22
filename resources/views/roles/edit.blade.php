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
            <label class="form-label">Permissions</label>
            <div class="mb-3">
                <input type="text" id="permission-search" class="form-control" placeholder="Search permissions...">
            </div>
            <div class="border rounded p-3" style="max-height: 400px; overflow-y: auto;">
                <div class="row" id="permissions-container">
                    @foreach ($permissions as $id => $name)
                        <div class="col-md-6 col-lg-4 mb-2 permission-item" data-permission="{{ strtolower($name) }}">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $id }}" id="permission_{{ $id }}"
                                       {{ in_array($id, $rolePermissions) ? 'checked' : '' }}>
                                <label class="form-check-label" for="permission_{{ $id }}">
                                    {{ $name }}
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-success">Update Role</button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('permission-search');
    const permissionItems = document.querySelectorAll('.permission-item');

    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase().trim();

        permissionItems.forEach(function(item) {
            const permissionName = item.getAttribute('data-permission');
            if (permissionName.includes(searchTerm)) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    });
});
</script>
@endsection

