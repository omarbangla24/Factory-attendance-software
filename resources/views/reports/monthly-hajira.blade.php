@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-100 py-4 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Monthly Hajira Report</h1>
            <p class="text-gray-600 mt-1">Attendance summary for the selected month</p>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow mb-6 p-4">
            <form method="GET" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label for="month" class="block text-sm font-medium text-gray-700 mb-1">Month</label>
                    <input type="month" name="month" value="{{ $month }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-blue-500">
                </div>
                <div>
                    <label for="employee_id" class="block text-sm font-medium text-gray-700 mb-1">Employee</label>
                    <select name="employee_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-blue-500">
                        <option value="">All Employees</option>
                        @foreach ($employees as $employee)
                            <option value="{{ $employee->id }}" {{ $employeeId == $employee->id ? 'selected' : '' }}>{{ $employee->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex-1">Filter</button>
                    <a href="{{ route('reports.monthly-hajira') }}" class="px-4 py-2 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400">Clear</a>
                </div>
            </form>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-gray-600 text-sm font-medium">Total Hajira</div>
                <div class="text-2xl font-bold text-gray-900 mt-2">{{ number_format($totals['total_hajira'], 1) }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-gray-600 text-sm font-medium">Present Days</div>
                <div class="text-2xl font-bold text-green-600 mt-2">{{ $totals['total_present'] }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-gray-600 text-sm font-medium">Absent Days</div>
                <div class="text-2xl font-bold text-red-600 mt-2">{{ $totals['total_absent'] }}</div>
            </div>
        </div>

        <!-- Report Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <!-- Mobile Card View -->
            <div class="sm:hidden">
                @forelse ($report as $row)
                    <div class="p-4 border-b border-gray-200">
                        <div class="font-semibold text-gray-900">{{ $row['employee_name'] }}</div>
                        <div class="text-sm text-gray-600">{{ $row['department'] }}</div>
                        <div class="mt-2 space-y-1">
                            <div class="text-sm"><span class="font-medium">Hajira:</span> {{ number_format($row['total_hajira'], 1) }}</div>
                            <div class="text-sm"><span class="font-medium">Present:</span> {{ $row['present_days'] }}</div>
                            <div class="text-sm"><span class="font-medium">Absent:</span> {{ $row['absent_days'] }}</div>
                        </div>
                    </div>
                @empty
                    <div class="p-4 text-center text-gray-500">No data available</div>
                @endforelse
            </div>

            <!-- Desktop Table View -->
            <div class="hidden sm:block overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employee</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Department</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total Hajira</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Present</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Absent</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($report as $row)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $row['employee_name'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $row['department'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-semibold">{{ number_format($row['total_hajira'], 1) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-green-600">{{ $row['present_days'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-red-600">{{ $row['absent_days'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-gray-500">No data available</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Print Button -->
        <div class="mt-6 flex gap-2">
            <button onclick="window.print()" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">Print</button>
            <a href="{{ route('reports.index') }}" class="px-4 py-2 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400">Back to Reports</a>
        </div>
    </div>
</div>

<style media="print">
    body { background: white; }
    .sm:hidden { display: table !important; }
    button { display: none; }
    a { display: none; }
</style>
@endsection
