@extends('layouts.app')

@section('title', 'Pensioners')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title d-inline">Pensioners</h4>
                    <a href="{{ route('pensioners.create') }}" class="btn btn-primary float-end">Add Pensioner</a>
                    <a href="{{ route('pensioners.move-retired') }}" class="btn btn-success float-end me-2" id="move-retired-btn">Move Retired to Pensioners</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>Employee ID</th>
                                    <th>Full Name</th>
                                    <th>Rank</th>
                                    <th>Department</th>
                                    <th>Retirement Date</th>
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
                                    <td>{{ $pensioner->employee_id }}</td>
                                    <td>
                                        <div class="fw-bold">{{ $pensioner->full_name }}</div>
                                        <small class="text-muted">{{ $pensioner->retirement_reason }}</small>
                                    </td>
                                    <td>{{ $pensioner->rank ? $pensioner->rank->name : 'N/A' }}</td>
                                    <td>{{ $pensioner->department ? $pensioner->department->department_name : 'N/A' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($pensioner->date_of_retirement)->format('d M, Y') }}</td>
                                    <td>{{ number_format($pensioner->years_of_service, 1) }}</td>
                                    <td>{{ number_format($pensioner->gratuity_amount, 2) }}</td>
                                    <td>{{ number_format($pensioner->pension_amount, 2) }}</td>
                                    <td>
                                        <span class="badge {{ $pensioner->status === 'Active' ? 'bg-success' : 'bg-secondary' }}">
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
                                                {{-- <li><hr class="dropdown-divider"></li> --}}
                                                {{-- <li><a class="dropdown-item text-danger" href="#">Delete</a></li> --}}
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="10" class="text-center">No pensioners found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center">
                        {{ $pensioners->links() }}
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