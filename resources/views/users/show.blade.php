@extends('layouts.app')

@section('title', 'Profile')

@section('content')
<div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="card border-0 shadow-lg" style="max-width: 600px; width: 100%; background: linear-gradient(135deg, #e0f7fa 0%, #ffffff 100%);">
        <div class="card-header text-center" style="background: #00bcd4;">
            <h4 class="mb-0 text-white font-weight-bold">{{ __('Profile') }}</h4>
        </div>
        <div class="card-body p-4">
            <div class="d-flex flex-column align-items-center mb-4">
                <img src="{{ $user->employee && $user->employee->photo_path ? asset('storage/' . $user->employee->photo_path) : asset('images/default-image.png') }}" alt="Profile" class="rounded-circle border border-2 mb-3" style="width: 100px; height: 100px; object-fit: cover;">
                <h4 class="card-title mb-1">{{ $user->username }}</h4>
                @php
                    // Fetch role name using Spatie permissions
                    $roleName = $user->roles->first()?->name ?? 'No role assigned';
                @endphp
                <span class="badge bg-secondary mb-2">{{ ucfirst($roleName) }}</span>
            </div>
            <ul class="list-group list-group-flush mb-4">
                <li class="list-group-item bg-transparent border-0 px-0">
                    <strong>{{__('Name')}}:</strong> {{ $user->employee->first_name ?? 'N/A' }} {{ $user->employee->surname ?? 'N/A' }}
                </li>
                <li class="list-group-item bg-transparent border-0 px-0">
                    <strong>{{__('Email')}}:</strong> {{ $user->employee->email ?? 'N/A' }}
                </li>
                <li class="list-group-item bg-transparent border-0 px-0">
                    <strong>{{__('Department')}}:</strong> {{ $user->employee && $user->employee->department ? $user->employee->department->department_name : 'N/A' }}
                </li>
            </ul>
            <a href="{{ route('dashboard') }}" class="btn btn-info w-100 rounded-pill font-weight-bold shadow-sm">
                <i class="bi bi-arrow-left"></i> {{__('Back to Dashboard')}}
            </a>
        </div>
    </div>
</div>
@endsection
