<?php $__env->startSection('title', 'Pensioner Details'); ?>

<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Pensioner Details</h4>
                    <a href="<?php echo e(route('pensioners.index')); ?>" class="btn btn-secondary float-end">Back to Pensioners</a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th>Staff No:</th>
                                    <td><?php echo e($pensioner->employee->staff_no ?? $pensioner->employee_id); ?></td>
                                </tr>
                                <tr>
                                    <th>Full Name:</th>
                                    <td><?php echo e($pensioner->full_name); ?></td>
                                </tr>
                                <tr>
                                    <th>Surname:</th>
                                    <td><?php echo e($pensioner->surname); ?></td>
                                </tr>
                                <tr>
                                    <th>First Name:</th>
                                    <td><?php echo e($pensioner->first_name); ?></td>
                                </tr>
                                <tr>
                                    <th>Middle Name:</th>
                                    <td><?php echo e($pensioner->middle_name ?: 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <th>Email:</th>
                                    <td><?php echo e($pensioner->email ?: 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <th>Phone Number:</th>
                                    <td><?php echo e($pensioner->phone_number ?: 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <th>Date of Birth:</th>
                                    <td><?php echo e(\Carbon\Carbon::parse($pensioner->date_of_birth)->format('d M, Y')); ?></td>
                                </tr>
                                <tr>
                                    <th>Place of Birth:</th>
                                    <td><?php echo e($pensioner->place_of_birth ?: 'N/A'); ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th>Date of First Appointment:</th>
                                    <td><?php echo e(\Carbon\Carbon::parse($pensioner->date_of_first_appointment)->format('d M, Y')); ?></td>
                                </tr>
                                <tr>
                                    <th>Date of Retirement:</th>
                                    <td><?php echo e(\Carbon\Carbon::parse($pensioner->date_of_retirement)->format('d M, Y')); ?></td>
                                </tr>
                                <tr>
                                    <th>Retirement Reason:</th>
                                    <td><?php echo e($pensioner->retirement_reason ?: 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <th>Retirement Type:</th>
                                    <td><?php echo e($pensioner->retirement_type); ?></td>
                                </tr>
                                <tr>
                                    <th>Department:</th>
                                    <td><?php echo e($pensioner->department ? $pensioner->department->department_name : 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <th>Rank:</th>
                                    <td><?php echo e($pensioner->rank ? $pensioner->rank->name : 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <th>Grade Level:</th>
                                    <td><?php echo e($pensioner->gradeLevel ? $pensioner->gradeLevel->name : 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <th>Step:</th>
                                    <td><?php echo e($pensioner->step ? $pensioner->step->name : 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <th>Salary Scale:</th>
                                    <td><?php echo e($pensioner->salaryScale ? $pensioner->salaryScale->full_name : 'N/A'); ?></td>
                                </tr>
                                <?php if($expectedRetirementDate): ?>
                                <tr>
                                    <th>Expected Retirement Date:</th>
                                    <td><?php echo e(\Carbon\Carbon::parse($expectedRetirementDate)->format('d M, Y')); ?></td>
                                </tr>
                                <?php endif; ?>
                                <?php if($overstayedDays > 0): ?>
                                <tr>
                                    <th>Overstayed Days:</th>
                                    <td class="text-<?php echo e($graceWaived ? 'warning' : 'danger'); ?> fw-bold"><?php echo e($overstayedDays); ?> days</td>
                                </tr>
                                <tr>
                                    <th>Grace Period Status:</th>
                                    <td class="text-<?php echo e($graceWaived ? 'success' : 'danger'); ?> fw-bold">
                                        <?php echo e($gracePeriodStatus); ?>

                                    </td>
                                </tr>
                                <?php if(!$graceWaived && $overstayAmount > 0): ?>
                                <tr>
                                    <th>Overstay Deduction:</th>
                                    <td class="text-danger fw-bold">₦<?php echo e(number_format($overstayAmount, 2)); ?></td>
                                </tr>
                                <?php endif; ?>
                                <?php endif; ?>
                                <?php if($overstayRemark): ?>
                                <tr>
                                    <th>Overstay Remark:</th>
                                    <td class="text-danger fw-bold"><?php echo e($overstayRemark); ?></td>
                                </tr>
                                <?php endif; ?>
                            </table>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <h5>Financial Information</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <th>Pension Amount:</th>
                                    <td><?php echo e(number_format($pensioner->pension_amount, 2)); ?></td>
                                </tr>
                                <tr>
                                    <th>Gratuity Amount:</th>
                                    <td><?php echo e(number_format($pensioner->gratuity_amount, 2)); ?></td>
                                </tr>
                                <tr>
                                    <th>Total Death Gratuity:</th>
                                    <td><?php echo e(number_format($pensioner->total_death_gratuity, 2)); ?></td>
                                </tr>
                                <tr>
                                    <th>Years of Service:</th>
                                    <td><?php echo e($pensioner->years_of_service); ?></td>
                                </tr>
                                <tr>
                                    <th>Pension Percentage:</th>
                                    <td><?php echo e($pensioner->pension_percentage); ?>%</td>
                                </tr>
                                <tr>
                                    <th>Gratuity Percentage:</th>
                                    <td><?php echo e($pensioner->gratuity_percentage); ?>%</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5>Banking Information</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <th>Bank:</th>
                                    <td><?php echo e($pensioner->bank ? $pensioner->bank->bank_name : 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <th>Account Number:</th>
                                    <td><?php echo e($pensioner->account_number ?: 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <th>Account Name:</th>
                                    <td><?php echo e($pensioner->account_name ?: 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td>
                                        <span class="badge <?php echo e($pensioner->status === 'Active' ? 'bg-success' : 'bg-secondary'); ?>">
                                            <?php echo e($pensioner->status); ?>

                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Local Govt. Area:</th>
                                    <td><?php echo e($pensioner->localGovArea ? $pensioner->localGovArea->lga : 'N/A'); ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h5>Additional Information</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <th>Address:</th>
                                    <td><?php echo e($pensioner->address ?: 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <th>Next of Kin Name:</th>
                                    <td><?php echo e($pensioner->next_of_kin_name ?: 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <th>Next of Kin Phone:</th>
                                    <td><?php echo e($pensioner->next_of_kin_phone ?: 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <th>Next of Kin Address:</th>
                                    <td><?php echo e($pensioner->next_of_kin_address ?: 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <th>Remarks:</th>
                                    <td><?php echo e($pensioner->remarks ?: 'N/A'); ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-12">
                            <a href="<?php echo e(route('pensioners.edit', $pensioner->id)); ?>" class="btn btn-primary">Edit Pensioner</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Rowwww\Herd\hrm_payroll_system_design\resources\views/pensioners/show.blade.php ENDPATH**/ ?>