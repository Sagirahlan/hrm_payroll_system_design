@extends('layouts.app')

@section('title', 'Merge Legacy Employees')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">
                        <i class="fas fa-code-merge me-2"></i>Merge Legacy Employees
                    </h4>
                    <a href="{{ route('pensioners.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i> Back to Pensioners
                    </a>
                </div>
                <div class="card-body">
                    {{-- Summary Stats --}}
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center py-3">
                                    <h3 class="mb-0" id="total-legacy">{{ count($pairs) + count($unmatched) }}</h3>
                                    <small>Total Legacy Employees</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center py-3">
                                    <h3 class="mb-0" id="total-matched">{{ count($pairs) }}</h3>
                                    <small>Matched (Ready to Merge)</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-dark">
                                <div class="card-body text-center py-3">
                                    <h3 class="mb-0" id="total-unmatched">{{ count($unmatched) }}</h3>
                                    <small>No Match Found</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center py-3">
                                    <h3 class="mb-0" id="total-merged">0</h3>
                                    <small>Merged This Session</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if(count($pairs) > 0)
                    {{-- Merge All Button --}}
                    <div class="mb-3">
                        <button class="btn btn-success" id="merge-all-btn" onclick="mergeAll()">
                            <i class="fas fa-check-double me-1"></i> Merge All Matched ({{ count($pairs) }})
                        </button>
                    </div>

                    {{-- Matched Pairs Table --}}
                    <h5 class="text-success mb-3"><i class="fas fa-check-circle me-1"></i> Matched Legacy Employees</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-sm">
                            <thead class="table-dark">
                                <tr>
                                    <th colspan="4" class="text-center bg-danger text-white">Legacy Employee (Will Be Deleted)</th>
                                    <th class="text-center bg-secondary text-white" style="width: 50px;">→</th>
                                    <th colspan="4" class="text-center bg-success text-white">Real Employee (Will Keep)</th>
                                    <th class="text-center" style="width: 120px;">Action</th>
                                </tr>
                                <tr>
                                    <th>Staff No</th>
                                    <th>Name</th>
                                    <th>Department</th>
                                    <th>Pensioner?</th>
                                    <th></th>
                                    <th>Staff No</th>
                                    <th>Name</th>
                                    <th>Department</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="pairs-tbody">
                                @foreach($pairs as $index => $pair)
                                <tr id="pair-row-{{ $index }}" data-legacy-id="{{ $pair['legacy']->employee_id }}" data-real-id="{{ $pair['real']->employee_id }}">
                                    <td><span class="badge bg-secondary">{{ $pair['legacy']->staff_no }}</span></td>
                                    <td>{{ $pair['legacy']->first_name }} {{ $pair['legacy']->surname }}</td>
                                    <td>{{ $pair['legacy']->department->department_name ?? 'N/A' }}</td>
                                    <td>
                                        @if($pair['has_pensioner'])
                                            <span class="badge bg-success">Yes</span>
                                        @else
                                            <span class="badge bg-secondary">No</span>
                                        @endif
                                    </td>
                                    <td class="text-center"><i class="fas fa-arrow-right text-primary"></i></td>
                                    <td><span class="badge bg-primary">{{ $pair['real']->staff_no }}</span></td>
                                    <td class="fw-bold">{{ $pair['real']->first_name }} {{ $pair['real']->surname }}</td>
                                    <td>{{ $pair['real']->department->department_name ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge {{ $pair['real']->status === 'Active' ? 'bg-success' : ($pair['real']->status === 'Hold' ? 'bg-warning text-dark' : 'bg-info') }}">
                                            {{ $pair['real']->status }}
                                        </span>
                                        @if($pair['real_has_pensioner'])
                                            <span class="badge bg-info">Has Pensioner</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-success merge-btn" onclick="mergePair({{ $index }}, {{ $pair['legacy']->employee_id }}, {{ $pair['real']->employee_id }})">
                                            <i class="fas fa-check me-1"></i>Merge
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif

                    @if(count($unmatched) > 0)
                    {{-- Unmatched Table --}}
                    <h5 class="text-warning mt-4 mb-3"><i class="fas fa-exclamation-triangle me-1"></i> Unmatched Legacy Employees (No Real Match Found)</h5>
                    <p class="text-muted small">These are legacy employees that could not be matched to any real employee. They may be genuinely unique pensioners, or the matching wasn't able to find their record. You can keep them or delete them.</p>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-sm">
                            <thead class="table-warning">
                                <tr>
                                    <th>Staff No</th>
                                    <th>Name</th>
                                    <th>Department</th>
                                    <th>Status</th>
                                    <th>Pensioner?</th>
                                    <th style="width: 150px;">Action</th>
                                </tr>
                            </thead>
                            <tbody id="unmatched-tbody">
                                @foreach($unmatched as $index => $legacy)
                                <tr id="unmatched-row-{{ $index }}">
                                    <td><span class="badge bg-secondary">{{ $legacy->staff_no }}</span></td>
                                    <td>{{ $legacy->first_name }} {{ $legacy->surname }}</td>
                                    <td>{{ $legacy->department->department_name ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge bg-info">{{ $legacy->status }}</span>
                                    </td>
                                    <td>
                                        @if($legacy->pensioner)
                                            <span class="badge bg-success">Yes — ₦{{ number_format($legacy->pensioner->pension_amount, 2) }}</span>
                                        @else
                                            <span class="badge bg-secondary">No</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-success" title="Keep as standalone pensioner">
                                            <i class="fas fa-check"></i> Keep
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" onclick="deleteLegacy({{ $index }}, {{ $legacy->employee_id }})" title="Delete this legacy employee">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif

                    @if(count($pairs) === 0 && count($unmatched) === 0)
                    <div class="alert alert-success text-center py-4">
                        <i class="fas fa-check-circle fa-3x mb-3"></i>
                        <h5>No Legacy Employees Found</h5>
                        <p class="mb-0">All legacy employees have been merged or there are none to merge.</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
let mergedCount = 0;

function mergePair(rowIndex, legacyId, realId) {
    const row = document.getElementById('pair-row-' + rowIndex);
    const btn = row.querySelector('.merge-btn');
    
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

    fetch('{{ route("pensioners.legacy.merge.process") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({ legacy_id: legacyId, real_id: realId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            row.style.backgroundColor = '#d4edda';
            row.style.transition = 'all 0.5s';
            setTimeout(() => {
                row.style.opacity = '0';
                setTimeout(() => row.remove(), 500);
            }, 500);
            mergedCount++;
            document.getElementById('total-merged').textContent = mergedCount;
            updateMatchedCount();
        } else {
            alert('Error: ' + data.message);
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check me-1"></i>Merge';
        }
    })
    .catch(error => {
        alert('Network error: ' + error.message);
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check me-1"></i>Merge';
    });
}

function mergeAll() {
    if (!confirm('Are you sure you want to merge ALL matched legacy employees? This action cannot be undone.')) return;

    const rows = document.querySelectorAll('#pairs-tbody tr');
    const mergeAllBtn = document.getElementById('merge-all-btn');
    mergeAllBtn.disabled = true;
    mergeAllBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Merging...';

    let promises = [];
    rows.forEach((row) => {
        const legacyId = row.dataset.legacyId;
        const realId = row.dataset.realId;
        const rowIndex = row.id.replace('pair-row-', '');

        promises.push(
            new Promise(resolve => setTimeout(resolve, promises.length * 200)).then(() =>
                fetch('{{ route("pensioners.legacy.merge.process") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ legacy_id: legacyId, real_id: realId })
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        row.style.backgroundColor = '#d4edda';
                        setTimeout(() => {
                            row.style.opacity = '0';
                            setTimeout(() => row.remove(), 300);
                        }, 300);
                        mergedCount++;
                        document.getElementById('total-merged').textContent = mergedCount;
                    }
                    return data;
                })
            )
        );
    });

    Promise.all(promises).then(() => {
        mergeAllBtn.innerHTML = '<i class="fas fa-check-double me-1"></i> All Done!';
        updateMatchedCount();
    });
}

function deleteLegacy(rowIndex, legacyId) {
    if (!confirm('Are you sure you want to DELETE this legacy employee and their pensioner record? This cannot be undone.')) return;

    const row = document.getElementById('unmatched-row-' + rowIndex);

    fetch('{{ route("pensioners.legacy.delete") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({ legacy_id: legacyId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            row.style.backgroundColor = '#f8d7da';
            row.style.transition = 'all 0.5s';
            setTimeout(() => {
                row.style.opacity = '0';
                setTimeout(() => row.remove(), 500);
            }, 500);
        } else {
            alert('Error: ' + data.message);
        }
    });
}

function updateMatchedCount() {
    const remaining = document.querySelectorAll('#pairs-tbody tr').length;
    document.getElementById('total-matched').textContent = remaining;
    const mergeAllBtn = document.getElementById('merge-all-btn');
    if (mergeAllBtn) {
        mergeAllBtn.querySelector('.me-1')?.parentElement && (mergeAllBtn.innerHTML = `<i class="fas fa-check-double me-1"></i> Merge All Matched (${remaining})`);
    }
}
</script>
@endsection
