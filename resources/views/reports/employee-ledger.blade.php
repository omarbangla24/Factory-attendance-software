@extends('layouts.app')
@section('content')
<div class="min-h-screen bg-gray-100 py-4 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Employee Ledger</h1>
        </div>
        <div class="bg-white rounded-lg shadow mb-6 p-4">
            <form method="GET" class="flex gap-4">
                <select name="employee_id" class="px-3 py-2 border border-gray-300 rounded-lg flex-1">
                    @foreach ($employees as $emp)
                        <option value="{{ $emp->id }}" {{ $employeeId == $emp->id ? 'selected' : '' }}>{{ $emp->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg">View</button>
            </form>
        </div>

        <!-- Employee Info -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">{{ $ledger['employee']['name'] }}</h2>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div><div class="text-gray-600 text-sm">Department</div><div class="font-semibold">{{ $ledger['employee']['department'] }}</div></div>
                <div><div class="text-gray-600 text-sm">Phone</div><div class="font-semibold">{{ $ledger['employee']['phone'] }}</div></div>
                <div><div class="text-gray-600 text-sm">Hajira Rate</div><div class="font-semibold">{{ number_format($ledger['employee']['hajira_rate'], 2) }}</div></div>
                <div><div class="text-gray-600 text-sm">OT Rate</div><div class="font-semibold">{{ number_format($ledger['employee']['overtime_rate'], 2) }}</div></div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-4"><div class="text-gray-600 text-sm">Advance Balance</div><div class="text-lg font-bold {{ $ledger['summary']['current_advance_balance'] > 0 ? 'text-orange-600' : 'text-green-600' }}">{{ number_format($ledger['summary']['current_advance_balance'], 2) }}</div></div>
            <div class="bg-white rounded-lg shadow p-4"><div class="text-gray-600 text-sm">Total Paid</div><div class="text-lg font-bold text-green-600">{{ number_format($ledger['summary']['total_paid'], 2) }}</div></div>
            <div class="bg-white rounded-lg shadow p-4"><div class="text-gray-600 text-sm">Total Due</div><div class="text-lg font-bold text-orange-600">{{ number_format($ledger['summary']['total_due'], 2) }}</div></div>
            <div class="bg-white rounded-lg shadow p-4"><div class="text-gray-600 text-sm">Salaries</div><div class="text-lg font-bold">{{ $ledger['summary']['total_salary_generated'] }}</div></div>
        </div>

        <!-- Salary Sheets -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="p-6 border-b border-gray-200"><h3 class="text-lg font-semibold">Salary History</h3></div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50"><tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Month</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Net Salary</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Paid</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Due</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    </tr></thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($ledger['salary_sheets'] as $salary)
                            <tr><td class="px-4 py-2">{{ $salary['month'] }}</td><td class="px-4 py-2 text-right">{{ number_format($salary['net_salary'], 0) }}</td><td class="px-4 py-2 text-right text-green-600">{{ number_format($salary['paid_amount'], 0) }}</td><td class="px-4 py-2 text-right text-orange-600">{{ number_format($salary['due_amount'], 0) }}</td><td class="px-4 py-2"><span class="px-2 py-1 text-xs font-semibold rounded {{ $salary['status'] == 'paid' ? 'bg-green-100 text-green-800' : ($salary['status'] == 'partial' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">{{ ucfirst($salary['status']) }}</span></td></tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-2 text-center text-gray-500">No salary sheets</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Payments -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b border-gray-200"><h3 class="text-lg font-semibold">Recent Payments</h3></div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50"><tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Amount</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Method</th>
                    </tr></thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($ledger['payments'] as $payment)
                            <tr><td class="px-4 py-2">{{ $payment['payment_date'] }}</td><td class="px-4 py-2 text-right font-semibold">{{ number_format($payment['amount'], 0) }}</td><td class="px-4 py-2 capitalize">{{ $payment['payment_method'] }}</td></tr>
                        @empty
                            <tr><td colspan="3" class="px-4 py-2 text-center text-gray-500">No payments</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-6"><button onclick="window.print()" class="px-4 py-2 bg-gray-600 text-white rounded-lg">Print</button></div>
    </div>
</div>
@endsection
