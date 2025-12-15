@extends('layouts.app')

@section('title', 'Add Pensioner')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Add Pensioner</h4>
                    <a href="{{ route('pensioners.index') }}" class="btn btn-secondary float-end">Cancel</a>
                </div>
                <div class="card-body">
                    @if($retirements->count() > 0)
                        <div class="alert alert-info">
                            Found {{ $retirements->count() }} retired employees without pensioner records. 
                            <a href="#" onclick="moveAllRetiredToPensioners(); return false;" class="btn btn-sm btn-success">Process All</a>
                        </div>
                        
                        <form action="{{ route('pensioners.store') }}" method="POST">
                            @csrf
                            
                            <div class="mb-3">
                                <label for="retirement_id" class="form-label">Retired Employee</label>
                                <select class="form-control" id="retirement_id" name="retirement_id" required>
                                    <option value="">Select Retired Employee</option>
                                    @foreach($retirements as $retirement)
                                        <option value="{{ $retirement->id }}">
                                            {{ $retirement->employee->full_name }} ({{ $retirement->employee->employee_id }}) - {{ $retirement->retirement_date }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="pension_amount" class="form-label">Pension Amount</label>
                                        <input type="number" step="0.01" class="form-control" id="pension_amount" name="pension_amount" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="gratuity_amount" class="form-label">Gratuity Amount</label>
                                        <input type="number" step="0.01" class="form-control" id="gratuity_amount" name="gratuity_amount" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="bank_id" class="form-label">Bank</label>
                                        <select class="form-control" id="bank_id" name="bank_id">
                                            <option value="">Select Bank</option>
                                            @php
                                                $banks = DB::table('banks')->get(['bank_id as id', 'bank_name as name']);
                                            @endphp
                                            @foreach($banks as $bank)
                                                <option value="{{ $bank->id }}">{{ $bank->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="status" class="form-label">Status</label>
                                        <select class="form-control" id="status" name="status">
                                            <option value="Active">Active</option>
                                            <option value="Terminated">Terminated</option>
                                            <option value="Deceased">Deceased</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="account_number" class="form-label">Account Number</label>
                                        <input type="text" class="form-control" id="account_number" name="account_number">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="account_name" class="form-label">Account Name</label>
                                        <input type="text" class="form-control" id="account_name" name="account_name">
                                    </div>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Add Pensioner</button>
                            <a href="{{ route('pensioners.index') }}" class="btn btn-secondary">Cancel</a>
                        </form>
                    @else
                        <div class="alert alert-info">
                            No retired employees found without pensioner records. 
                            All retired employees have been moved to the pensioners table.
                        </div>
                        <a href="{{ route('pensioners.index') }}" class="btn btn-primary">View Pensioners</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function moveAllRetiredToPensioners() {
    if (confirm('Are you sure you want to process all retired employees to pensioners? This will move all eligible retired employees to the pensioners table.')) {
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
        });
    }
}
</script>
@endsection