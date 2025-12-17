@extends('layouts.app')

@section('title', 'Pensioner Details')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Pensioner Details</h4>
                    <a href="{{ route('pensioners.index') }}" class="btn btn-secondary float-end">Back to Pensioners</a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th>Employee ID:</th>
                                    <td>{{ $pensioner->employee_id }}</td>
                                </tr>
                                <tr>
                                    <th>Full Name:</th>
                                    <td>{{ $pensioner->full_name }}</td>
                                </tr>
                                <tr>
                                    <th>Surname:</th>
                                    <td>{{ $pensioner->surname }}</td>
                                </tr>
                                <tr>
                                    <th>First Name:</th>
                                    <td>{{ $pensioner->first_name }}</td>
                                </tr>
                                <tr>
                                    <th>Middle Name:</th>
                                    <td>{{ $pensioner->middle_name ?: 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Email:</th>
                                    <td>{{ $pensioner->email ?: 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Phone Number:</th>
                                    <td>{{ $pensioner->phone_number ?: 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Date of Birth:</th>
                                    <td>{{ \Carbon\Carbon::parse($pensioner->date_of_birth)->format('d M, Y') }}</td>
                                </tr>
                                <tr>
                                    <th>Place of Birth:</th>
                                    <td>{{ $pensioner->place_of_birth ?: 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th>Date of First Appointment:</th>
                                    <td>{{ \Carbon\Carbon::parse($pensioner->date_of_first_appointment)->format('d M, Y') }}</td>
                                </tr>
                                <tr>
                                    <th>Date of Retirement:</th>
                                    <td>{{ \Carbon\Carbon::parse($pensioner->date_of_retirement)->format('d M, Y') }}</td>
                                </tr>
                                <tr>
                                    <th>Retirement Reason:</th>
                                    <td>{{ $pensioner->retirement_reason ?: 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Retirement Type:</th>
                                    <td>{{ $pensioner->retirement_type }}</td>
                                </tr>
                                <tr>
                                    <th>Department:</th>
                                    <td>{{ $pensioner->department ? $pensioner->department->department_name : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Rank:</th>
                                    <td>{{ $pensioner->rank ? $pensioner->rank->name : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Grade Level:</th>
                                    <td>{{ $pensioner->gradeLevel ? $pensioner->gradeLevel->name : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Step:</th>
                                    <td>{{ $pensioner->step ? $pensioner->step->name : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Salary Scale:</th>
                                    <td>{{ $pensioner->salaryScale ? $pensioner->salaryScale->full_name : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Overstayed:</th>
                                    <td class="text-danger fw-bold">{{ $overstayRemark ?: 'N/A' }}</td>
                                </tr>
                                @if(isset($overstayAmount) && $overstayAmount > 0)
                                <tr>
                                    <th>Overstay Amount:</th>
                                    <td class="text-danger fw-bold">₦{{ number_format($overstayAmount, 2) }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <h5>Financial Information</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <th>Pension Amount:</th>
                                    <td>{{ number_format($pensioner->pension_amount, 2) }}</td>
                                </tr>
                                <tr>
                                    <th>Gratuity Amount:</th>
                                    <td>{{ number_format($pensioner->gratuity_amount, 2) }}</td>
                                </tr>
                                <tr>
                                    <th>Total Death Gratuity:</th>
                                    <td>{{ number_format($pensioner->total_death_gratuity, 2) }}</td>
                                </tr>
                                <tr>
                                    <th>Years of Service:</th>
                                    <td>{{ $pensioner->years_of_service }}</td>
                                </tr>
                                <tr>
                                    <th>Pension Percentage:</th>
                                    <td>{{ $pensioner->pension_percentage }}%</td>
                                </tr>
                                <tr>
                                    <th>Gratuity Percentage:</th>
                                    <td>{{ $pensioner->gratuity_percentage }}%</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5>Banking Information</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <th>Bank:</th>
                                    <td>{{ $pensioner->bank ? $pensioner->bank->bank_name : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Account Number:</th>
                                    <td>{{ $pensioner->account_number ?: 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Account Name:</th>
                                    <td>{{ $pensioner->account_name ?: 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td>
                                        <span class="badge {{ $pensioner->status === 'Active' ? 'bg-success' : 'bg-secondary' }}">
                                            {{ $pensioner->status }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Local Govt. Area:</th>
                                    <td>{{ $pensioner->localGovArea ? $pensioner->localGovArea->lga : 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h5>Additional Information</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <th>Address:</th>
                                    <td>{{ $pensioner->address ?: 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Next of Kin Name:</th>
                                    <td>{{ $pensioner->next_of_kin_name ?: 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Next of Kin Phone:</th>
                                    <td>{{ $pensioner->next_of_kin_phone ?: 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Next of Kin Address:</th>
                                    <td>{{ $pensioner->next_of_kin_address ?: 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Remarks:</th>
                                    <td>{{ $pensioner->remarks ?: 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-12">
                            <a href="{{ route('pensioners.edit', $pensioner->id) }}" class="btn btn-primary">Edit Pensioner</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection