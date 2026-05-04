<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800">Dashboard</h2>
    </x-slot>

    <!-- Today's Summary -->
    <div class="mb-8">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Today's Summary</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <!-- Today Present -->
            <div class="bg-white rounded-lg shadow p-4 md:p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-xs md:text-sm">Present Today</p>
                        <p class="text-2xl md:text-3xl font-bold text-green-600">{{ $todays_summary['total_present'] }}</p>
                    </div>
                    <svg class="w-8 h-8 md:w-12 md:h-12 text-green-200" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                </div>
            </div>

            <!-- Today Absent -->
            <div class="bg-white rounded-lg shadow p-4 md:p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-xs md:text-sm">Absent Today</p>
                        <p class="text-2xl md:text-3xl font-bold text-red-600">{{ $todays_summary['total_absent'] }}</p>
                    </div>
                    <svg class="w-8 h-8 md:w-12 md:h-12 text-red-200" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                    </svg>
                </div>
            </div>

            <!-- Today Total Hajira -->
            <div class="bg-white rounded-lg shadow p-4 md:p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-xs md:text-sm">Today Hajira</p>
                        <p class="text-2xl md:text-3xl font-bold text-blue-600">{{ number_format($todays_summary['total_hajira'], 1) }}</p>
                    </div>
                    <svg class="w-8 h-8 md:w-12 md:h-12 text-blue-200" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"></path>
                    </svg>
                </div>
            </div>

            <!-- Today Overtime Hours -->
            <div class="bg-white rounded-lg shadow p-4 md:p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-xs md:text-sm">OT Hours Today</p>
                        <p class="text-2xl md:text-3xl font-bold text-purple-600">{{ number_format($todays_summary['total_overtime_hours'], 1) }}</p>
                    </div>
                    <svg class="w-8 h-8 md:w-12 md:h-12 text-purple-200" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3.586L7.707 9.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 10.586V7z" clip-rule="evenodd"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- This Month's Summary -->
    <div class="mb-8">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">This Month's Summary</h3>
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8">
            <!-- Month Total Hajira -->
            <div class="bg-white rounded-lg shadow p-4 md:p-6">
                <p class="text-gray-500 text-xs md:text-sm mb-1">Total Hajira</p>
                <p class="text-xl md:text-2xl font-bold text-blue-600">{{ number_format($month_summary['total_hajira'], 1) }}</p>
            </div>

            <!-- Month Overtime Hours -->
            <div class="bg-white rounded-lg shadow p-4 md:p-6">
                <p class="text-gray-500 text-xs md:text-sm mb-1">OT Hours</p>
                <p class="text-xl md:text-2xl font-bold text-purple-600">{{ number_format($month_summary['total_overtime_hours'], 1) }}</p>
            </div>

            <!-- Month Salary Payable -->
            <div class="bg-white rounded-lg shadow p-4 md:p-6">
                <p class="text-gray-500 text-xs md:text-sm mb-1">Payable</p>
                <p class="text-xl md:text-2xl font-bold text-indigo-600">{{ number_format($salary_summary['total_salary_payable'], 0) }}</p>
            </div>

            <!-- Total Advance Balance -->
            <div class="bg-white rounded-lg shadow p-4 md:p-6">
                <p class="text-gray-500 text-xs md:text-sm mb-1">Adv. Balance</p>
                <p class="text-xl md:text-2xl font-bold text-orange-600">{{ number_format($salary_summary['total_advance_balance'], 0) }}</p>
            </div>

            <!-- Total Salary Due -->
            <div class="bg-white rounded-lg shadow p-4 md:p-6">
                <p class="text-gray-500 text-xs md:text-sm mb-1">Total Due</p>
                <p class="text-xl md:text-2xl font-bold text-red-600">{{ number_format($salary_summary['total_due'], 0) }}</p>
            </div>
        </div>

        <!-- Payment Status -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-white rounded-lg shadow p-4 md:p-6">
                <p class="text-gray-500 text-sm mb-2">Total Paid This Month</p>
                <p class="text-2xl md:text-3xl font-bold text-green-600">{{ number_format($salary_summary['total_paid_this_month'], 0) }}</p>
                <p class="text-xs text-gray-500 mt-2">{{ number_format(($salary_summary['total_salary_payable'] > 0 ? ($salary_summary['total_paid_this_month'] / $salary_summary['total_salary_payable'] * 100) : 0), 1) }}% Paid</p>
            </div>
            <div class="bg-white rounded-lg shadow p-4 md:p-6">
                <p class="text-gray-500 text-sm mb-2">Remaining Due</p>
                <p class="text-2xl md:text-3xl font-bold text-red-600">{{ number_format($salary_summary['total_due'], 0) }}</p>
                <p class="text-xs text-gray-500 mt-2">{{ number_format(($salary_summary['total_salary_payable'] > 0 ? ($salary_summary['total_due'] / $salary_summary['total_salary_payable'] * 100) : 0), 1) }}% Pending</p>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="mb-8">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <a href="{{ route('attendance.index') }}" class="bg-blue-50 hover:bg-blue-100 rounded-lg p-4 text-center transition">
                <svg class="w-8 h-8 text-blue-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-xs md:text-sm font-medium text-blue-900">Daily Hajira</p>
            </a>

            <a href="{{ route('employees.index') }}" class="bg-green-50 hover:bg-green-100 rounded-lg p-4 text-center transition">
                <svg class="w-8 h-8 text-green-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                <p class="text-xs md:text-sm font-medium text-green-900">Add Employee</p>
            </a>

            <a href="{{ route('advances.index') }}" class="bg-purple-50 hover:bg-purple-100 rounded-lg p-4 text-center transition">
                <svg class="w-8 h-8 text-purple-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-xs md:text-sm font-medium text-purple-900">Add Advance</p>
            </a>

            <a href="{{ route('salaries.index') }}" class="bg-pink-50 hover:bg-pink-100 rounded-lg p-4 text-center transition">
                <svg class="w-8 h-8 text-pink-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
                <p class="text-xs md:text-sm font-medium text-pink-900">Generate Salary</p>
            </a>
        </div>
    </div>

    <!-- Recent Activity (2 column grid on mobile, 2 column on desktop) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Advances -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-4 md:p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Recent Advances</h3>
            </div>
            <div class="divide-y divide-gray-200">
                @forelse($recent_advances as $advance)
                    <div class="p-4 md:p-6 hover:bg-gray-50 transition">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <p class="font-medium text-gray-900 text-sm md:text-base">{{ $advance['employee_name'] }}</p>
                                <p class="text-xs text-gray-500 mt-1">{{ $advance['reason'] ?? 'No reason provided' }}</p>
                                <p class="text-xs text-gray-400 mt-1">{{ \Carbon\Carbon::parse($advance['date'])->format('M d, Y') }}</p>
                            </div>
                            <p class="font-bold text-orange-600 text-sm md:text-base">{{ number_format($advance['amount'], 0) }}</p>
                        </div>
                    </div>
                @empty
                    <div class="p-4 md:p-6 text-center text-gray-500 text-sm">
                        No recent advances
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Recent Payments -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-4 md:p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Recent Payments</h3>
            </div>
            <div class="divide-y divide-gray-200">
                @forelse($recent_payments as $payment)
                    <div class="p-4 md:p-6 hover:bg-gray-50 transition">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <p class="font-medium text-gray-900 text-sm md:text-base">{{ $payment['employee_name'] }}</p>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="inline-block px-2 py-1 bg-gray-100 text-gray-700 rounded text-xs font-medium capitalize">
                                        {{ str_replace('_', ' ', $payment['payment_method']) }}
                                    </span>
                                    <p class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($payment['payment_date'])->format('M d, Y') }}</p>
                                </div>
                            </div>
                            <p class="font-bold text-green-600 text-sm md:text-base">{{ number_format($payment['amount'], 0) }}</p>
                        </div>
                    </div>
                @empty
                    <div class="p-4 md:p-6 text-center text-gray-500 text-sm">
                        No recent payments
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
