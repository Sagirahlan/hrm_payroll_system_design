@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="card border-primary shadow">
        <div class="card-header" style="background-color: skyblue; color: white;">
            <h5 class="mb-0">Biometric Data</h5>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <a href="{{ route('biometrics.create') }}" class="btn btn-primary">Add Biometric Data</a>
            </div>

            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="table-responsive">
                <table class="table table-striped table-bordered align-items-center mb-0">
                    <thead class="table-primary">
                        <tr>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Employee</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Verification Status</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Verification Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($biometrics as $biometric)
                            <tr>
                                <td class="fw-bold">{{ $biometric->employee->first_name }} {{ $biometric->employee->surname }}</td>
                                <td>
                                    <span class="badge bg-success">
                                        {{ $biometric->verification_status }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">
                                        {{ $biometric->verification_date ?? 'N/A' }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $biometrics->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
