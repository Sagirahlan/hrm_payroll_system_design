@extends('layouts.app')

@section('title', 'Import Legacy Pensioners')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Import Legacy Pensioners</h4>
                    <a href="{{ route('pensioners.index') }}" class="btn btn-secondary btn-sm">Back</a>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h5 class="alert-heading"><i class="fas fa-info-circle me-2"></i> Instructions</h5>
                        <p class="mb-0">This feature allows you to bulk import pensioners who are already retired and have pre-computed amounts.</p>
                        <p>The system will:</p>
                        <ul class="mb-2">
                            <li>Find the employee by <strong>Staff No</strong>.</li>
                            <li>Create a <strong>Retirement</strong> record if one doesn't exist.</li>
                            <li>Set the status to <strong>Gratuity Paid</strong>.</li>
                            <li>Update/Create the <strong>Pensioner</strong> record with your provided amounts.</li>
                        </ul>
                        <hr>
                        <p class="mb-0">Please use the template below to ensure your data is formatted correctly.</p>
                    </div>

                    <div class="d-grid gap-2 mb-4">
                        <a href="{{ route('pensioners.legacy.template') }}" class="btn btn-outline-primary">
                            <i class="fas fa-download me-2"></i> Download CSV Template
                        </a>
                    </div>

                    <form action="{{ route('pensioners.legacy.process') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="file" class="form-label">Upload CSV/Excel File</label>
                            <input type="file" class="form-control @error('file') is-invalid @enderror" id="file" name="file" required accept=".csv, .xlsx, .xls">
                            @error('file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary" onclick="return confirm('Are you sure you want to import this file? This will modify database records.')">
                                <i class="fas fa-file-import me-2"></i> Import Pensioners
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
