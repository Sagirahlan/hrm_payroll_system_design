@extends('layouts.app')

@section('title', 'Edit Pensioner')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Edit Pensioner - {{ $pensioner->full_name }}</h4>
                    <a href="{{ route('pensioners.show', $pensioner->id) }}" class="btn btn-secondary float-end">Cancel</a>
                </div>
                <div class="card-body">
                    <form action="{{ route('pensioners.update', $pensioner->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="pension_amount" class="form-label">Pension Amount</label>
                                    <input type="number" step="0.01" class="form-control" id="pension_amount" name="pension_amount" value="{{ old('pension_amount', $pensioner->pension_amount) }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="gratuity_amount" class="form-label">Gratuity Amount</label>
                                    <input type="number" step="0.01" class="form-control" id="gratuity_amount" name="gratuity_amount" value="{{ old('gratuity_amount', $pensioner->gratuity_amount) }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="bank_id" class="form-label">Bank</label>
                                    <select class="form-control" id="bank_id" name="bank_id">
                                        <option value="">Select Bank</option>
                                        @foreach($banks as $bank)
                                            <option value="{{ $bank->id }}" {{ $pensioner->bank_id == $bank->id ? 'selected' : '' }}>
                                                {{ $bank->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="account_number" class="form-label">Account Number</label>
                                    <input type="text" class="form-control" id="account_number" name="account_number" value="{{ old('account_number', $pensioner->account_number) }}">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="account_name" class="form-label">Account Name</label>
                                    <input type="text" class="form-control" id="account_name" name="account_name" value="{{ old('account_name', $pensioner->account_name) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-control" id="status" name="status">
                                        <option value="Active" {{ $pensioner->status == 'Active' ? 'selected' : '' }}>Active</option>
                                        <option value="Terminated" {{ $pensioner->status == 'Terminated' ? 'selected' : '' }}>Terminated</option>
                                        <option value="Deceased" {{ $pensioner->status == 'Deceased' ? 'selected' : '' }}>Deceased</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="next_of_kin_name" class="form-label">Next of Kin Name</label>
                            <input type="text" class="form-control" id="next_of_kin_name" name="next_of_kin_name" value="{{ old('next_of_kin_name', $pensioner->next_of_kin_name) }}">
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="next_of_kin_phone" class="form-label">Next of Kin Phone</label>
                                    <input type="text" class="form-control" id="next_of_kin_phone" name="next_of_kin_phone" value="{{ old('next_of_kin_phone', $pensioner->next_of_kin_phone) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="next_of_kin_address" class="form-label">Next of Kin Address</label>
                                    <textarea class="form-control" id="next_of_kin_address" name="next_of_kin_address" rows="2">{{ old('next_of_kin_address', $pensioner->next_of_kin_address) }}</textarea>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">Update Pensioner</button>
                        <a href="{{ route('pensioners.show', $pensioner->id) }}" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection