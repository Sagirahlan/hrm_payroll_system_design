@extends('layouts.app')

@section('content')
<div class="container">
    <h1>SMS Notifications</h1>
   
        <a href="{{ route('sms.create') }}" class="btn btn-primary mb-3">Send SMS</a>
    

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-striped">
        <thead>
            <tr>
                <th>Sent By</th>
                <th>Recipient Type</th>
                <th>Message</th>
                <th>Status</th>
                <th>Sent At</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($smsNotifications as $sms)
                <tr>
                    <td>{{ $sms->user->username }}</td>
                    <td>{{ $sms->recipient_type }}</td>
                    <td>{{ $sms->message }}</td>
                    <td>{{ $sms->status }}</td>
                    <td>{{ $sms->sent_at ?? 'N/A' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    {{ $smsNotifications->links() }}
</div>
@endsection