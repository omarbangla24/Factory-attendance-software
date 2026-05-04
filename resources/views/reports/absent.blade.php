@extends('layouts.app')
@section('content')
<div class="min-h-screen bg-gray-100 py-4 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">Absent Report</h1>
        <div class="bg-white rounded-lg shadow mb-6 p-4">
            <form method="GET" class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <input type="date" name="from_date" value="{{ $fromDate }}" class="px-3 py-2 border border-gray-300 rounded-lg">
                <input type="date" name="to_date" value="{{ $toDate }}" class="px-3 py-2 border border-gray-300 rounded-lg">
                <select name="employee_id" class="px-3 py-2 border border-gray-300 rounded-lg">
                    <option value="">All</option>
                    @foreach ($employees as $emp)
                        <option value="{{ $emp->id }}" {{ $employeeId == $emp->id ? 'selected' : '' }}>{{ $emp->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg">Filter</button>
            </form>
        </div>
        <div class="bg-white rounded-lg shadow mb-6 p-6">
            <div class="text-gray-600 text-sm font-medium">Total Absence Records</div>
            <div class="text-2xl font-bold text-gray-900 mt-2">{{ $totals['total_absence_records'] }}</div>
        </div>
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="hidden sm:block">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employee</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Department</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Days</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dates</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($report as $row)
                            <tr>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $row['employee_name'] }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $row['department'] }}</td>
                                <td class="px-6 py-4 text-sm text-right">{{ $row['total_absent_days'] }}</td>
                                <td class="px-6 py-4 text-sm">{{ implode(', ', array_slice($row['absent_dates'], 0, 5)) }}{{ count($row['absent_dates']) > 5 ? '...' : '' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-6 py-4 text-center text-gray-500">No data</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-6">
            <button onclick="window.print()" class="px-4 py-2 bg-gray-600 text-white rounded-lg">Print</button>
        </div>
    </div>
</div>
@endsection
