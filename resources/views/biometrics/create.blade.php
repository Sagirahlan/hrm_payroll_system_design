@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    @can('create_biometrics')
    <div class="mb-3">
        <a href="{{ route('biometrics.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Biometrics
        </a>
    </div>
    
    <div class="card border-primary shadow">
        <div class="card-header" style="background-color: skyblue; color: white;">
            <h5 class="mb-0">Add Biometric Data</h5>
        </div>
        <div class="card-body">
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <div class="card border-primary shadow">
                        <div class="card-header" style="background-color: skyblue; color: white;">
                            <strong>Biometric Entry Form</strong>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('biometrics.store') }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label for="employee_id" class="form-label">Employee</label>
                                    <select name="employee_id" class="form-control" required {{ request('employee_id') ? 'readonly' : '' }}>
                                        @if(request('employee_id'))
                                            @php
                                                $selectedEmployee = $employees->firstWhere('employee_id', request('employee_id'));
                                            @endphp
                                            @if($selectedEmployee)
                                                <option value="{{ $selectedEmployee->employee_id }}" selected>{{ $selectedEmployee->first_name }} {{ $selectedEmployee->middle_name }} {{ $selectedEmployee->surname }}</option>
                                            @endif
                                        @else
                                            <option value="">Select an Employee</option>
                                            @foreach ($employees as $employee)
                                                <option value="{{ $employee->employee_id }}">{{ $employee->first_name }} {{ $employee->middle_name }} {{ $employee->surname }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                    @error('employee_id') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>

                                {{-- Hidden Fingerprint Template Field --}}
                                <textarea id="fingerprintData" name="fingerprint_data" hidden></textarea>

                                <div class="mb-3">
                                    <label class="form-label">Fingerprint Capture</label>
                                    <div class="border rounded p-3 bg-light">
                                        <p id="scannerStatus" class="text-muted mb-2">Waiting for fingerprint scan...</p>
                                        <button type="button" class="btn btn-outline-primary" onclick="captureFingerprint()">Capture Fingerprint</button>
                                    </div>
                                    @error('fingerprint_data') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>

                                <button type="submit" class="btn btn-primary">Save Biometric Data</button>
                                <a href="{{ route('biometrics.index') }}" class="btn btn-secondary">Cancel</a>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="alert alert-warning">
        You don't have permission to create biometric data.
    </div>
    @endcan
</div>

{{-- JavaScript to simulate fingerprint capture --}}
<script>
    function captureFingerprint() {
        // Simulate captured fingerprint template (this should come from real scanner in production)
        const fakeFingerprintTemplate = "FAKE_TEMPLATE_{{ uniqid() }}"; // replace with real data from scanner

        document.getElementById('fingerprintData').value = fakeFingerprintTemplate;
        document.getElementById('scannerStatus').innerText = "Fingerprint captured successfully!";
        document.getElementById('scannerStatus').classList.remove('text-muted');
        document.getElementById('scannerStatus').classList.add('text-success');
    }
</script>
@endsection
