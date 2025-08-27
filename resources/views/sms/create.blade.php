@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Send SMS Notification</h1>
    <form action="{{ route('sms.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="recipient_type" class="form-label">Recipient Type</label>
            <select name="recipient_type" class="form-control" required>
                <option value="All">All Employees</option>
                <option value="Department">Department</option>
                <option value="GradeLevel">Grade Level</option>
            </select>
            @error('recipient_type') <span class="text-danger">{{ $message }}</span> @enderror
        </div>

        <div class="mb-3" id="department_select" style="display:none;">
            <label for="recipient_id" class="form-label">Department</label>
            <select name="recipient_id" class="form-control">
                @foreach ($departments as $department)
                    <option value="{{ $department->department_id }}">{{ $department->department_name }}</option>
                @endforeach
            </select>
            @error('recipient_id') <span class="text-danger">{{ $message }}</span> @enderror
        </div>

        <div class="mb-3" id="grade_level_select" style="display:none;">
            <label for="grade_level_id" class="form-label">Grade Level & Step</label>
            <select name="grade_level_id" class="form-control">
                @foreach ($salaryScales as $scale)
                    <option value="{{ $scale->scale_id }}">
                        Grade {{ $scale->grade_level }}, Step {{ $scale->step_level }} ({{ $scale->scale_name }})
                    </option>
                @endforeach
            </select>
            @error('grade_level_id') <span class="text-danger">{{ $message }}</span> @enderror
        </div>

        <div class="mb-3">
            <label for="message" class="form-label">Message (Max 160 characters)</label>
            <textarea name="message" class="form-control" required maxlength="160"></textarea>
            @error('message') <span class="text-danger">{{ $message }}</span> @enderror
        </div>
        <button type="submit" class="btn btn-primary">Send SMS</button>
    </form>
</div>

<script>
    document.querySelector('[name="recipient_type"]').addEventListener('change', function () {
        const recipientType = this.value;
        document.getElementById('department_select').style.display = recipientType === 'Department' ? 'block' : 'none';
        document.getElementById('grade_level_select').style.display = recipientType === 'GradeLevel' ? 'block' : 'none';
    });
</script>
@endsection
