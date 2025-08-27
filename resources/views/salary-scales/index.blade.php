@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="card border-primary shadow">
        <div class="card-header" style="background-color: skyblue; color: white;">
            <h5 class="mb-0">Manage Salary Scales</h5>
        </div>
        <div class="card-body">
            <!-- Search and Filter Section -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card bg-light">
                        <div class="card-body">
                            <form method="GET" action="{{ route('salary-scales.index') }}" id="filterForm">
                                <div class="row g-3">
                                    <!-- Search Input -->
                                    <div class="col-md-4">
                                        <label for="search" class="form-label">Search</label>
                                        <input type="text" 
                                               class="form-control" 
                                               id="search" 
                                               name="search" 
                                               value="{{ request('search') }}" 
                                               placeholder="Search by scale name or description...">
                                    </div>

                                    <!-- Grade Level Filter -->
                                    <div class="col-md-2">
                                        <label for="grade_level" class="form-label">Grade Level</label>
                                        <select class="form-select" id="grade_level" name="grade_level">
                                            <option value="">All Grades</option>
                                            @foreach($gradeLevels as $grade)
                                                <option value="{{ $grade }}" {{ request('grade_level') == $grade ? 'selected' : '' }}>
                                                    Grade {{ $grade }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Step Level Filter -->
                                    <div class="col-md-2">
                                        <label for="step_level" class="form-label">Step Level</label>
                                        <select class="form-select" id="step_level" name="step_level">
                                            <option value="">All Steps</option>
                                            @foreach($stepLevels as $step)
                                                <option value="{{ $step }}" {{ request('step_level') == $step ? 'selected' : '' }}>
                                                    Step {{ $step }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Minimum Salary Filter -->
                                    <div class="col-md-2">
                                        <label for="min_salary" class="form-label">Min Salary (₦)</label>
                                        <input type="number" 
                                               class="form-control" 
                                               id="min_salary" 
                                               name="min_salary" 
                                               value="{{ request('min_salary') }}" 
                                               placeholder="0" 
                                               step="1000">
                                    </div>

                                    <!-- Maximum Salary Filter -->
                                    <div class="col-md-2">
                                        <label for="max_salary" class="form-label">Max Salary (₦)</label>
                                        <input type="number" 
                                               class="form-control" 
                                               id="max_salary" 
                                               name="max_salary" 
                                               value="{{ request('max_salary') }}" 
                                               placeholder="1000000" 
                                               step="1000">
                                    </div>
                                </div>

                                <div class="row mt-3">
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary me-2">
                                            <i class="fas fa-search"></i> Search & Filter
                                        </button>
                                        <a href="{{ route('salary-scales.index') }}" class="btn btn-secondary me-2">
                                            <i class="fas fa-refresh"></i> Reset
                                        </a>
                                        <button type="button" class="btn btn-outline-primary" id="toggleFilters">
                                            <i class="fas fa-filter"></i> Toggle Advanced Filters
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Results Summary -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="mb-3">
                        <a href="{{ route('salary-scales.create') }}"
                           class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add New Salary Scale
                        </a>
                    </div>
                </div>
                <div class="col-md-6 text-end">
                    <p class="text-muted mb-0">
                        Showing {{ $salaryScales->firstItem() ?? 0 }} to {{ $salaryScales->lastItem() ?? 0 }} 
                        of {{ $salaryScales->total() }} results
                    </p>
                </div>
            </div>

            <!-- Sorting Controls -->
            <div class="row mb-3">
                <div class="col-12">
                    <div class="d-flex align-items-center">
                        <span class="me-2">Sort by:</span>
                        <div class="btn-group" role="group">
                            <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'scale_name', 'sort_order' => request('sort_order') === 'desc' ? 'asc' : 'desc']) }}" 
                               class="btn btn-sm {{ request('sort_by') === 'scale_name' || !request('sort_by') ? 'btn-primary' : 'btn-outline-primary' }}">
                                Scale Name 
                                @if(request('sort_by') === 'scale_name' || !request('sort_by'))
                                    <i class="fas fa-sort-{{ request('sort_order') === 'desc' ? 'down' : 'up' }}"></i>
                                @endif
                            </a>
                            <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'basic_salary', 'sort_order' => request('sort_order') === 'desc' ? 'asc' : 'desc']) }}" 
                               class="btn btn-sm {{ request('sort_by') === 'basic_salary' ? 'btn-primary' : 'btn-outline-primary' }}">
                                Salary 
                                @if(request('sort_by') === 'basic_salary')
                                    <i class="fas fa-sort-{{ request('sort_order') === 'desc' ? 'down' : 'up' }}"></i>
                                @endif
                            </a>
                            <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'grade_level', 'sort_order' => request('sort_order') === 'desc' ? 'asc' : 'desc']) }}" 
                               class="btn btn-sm {{ request('sort_by') === 'grade_level' ? 'btn-primary' : 'btn-outline-primary' }}">
                                Grade 
                                @if(request('sort_by') === 'grade_level')
                                    <i class="fas fa-sort-{{ request('sort_order') === 'desc' ? 'down' : 'up' }}"></i>
                                @endif
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-striped table-bordered align-items-center mb-0">
                    <thead class="table-primary">
                        <tr>
                            <th>Scale Name</th>
                            <th>Basic Salary</th>
                            <th>Grade Level</th>
                            <th>Step Level</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($salaryScales as $scale)
                            <tr>
                                <td>
                                    <strong>{{ $scale->scale_name }}</strong>
                                </td>
                                <td>
                                    <span class="badge bg-success">₦{{ number_format($scale->basic_salary, 2) }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $scale->grade_level }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-warning">{{ $scale->step_level }}</span>
                                </td>
                                <td>{{ $scale->description ?? 'N/A' }}</td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" id="actionDropdown{{ $scale->scale_id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                            Actions
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="actionDropdown{{ $scale->scale_id }}">
                                            <li>
                                                <a class="dropdown-item" href="{{ route('salary-scales.edit', $scale->scale_id) }}">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                            </li>
                                            <li>
                                                <form action="{{ route('salary-scales.destroy', $scale->scale_id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this salary scale?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="fas fa-search fa-2x mb-2"></i><br>
                                    @if(request()->hasAny(['search', 'grade_level', 'step_level', 'min_salary', 'max_salary']))
                                        No salary scales found matching your search criteria.
                                        <br><a href="{{ route('salary-scales.index') }}" class="btn btn-sm btn-primary mt-2">Clear Filters</a>
                                    @else
                                        No salary scales found.
                                        <br><a href="{{ route('salary-scales.create') }}" class="btn btn-sm btn-primary mt-2">Add First Salary Scale</a>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Pagination -->
<div class="mt-3">
    {{ $salaryScales->links('pagination::bootstrap-5') }}
</div>

<style>
    .advanced-filters {
        display: none;
    }
    .advanced-filters.show {
        display: block;
    }
    .badge {
        font-size: 0.75em;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-submit form on filter change (optional)
    const filterInputs = document.querySelectorAll('#filterForm select, #filterForm input[type="number"]');
    filterInputs.forEach(input => {
        input.addEventListener('change', function() {
            // Uncomment the line below to auto-submit on change
            // document.getElementById('filterForm').submit();
        });
    });

    // Toggle advanced filters
    const toggleBtn = document.getElementById('toggleFilters');
    const advancedFilters = document.querySelectorAll('.col-md-2');
    
    toggleBtn?.addEventListener('click', function() {
        const isVisible = advancedFilters[1]?.style.display !== 'none';
        advancedFilters.forEach((filter, index) => {
            if (index >= 1) { // Keep search visible, hide others
                filter.style.display = isVisible ? 'none' : 'block';
            }
        });
        this.innerHTML = isVisible ? 
            '<i class="fas fa-filter"></i> Show Advanced Filters' : 
            '<i class="fas fa-filter"></i> Hide Advanced Filters';
    });

    // Real-time search (optional)
    const searchInput = document.getElementById('search');
    let searchTimeout;
    
    searchInput?.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            // Uncomment to enable real-time search
            // document.getElementById('filterForm').submit();
        }, 500);
    });
});
</script>
@endsection