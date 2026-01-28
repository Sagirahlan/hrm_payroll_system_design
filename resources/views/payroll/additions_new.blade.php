@extends('layouts.app')

@section('title', 'Payroll Additions')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Payroll Additions</h4>
                    <p class="mb-0">Manage employee additions and allowances</p>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Add New Addition</h5>
                            <form action="{{ route('payroll.additions.bulk.store') }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label for="type_id" class="form-label">Addition Type</label>
                                    <select name="type_id" id="type_id" class="form-control" required>
                                        <option value="">Select Addition Type</option>
                                        @foreach($additionTypes as $type)
                                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="amount" class="form-label">Amount</label>
                                            <input type="number" name="amount" id="amount" class="form-control" step="0.01" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="amount_type" class="form-label">Type</label>
                                            <select name="amount_type" id="amount_type" class="form-control" required>
                                                <option value="fixed">Fixed Amount</option>
                                                <option value="percentage">Percentage</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="period" class="form-label">Period</label>
                                    <select name="period" id="period" class="form-control" required>
                                        <option value="OneTime">One Time</option>
                                        <option value="Monthly">Monthly</option>
                                        <option value="Perpetual">Perpetual</option>
                                    </select>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="start_date" class="form-label">Start Date</label>
                                            <input type="date" name="start_date" id="start_date" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="end_date" class="form-label">End Date (Optional)</label>
                                            <input type="date" name="end_date" id="end_date" class="form-control">
                                        </div>
                                    </div>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">Save Addition</button>
                            </form>
                        </div>
                        
                        <div class="col-md-6">
                            <h5>Employee Search</h5>
                            <form action="{{ route('payroll.additions') }}" method="GET">
                                <div class="input-group mb-3">
                                    <input type="text" name="search" class="form-control" placeholder="Search employees..." value="{{ request('search') }}">
                                    <button class="btn btn-outline-secondary" type="submit">Search</button>
                                </div>
                            </form>
                            
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Select</th>
                                            <th>Staff No</th>
                                            <th>Name</th>
                                            <th>Department</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($employees as $employee)
                                            <tr>
                                                <td>
                                                    <input type="checkbox" name="employee_ids[]" value="{{ $employee->employee_id }}">
                                                </td>
                                                <td>{{ $employee->staff_no }}</td>
                                                <td>{{ $employee->first_name }} {{ $employee->surname }}</td>
                                                <td>{{ $employee->department->department_name ?? 'N/A' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center">No employees found</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="d-flex justify-content-center">
                                {{ $employees->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection