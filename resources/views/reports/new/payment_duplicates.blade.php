@extends('layouts.app')

@section('title', 'Duplicate Payment Transactions')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title text-danger font-weight-bold">
                        <i class="fas fa-exclamation-triangle"></i> Identified Duplicate Payment Transactions
                    </h3>
                    <div class="card-tools">
                        <a href="{{ url()->previous() }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Showing transactions that share the same <strong>Name, Staff ID, Account Number, Account Name, and Appointment Type</strong>.
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="bg-dark text-white">
                                <tr>
                                    <th>Employee Name</th>
                                    <th>Staff ID</th>
                                    <th>Appointment Type</th>
                                    <th>Account Number</th>
                                    <th>Account Name</th>
                                    <th class="text-center">Occurrences</th>
                                    <th class="text-right">Total Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($duplicates as $duplicate)
                                    <tr>
                                        <td class="font-weight-bold">{{ $duplicate->first_name }} {{ $duplicate->surname }}</td>
                                        <td>{{ $duplicate->staff_no }}</td>
                                        <td>{{ $duplicate->appointment_type_name ?? 'N/A' }}</td>
                                        <td><code>{{ $duplicate->account_number }}</code></td>
                                        <td>{{ $duplicate->account_name }}</td>
                                        <td class="text-center">
                                            <span class="badge badge-danger" style="font-size: 1rem;">
                                                {{ $duplicate->duplicate_count }}
                                            </span>
                                        </td>
                                        <td class="text-right font-weight-bold text-danger">
                                            NGN {{ number_format($duplicate->total_amount, 2) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <div class="text-success">
                                                <i class="fas fa-check-circle fa-3x mb-3"></i>
                                                <h4>No duplicates found for the selected filters.</h4>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    <p class="text-muted small">
                        * Duplicate detection is based on exact matches of Name, Staff ID, Account Number, Account Name, and Appointment Type within the filtered criteria.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
