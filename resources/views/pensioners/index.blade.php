@extends('layouts.app')

@section('title', 'Pensioners')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title d-inline">Pensioners</h4>
                   
                    
                </div>
                <div class="card-body">
                    <!-- Search and Filter Form -->
                    <form method="GET" action="{{ route('pensioners.index') }}" class="mb-4">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    <input type="text" name="search" class="form-control" 
                                           placeholder="Search Staff No or name..." 
                                           value="{{ request('search') }}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <select name="status" class="form-select">
                                    <option value="">All Statuses</option>
                                    <option value="Active" {{ request('status') == 'Active' ? 'selected' : '' }}>Active</option>
                                    <option value="Deceased" {{ request('status') == 'Deceased' ? 'selected' : '' }}>Deceased</option>
                                    <option value="Suspended" {{ request('status') == 'Suspended' ? 'selected' : '' }}>Suspended</option>
                                    <option value="Not Eligible" {{ request('status') == 'Not Eligible' ? 'selected' : '' }}>Not Eligible</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="retirement_type" class="form-select">
                                    <option value="">All Retirement Types</option>
                                    <option value="Voluntary" {{ request('retirement_type') == 'Voluntary' ? 'selected' : '' }}>Voluntary</option>
                                    
                                </select>
                            </div>
                            <div class="col-md-2">
                                <div class="btn-group w-100" role="group">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="fas fa-filter me-1"></i> Filter
                                    </button>
                                    <a href="{{ route('pensioners.index') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-times"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        @if(request()->hasAny(['search', 'status', 'retirement_type']))
                            <div class="mt-2">
                                <small class="text-muted">Active filters:</small>
                                @if(request('search'))
                                    <span class="badge bg-info ms-1">Search: "{{ request('search') }}"</span>
                                @endif
                                @if(request('status'))
                                    <span class="badge bg-warning text-dark ms-1">Status: {{ request('status') }}</span>
                                @endif
                                @if(request('retirement_type'))
                                    <span class="badge bg-success ms-1">Type: {{ request('retirement_type') }}</span>
                                @endif
                            </div>
                        @endif
                    </form>

                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>Staff No</th>
                                    <th>Full Name</th>
                                    <th>Rank</th>
                                    <th>Department</th>
                                    <th>Retirement Date</th>
                                    <th>Expected Retirement Date</th>
                                    <th>Yrs of Svc</th>
                                    <th>Gratuity</th>
                                    <th>Pension</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pensioners as $pensioner)
                                <tr>
                                    <td>{{ $pensioner->employee->staff_no ?? $pensioner->employee_id }}</td>
                                    <td>
                                        <div class="fw-bold">{{ $pensioner->full_name }}</div>
                                        <small class="text-muted">{{ $pensioner->retirement_reason }}</small>
                                    </td>
                                    <td>{{ $pensioner->rank ? $pensioner->rank->name : 'N/A' }}</td>
                                    <td>{{ $pensioner->department ? $pensioner->department->department_name : 'N/A' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($pensioner->date_of_retirement)->format('d M, Y') }}</td>
                                    <td>
                                        @if($pensioner->employee && $pensioner->employee->expected_retirement_date)
                                            {{ \Carbon\Carbon::parse($pensioner->employee->expected_retirement_date)->format('d M, Y') }}
                                        @elseif($pensioner->expected_retirement_date)
                                            {{ \Carbon\Carbon::parse($pensioner->expected_retirement_date)->format('d M, Y') }}
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>{{ number_format($pensioner->years_of_service, 1) }}</td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span>{{ number_format($pensioner->gratuity_amount, 2) }}</span>
                                            @if($pensioner->is_gratuity_paid)
                                                <span class="badge bg-success btn-sm p-1" style="font-size: 0.7rem;">Paid on {{ \Carbon\Carbon::parse($pensioner->gratuity_paid_date)->format('d/m/Y') }}</span>
                                            @else
                                                <span class="badge bg-warning text-dark btn-sm p-1" style="font-size: 0.7rem;">Unpaid</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>{{ number_format($pensioner->pension_amount, 2) }}</td>
                                    <td>
                                        <span class="badge {{ $pensioner->status === 'Active' ? 'bg-success' : ($pensioner->status === 'Deceased' ? 'bg-dark' : ($pensioner->status === 'Not Eligible' ? 'bg-danger' : 'bg-secondary')) }}">
                                            {{ $pensioner->status }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                Actions
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="{{ route('pensioners.show', $pensioner->id) }}">View Details</a></li>
                                                <li><a class="dropdown-item" href="{{ route('pensioners.edit', $pensioner->id) }}">Edit Record</a></li>
                                                
                                                @if(!$pensioner->is_gratuity_paid)
                                                    <li>
                                                        <form action="{{ route('pensioners.mark-gratuity-paid', $pensioner->id) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="dropdown-item text-primary" onclick="return confirm('Are you sure you want to mark gratuity as paid?')">
                                                                <i class="fas fa-check-circle me-1"></i> Pay Gratuity
                                                            </button>
                                                        </form>
                                                    </li>
                                                @endif

                                                @if($pensioner->status !== 'Deceased')
                                                    <li>
                                                        <form action="{{ route('pensioners.mark-deceased', $pensioner->id) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Are you sure you want to mark this pensioner as deceased? This will stop future pension payments.')">
                                                                <i class="fas fa-book-dead me-1"></i> Mark Deceased
                                                            </button>
                                                        </form>
                                                    </li>
                                                @endif
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="11" class="text-center">No pensioners found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center">
                        {{ $pensioners->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('move-retired-btn').addEventListener('click', function(e) {
    e.preventDefault();
    const btn = this;
    const originalText = btn.innerHTML;
    
    btn.innerHTML = 'Processing...';
    btn.disabled = true;
    
    fetch('{{ route("pensioners.move-retired") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while processing retired employees.');
    })
    .finally(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
});
</script>
@endsection