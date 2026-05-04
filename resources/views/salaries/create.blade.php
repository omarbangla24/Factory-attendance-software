@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-100 py-4 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Generate Salary</h1>
            <p class="text-gray-600 mt-1">Generate salary sheets for active employees</p>
        </div>

        <!-- Form -->
        <form action="{{ route('salaries.store') }}" method="POST" class="bg-white rounded-lg shadow p-6">
            @csrf

            <div class="mb-6">
                <label for="month" class="block text-sm font-medium text-gray-700 mb-2">Month <span class="text-red-600">*</span></label>
                <input type="month" name="month" id="month" value="{{ old('month', $month) }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-blue-500 @error('month') border-red-500 @enderror">
                @error('month')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-3">Generate For:</label>
                <div class="space-y-3">
                    <label class="flex items-center">
                        <input type="radio" name="employee_ids_type" value="all" checked class="mr-3" onchange="toggleEmployeeSelect()">
                        <span class="text-gray-700">All Active Employees ({{ $employees->count() }})</span>
                    </label>
                    <label class="flex items-center">
                        <input type="radio" name="employee_ids_type" value="selected" class="mr-3" onchange="toggleEmployeeSelect()">
                        <span class="text-gray-700">Selected Employees</span>
                    </label>
                </div>
            </div>

            <div id="employee-select-box" class="mb-6 hidden">
                <label for="employee_ids" class="block text-sm font-medium text-gray-700 mb-2">Select Employees <span class="text-red-600">*</span></label>
                <div class="border border-gray-300 rounded-lg p-4 max-h-64 overflow-y-auto">
                    @foreach ($employees as $employee)
                        <label class="flex items-center mb-2 last:mb-0">
                            <input type="checkbox" name="employee_ids[]" value="{{ $employee->id }}" class="mr-3">
                            <span class="text-gray-700">{{ $employee->name }} - {{ $employee->department }}</span>
                        </label>
                    @endforeach
                </div>
                @error('employee_ids.*')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <p class="text-sm text-blue-800">
                    <strong>Note:</strong> If salary sheets already exist for this month:
                    <ul class="list-disc ml-6 mt-2 space-y-1">
                        <li>Draft sheets will be regenerated with new attendance data</li>
                        <li>Locked sheets will not be modified</li>
                    </ul>
                </p>
            </div>

            <div class="flex gap-4">
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">Generate</button>
                <a href="{{ route('salaries.index') }}" class="px-6 py-2 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400 font-medium">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
function toggleEmployeeSelect() {
    const type = document.querySelector('input[name="employee_ids_type"]:checked').value;
    const box = document.getElementById('employee-select-box');
    
    if (type === 'selected') {
        box.classList.remove('hidden');
    } else {
        box.classList.add('hidden');
    }
}
</script>
@endsection
