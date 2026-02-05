<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create_disciplinary')): ?>
    <div class="mb-3">
        <a href="<?php echo e(route('disciplinary.index')); ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Disciplinary Actions
        </a>
    </div>
    
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">New Disciplinary Action</h6>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <form action="<?php echo e(route('disciplinary.store')); ?>" method="POST">
                                <?php echo csrf_field(); ?>
                                <div class="form-group mb-3">
                                    <label for="employee_id" class="form-label">Select Employee *</label>
                                    <select name="employee_id" id="employee_id" class="form-select" required>
                                        <option value="">Select an employee</option>
                                    </select>
                                    <?php $__errorArgs = ['employee_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="text-danger"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="action_type" class="form-label">Action Type *</label>
                                    <select name="action_type" class="form-select" required>
                                        <option value="">Select action type</option>
                                        <option value="suspended">Suspended</option>
                                        <option value="hold">Hold</option>
                                        <option value="query">Query</option>
                                        <option value="terminated">Terminated</option>
                                    </select>
                                    <?php $__errorArgs = ['action_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="text-danger"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="description" class="form-label">Description *</label>
                                    <textarea name="description" class="form-control" rows="3" required></textarea>
                                    <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="text-danger"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="action_date" class="form-label">Action Date *</label>
                                    <input type="date" name="action_date" class="form-control" required>
                                    <?php $__errorArgs = ['action_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="text-danger"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="status" class="form-label">Status *</label>
                                    <select name="status" class="form-select" required>
                                        <option value="Open">Open</option>
                                    </select>
                                    <?php $__errorArgs = ['status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="text-danger"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>

                                <div class="d-flex justify-content-start mt-4">
                                    <button type="submit" class="btn btn-primary">Save Disciplinary Action</button>
                                    <a href="<?php echo e(route('disciplinary.index')); ?>" class="btn btn-secondary ms-2">Cancel</a>
                                </div>
                            </form>
                        </div>

                        <div class="col-md-6">
                            <!-- Search and Filter Form -->
                            <form action="<?php echo e(route('disciplinary.create')); ?>" method="GET" class="mb-4">
                                <div class="row">
                                    <div class="col-md-6">
                                        <input type="text" name="search" id="employeeSearch" class="form-control" placeholder="Search by name or staff ID" value="<?php echo e(request('search')); ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <select name="department" class="form-select">
                                            <option value="">All Departments</option>
                                            <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $department): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($department->department_id); ?>" <?php echo e(request('department') == $department->department_id ? 'selected' : ''); ?>>
                                                    <?php echo e($department->department_name); ?>

                                                </option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="submit" class="btn btn-primary">Filter</button>
                                        <a href="<?php echo e(route('disciplinary.create')); ?>" class="btn btn-secondary">Clear</a>
                                    </div>
                                </div>
                            </form>

                            <h6>Active Employees</h6>
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Staff ID</th>
                                            <th>Department</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="employeesTable">
                                        <?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td><?php echo e($employee->first_name); ?> <?php echo e($employee->surname); ?></td>
                                            <td><?php echo e($employee->staff_no); ?></td>
                                            <td><?php echo e($employee->department->department_name ?? 'N/A'); ?></td>
                                            <td>
                                                <span class="badge bg-success"><?php echo e($employee->status); ?></span>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-primary select-employee"
                                                        data-id="<?php echo e($employee->employee_id); ?>"
                                                        data-name="<?php echo e($employee->first_name); ?> <?php echo e($employee->surname); ?>">
                                                    Select
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <div class="d-flex justify-content-center mt-3">
                                <?php echo e($employees->appends(request()->query())->links('pagination::bootstrap-4')); ?>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="alert alert-warning">
        You don't have permission to create disciplinary actions.
    </div>
    <?php endif; ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Select employee functionality
        document.querySelectorAll('.select-employee').forEach(button => {
            button.addEventListener('click', function() {
                const employeeId = this.getAttribute('data-id');
                const employeeName = this.getAttribute('data-name');

                // Set the selected employee in the dropdown
                const select = document.getElementById('employee_id');

                // Check if option already exists, if not create it
                let option = select.querySelector(`option[value="${employeeId}"]`);
                if (!option) {
                    option = document.createElement('option');
                    option.value = employeeId;
                    option.textContent = employeeName;
                    select.appendChild(option);
                }

                // Select the option
                option.selected = true;

                // Scroll to the form section
                document.querySelector('#employee_id').scrollIntoView({ behavior: 'smooth' });
            });
        });
    });
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Rowwww\Herd\hrm_payroll_system_design\resources\views/disciplinary/create.blade.php ENDPATH**/ ?>