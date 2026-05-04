@extends('layouts.app')
@section('content')
<div class="min-h-screen bg-gray-100 py-4 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">Payment Report</h1>
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
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-6"><div class="text-gray-600 text-sm">Total Paid</div><div class="text-2xl font-bold">{{ number_format($summary['total_paid'], 2) }}</div></div>
            <div class="bg-white rounded-lg shadow p-6"><div class="text-gray-600 text-sm">Payment Count</div><div class="text-2xl font-bold">{{ $summary['payment_count'] }}</div></div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h3 class="text-lg font-semibold mb-4">Payment Method Summary</h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                @foreach ($summary['method_summary'] as $method => $data)
                    <div class="p-4 border border-gray-200 rounded-lg">
                        <div class="font-medium capitalize">{{ $method }}</div>
                        <div class="text-sm text-gray-600">Count: {{ $data['count'] }}</div>
                        <div class="text-lg font-semibold">{{ number_format($data['total'], 2) }}</div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="hidden sm:block overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50"><tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employee</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Method</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Note</th>
                    </tr></thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($payments as $payment)
                            <tr><td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $payment['employee_name'] }}</td><td class="px-6 py-4 text-sm text-gray-600">{{ $payment['payment_date'] }}</td><td class="px-6 py-4 text-sm text-right font-semibold">{{ number_format($payment['amount'], 2) }}</td><td class="px-6 py-4 text-sm capitalize">{{ $payment['payment_method'] }}</td><td class="px-6 py-4 text-sm text-gray-600">{{ $payment['note'] ?? '—' }}</td></tr>
                        @empty
                            <tr><td colspan="5" class="px-6 py-4 text-center text-gray-500">No payments</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-6"><button onclick="window.print()" class="px-4 py-2 bg-gray-600 text-white rounded-lg">Print</button></div>
    </div>
</div>
@endsection
