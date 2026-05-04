@extends('layouts.app')
@section('content')
<div class="min-h-screen bg-gray-100 py-4 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Accounts Summary</h1>
            <p class="text-gray-600">Financial overview for {{ $month }}</p>
        </div>
        <div class="bg-white rounded-lg shadow mb-6 p-4">
            <form method="GET" class="flex gap-4">
                <input type="month" name="month" value="{{ $month }}" class="px-3 py-2 border border-gray-300 rounded-lg flex-1">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg">View</button>
            </form>
        </div>

        <!-- Summary Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-gray-600 text-sm font-medium">Total Salary</div>
                <div class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($summary['total_salary'], 0) }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-gray-600 text-sm font-medium">Employees</div>
                <div class="text-3xl font-bold text-gray-900 mt-2">{{ $summary['employee_count'] }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-gray-600 text-sm font-medium">Total Paid</div>
                <div class="text-3xl font-bold text-green-600 mt-2">{{ number_format($summary['total_paid'], 0) }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-gray-600 text-sm font-medium">Total Due</div>
                <div class="text-3xl font-bold text-orange-600 mt-2">{{ number_format($summary['total_due'], 0) }}</div>
            </div>
        </div>

        <!-- Breakdown -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <div class="bg-blue-50 rounded-lg p-6">
                <div class="text-blue-900 font-semibold mb-4">Salary Components</div>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span>Basic</span><span class="font-semibold">{{ number_format($summary['total_basic'], 0) }}</span></div>
                    <div class="flex justify-between"><span>Overtime</span><span class="font-semibold">{{ number_format($summary['total_overtime_pay'], 0) }}</span></div>
                    <div class="flex justify-between"><span>Adjustments</span><span class="font-semibold">{{ number_format($summary['total_adjustments'], 0) }}</span></div>
                </div>
            </div>
            <div class="bg-purple-50 rounded-lg p-6">
                <div class="text-purple-900 font-semibold mb-4">Advance Management</div>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span>Given</span><span class="font-semibold">{{ number_format($summary['total_advance_given'], 0) }}</span></div>
                    <div class="flex justify-between"><span>Deducted</span><span class="font-semibold">{{ number_format($summary['total_advance_deducted'], 0) }}</span></div>
                    <div class="flex justify-between border-t pt-2"><span>Balance</span><span class="font-bold">{{ number_format($summary['total_advance_given'] - $summary['total_advance_deducted'], 0) }}</span></div>
                </div>
            </div>
            <div class="bg-green-50 rounded-lg p-6">
                <div class="text-green-900 font-semibold mb-4">Payment Status</div>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span>Paid</span><span class="font-semibold text-green-600">{{ number_format($summary['total_paid'], 0) }}</span></div>
                    <div class="flex justify-between"><span>Due</span><span class="font-semibold text-orange-600">{{ number_format($summary['total_due'], 0) }}</span></div>
                    <div class="flex justify-between border-t pt-2"><span>Payments</span><span class="font-bold">{{ $summary['payment_count'] }}</span></div>
                </div>
            </div>
        </div>

        <div class="mt-6">
            <button onclick="window.print()" class="px-4 py-2 bg-gray-600 text-white rounded-lg">Print</button>
        </div>
    </div>
</div>
@endsection
