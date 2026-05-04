@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-100 py-4 px-4 sm:px-6 lg:px-8">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900">{{ $employee->name }}'s Payment History</h1>
            <p class="text-gray-600 mt-1">{{ $employee->department }}</p>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow mb-6 p-4">
            <form method="GET" class="flex flex-col sm:flex-row gap-4 items-end">
                <div class="flex-1">
                    <label for="month" class="block text-sm font-medium text-gray-700 mb-1">Month (Optional)</label>
                    <input type="month" name="month" id="month" value="{{ $month }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-blue-500">
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Filter</button>
                    <a href="{{ route('payments.employee-history', $employee) }}" class="px-4 py-2 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400">Clear</a>
                </div>
            </form>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-gray-600 text-sm font-medium">Total Paid</div>
                <div class="text-3xl font-bold text-green-600 mt-2">{{ number_format($summary['total_paid'], 2) }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-gray-600 text-sm font-medium">Total Payments</div>
                <div class="text-3xl font-bold text-blue-600 mt-2">{{ $summary['total_payments'] }}</div>
            </div>
        </div>

        <!-- Payment History -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            @if (count($paymentHistory) > 0)
                <!-- Mobile Card View -->
                <div class="grid grid-cols-1 md:hidden gap-4 p-4">
                    @foreach ($paymentHistory as $payment)
                        <div class="border rounded-lg p-4 space-y-2">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="font-semibold text-gray-900">{{ number_format($payment->amount, 2) }} PKR</p>
                                    <p class="text-sm text-gray-600">{{ $payment->payment_date }}</p>
                                </div>
                                <span class="inline-block px-2 py-1 rounded text-xs font-semibold
                                    {{ $payment->payment_method === 'cash' ? 'bg-blue-100 text-blue-800' : '' }}
                                    {{ $payment->payment_method === 'bank' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $payment->payment_method === 'mobile_banking' ? 'bg-purple-100 text-purple-800' : '' }}
                                ">{{ str_replace('_', ' ', ucfirst($payment->payment_method)) }}</span>
                            </div>
                            @if ($payment->salarySheet)
                                <p class="text-sm text-gray-600">Salary: {{ $payment->salarySheet->month }}</p>
                            @endif
                            @if ($payment->note)
                                <p class="text-sm text-gray-700 italic">{{ $payment->note }}</p>
                            @endif
                            <p class="text-xs text-gray-500">By: {{ $payment->createdByUser->name ?? '-' }}</p>
                        </div>
                    @endforeach
                </div>

                <!-- Desktop Table View -->
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Date</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Amount</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Method</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Salary Month</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Note</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Recorded By</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach ($paymentHistory as $payment)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ $payment->payment_date }}</td>
                                    <td class="px-6 py-4 text-sm font-semibold text-gray-900">{{ number_format($payment->amount, 2) }}</td>
                                    <td class="px-6 py-4 text-sm">
                                        <span class="inline-block px-3 py-1 rounded text-xs font-semibold
                                            {{ $payment->payment_method === 'cash' ? 'bg-blue-100 text-blue-800' : '' }}
                                            {{ $payment->payment_method === 'bank' ? 'bg-green-100 text-green-800' : '' }}
                                            {{ $payment->payment_method === 'mobile_banking' ? 'bg-purple-100 text-purple-800' : '' }}
                                        ">{{ str_replace('_', ' ', ucfirst($payment->payment_method)) }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $payment->salarySheet->month ?? '-' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $payment->note ?? '-' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $payment->createdByUser->name ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-8 text-center text-gray-600">
                    <p>No payment history found.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
