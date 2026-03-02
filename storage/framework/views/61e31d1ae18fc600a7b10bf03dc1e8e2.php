

<?php $__env->startSection('title', 'Merge Legacy Employees'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">
                        <i class="fas fa-code-merge me-2"></i>Merge Legacy Employees
                    </h4>
                    <a href="<?php echo e(route('pensioners.index')); ?>" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i> Back to Pensioners
                    </a>
                </div>
                <div class="card-body">
                    
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center py-3">
                                    <h3 class="mb-0" id="total-legacy"><?php echo e(count($pairs) + count($unmatched)); ?></h3>
                                    <small>Total Legacy Employees</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center py-3">
                                    <h3 class="mb-0" id="total-matched"><?php echo e(count($pairs)); ?></h3>
                                    <small>Matched (Ready to Merge)</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-dark">
                                <div class="card-body text-center py-3">
                                    <h3 class="mb-0" id="total-unmatched"><?php echo e(count($unmatched)); ?></h3>
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

                    <?php if(count($pairs) > 0): ?>
                    
                    <div class="mb-3">
                        <button class="btn btn-success" id="merge-all-btn" onclick="mergeAll()">
                            <i class="fas fa-check-double me-1"></i> Merge All Matched (<?php echo e(count($pairs)); ?>)
                        </button>
                    </div>

                    
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
                                <?php $__currentLoopData = $pairs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $pair): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr id="pair-row-<?php echo e($index); ?>" data-legacy-id="<?php echo e($pair['legacy']->employee_id); ?>" data-real-id="<?php echo e($pair['real']->employee_id); ?>">
                                    <td><span class="badge bg-secondary"><?php echo e($pair['legacy']->staff_no); ?></span></td>
                                    <td><?php echo e($pair['legacy']->first_name); ?> <?php echo e($pair['legacy']->surname); ?></td>
                                    <td><?php echo e($pair['legacy']->department->department_name ?? 'N/A'); ?></td>
                                    <td>
                                        <?php if($pair['has_pensioner']): ?>
                                            <span class="badge bg-success">Yes</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">No</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center"><i class="fas fa-arrow-right text-primary"></i></td>
                                    <td><span class="badge bg-primary"><?php echo e($pair['real']->staff_no); ?></span></td>
                                    <td class="fw-bold"><?php echo e($pair['real']->first_name); ?> <?php echo e($pair['real']->surname); ?></td>
                                    <td><?php echo e($pair['real']->department->department_name ?? 'N/A'); ?></td>
                                    <td>
                                        <span class="badge <?php echo e($pair['real']->status === 'Active' ? 'bg-success' : ($pair['real']->status === 'Hold' ? 'bg-warning text-dark' : 'bg-info')); ?>">
                                            <?php echo e($pair['real']->status); ?>

                                        </span>
                                        <?php if($pair['real_has_pensioner']): ?>
                                            <span class="badge bg-info">Has Pensioner</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-success merge-btn" onclick="mergePair(<?php echo e($index); ?>, <?php echo e($pair['legacy']->employee_id); ?>, <?php echo e($pair['real']->employee_id); ?>)">
                                            <i class="fas fa-check me-1"></i>Merge
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>

                    <?php if(count($unmatched) > 0): ?>
                    
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
                                <?php $__currentLoopData = $unmatched; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $legacy): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr id="unmatched-row-<?php echo e($index); ?>">
                                    <td><span class="badge bg-secondary"><?php echo e($legacy->staff_no); ?></span></td>
                                    <td><?php echo e($legacy->first_name); ?> <?php echo e($legacy->surname); ?></td>
                                    <td><?php echo e($legacy->department->department_name ?? 'N/A'); ?></td>
                                    <td>
                                        <span class="badge bg-info"><?php echo e($legacy->status); ?></span>
                                    </td>
                                    <td>
                                        <?php if($legacy->pensioner): ?>
                                            <span class="badge bg-success">Yes — ₦<?php echo e(number_format($legacy->pensioner->pension_amount, 2)); ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">No</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-success" title="Keep as standalone pensioner">
                                            <i class="fas fa-check"></i> Keep
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" onclick="deleteLegacy(<?php echo e($index); ?>, <?php echo e($legacy->employee_id); ?>)" title="Delete this legacy employee">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>

                    <?php if(count($pairs) === 0 && count($unmatched) === 0): ?>
                    <div class="alert alert-success text-center py-4">
                        <i class="fas fa-check-circle fa-3x mb-3"></i>
                        <h5>No Legacy Employees Found</h5>
                        <p class="mb-0">All legacy employees have been merged or there are none to merge.</p>
                    </div>
                    <?php endif; ?>
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

    fetch('<?php echo e(route("pensioners.legacy.merge.process")); ?>', {
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
                fetch('<?php echo e(route("pensioners.legacy.merge.process")); ?>', {
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

    fetch('<?php echo e(route("pensioners.legacy.delete")); ?>', {
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
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Rowwww\Herd\hrm_payroll_system_design\resources\views/pensioners/merge_legacy.blade.php ENDPATH**/ ?>