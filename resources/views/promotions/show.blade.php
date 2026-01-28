@extends('layouts.app')

@section('title', 'Promotion/Demotion Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Promotion/Demotion Details</h3>
                    <div>
                        <a href="{{ route('promotions.index') }}" class="btn btn-secondary">Back to List</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th>Promotion ID:</th>
                                    <td>{{ $promotion->id }}</td>
                                </tr>
                                <tr>
                                    <th>Employee:</th>
                                    <td>
                                        {{ trim($promotion->employee->first_name . ' ' . $promotion->employee->middle_name . ' ' . $promotion->employee->surname) ?? 'N/A' }}<br>
                                        <small class="text-muted">Staff No: {{ $promotion->employee->staff_no ?? 'N/A' }}</small>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Type:</th>
                                    <td>
                                        <span class="badge badge-{{ $promotion->promotion_type === 'promotion' ? 'success' : 'warning' }} text-black">
                                            {{ ucfirst($promotion->promotion_type) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Previous Grade Level:</th>
                                    <td>{{ $promotion->previous_grade_level ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>New Grade Level:</th>
                                    <td>{{ $promotion->new_grade_level ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Previous Step:</th>
                                    <td>{{ $promotion->previous_step ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>New Step:</th>
                                    <td>{{ $promotion->new_step ?? 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th>Promotion Date:</th>
                                    <td>{{ \Carbon\Carbon::parse($promotion->promotion_date)->format('Y-m-d') }}</td>
                                </tr>
                                <tr>
                                    <th>Effective Date:</th>
                                    <td>{{ \Carbon\Carbon::parse($promotion->effective_date)->format('Y-m-d') }}</td>
                                </tr>
                                <tr>
                                    <th>Approving Authority:</th>
                                    <td>{{ $promotion->approving_authority ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td>
                                        <span class="badge badge-{{ $promotion->status === 'approved' ? 'success' : ($promotion->status === 'rejected' ? 'danger' : 'warning') }} text-black">
                                            {{ ucfirst($promotion->status) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Created By:</th>
                                    <td>{{ $promotion->creator->name ?? 'System' }}</td>
                                </tr>
                                <tr>
                                    <th>Created At:</th>
                                    <td>{{ $promotion->created_at->format('Y-m-d H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <th>Updated At:</th>
                                    <td>{{ $promotion->updated_at->format('Y-m-d H:i:s') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-12">
                            <h5>Reason</h5>
                            <p class="bg-light p-3 border rounded">
                                {{ $promotion->reason ?? 'No reason provided.' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection