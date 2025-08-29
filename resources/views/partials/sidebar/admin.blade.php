<!-- Employee Management Dropdown -->
<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
        <i class="fas fa-users"></i> Employee Management
    </a>
    <ul class="dropdown-menu">
        <li>
            <a class="dropdown-item" href="{{ route('employees.index') }}">
                <i class="fas fa-list"></i> Employee List
            </a>
        </li>
        <li>
            <a class="dropdown-item" href="{{ route('employees.create') }}">
                <i class="fas fa-user-plus"></i> Add Employee
            </a>
        </li>
        <li><hr class="dropdown-divider"></li>
        <li>
            <a class="dropdown-item" href="{{ route('pending-changes.index') }}">
                <i class="fas fa-hourglass-half"></i> Pending Changes
            </a>
        </li>
        <li><hr class="dropdown-divider"></li>
        <li>
            <a class="dropdown-item" href="{{ route('departments.index') }}">
                <i class="fas fa-sitemap"></i> Departments
            </a>
        </li>
        <li>
            <a class="dropdown-item" href="{{ route('grade-levels.index') }}">
                <i class="fas fa-money-check"></i> Salary Scales
            </a>
        </li>
    </ul>
</li>

<!-- User Management -->
<li class="nav-item">
    <a class="nav-link" href="{{ route('users.index') }}">
        <i class="fas fa-user-cog"></i> Manage Users
    </a>
</li>

<!-- Audit Logs -->
<li class="nav-item">
    <a class="nav-link" href="{{ route('audit-trails.index') }}">
        <i class="fas fa-clipboard-list"></i> Audit Logs
    </a>
</li>