@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-100 py-4 px-4 sm:px-6 lg:px-8">
    <div class="max-w-2xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Lock & Finalize Salary</h1>
            <p class="text-gray-600 mt-1">Review and lock the salary sheet</p>
        </div>

        <!-- Salary Card -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <div class="mb-6 pb-6 border-b border-gray-200">
                <h2 class="text-2xl font-bold text-gray-900">{{ $salarySheet->employee->name }}</h2>
                <p class="text-gray-600">{{ $salarySheet->employee->department }} • {{ $salarySheet->month }}</p>
            </div>

            <!-- Calculation Breakdown -->
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Salary Breakdown</h3>
                <div class="space-y-3 bg-gray-50 p-4 rounded-lg">
                    <div class="flex justify-between">
                        <span class="text-gray-700">Basic Amount</span>
                        <span class="font-semibold">{{ number_format($salarySheet->basic_amount, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-700">Overtime Amount</span>
                        <span class="font-semibold">{{ number_format($salarySheet->overtime_amount, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-700">Current Adjustment</span>
                        <span class="font-semibold">{{ number_format($salarySheet->adjustment_amount, 2) }}</span>
                    </div>
                    <div class="border-t pt-3 flex justify-between">
                        <span class="text-gray-700">Subtotal</span>
                        <span class="font-semibold">{{ number_format($salarySheet->basic_amount + $salarySheet->overtime_amount + $salarySheet->adjustment_amount, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-700">Advance Deducted</span>
                        <span class="font-semibold text-red-600">-{{ number_format($salarySheet->advance_deducted, 2) }}</span>
                    </div>
                    <div class="border-t pt-3 flex justify-between bg-white -mx-4 -mb-4 px-4 py-3">
                        <span class="font-bold text-gray-900">Net Salary</span>
                        <span class="font-bold text-2xl text-gray-900">{{ number_format($salarySheet->net_salary, 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Adjustment Form -->
            <form action="{{ route('salaries.update', $salarySheet) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-6">
                    <label for="adjustment_amount" class="block text-sm font-medium text-gray-700 mb-2">
                        Adjustment Amount
                        <span class="text-gray-600 text-xs">(Optional - positive or negative)</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-4 top-2 text-gray-600">{{ $currencySymbol }}</span>
                        <input type="number" name="adjustment_amount" id="adjustment_amount" step="0.01" value="{{ old('adjustment_amount', $salarySheet->adjustment_amount) }}" class="w-full pl-12 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-blue-500 @error('adjustment_amount') border-red-500 @enderror">
                    </div>
                    <p class="text-xs text-gray-600 mt-1">Will be added to basic + overtime (positive) or subtracted (negative)</p>
                    @error('adjustment_amount')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Deductions Info -->
                @if ($salarySheet->deductions->count() > 0)
                    <div class="mb-6 p-4 bg-orange-50 border border-orange-200 rounded-lg">
                        <h4 class="font-semibold text-orange-900 mb-2">Advance Deductions</h4>
                        <div class="space-y-2">
                            @foreach ($salarySheet->deductions as $deduction)
                                <div class="flex justify-between text-sm">
                                    <span class="text-orange-800">{{ $deduction->advance->reason }}</span>
                                    <span class="font-semibold text-orange-900">-{{ number_format($deduction->amount, 2) }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Preview -->
                <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <p class="text-sm text-blue-800">
                        <strong>Final Net Salary:</strong>
                        <span id="final-net" class="font-bold text-lg text-blue-600 float-right">{{ number_format($salarySheet->net_salary, 2) }}</span>
                    </p>
                </div>

                <!-- Actions -->
                <div class="flex gap-4">
                    <button type="submit" class="flex-1 px-4 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 font-semibold">
                        Lock & Finalize
                    </button>
                    <a href="{{ route('salaries.show', $salarySheet) }}" class="flex-1 px-4 py-3 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400 font-semibold text-center">
                        Cancel
                    </a>
                </div>
            </form>
        </div>

        <!-- Info Box -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <p class="text-sm text-blue-800">
                <strong>⚠️ Important:</strong> Once locked, this salary sheet cannot be edited. Make sure all information is correct before finalizing.
            </p>
        </div>
    </div>
</div>

<script>
document.getElementById('adjustment_amount').addEventListener('input', function() {
    const basic = {{ $salarySheet->basic_amount }};
    const overtime = {{ $salarySheet->overtime_amount }};
    const adjustment = parseFloat(this.value) || 0;
    const deducted = {{ $salarySheet->advance_deducted }};
    
    const netSalary = basic + overtime + adjustment - deducted;
    document.getElementById('final-net').textContent = netSalary.toFixed(2);
});
</script>
@endsection
