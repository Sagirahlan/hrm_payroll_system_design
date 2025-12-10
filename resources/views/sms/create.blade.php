@extends('layouts.app')

@section('content')
<div class="container">
    @can('manage_sms')
    <h1>Send SMS Notification</h1>
    <form action="{{ route('sms.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="recipient_type" class="form-label">Recipient Type</label>
            <select name="recipient_type" class="form-control" id="recipient_type" required>
                <option value="All">All Employees</option>
                <option value="Department">Department</option>
                <option value="GradeLevel">Grade Level</option>
                <option value="Cadre">Cadre</option>
                <option value="AppointmentType">Appointment Type</option>
                <option value="Status">Status</option>
                <option value="Gender">Gender</option>
                <option value="State">State</option>
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
            <label for="grade_level_id" class="form-label">Grade Level</label>
            <select name="grade_level_id" class="form-control">
                @foreach ($gradeLevels as $level)
                    <option value="{{ $level->id }}">
                        Grade {{ $level->grade_level }}, Step {{ $level->step_level }} ({{ $level->name }})
                    </option>
                @endforeach
            </select>
            @error('grade_level_id') <span class="text-danger">{{ $message }}</span> @enderror
        </div>

        <div class="mb-3" id="cadre_select" style="display:none;">
            <label for="cadre_id" class="form-label">Cadre</label>
            <select name="cadre_id" class="form-control">
                @foreach ($cadres as $cadre)
                    <option value="{{ $cadre->cadre_id }}">{{ $cadre->name }}</option>
                @endforeach
            </select>
            @error('cadre_id') <span class="text-danger">{{ $message }}</span> @enderror
        </div>

        <div class="mb-3" id="appointment_type_select" style="display:none;">
            <label for="appointment_type_id" class="form-label">Appointment Type</label>
            <select name="appointment_type_id" class="form-control">
                @foreach ($appointmentTypes as $type)
                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                @endforeach
            </select>
            @error('appointment_type_id') <span class="text-danger">{{ $message }}</span> @enderror
        </div>

        <div class="mb-3" id="status_select" style="display:none;">
            <label for="status" class="form-label">Status</label>
            <select name="status" class="form-control">
                @foreach ($statuses as $status)
                    <option value="{{ $status }}">{{ $status }}</option>
                @endforeach
            </select>
            @error('status') <span class="text-danger">{{ $message }}</span> @enderror
        </div>

        <div class="mb-3" id="gender_select" style="display:none;">
            <label for="gender" class="form-label">Gender</label>
            <select name="gender" class="form-control">
                @foreach ($genders as $gender)
                    <option value="{{ $gender }}">{{ $gender }}</option>
                @endforeach
            </select>
            @error('gender') <span class="text-danger">{{ $message }}</span> @enderror
        </div>

        <div class="mb-3" id="state_select" style="display:none;">
            <label for="state_id" class="form-label">State</label>
            <select name="state_id" class="form-control">
                @foreach ($states as $state)
                    <option value="{{ $state->state_id }}">{{ $state->name }}</option>
                @endforeach
            </select>
            @error('state_id') <span class="text-danger">{{ $message }}</span> @enderror
        </div>

        <div class="mb-3">
            <label for="message" class="form-label">Message (Max 160 characters)</label>
            <textarea name="message" class="form-control" required maxlength="160" rows="4"></textarea>
            @error('message') <span class="text-danger">{{ $message }}</span> @enderror
        </div>
        <button type="submit" class="btn btn-primary">Send SMS</button>
    </form>
    @else
    <div class="alert alert-warning">
        You don't have permission to send SMS notifications.
    </div>
    @endcan
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const recipientTypeSelect = document.getElementById('recipient_type');

        // Function to show/hide filter options
        function toggleFilterOptions() {
            const recipientType = recipientTypeSelect.value;

            // Hide all conditional selects
            document.getElementById('department_select').style.display = 'none';
            document.getElementById('grade_level_select').style.display = 'none';
            document.getElementById('cadre_select').style.display = 'none';
            document.getElementById('appointment_type_select').style.display = 'none';
            document.getElementById('status_select').style.display = 'none';
            document.getElementById('gender_select').style.display = 'none';
            document.getElementById('state_select').style.display = 'none';

            // Show the relevant select based on recipient type
            switch(recipientType) {
                case 'Department':
                    document.getElementById('department_select').style.display = 'block';
                    break;
                case 'GradeLevel':
                    document.getElementById('grade_level_select').style.display = 'block';
                    break;
                case 'Cadre':
                    document.getElementById('cadre_select').style.display = 'block';
                    break;
                case 'AppointmentType':
                    document.getElementById('appointment_type_select').style.display = 'block';
                    break;
                case 'Status':
                    document.getElementById('status_select').style.display = 'block';
                    break;
                case 'Gender':
                    document.getElementById('gender_select').style.display = 'block';
                    break;
                case 'State':
                    document.getElementById('state_select').style.display = 'block';
                    break;
            }
        }

        // Add event listener to the recipient type select
        recipientTypeSelect.addEventListener('change', toggleFilterOptions);

        // Initialize the form on page load
        toggleFilterOptions();
    });
</script>
@endsection
