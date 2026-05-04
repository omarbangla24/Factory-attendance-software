@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-100 py-4 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-4xl font-bold text-gray-900">Reports</h1>
            <p class="text-gray-600 mt-2">View and analyze payroll data</p>
        </div>

        <!-- Reports Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Monthly Hajira Report -->
            <a href="{{ route('reports.monthly-hajira') }}" class="bg-white rounded-lg shadow hover:shadow-lg transition p-6">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900">Monthly Hajira</h3>
                <p class="text-sm text-gray-600 mt-2">Attendance summary by month and employee</p>
            </a>

            <!-- Overtime Report -->
            <a href="{{ route('reports.overtime') }}" class="bg-white rounded-lg shadow hover:shadow-lg transition p-6">
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 2m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900">Overtime</h3>
                <p class="text-sm text-gray-600 mt-2">Hours and amounts by employee</p>
            </a>

            <!-- Absent Report -->
            <a href="{{ route('reports.absent') }}" class="bg-white rounded-lg shadow hover:shadow-lg transition p-6">
                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900">Absent Records</h3>
                <p class="text-sm text-gray-600 mt-2">Absence dates and counts</p>
            </a>

            <!-- Advance Report -->
            <a href="{{ route('reports.advance') }}" class="bg-white rounded-lg shadow hover:shadow-lg transition p-6">
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900">Advances</h3>
                <p class="text-sm text-gray-600 mt-2">Advance given, deducted, and balance</p>
            </a>

            <!-- Salary Sheet Report -->
            <a href="{{ route('reports.salary-sheet') }}" class="bg-white rounded-lg shadow hover:shadow-lg transition p-6">
                <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900">Salary Sheets</h3>
                <p class="text-sm text-gray-600 mt-2">Complete salary breakdown by month</p>
            </a>

            <!-- Payment Report -->
            <a href="{{ route('reports.payment') }}" class="bg-white rounded-lg shadow hover:shadow-lg transition p-6">
                <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900">Payments</h3>
                <p class="text-sm text-gray-600 mt-2">Payment history and method breakdown</p>
            </a>

            <!-- Employee Ledger -->
            <a href="{{ route('reports.employee-ledger') }}" class="bg-white rounded-lg shadow hover:shadow-lg transition p-6">
                <div class="w-12 h-12 bg-cyan-100 rounded-lg flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900">Employee Ledger</h3>
                <p class="text-sm text-gray-600 mt-2">Complete record for individual employee</p>
            </a>

            <!-- Accounts Summary -->
            <a href="{{ route('reports.accounts-summary') }}" class="bg-white rounded-lg shadow hover:shadow-lg transition p-6">
                <div class="w-12 h-12 bg-pink-100 rounded-lg flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900">Accounts Summary</h3>
                <p class="text-sm text-gray-600 mt-2">Financial overview for the month</p>
            </a>
        </div>
    </div>
</div>
@endsection
