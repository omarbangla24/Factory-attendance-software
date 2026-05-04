@extends('layouts.app')
@section('content')
<div class="min-h-screen bg-gray-100 py-4 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">Salary Sheet Report</h1>
        <div class="bg-white rounded-lg shadow mb-6 p-4">
            <form method="GET" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <input type="month" name="month" value="{{ $month }}" class="px-3 py-2 border border-gray-300 rounded-lg">
                <select name="employee_id" class="px-3 py-2 border border-gray-300 rounded-lg">
                    <option value="">All</option>
                    @foreach ($employees as $emp)
                        <option value="{{ $emp->id }}" {{ $employeeId == $emp->id ? 'selected' : '' }}>{{ $emp->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg">Filter</button>
            </form>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-6"><div class="text-gray-600 text-sm">Total Net Salary</div><div class="text-2xl font-bold">{{ number_format($totals['total_net_salary'], 2) }}</div></div>
            <div class="bg-white rounded-lg shadow p-6"><div class="text-gray-600 text-sm">Total Paid</div><div class="text-2xl font-bold text-green-600">{{ number_format($totals['total_paid'], 2) }}</div></div>
            <div class="bg-white rounded-lg shadow p-6"><div class="text-gray-600 text-sm">Total Due</div><div class="text-2xl font-bold text-orange-600">{{ number_format($totals['total_due'], 2) }}</div></div>
        </div>
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="hidden sm:block overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50"><tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Employee</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Basic</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">OT</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Advance</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Adjust</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Net</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Paid</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Due</th>
                    </tr></thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($report as $row)
                            <tr class="hover:bg-gray-50"><td class="px-4 py-2 font-medium text-gray-900">{{ $row['employee_name'] }}</td><td class="px-4 py-2 text-right">{{ number_format($row['basic_amount'], 0) }}</td><td class="px-4 py-2 text-right">{{ number_format($row['overtime_amount'], 0) }}</td><td class="px-4 py-2 text-right">{{ number_format($row['advance_deducted'], 0) }}</td><td class="px-4 py-2 text-right">{{ number_format($row['adjustment_amount'], 0) }}</td><td class="px-4 py-2 text-right font-semibold">{{ number_format($row['net_salary'], 0) }}</td><td class="px-4 py-2 text-right text-green-600">{{ number_format($row['paid_amount'], 0) }}</td><td class="px-4 py-2 text-right text-orange-600">{{ number_format($row['due_amount'], 0) }}</td></tr>
                        @empty
                            <tr><td colspan="8" class="px-4 py-2 text-center text-gray-500">No data</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-6"><button onclick="window.print()" class="px-4 py-2 bg-gray-600 text-white rounded-lg">Print</button></div>
    </div>
</div>
@endsection
