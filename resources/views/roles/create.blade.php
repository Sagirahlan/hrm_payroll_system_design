@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Create New Role</h2>

    <form action="{{ route('roles.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="role_name" class="form-label">Role Name</label>
            <input type="text" name="role_name" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Description (optional)</label>
            <textarea name="description" class="form-control"></textarea>
        </div>

        <div class="mb-3">
            <label for="permissions" class="form-label">Permissions</label>
            <select name="permissions[]" class="form-control select2" multiple="multiple">
                @php
                    $allPermissions = [
                        'view_employees',
                        'manage_employees',
                        'manage_payroll',
                        'manage_sms',
                        'view_audit_logs',
                        'manage_disciplinary',
                        'manage_retirement',
                        'manage_biometrics',
                        'manage_departments',
                        'manage_users',
                        'manage_reports'
                    ];
                @endphp

                @foreach ($allPermissions as $permission)
                    <option value="{{ $permission }}">
                        {{ ucwords(str_replace('_', ' ', $permission)) }}
                    </option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Save Role</button>
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
