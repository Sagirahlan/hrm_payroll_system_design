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

        <!-- Selection Helper for All Pages -->
        @can('approve_promotions')
            <div id="selection-helper" class="alert alert-info d-none mb-3">
                <span id="selection-message"></span>
                <a href="#" id="select-all-matching" class="fw-bold text-primary"></a>
            </div>
        @endcan

        <!-- Bulk Action Buttons -->
        @can('approve_promotions')
            <div class="d-flex justify-content-end mb-3 gap-2" id="bulkActionButtons" style="display: none !important;">
                <button type="button" class="btn btn-success btn-sm" id="btnApproveAll">
                    <i class="fas fa-check-double"></i> Approve Selected (<span id="selectedCount">0</span>)
                </button>
                <button type="button" class="btn btn-danger btn-sm" id="btnRejectAll">
                    <i class="fas fa-times-circle"></i> Reject Selected (<span id="selectedCountReject">0</span>)
                </button>
            </div>
            
            <!-- Hidden inputs for select all pages functionality -->
            <input type="hidden" id="select_all_pages" value="0">
            <input type="hidden" id="filter_search" value="{{ request('search') }}">
            <input type="hidden" id="filter_status" value="{{ request('status') }}">
            <input type="hidden" id="filter_department" value="{{ request('department') }}">
            <input type="hidden" id="filter_year" value="{{ request('year') }}">
        @endcan

        <div class="table-responsive">
            <table class="table align-middle table-row-dashed fs-6 gy-5">
                <thead>
                    <tr class="text-start text-muted fw-bolder fs-7 text-uppercase gs-0">
                        @can('approve_promotions')
                        <th class="w-10px pe-2">
                            <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                                <input class="form-check-input" type="checkbox" id="selectAll" />
                            </div>
                        </th>
                        @endcan
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
                            @can('approve_promotions')
                            <td>
                                @if($increment->status == 'pending')
                                    <div class="form-check form-check-sm form-check-custom form-check-solid">
                                        <input class="form-check-input increment-checkbox" type="checkbox" name="increment_ids[]" value="{{ $increment->id }}" />
                                    </div>
                                @endif
                            </td>
                            @endcan
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
                            <td colspan="9" class="text-center text-muted">No increment records found.</td>
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
    document.addEventListener('DOMContentLoaded', function() {
        const selectAll = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.increment-checkbox');
        const bulkActionButtons = document.getElementById('bulkActionButtons');
        const selectedCount = document.getElementById('selectedCount');
        const selectedCountReject = document.getElementById('selectedCountReject');
        const btnApproveAll = document.getElementById('btnApproveAll');
        const btnRejectAll = document.getElementById('btnRejectAll');
        
        // Select all pages elements
        const selectAllPagesInput = document.getElementById('select_all_pages');
        const selectionHelper = document.getElementById('selection-helper');
        const selectionMessage = document.getElementById('selection-message');
        const selectAllMatchingLink = document.getElementById('select-all-matching');
        
        // Get data from server
        const totalPending = {{ $increments->total() }};
        const visibleCount = {{ $increments->count() }};
        const pendingOnPage = checkboxes.length;

        // Update selected count and show/hide bulk action buttons
        function updateBulkActions() {
            const checkedCount = document.querySelectorAll('.increment-checkbox:checked').length;
            
            // Update count display
            if (selectAllPagesInput && selectAllPagesInput.value === '1') {
                if (selectedCount) selectedCount.textContent = totalPending;
                if (selectedCountReject) selectedCountReject.textContent = totalPending;
            } else {
                if (selectedCount) selectedCount.textContent = checkedCount;
                if (selectedCountReject) selectedCountReject.textContent = checkedCount;
            }
            
            if (bulkActionButtons) {
                if (checkedCount > 0 || (selectAllPagesInput && selectAllPagesInput.value === '1')) {
                    bulkActionButtons.style.display = 'flex';
                } else {
                    bulkActionButtons.style.display = 'none';
                }
            }
            
            // Update "Select All" checkbox state
            if (selectAll && checkboxes.length > 0) {
                const allChecked = checkedCount === checkboxes.length;
                selectAll.checked = allChecked;
                checkGlobalSelectVisibility(allChecked);
            }
        }
        
        function checkGlobalSelectVisibility(allVisibleChecked) {
            if (!selectionHelper || !selectAllMatchingLink) return;
            
            // Only show helper if:
            // 1. All visible checkboxes are checked
            // 2. Total pending > visible pending
            // 3. We haven't already selected all pages
            if (allVisibleChecked && totalPending > visibleCount && selectAllPagesInput.value === '0') {
                selectionHelper.classList.remove('d-none');
                selectionMessage.textContent = `All ${pendingOnPage} pending increment(s) on this page are selected. `;
                selectAllMatchingLink.textContent = `Select all ${totalPending} pending increments`;
                selectAllMatchingLink.classList.remove('d-none');
            } else if (selectAllPagesInput.value === '1') {
                selectionHelper.classList.remove('d-none');
                selectionMessage.textContent = `All ${totalPending} pending increments matching the search are selected.`;
                selectAllMatchingLink.classList.add('d-none');
            } else {
                selectionHelper.classList.add('d-none');
            }
        }

        // Handle Select All Matching Link
        if (selectAllMatchingLink) {
            selectAllMatchingLink.addEventListener('click', function(e) {
                e.preventDefault();
                selectAllPagesInput.value = '1';
                checkGlobalSelectVisibility(true);
                updateBulkActions();
            });
        }

        // Handle "Select All" checkbox
        if (selectAll) {
            selectAll.addEventListener('change', function() {
                const isChecked = this.checked;
                
                // Reset global select if unchecking
                if (!isChecked && selectAllPagesInput) {
                    selectAllPagesInput.value = '0';
                }
                
                checkboxes.forEach(cb => {
                    cb.checked = isChecked;
                });
                
                updateBulkActions();
                if (selectAllPagesInput) {
                    checkGlobalSelectVisibility(isChecked);
                }
            });
        }

        // Handle individual checkboxes
        checkboxes.forEach(cb => {
            cb.addEventListener('change', function() {
                // If any individual checkbox changes, reset select all pages mode
                if (selectAllPagesInput) {
                    selectAllPagesInput.value = '0';
                }
                updateBulkActions();
            });
        });

        // Handle Approve All
        if (btnApproveAll) {
            btnApproveAll.addEventListener('click', function() {
                const isGlobal = selectAllPagesInput && selectAllPagesInput.value === '1';
                const selectedIds = Array.from(document.querySelectorAll('.increment-checkbox:checked'))
                    .map(cb => cb.value);
                const count = isGlobal ? totalPending : selectedIds.length;
                
                if (!isGlobal && selectedIds.length === 0) {
                    Swal.fire({
                        text: 'Please select at least one increment to approve.',
                        icon: 'warning',
                        confirmButtonText: 'OK'
                    });
                    return;
                }

                Swal.fire({
                    title: 'Approve Selected Increments',
                    text: `Are you sure you want to approve ${count} increment(s)?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, approve!',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#28a745'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Submit bulk approve
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = '{{ route("promotions.bulk-approve") }}';
                        
                        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                        const csrfInput = document.createElement('input');
                        csrfInput.type = 'hidden';
                        csrfInput.name = '_token';
                        csrfInput.value = csrfToken;
                        form.appendChild(csrfInput);

                        // Add select_all_pages flag and filters
                        const selectAllInput = document.createElement('input');
                        selectAllInput.type = 'hidden';
                        selectAllInput.name = 'select_all_pages';
                        selectAllInput.value = isGlobal ? '1' : '0';
                        form.appendChild(selectAllInput);

                        if (isGlobal) {
                            // Add filter values
                            ['filter_search', 'filter_status', 'filter_department', 'filter_year'].forEach(filterId => {
                                const filterInput = document.getElementById(filterId);
                                if (filterInput && filterInput.value) {
                                    const input = document.createElement('input');
                                    input.type = 'hidden';
                                    input.name = filterId;
                                    input.value = filterInput.value;
                                    form.appendChild(input);
                                }
                            });
                        } else {
                            // Add selected IDs
                            selectedIds.forEach(id => {
                                const input = document.createElement('input');
                                input.type = 'hidden';
                                input.name = 'increment_ids[]';
                                input.value = id;
                                form.appendChild(input);
                            });
                        }

                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            });
        }

        // Handle Reject All
        if (btnRejectAll) {
            btnRejectAll.addEventListener('click', function() {
                const isGlobal = selectAllPagesInput && selectAllPagesInput.value === '1';
                const selectedIds = Array.from(document.querySelectorAll('.increment-checkbox:checked'))
                    .map(cb => cb.value);
                const count = isGlobal ? totalPending : selectedIds.length;
                
                if (!isGlobal && selectedIds.length === 0) {
                    Swal.fire({
                        text: 'Please select at least one increment to reject.',
                        icon: 'warning',
                        confirmButtonText: 'OK'
                    });
                    return;
                }

                Swal.fire({
                    title: `Reject ${count} Increment(s)`,
                    text: 'Please provide a reason for rejection:',
                    input: 'textarea',
                    inputPlaceholder: 'Reason for rejection...',
                    showCancelButton: true,
                    confirmButtonText: 'Reject',
                    cancelButtonText: 'Cancel',
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
                        // Submit bulk reject
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = '{{ route("promotions.bulk-reject") }}';
                        
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

                        // Add select_all_pages flag and filters
                        const selectAllInput = document.createElement('input');
                        selectAllInput.type = 'hidden';
                        selectAllInput.name = 'select_all_pages';
                        selectAllInput.value = isGlobal ? '1' : '0';
                        form.appendChild(selectAllInput);

                        if (isGlobal) {
                            // Add filter values
                            ['filter_search', 'filter_status', 'filter_department', 'filter_year'].forEach(filterId => {
                                const filterInput = document.getElementById(filterId);
                                if (filterInput && filterInput.value) {
                                    const input = document.createElement('input');
                                    input.type = 'hidden';
                                    input.name = filterId;
                                    input.value = filterInput.value;
                                    form.appendChild(input);
                                }
                            });
                        } else {
                            // Add selected IDs
                            selectedIds.forEach(id => {
                                const input = document.createElement('input');
                                input.type = 'hidden';
                                input.name = 'increment_ids[]';
                                input.value = id;
                                form.appendChild(input);
                            });
                        }

                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            });
        }
        
        // Initial update
        updateBulkActions();
    });

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
