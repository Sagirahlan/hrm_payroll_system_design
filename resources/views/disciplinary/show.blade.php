@extends('layouts.app')

@section('title', 'Disciplinary Action Details')

@section('content')


    <!-- Disciplinary History Section -->
</div>
<div class="container d-flex justify-content-center mt-4">
    <div class="card border-0 shadow-lg" style="max-width: 900px; width: 100%; background: linear-gradient(135deg, #e0f7fa 0%, #ffffff 100%);">
        <div class="card-header text-center" style="background: #00bcd4;">
            <h4 class="mb-0 text-white font-weight-bold">
                Disciplinary History for {{ $action->employee ? $action->employee->first_name . ' ' . $action->employee->surname : 'N/A' }}
            </h4>
        </div>
        <div class="card-body p-4">
            @if ($disciplinaryHistory->isEmpty())
                <p class="text-center">No other disciplinary actions found for this employee.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead style="background: #b2ebf2;">
                            <tr>
                                <th>Action Type</th>
                                <th>Description</th>
                                <th>Action Date</th>
                                <th>Resolution Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($disciplinaryHistory as $history)
                                <tr>
                                    <td>{{ $history->action_type }}</td>
                                    <td>{{ $history->description ?? 'N/A' }}</td>
                                    <td>{{ $history->action_date }}</td>
                                    <td>{{ $history->resolution_date ?? 'N/A' }}</td>
                                    <td>{{ $history->status }}</td>
                                    
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection