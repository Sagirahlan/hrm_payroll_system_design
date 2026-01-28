@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header border-0 pt-6">
        <div class="card-title">
            <h3>Increment History</h3>
        </div>
        <div class="card-toolbar">
            <a href="{{ route('promotions.increments.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> New Increment
            </a>
            <a href="{{ route('promotions.index') }}" class="btn btn-light btn-sm ms-2">
                <i class="fas fa-arrow-left"></i> Back to Promotions
            </a>
        </div>
    </div>
    
    <div class="card-body py-4">
        <!-- Search and Filter Form -->
        <form action="{{ route('promotions.increments.index') }}" method="GET" class="mb-5">
            <div class="row g-3">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="Search by name or staff no..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="department" class="form-select" data-control="select2" data-placeholder="Select Department">
                        <option value="">All Departments</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->department_id }}" {{ request('department') == $dept->department_id ? 'selected' : '' }}>
                                {{ $dept->department_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="year" class="form-select">
                        <option value="">All Years</option>
                        @for($y = date('Y'); $y >= 2020; $y--)
                            <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </div>
            <div class="d-flex justify-content-end mt-2">
                 <a href="{{ route('promotions.increments.index') }}" class="btn btn-light btn-sm">Reset Filters</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table align-middle table-row-dashed fs-6 gy-5">
                <thead>
                    <tr class="text-start text-muted fw-bolder fs-7 text-uppercase gs-0">
                        <th>Staff No</th>
                        <th>Employee Name</th>
                        <th>Grade Level</th>
                        <th>Previous Step</th>
                        <th>New Step</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 fw-bold">
                    @forelse($increments as $increment)
                        <tr>
                            <td>{{ $increment->employee->staff_no ?? $increment->employee->employee_id }}</td>
                            <td>{{ $increment->employee->full_name }}</td>
                            <td>{{ $increment->gradeLevel ?? $increment->new_grade_level }}</td>
                            <td>{{ $increment->previous_step }}</td>
                            <td>
                                <span class="badge badge-success text-dark">{{ $increment->new_step }}</span>
                            </td>
                            <td>{{ \Carbon\Carbon::parse($increment->created_at)->format('d M, Y H:i') }}</td>
                            <td>
                                @if($increment->status == 'approved')
                                    <span class="badge badge-success text-dark">Approved</span>
                                @elseif($increment->status == 'pending')
                                    <span class="badge badge-warning text-dark">Pending</span>
                                @else
                                    <span class="badge badge-danger text-dark">Rejected</span>
                                @endif
                            </td>
                            <td>
                                @if($increment->status == 'pending')
                                    @can('approve_promotions')
                                        <div class="d-flex justify-content-start flex-shrink-0">
                                            <form action="{{ route('promotions.approve', $increment->id) }}" method="POST" class="me-1">
                                                @csrf
                                                <button type="submit" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1" title="Approve" onclick="return confirm('Are you sure you want to approve this increment?')">
                                                    <i class="fas fa-check text-success"></i>
                                                </button>
                                            </form>
                                            <form action="{{ route('promotions.reject', $increment->id) }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="approval_notes" value="Rejected via Increment History" />
                                                <button type="button" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm" title="Reject" onclick="promptReject('{{ route('promotions.reject', $increment->id) }}')">
                                                    <i class="fas fa-times text-danger"></i>
                                                </button>
                                            </form>
                                        </div>
                                    @endcan
                                @else
                                    <span class="text-muted fs-7">{{ $increment->approving_authority ?? 'N/A' }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">No increment records found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-end mt-4">
            {{ $increments->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function promptReject(url) {
        Swal.fire({
            title: 'Reject Increment',
            text: "Please provide a reason for rejection:",
            input: 'textarea',
            inputPlaceholder: 'Reason for rejection...',
            showCancelButton: true,
            confirmButtonText: 'Reject',
            confirmButtonColor: '#d33',
            showLoaderOnConfirm: true,
            preConfirm: (reason) => {
                if (!reason) {
                    Swal.showValidationMessage('Reason is required');
                    return false;
                }
                return reason;
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed) {
                // Create a temporary form to submit
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = url;
                
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = csrfToken;
                form.appendChild(csrfInput);

                const reasonInput = document.createElement('input');
                reasonInput.type = 'hidden';
                reasonInput.name = 'approval_notes';
                reasonInput.value = result.value;
                form.appendChild(reasonInput);

                document.body.appendChild(form);
                form.submit();
            }
        });
    }
</script>
@endpush
