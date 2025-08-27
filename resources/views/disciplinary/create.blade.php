@extends('layouts.app')

@section('content')
<div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="card border-0 shadow-lg" style="max-width: 600px; width: 100%; background: linear-gradient(135deg, #e0f7fa 0%, #ffffff 100%);">
        <div class="card-header text-center" style="background: #00bcd4;">
            <h4 class="mb-0 text-white font-weight-bold">New Disciplinary Action</h4>
        </div>
        <div class="card-body p-4">
            <form action="{{ route('disciplinary.store') }}" method="POST">
                @csrf
                <div class="form-group mb-4">
                    <label for="employeeSearch" class="form-label font-weight-bold">Search Employee</label>
                    <input type="text" id="employeeSearch" class="form-control mb-2" placeholder="Type to search employee...">
                    <label for="employee_id" class="form-label font-weight-bold">Employee</label>
                    <select name="employee_id" id="employee_ids" class="form-select" required>
                        <option value="" disabled selected>Select employee</option>
                        @foreach ($employees as $employee)
                            <option value="{{ $employee->employee_id }}">{{ $employee->first_name }} {{ $employee->surname }}</option>
                        @endforeach
                    </select>
                    @error('employee_id') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
                <div class="form-group mb-4">
                    <label for="action_type" class="form-label font-weight-bold">Action Type</label>
                    <select name="action_type" class="form-select rounded-pill" required>
                        <option value="" disabled selected>Select action type</option>
                        <option value="suspended">Suspended</option>
                        <option value="warning">Warning</option>
                        <option value="terminated">Terminated</option>
                    </select>
                    @error('action_type') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
                <div class="form-group mb-4">
                    <label for="description" class="form-label font-weight-bold">Description</label>
                    <textarea name="description" class="form-control rounded" rows="3"></textarea>
                    @error('description') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
                <div class="form-group mb-4">
                    <label for="action_date" class="form-label font-weight-bold">Action Date</label>
                    <input type="date" name="action_date" class="form-control rounded-pill" required>
                    @error('action_date') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
                <div class="form-group mb-4">
                    <label for="status" class="form-label font-weight-bold">Status</label>
                    <select name="status" class="form-select rounded-pill" required>
                        <option value="Open">Open</option>
                        <option value="Resolved">Resolved</option>
                        <option value="Pending">Pending</option>
                    </select>
                    @error('status') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
                <button type="submit" class="btn btn-info w-100 rounded-pill font-weight-bold shadow-sm">Save</button>
            </form>
        </div>
    </div>
</div>
<script>
    document.getElementById('employeeSearch').addEventListener('input', function() {
        const search = this.value.toLowerCase();
        const select = document.getElementById('employee_ids');
        for (let option of select.options) {
            const text = option.text.toLowerCase();
            option.style.display = text.includes(search) ? '' : 'none';
        }
    });
</script>
@endsection