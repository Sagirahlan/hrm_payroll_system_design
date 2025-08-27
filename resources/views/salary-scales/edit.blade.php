<!-- resources/views/payroll/payslip.blade.php -->
@extends('layouts.app')

@section('content')
<body class="p-6 bg-gray-100">
    <div class="max-w-7xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">Edit Salary Scale</h1>

        <!-- Success/Error Messages -->
        @if (session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded">
                {{ session('success') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Salary Scale Form -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <form action="{{ route('salary-scales.update', $salaryScale->scale_id) }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label for="scale_name" class="block text-sm font-medium text-gray-700">Scale Name</label>
                    <input type="text" name="scale_name" id="scale_name" value="{{ $salaryScale->scale_name }}"
                           class="border p-2 w-full rounded" required>
                </div>
                <div>
                    <label for="basic_salary" class="block text-sm font-medium text-gray-700">Basic Salary</label>
                    <input type="number" name="basic_salary" id="basic_salary" step="0.01" value="{{ $salaryScale->basic_salary }}"
                           class="border p-2 w-full rounded" required>
                </div>
                <div>
                    <label for="grade_level" class="block text-sm font-medium text-gray-700">Grade Level</label>
                    <input type="number" name="grade_level" id="grade_level" min="1" value="{{ $salaryScale->grade_level }}"
                           class="border p-2 w-full rounded" required>
                </div>
                <div>
                    <label for="step_level" class="block text-sm font-medium text-gray-700">Step Level</label>
                    <input type="number" name="step_level" id="step_level" min="1" value="{{ $salaryScale->step_level }}"
                           class="border p-2 w-full rounded" required>
                </div>
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea name="description" id="description" class="border p-2 w-full rounded">{{ $salaryScale->description }}</textarea>
                </div>
                <div class="flex space-x-4">
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition">
                        Update Salary Scale
                    </button>
                    <a href="{{ route('salary-scales.index') }}"
                       class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700 transition">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>

@endsection