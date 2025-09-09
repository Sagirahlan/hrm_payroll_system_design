<a class="nav-link" href="{{ route('employees.index') }}">
    <i class="fas fa-users"></i> Employees
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
<a class="nav-link" href="{{ route('biometrics.index') }}">
    <i class="fas fa-fingerprint"></i> Biometrics
</a>
<a class="nav-link" href="{{ route('disciplinary.index') }}">
    <i class="fas fa-gavel"></i> Disciplinary
</a>
<a class="nav-link" href="{{ route('retirements.index') }}">
    <i class="fas fa-user-slash"></i> Retirements
</a>
<a class="nav-link" href="{{ route('pensioners.index') }}">
    <i class="fas fa-user-shield"></i> Pensioners
</a>
<a class="nav-link" href="{{ route('sms.index') }}">
    <i class="fas fa-sms"></i> SMS Notifications
</a>
