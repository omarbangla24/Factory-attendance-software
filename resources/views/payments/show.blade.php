@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-100 py-4 px-4 sm:px-6 lg:px-8">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <h1 class="text-3xl font-bold text-gray-900">Payment Details</h1>
            <a href="{{ route('payments.index', ['month' => $salarySheet->month]) }}" class="mt-4 sm:mt-0 text-blue-600 hover:text-blue-900">← Back</a>
        </div>

        @if (session('success'))
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6 text-green-800">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2">
                <!-- Employee & Salary Info -->
                <div class="bg-white rounded-lg shadow p-6 mb-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">{{ $salarySheet->employee->name }}</h2>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <div>
                            <p class="text-gray-600 text-sm">Department</p>
                            <p class="font-semibold text-gray-900">{{ $salarySheet->employee->department }}</p>
                        </div>
                        <div>
                            <p class="text-gray-600 text-sm">Month</p>
                            <p class="font-semibold text-gray-900">{{ $salarySheet->month }}</p>
                        </div>
                        <div>
                            <p class="text-gray-600 text-sm">Net Salary</p>
                            <p class="font-semibold text-gray-900">{{ number_format($salarySheet->net_salary, 2) }}</p>
                        </div>
                        <div>
                            <p class="text-gray-600 text-sm">Status</p>
                            <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold
                                {{ $salarySheet->status === 'locked' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                {{ $salarySheet->status === 'partial' ? 'bg-blue-100 text-blue-800' : '' }}
                                {{ $salarySheet->status === 'paid' ? 'bg-green-100 text-green-800' : '' }}
                            ">{{ ucfirst($salarySheet->status) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Payment Summary -->
                <div class="bg-white rounded-lg shadow p-6 mb-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Payment Summary</h3>
                    <div class="space-y-4">
                        <div class="flex justify-between border-b pb-3">
                            <span class="text-gray-700">Net Salary</span>
                            <span class="font-semibold">{{ number_format($salarySheet->net_salary, 2) }}</span>
                        </div>
                        <div class="flex justify-between border-b pb-3">
                            <span class="text-gray-700">Total Paid</span>
                            <span class="font-semibold text-green-600">{{ number_format($salarySheet->paid_amount, 2) }}</span>
                        </div>
                        <div class="flex justify-between bg-orange-50 p-3 rounded-lg">
                            <span class="font-bold text-gray-900">Due Amount</span>
                            <span class="font-bold text-orange-600">{{ number_format($salarySheet->due_amount, 2) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Payment History Table -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Payment History</h3>
                    
                    @if (count($paymentHistory) > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-gray-50 border-b">
                                    <tr>
                                        <th class="px-4 py-2 text-left font-semibold text-gray-900">Date</th>
                                        <th class="px-4 py-2 text-left font-semibold text-gray-900">Amount</th>
                                        <th class="px-4 py-2 text-left font-semibold text-gray-900">Method</th>
                                        <th class="px-4 py-2 text-left font-semibold text-gray-900">Note</th>
                                        <th class="px-4 py-2 text-left font-semibold text-gray-900">By</th>
                                        <th class="px-4 py-2 text-center font-semibold text-gray-900">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y">
                                    @foreach ($paymentHistory as $payment)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-2">{{ $payment->payment_date }}</td>
                                            <td class="px-4 py-2 font-semibold">{{ number_format($payment->amount, 2) }}</td>
                                            <td class="px-4 py-2">
                                                <span class="inline-block px-2 py-1 rounded text-xs font-semibold
                                                    {{ $payment->payment_method === 'cash' ? 'bg-blue-100 text-blue-800' : '' }}
                                                    {{ $payment->payment_method === 'bank' ? 'bg-green-100 text-green-800' : '' }}
                                                    {{ $payment->payment_method === 'mobile_banking' ? 'bg-purple-100 text-purple-800' : '' }}
                                                ">{{ str_replace('_', ' ', ucfirst($payment->payment_method)) }}</span>
                                            </td>
                                            <td class="px-4 py-2">{{ $payment->note ?? '-' }}</td>
                                            <td class="px-4 py-2 text-gray-600">{{ $payment->createdByUser->name ?? '-' }}</td>
                                            <td class="px-4 py-2 text-center">
                                                <form action="{{ route('payments.destroy', $payment) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to reverse this payment?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900 text-sm">Reverse</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-gray-600 text-center py-8">No payments recorded yet</p>
                    @endif
                </div>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <!-- Quick Stats -->
                <div class="bg-white rounded-lg shadow p-6 mb-6 sticky top-4">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Quick Stats</h3>
                    <div class="space-y-4">
                        <div class="bg-green-50 p-4 rounded-lg">
                            <p class="text-gray-600 text-sm">Total Paid</p>
                            <p class="text-2xl font-bold text-green-600 mt-1">{{ number_format($salarySheet->paid_amount, 2) }}</p>
                        </div>
                        <div class="bg-orange-50 p-4 rounded-lg">
                            <p class="text-gray-600 text-sm">Due Amount</p>
                            <p class="text-2xl font-bold text-orange-600 mt-1">{{ number_format($salarySheet->due_amount, 2) }}</p>
                        </div>
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <p class="text-gray-600 text-sm">Total Payments</p>
                            <p class="text-2xl font-bold text-blue-600 mt-1">{{ count($paymentHistory) }}</p>
                        </div>
                        
                        @if ($salarySheet->due_amount > 0)
                            <a href="{{ route('payments.pay', $salarySheet) }}" class="block w-full px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-center font-medium">
                                Record Payment
                            </a>
                        @else
                            <div class="bg-green-100 text-green-800 p-3 rounded-lg text-center font-semibold">
                                ✓ Fully Paid
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Payment Status Info -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <p class="text-sm text-blue-800">
                        <strong>Status Logic:</strong>
                    </p>
                    <ul class="list-disc ml-6 mt-2 text-sm text-blue-700 space-y-1">
                        <li>Locked: No payments yet</li>
                        <li>Partial: Some amount paid</li>
                        <li>Paid: Fully paid</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
