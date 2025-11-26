@extends('layouts.app')

@section('title', 'Probation Employee Details - ' . $employee->first_name . ' ' . $employee->surname)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Probation Employee Details</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h5>Personal Information</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Staff Number:</strong></td>
                                    <td>{{ $employee->staff_no }}</td>
                                    <td><strong>Full Name:</strong></td>
                                    <td>{{ $employee->first_name }} {{ $employee->middle_name }} {{ $employee->surname }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Gender:</strong></td>
                                    <td>{{ $employee->gender }}</td>
                                    <td><strong>Date of Birth:</strong></td>
                                    <td>{{ \Carbon\Carbon::parse($employee->date_of_birth)->format('d/m/Y') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Email:</strong></td>
                                    <td>{{ $employee->email ?? 'N/A' }}</td>
                                    <td><strong>Mobile No:</strong></td>
                                    <td>{{ $employee->mobile_no }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Address:</strong></td>
                                    <td colspan="3">{{ $employee->address }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-4 text-center">
                            @if($employee->photo_path)
                                <img src="{{ asset('storage/' . $employee->photo_path) }}" alt="Employee Photo" class="img-fluid rounded" style="max-height: 200px;">
                            @else
                                <div class="bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                    <span class="text-muted">No Photo</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <h5>Employment Information</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Department:</strong></td>
                                    <td>{{ $employee->department->department_name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Grade Level:</strong></td>
                                    <td>{{ $employee->gradeLevel->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Step:</strong></td>
                                    <td>{{ $employee->step->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Date of First Appointment:</strong></td>
                                    <td>{{ \Carbon\Carbon::parse($employee->date_of_first_appointment)->format('d/m/Y') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Appointment Type:</strong></td>
                                    <td>{{ $employee->appointmentType->name ?? 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                        
                        <div class="col-md-6">
                            <h5>Probation Information</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>On Probation:</strong></td>
                                    <td>
                                        @if($employee->on_probation)
                                            <span class="badge bg-warning">Yes</span>
                                        @else
                                            <span class="badge bg-secondary">No</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Probation Status:</strong></td>
                                    <td>
                                        @if($employee->probation_status == 'pending')
                                            <span class="badge bg-warning">Pending</span>
                                        @elseif($employee->probation_status == 'approved')
                                            <span class="badge bg-success">Approved</span>
                                        @elseif($employee->probation_status == 'rejected')
                                            <span class="badge bg-danger">Rejected</span>
                                        @else
                                            <span class="badge bg-secondary">N/A</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Probation Start Date:</strong></td>
                                    <td>
                                        {{ $employee->probation_start_date ? \Carbon\Carbon::parse($employee->probation_start_date)->format('d/m/Y') : 'N/A' }}
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Probation End Date:</strong></td>
                                    <td>
                                        {{ $employee->probation_end_date ? \Carbon\Carbon::parse($employee->probation_end_date)->format('d/m/Y') : 'N/A' }}
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Days Remaining:</strong></td>
                                    <td>
                                        @if($employee->on_probation)
                                            @if($employee->hasProbationPeriodEnded())
                                                <span class="text-danger">Probation Ended</span>
                                            @else
                                                {{ $employee->getRemainingProbationDays() }} days
                                            @endif
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Can be Evaluated:</strong></td>
                                    <td>
                                        @if($employee->canBeEvaluatedForProbation())
                                            <span class="badge bg-success">Yes</span>
                                        @else
                                            <span class="badge bg-warning">No ({{ $employee->getRemainingProbationDays() }} days remaining)</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($employee->probation_notes)
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h5>Probation Notes</h5>
                            <div class="card">
                                <div class="card-body">
                                    <p>{{ $employee->probation_notes }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h5>Probation Actions</h5>
                            <div class="btn-group" role="group">
                                @if($employee->on_probation && $employee->probation_status == 'pending')
                                    @if($employee->canBeEvaluatedForProbation())
                                        <form method="POST" action="{{ route('probation.approve', $employee) }}" style="display:inline;" onsubmit="return confirm('Are you sure you want to approve this employee\'s probation?')">
                                            @csrf
                                            @method('POST')
                                            <button type="submit" class="btn btn-success">Approve Probation</button>
                                        </form>
                                        <form method="POST" action="{{ route('probation.reject', $employee) }}" style="display:inline;" onsubmit="return confirm('Are you sure you want to reject this employee\'s probation?')">
                                            @csrf
                                            @method('POST')
                                            <button type="submit" class="btn btn-danger">Reject Probation</button>
                                        </form>
                                    @else
                                        <button class="btn btn-secondary" disabled>
                                            Wait {{ $employee->getRemainingProbationDays() }} days to evaluate
                                        </button>
                                    @endif
                                    
                                    <!-- Extend probation form -->
                                    <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#extendModal">
                                        Extend Probation
                                    </button>
                                @elseif($employee->probation_status == 'approved')
                                    <span class="badge bg-success fs-6">Probation Approved</span>
                                @elseif($employee->probation_status == 'rejected')
                                    <span class="badge bg-danger fs-6">Probation Rejected</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Extend Probation Modal -->
    <div class="modal fade" id="extendModal" tabindex="-1" aria-labelledby="extendModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="extendModalLabel">Extend Probation Period</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('probation.extend', $employee) }}">
                    @csrf
                    @method('POST')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="extension_months" class="form-label">Extension Months</label>
                            <select name="extension_months" id="extension_months" class="form-control" required>
                                <option value="">Select months to extend</option>
                                @for($i = 1; $i <= 6; $i++)
                                    <option value="{{ $i }}">{{ $i }} month{{ $i > 1 ? 's' : '' }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="extension_reason" class="form-label">Reason for Extension</label>
                            <textarea name="extension_reason" id="extension_reason" class="form-control" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Extend Probation</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection