@extends('layouts.app')

@section('title', 'Probation Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Employees on Probation</h4>
                </div>
                <div class="card-body">
                    <!-- Filters -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <input type="text" class="form-control" id="search" name="search" placeholder="Search employees..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3">
                            <select class="form-control" name="department" id="department">
                                <option value="">All Departments</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->department_id }}" {{ request('department') == $dept->department_id ? 'selected' : '' }}>
                                        {{ $dept->department_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-control" name="probation_status" id="probation_status">
                                <option value="">All Probation Status</option>
                                <option value="pending" {{ request('probation_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="approved" {{ request('probation_status') == 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="rejected" {{ request('probation_status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-primary" id="filterBtn">Filter</button>
                            <a href="{{ route('probation.index') }}" class="btn btn-secondary">Clear</a>
                        </div>
                    </div>

                    <!-- Employee Table -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Staff No.</th>
                                    <th>Name</th>
                                    <th>Department</th>
                                    <th>Probation Start</th>
                                    <th>Probation End</th>
                                    <th>Days Remaining</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="employeeTableBody">
                                @forelse($employees as $employee)
                                <tr>
                                    <td>{{ $employee->staff_no }}</td>
                                    <td>
                                        <a href="{{ route('probation.show', $employee) }}">
                                            {{ $employee->first_name }} {{ $employee->middle_name }} {{ $employee->surname }}
                                        </a>
                                    </td>
                                    <td>{{ $employee->department->department_name ?? 'N/A' }}</td>
                                    <td>{{ $employee->probation_start_date ? \Carbon\Carbon::parse($employee->probation_start_date)->format('d/m/Y') : 'N/A' }}</td>
                                    <td>{{ $employee->probation_end_date ? \Carbon\Carbon::parse($employee->probation_end_date)->format('d/m/Y') : 'N/A' }}</td>
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
                                    <td>
                                        @if($employee->probation_status == 'pending')
                                            <span class="badge bg-warning">On Probation</span>
                                        @elseif($employee->probation_status == 'approved')
                                            <span class="badge bg-success">Approved</span>
                                        @elseif($employee->probation_status == 'rejected')
                                            <span class="badge bg-danger">Rejected</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($employee->probation_status == 'pending')
                                            @if($employee->canBeEvaluatedForProbation())
                                                <form method="POST" action="{{ route('probation.approve', $employee) }}" style="display:inline;" onsubmit="return confirm('Are you sure you want to approve this employee\'s probation?')">
                                                    @csrf
                                                    @method('POST')
                                                    <button type="submit" class="btn btn-success btn-sm">Approve</button>
                                                </form>
                                            @else
                                                <button class="btn btn-sm btn-secondary" disabled title="Wait {{ $employee->getRemainingProbationDays() }} days for approval">
                                                    Approve (Wait {{ $employee->getRemainingProbationDays() }} days)
                                                </button>
                                            @endif

                                            {{-- Rejection is now allowed at any time --}}
                                            <button type="button" class="btn btn-danger btn-sm" onclick="openRejectModal('{{ route('probation.reject', $employee) }}', '{{ $employee->first_name }} {{ $employee->surname }}')">
                                                Reject
                                            </button>
                                        @else
                                            <span class="text-muted">Action Complete</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center">No employees on probation found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center">
                        {{ $employees->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Rejection Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="rejectForm" method="POST" action="">
                @csrf
                @method('POST')
                <div class="modal-header">
                    <h5 class="modal-title" id="rejectModalLabel">Reject Probation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to reject probation for <strong id="rejectEmployeeName"></strong>?</p>
                    <p class="text-danger">This action will terminate the employee.</p>
                    
                    <div class="mb-3">
                        <label for="probation_notes" class="form-label">Reason for Rejection <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="probation_notes" name="probation_notes" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject Probation</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openRejectModal(url, employeeName) {
    document.getElementById('rejectForm').action = url;
    document.getElementById('rejectEmployeeName').textContent = employeeName;
    var myModal = new bootstrap.Modal(document.getElementById('rejectModal'));
    myModal.show();
}

document.getElementById('filterBtn').addEventListener('click', function() {
    const search = document.getElementById('search').value;
    const department = document.getElementById('department').value;
    const probationStatus = document.getElementById('probation_status').value;
    
    let url = '{{ route("probation.index") }}';
    const params = [];
    
    if (search) params.push('search=' + encodeURIComponent(search));
    if (department) params.push('department=' + encodeURIComponent(department));
    if (probationStatus) params.push('probation_status=' + encodeURIComponent(probationStatus));
    
    if (params.length > 0) {
        url += '?' + params.join('&');
    }
    
    window.location.href = url;
});

// Allow pressing Enter in search field to trigger filter
document.getElementById('search').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        document.getElementById('filterBtn').click();
    }
});
</script>

@endsection