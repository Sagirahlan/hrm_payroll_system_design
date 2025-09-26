<a class="nav-link" href="{{ route('payroll.index') }}">
    <i class="fas fa-money-bill-wave"></i> Payroll
</a>
<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
        <i class="fas fa-users-cog"></i> Manage All Deductions/Additions
    </a>
    <ul class="dropdown-menu">
        <li>
            <a class="dropdown-item" href="{{ route('payroll.adjustments.manage') }}">
                <i class="fas fa-users"></i> Employee Adjustments
            </a>
        </li>
        <li><hr class="dropdown-divider"></li>
        <li>
            <a class="dropdown-item" href="{{ route('deduction-types.index') }}">
                <i class="fas fa-minus-circle"></i> Deduction Types
            </a>
        </li>
        <li>
            <a class="dropdown-item" href="{{ route('addition-types.index') }}">
                <i class="fas fa-plus-circle"></i> Addition Types
            </a>
        </li>
    </ul>
</li>
<a class="nav-link" href="{{ route('payroll.generate') }}">
    <i class="fas fa-cogs"></i> Generate Payroll
</a>
<a class="nav-link" href="{{ route('payroll.export') }}">
    <i class="fas fa-file-export"></i> Export Payroll
</a>
