@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-100 py-4 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <h1 class="text-3xl font-bold text-gray-900">Salary Payments</h1>
                <a href="{{ route('salaries.index') }}" class="mt-4 sm:mt-0 text-blue-600 hover:text-blue-900">← Back to Salaries</a>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow mb-6 p-4">
            <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="month" class="block text-sm font-medium text-gray-700 mb-1">Month</label>
                    <input type="month" name="month" id="month" value="{{ $month }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-blue-500">
                </div>
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Payment Status</label>
                    <select name="status" id="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-blue-500">
                        <option value="">All Status</option>
                        @foreach ($statuses as $s)
                            <option value="{{ $s }}" {{ $status === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </div>
            </form>
            <div class="mt-4 flex gap-2">
                <button onclick="document.querySelector('form').submit()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Filter</button>
                <a href="{{ route('payments.index') }}" class="px-4 py-2 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400">Clear</a>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-gray-600 text-sm font-medium">Total Salary</div>
                <div class="text-2xl font-bold text-gray-900 mt-2">{{ number_format($summary['total_salary'], 2) }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-gray-600 text-sm font-medium">Total Paid</div>
                <div class="text-2xl font-bold text-green-600 mt-2">{{ number_format($summary['total_paid'], 2) }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-gray-600 text-sm font-medium">Total Due</div>
                <div class="text-2xl font-bold text-orange-600 mt-2">{{ number_format($summary['total_due'], 2) }}</div>
            </div>
        </div>

        <!-- Mobile Card View -->
        <div class="grid grid-cols-1 md:hidden gap-4">
            @forelse ($salaries as $salary)
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="p-4 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-blue-100">
                        <h3 class="text-lg font-bold text-gray-900">{{ $salary->employee->name }}</h3>
                        <p class="text-sm text-gray-600">{{ $salary->month }}</p>
                    </div>
                    <div class="p-4 space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Net Salary:</span>
                            <span class="font-semibold">{{ number_format($salary->net_salary, 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Paid:</span>
                            <span class="font-semibold text-green-600">{{ number_format($salary->paid_amount, 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Due:</span>
                            <span class="font-semibold text-orange-600">{{ number_format($salary->due_amount, 2) }}</span>
                        </div>
                        <div class="border-t pt-3 flex justify-between">
                            <span class="font-bold">Payments:</span>
                            <span class="font-bold text-lg">{{ count($salary->salaryPayments) }}</span>
                        </div>
                        <div class="flex gap-2 pt-3">
                            <a href="{{ route('payments.show', $salary) }}" class="flex-1 px-3 py-2 bg-blue-600 text-white rounded-lg text-center text-sm hover:bg-blue-700">View</a>
                            @if ($salary->due_amount > 0)
                                <a href="{{ route('payments.pay', $salary) }}" class="flex-1 px-3 py-2 bg-green-600 text-white rounded-lg text-center text-sm hover:bg-green-700">Pay</a>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-lg shadow p-6 text-center">
                    <p class="text-gray-600">No salary sheets found for this month.</p>
                </div>
            @endforelse
        </div>

        <!-- Desktop Table View -->
        <div class="hidden md:block bg-white rounded-lg shadow overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Employee</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Net Salary</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Paid</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Due</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Payments</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Status</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($salaries as $salary)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $salary->employee->name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ number_format($salary->net_salary, 2) }}</td>
                            <td class="px-6 py-4 text-sm text-green-600 font-semibold">{{ number_format($salary->paid_amount, 2) }}</td>
                            <td class="px-6 py-4 text-sm text-orange-600 font-semibold">{{ number_format($salary->due_amount, 2) }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ count($salary->salaryPayments) }}</td>
                            <td class="px-6 py-4 text-sm">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold
                                    {{ $salary->status === 'locked' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                    {{ $salary->status === 'partial' ? 'bg-blue-100 text-blue-800' : '' }}
                                    {{ $salary->status === 'paid' ? 'bg-green-100 text-green-800' : '' }}
                                ">{{ ucfirst($salary->status) }}</span>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <div class="flex gap-2">
                                    <a href="{{ route('payments.show', $salary) }}" class="text-blue-600 hover:text-blue-900">View</a>
                                    @if ($salary->due_amount > 0)
                                        <a href="{{ route('payments.pay', $salary) }}" class="text-green-600 hover:text-green-900">Pay</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-600">No salary sheets found for this month.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if ($salaries->hasPages())
            <div class="mt-6">
                {{ $salaries->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
