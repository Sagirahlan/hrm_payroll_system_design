@extends('layouts.app')

@section('title', 'Test Page')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <h1>Test Page</h1>
            <p>If you can see this page, Laravel routing is working correctly!</p>
            <a href="{{ url('/') }}" class="btn btn-primary">Go Home</a>
        </div>
    </div>
</div>
@endsection