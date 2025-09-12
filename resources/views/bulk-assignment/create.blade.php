@extends('layouts.app')

@section('title', 'Bulk Assignment')

@section('content')
<div class="container-fluid"
    x-data="{
        adjustmentType: '',
        additionTypes: {{ json_encode($additionTypes) }},
        deductionTypes: {{ json_encode($deductionTypes) }},
        get items() {
            if (this.adjustmentType === 'addition') return this.additionTypes;
            if (this.adjustmentType === 'deduction') return this.deductionTypes;
            return [];
        }
    }">

    <h1 class="mb-4">Bulk Additions & Deductions</h1>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Please fix the following errors:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form action="{{ route('bulk-assignment.store') }}" method="POST" id="bulk-assignment-form">
        @csrf
        <input type="hidden" name="select_all_pages" id="select_all_pages" value="0">
        {{-- Hidden fields to carry over filter query params --}}
        <input type="hidden" name="search" value="{{ request('search') }}">
        <input type="hidden" name="department_id" value="{{ request('department_id') }}">
        <input type="hidden" name="grade_level_id" value="{{ request('grade_level_id') }}">

        <div class="row">
            <!-- Left Column: Assignment Details -->
            <div class="col-md-5">
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">1. Define the Adjustment</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="adjustment_type" class="form-label">Adjustment Type</label>
                            <select name="adjustment_type" id="adjustment_type" class="form-select" x-model="adjustmentType" required>
                                <option value="" selected disabled>-- Select Type --</option>
                                <option value="addition">Addition</option>
                                <option value="deduction">Deduction</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="type_id" class="form-label">Specific Item</label>
                            <select name="type_id" id="type_id" class="form-select" :disabled="!adjustmentType || items.length === 0" required>
                                <template x-if="!adjustmentType">
                                    <option value="" selected disabled>-- First Select an Adjustment Type --</option>
                                </template>
                                <template x-if="adjustmentType && items.length === 0">
                                    <option value="" selected disabled>-- No items available --</option>
                                </template>
                                <template x-for="item in items" :key="item.id">
                                    <option :value="item.id" x-text="item.name"></option>
                                </template>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-sm-8">
                                <div class="mb-3">
                                    <label for="amount" class="form-label">Amount</label>
                                    <input type="number" name="amount" id="amount" class="form-control" required step="0.01" placeholder="Enter amount or %">
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="mb-3">
                                    <label for="amount_type" class="form-label">Type</label>
                                    <select name="amount_type" id="amount_type" class="form-select" required>
                                        <option value="fixed">Fixed</option>
                                        <option value="percentage">Percentage</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="period" class="form-label">Frequency</label>
                            <select name="period" id="period" class="form-select" required>
                                <option value="OneTime">One Time</option>
                                <option value="Monthly">Monthly</option>
                                <option value="Perpetual">Perpetual</option>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-sm-6 mb-3">
                                <label for="start_date" class="form-label">Start Date</label>
                                <input type="date" name="start_date" id="start_date" class="form-control" required>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <label for="end_date" class="form-label">End Date <small class="text-muted">(Optional)</small></label>
                                <input type="date" name="end_date" id="end_date" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg">Assign to Selected Employees</button>
                </div>
            </div>

            <!-- Right Column: Employee Selection -->
            <div class="col-md-7">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0">2. Select Employees</h5>
                    </div>
                    <div class="card-body">
                        <!-- Filter Form -->
                        <form action="{{ route('bulk-assignment.create') }}" method="GET" id="employee-filter-form">
                            <div class="input-group mb-3">
                                <input type="text" name="search" class="form-control" placeholder="Search by name or ID..." value="{{ request('search') }}">
                                <button class="btn btn-outline-secondary" type="submit">Search</button>
                                <a href="{{ route('bulk-assignment.create') }}" class="btn btn-outline-danger" title="Clear Search">Clear</a>
                            </div>
                        </form>

                        <div class="table-responsive" id="employee-list-scroll-container" style="max-height: 450px;" data-next-page-url="{{ $employees->nextPageUrl() }}">
                            <table class="table table-bordered table-striped table-hover">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th class="text-center"><input type="checkbox" id="select-all"></th>
                                        <th>Employee ID</th>
                                        <th>Name</th>
                                        <th>Department</th>
                                    </tr>
                                </thead>
                                <tbody id="employee-list-tbody">
                                    @include('bulk-assignment._employee_rows', ['employees' => $employees])
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const scrollContainer = document.getElementById('employee-list-scroll-container');
        const tbody = document.getElementById('employee-list-tbody');
        let isLoading = false;

        scrollContainer.addEventListener('scroll', function() {
            if (isLoading) return;

            let nextPageUrl = scrollContainer.dataset.nextPageUrl;
            if (!nextPageUrl) return;

            if (scrollContainer.scrollTop + scrollContainer.clientHeight >= scrollContainer.scrollHeight - 100) {
                isLoading = true;
                fetch(nextPageUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(response => response.json())
                    .then(data => {
                        if (data.html) {
                            tbody.insertAdjacentHTML('beforeend', data.html);
                        }
                        scrollContainer.dataset.nextPageUrl = data.next_page_url || '';
                        isLoading = false;
                    })
                    .catch(() => isLoading = false);
            }
        });

        const selectAllCheckbox = document.getElementById('select-all');
        const selectAllPagesInput = document.getElementById('select_all_pages');

        selectAllCheckbox.addEventListener('change', function () {
            const isChecked = this.checked;
            tbody.querySelectorAll('.employee-checkbox').forEach(checkbox => {
                checkbox.checked = isChecked;
                checkbox.disabled = isChecked;
            });
            selectAllPagesInput.value = isChecked ? '1' : '0';
        });
    });
</script>
@endpush
@endsection