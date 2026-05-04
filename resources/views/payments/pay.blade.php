@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-100 py-4 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <h1 class="text-3xl font-bold text-gray-900">Record Payment</h1>
            <a href="{{ route('payments.index', ['month' => $salarySheet->month]) }}" class="mt-4 sm:mt-0 text-blue-600 hover:text-blue-900">← Back</a>
        </div>

        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                <ul class="list-disc ml-6 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li class="text-red-700 text-sm">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left: Payment Form -->
            <div class="lg:col-span-2">
                <!-- Salary Info Card -->
                <div class="bg-white rounded-lg shadow p-6 mb-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">{{ $salarySheet->employee->name }}</h2>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <div class="bg-blue-50 rounded-lg p-3">
                            <p class="text-gray-600 text-xs">Net Salary</p>
                            <p class="text-lg font-bold text-blue-600 mt-1">{{ number_format($salarySheet->net_salary, 2) }}</p>
                        </div>
                        <div class="bg-green-50 rounded-lg p-3">
                            <p class="text-gray-600 text-xs">Paid</p>
                            <p class="text-lg font-bold text-green-600 mt-1">{{ number_format($salarySheet->paid_amount, 2) }}</p>
                        </div>
                        <div class="bg-orange-50 rounded-lg p-3">
                            <p class="text-gray-600 text-xs">Due</p>
                            <p class="text-lg font-bold text-orange-600 mt-1">{{ number_format($salarySheet->due_amount, 2) }}</p>
                        </div>
                        <div class="bg-purple-50 rounded-lg p-3">
                            <p class="text-gray-600 text-xs">Month</p>
                            <p class="text-lg font-bold text-purple-600 mt-1">{{ $salarySheet->month }}</p>
                        </div>
                    </div>
                </div>

                <!-- Payment Form -->
                <form action="{{ route('payments.store', $salarySheet) }}" method="POST" class="bg-white rounded-lg shadow p-6">
                    @csrf

                    <div class="mb-6">
                        <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">
                            Payment Amount <span class="text-red-600">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-4 top-2 text-gray-600">PKR</span>
                            <input type="number" name="amount" id="amount" step="0.01" value="{{ old('amount') }}" 
                                max="{{ $salarySheet->due_amount }}" placeholder="0.00"
                                class="w-full pl-12 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-blue-500 @error('amount') border-red-500 @enderror">
                        </div>
                        <p class="text-xs text-gray-600 mt-1">Maximum: {{ number_format($salarySheet->due_amount, 2) }}</p>
                        @error('amount')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-6">
                        <label for="payment_date" class="block text-sm font-medium text-gray-700 mb-2">
                            Payment Date <span class="text-red-600">*</span>
                        </label>
                        <input type="date" name="payment_date" id="payment_date" value="{{ old('payment_date', date('Y-m-d')) }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-blue-500 @error('payment_date') border-red-500 @enderror">
                        @error('payment_date')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-6">
                        <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-2">
                            Payment Method <span class="text-red-600">*</span>
                        </label>
                        <select name="payment_method" id="payment_method" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-blue-500 @error('payment_method') border-red-500 @enderror">
                            <option value="">Select method...</option>
                            <option value="cash" {{ old('payment_method') === 'cash' ? 'selected' : '' }}>Cash</option>
                            <option value="bank" {{ old('payment_method') === 'bank' ? 'selected' : '' }}>Bank Transfer</option>
                            <option value="mobile_banking" {{ old('payment_method') === 'mobile_banking' ? 'selected' : '' }}>Mobile Banking</option>
                        </select>
                        @error('payment_method')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-6">
                        <label for="note" class="block text-sm font-medium text-gray-700 mb-2">Note (Optional)</label>
                        <textarea name="note" id="note" rows="3" placeholder="Add any notes about this payment..."
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-blue-500">{{ old('note') }}</textarea>
                        @error('note')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex gap-4">
                        <button type="submit" class="flex-1 px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 font-semibold">
                            Record Payment
                        </button>
                        <a href="{{ route('payments.show', $salarySheet) }}" class="flex-1 px-6 py-2 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400 font-semibold text-center">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>

            <!-- Right: Payment History -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow p-6 sticky top-4">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Payment History</h3>
                    
                    @if (count($paymentHistory) > 0)
                        <div class="space-y-3">
                            @foreach ($paymentHistory as $payment)
                                <div class="border-b pb-3 last:border-b-0">
                                    <div class="flex justify-between items-start mb-1">
                                        <span class="text-sm font-semibold text-gray-900">{{ number_format($payment->amount, 2) }}</span>
                                        <span class="text-xs px-2 py-1 rounded
                                            {{ $payment->payment_method === 'cash' ? 'bg-blue-100 text-blue-800' : '' }}
                                            {{ $payment->payment_method === 'bank' ? 'bg-green-100 text-green-800' : '' }}
                                            {{ $payment->payment_method === 'mobile_banking' ? 'bg-purple-100 text-purple-800' : '' }}
                                        ">{{ str_replace('_', ' ', ucfirst($payment->payment_method)) }}</span>
                                    </div>
                                    <p class="text-xs text-gray-600">{{ $payment->payment_date }}</p>
                                    @if ($payment->note)
                                        <p class="text-xs text-gray-500 mt-1">{{ $payment->note }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-4 pt-4 border-t">
                            <p class="text-sm text-gray-600">Total Payments</p>
                            <p class="text-2xl font-bold text-gray-900">{{ count($paymentHistory) }}</p>
                        </div>
                    @else
                        <p class="text-gray-600 text-sm text-center py-6">No payments recorded yet</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
