@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-100 py-4 px-4 sm:px-6 lg:px-8 print:hidden">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <h1 class="text-3xl font-bold text-gray-900">Salary Details</h1>
            <div class="flex items-center gap-3 mt-4 sm:mt-0">
                <button onclick="window.print()"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    Print Receipt
                </button>
                <a href="{{ route('salaries.index', ['month' => $salarySheet->month]) }}" class="text-blue-600 hover:text-blue-900">← Back to Salaries</a>
            </div>
        </div>

        @if ($hasPendingAdvances)
            <div class="bg-amber-50 border border-amber-300 rounded-lg p-4 mb-6 flex items-start gap-3">
                <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <div class="flex-1">
                    <p class="text-sm font-semibold text-amber-800">Pending advance not yet deducted</p>
                    <p class="text-xs text-amber-700 mt-0.5">A new advance was added after this salary was generated. Apply it now to update the net salary without regenerating attendance data.</p>
                </div>
                <form action="{{ route('salaries.apply-advances', $salarySheet) }}" method="POST" class="shrink-0">
                    @csrf
                    @method('PATCH')
                    <button type="submit"
                            onclick="return confirm('Apply pending advances to this salary sheet?')"
                            class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold rounded-lg transition-colors whitespace-nowrap">
                        Apply Advances
                    </button>
                </form>
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                <h3 class="font-bold text-red-800 mb-2">Errors:</h3>
                <ul class="list-disc ml-6 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li class="text-red-700">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('success'))
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6 text-green-800">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6 text-red-800">
                {{ session('error') }}
            </div>
        @endif

        <!-- Main Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column: Salary Details -->
            <div class="lg:col-span-2">
                <!-- Employee Info Card -->
                <div class="bg-white rounded-lg shadow p-6 mb-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Employee Information</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <p class="text-gray-600 text-sm">Name</p>
                            <p class="font-semibold text-gray-900">{{ $salarySheet->employee->name }}</p>
                        </div>
                        <div>
                            <p class="text-gray-600 text-sm">Department</p>
                            <p class="font-semibold text-gray-900">{{ $salarySheet->employee->department }}</p>
                        </div>
                        <div>
                            <p class="text-gray-600 text-sm">Hajira Rate</p>
                            <p class="font-semibold text-gray-900">{{ number_format($salarySheet->employee->hajira_rate, 2) }}</p>
                        </div>
                        <div>
                            <p class="text-gray-600 text-sm">Overtime Rate</p>
                            <p class="font-semibold text-gray-900">{{ number_format($salarySheet->employee->overtime_rate, 2) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Attendance Summary Card -->
                <div class="bg-white rounded-lg shadow p-6 mb-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Attendance Summary</h2>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <div class="bg-blue-50 rounded-lg p-4">
                            <p class="text-gray-600 text-sm">Total Hajira</p>
                            <p class="text-2xl font-bold text-blue-600 mt-1">{{ number_format($salarySheet->total_hajira, 1) }}</p>
                        </div>
                        <div class="bg-red-50 rounded-lg p-4">
                            <p class="text-gray-600 text-sm">Absent Days</p>
                            <p class="text-2xl font-bold text-red-600 mt-1">{{ $salarySheet->absent_days }}</p>
                        </div>
                        <div class="bg-purple-50 rounded-lg p-4">
                            <p class="text-gray-600 text-sm">Overtime Hours</p>
                            <p class="text-2xl font-bold text-purple-600 mt-1">{{ number_format($salarySheet->total_overtime_hours, 1) }}</p>
                        </div>
                        <div class="bg-green-50 rounded-lg p-4">
                            <p class="text-gray-600 text-sm">Month</p>
                            <p class="text-xl font-bold text-green-600 mt-1">{{ $salarySheet->month }}</p>
                        </div>
                    </div>
                </div>

                <!-- Salary Calculation Card -->
                <div class="bg-white rounded-lg shadow p-6 mb-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Salary Calculation</h2>
                    <div class="space-y-4">
                        <div class="flex justify-between border-b pb-3">
                            <span class="text-gray-700">Basic Amount (Hajira × Rate)</span>
                            <span class="font-semibold">{{ number_format($salarySheet->total_hajira, 1) }} × {{ number_format($salarySheet->employee->hajira_rate, 2) }} = <strong class="text-gray-900">{{ $currencySymbol }}{{ number_format($salarySheet->basic_amount, 2) }}</strong></span>
                        </div>
                        <div class="flex justify-between border-b pb-3">
                            <span class="text-gray-700">Overtime Amount (Hours × Rate)</span>
                            <span class="font-semibold">{{ number_format($salarySheet->total_overtime_hours, 1) }} × {{ number_format($salarySheet->employee->overtime_rate, 2) }} = <strong class="text-gray-900">{{ $currencySymbol }}{{ number_format($salarySheet->overtime_amount, 2) }}</strong></span>
                        </div>
                        <div class="flex justify-between border-b pb-3">
                            <span class="text-gray-700">Adjustment Amount</span>
                            <span class="font-semibold text-gray-900">{{ $currencySymbol }}{{ number_format($salarySheet->adjustment_amount, 2) }}</span>
                        </div>
                        <div class="flex justify-between border-b pb-3">
                            <span class="text-gray-700">Advance Deducted</span>
                            <span class="font-semibold text-red-600">-{{ $currencySymbol }}{{ number_format($salarySheet->advance_deducted, 2) }}</span>
                        </div>
                        <div class="flex justify-between bg-gray-50 p-3 rounded-lg">
                            <span class="font-bold text-gray-900">Net Salary</span>
                            <span class="font-bold text-lg text-gray-900">{{ $currencySymbol }}{{ number_format($salarySheet->net_salary, 2) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Advance Deductions Table -->
                @if ($salarySheet->deductions->count() > 0)
                    <div class="bg-white rounded-lg shadow p-6 mb-6">
                        <h2 class="text-xl font-bold text-gray-900 mb-4">Advance Deductions</h2>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-gray-50 border-b">
                                    <tr>
                                        <th class="px-4 py-2 text-left font-semibold text-gray-900">Date</th>
                                        <th class="px-4 py-2 text-left font-semibold text-gray-900">Reason</th>
                                        <th class="px-4 py-2 text-right font-semibold text-gray-900">Advance</th>
                                        <th class="px-4 py-2 text-right font-semibold text-gray-900">Deducted</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y">
                                    @foreach ($salarySheet->deductions as $deduction)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-2">{{ $deduction->advance->date }}</td>
                                            <td class="px-4 py-2">{{ $deduction->advance->reason }}</td>
                                            <td class="px-4 py-2 text-right">{{ $currencySymbol }}{{ number_format($deduction->advance->amount, 2) }}</td>
                                            <td class="px-4 py-2 text-right font-semibold text-red-600">{{ $currencySymbol }}{{ number_format($deduction->amount, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Right Column: Status & Actions -->
            <div class="lg:col-span-1">
                <!-- Status Card -->
                <div class="bg-white rounded-lg shadow p-6 mb-6 sticky top-4">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Status</h2>
                    
                    <div class="mb-6">
                        <span class="inline-block px-4 py-2 rounded-full text-sm font-semibold
                            {{ $salarySheet->status === 'draft' ? 'bg-yellow-100 text-yellow-800' : '' }}
                            {{ $salarySheet->status === 'locked' ? 'bg-green-100 text-green-800' : '' }}
                            {{ $salarySheet->status === 'partial' ? 'bg-blue-100 text-blue-800' : '' }}
                            {{ $salarySheet->status === 'paid' ? 'bg-purple-100 text-purple-800' : '' }}
                        ">
                            {{ ucfirst($salarySheet->status) }}
                        </span>
                    </div>

                    @if ($salarySheet->status === 'draft')
                        <div class="space-y-2">
                            <a href="{{ route('salaries.edit', $salarySheet) }}" class="block w-full px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-center font-medium">
                                Lock & Finalize
                            </a>
                            <form action="{{ route('salaries.regenerate', $salarySheet) }}" method="POST" class="inline-block w-full">
                                @csrf
                                @method('PATCH')
                                <button type="submit" onclick="return confirm('Regenerate with latest attendance data?')" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
                                    Regenerate
                                </button>
                            </form>
                        </div>
                    @endif

                    @if ($salarySheet->locked_at)
                        <div class="mt-4 p-3 bg-gray-50 rounded-lg">
                            <p class="text-xs text-gray-600">Locked on</p>
                            <p class="font-semibold text-gray-900">{{ $salarySheet->locked_at->format('d M Y H:i') }}</p>
                        </div>
                    @endif
                </div>

                <!-- Payment Summary -->
                <div class="bg-white rounded-lg shadow p-6 mb-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Payment Summary</h2>
                    <div class="space-y-3">
                        <div class="flex justify-between border-b pb-2">
                            <span class="text-gray-600">Net Salary</span>
                            <span class="font-semibold">{{ $currencySymbol }}{{ number_format($salarySheet->net_salary, 2) }}</span>
                        </div>
                        <div class="flex justify-between border-b pb-2">
                            <span class="text-gray-600">Paid</span>
                            <span class="font-semibold text-green-600">{{ $currencySymbol }}{{ number_format($salarySheet->paid_amount, 2) }}</span>
                        </div>
                        <div class="flex justify-between bg-orange-50 p-2 rounded">
                            <span class="font-bold text-gray-900">Due</span>
                            <span class="font-bold text-orange-600">{{ $currencySymbol }}{{ number_format($salarySheet->due_amount, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════ PRINT RECEIPT ═══════════════════════════ --}}
<div class="hidden print:block print-receipt">
    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 24px; border: 1px solid #ddd;">

        {{-- Header --}}
        <div style="text-align:center; border-bottom: 2px solid #1e40af; padding-bottom: 16px; margin-bottom: 16px;">
            <h1 style="font-size: 22px; font-weight: bold; color: #1e40af; margin:0;">{{ $companyName }}</h1>
            @if($companyPhone)<p style="margin:4px 0; font-size:13px; color:#555;">Phone: {{ $companyPhone }}</p>@endif
            @if($companyAddress)<p style="margin:4px 0; font-size:13px; color:#555;">{{ $companyAddress }}</p>@endif
            <h2 style="font-size:16px; font-weight:bold; color:#111; margin-top:12px; letter-spacing:1px;">SALARY RECEIPT</h2>
            <p style="font-size:13px; color:#555; margin:2px 0;">Month: {{ \Carbon\Carbon::parse($salarySheet->month.'-01')->format('F Y') }}</p>
        </div>

        {{-- Employee Info --}}
        <table style="width:100%; border-collapse:collapse; margin-bottom:16px; font-size:13px;">
            <tr>
                <td style="padding:4px 0; color:#555; width:40%;">Employee Name</td>
                <td style="padding:4px 0; font-weight:bold;">: {{ $salarySheet->employee->name }}</td>
                <td style="padding:4px 0; color:#555; width:30%;">Department</td>
                <td style="padding:4px 0; font-weight:bold;">: {{ $salarySheet->employee->department }}</td>
            </tr>
            <tr>
                <td style="padding:4px 0; color:#555;">Hajira Rate</td>
                <td style="padding:4px 0; font-weight:bold;">: {{ $currencySymbol }}{{ number_format($salarySheet->employee->hajira_rate, 2) }}</td>
                <td style="padding:4px 0; color:#555;">OT Rate</td>
                <td style="padding:4px 0; font-weight:bold;">: {{ $currencySymbol }}{{ number_format($salarySheet->employee->overtime_rate, 2) }}/hr</td>
            </tr>
        </table>

        {{-- Attendance Summary --}}
        <table style="width:100%; border-collapse:collapse; margin-bottom:16px; font-size:13px; background:#f0f7ff; border-radius:6px;">
            <thead>
                <tr style="background:#1e40af; color:#fff;">
                    <th style="padding:8px; text-align:center;">Total Hajira</th>
                    <th style="padding:8px; text-align:center;">Absent Days</th>
                    <th style="padding:8px; text-align:center;">OT Hours</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="padding:8px; text-align:center; font-weight:bold; font-size:15px;">{{ number_format($salarySheet->total_hajira, 1) }}</td>
                    <td style="padding:8px; text-align:center; font-weight:bold; font-size:15px; color:#dc2626;">{{ $salarySheet->absent_days }}</td>
                    <td style="padding:8px; text-align:center; font-weight:bold; font-size:15px; color:#7c3aed;">{{ number_format($salarySheet->total_overtime_hours, 1) }}h</td>
                </tr>
            </tbody>
        </table>

        {{-- Salary Calculation --}}
        <table style="width:100%; border-collapse:collapse; font-size:13px; margin-bottom:16px;">
            <tr style="border-bottom:1px solid #e5e7eb;">
                <td style="padding:7px 4px; color:#555;">Basic Amount</td>
                <td style="padding:7px 4px; color:#555; text-align:center;">{{ number_format($salarySheet->total_hajira,1) }} × {{ $currencySymbol }}{{ number_format($salarySheet->employee->hajira_rate,2) }}</td>
                <td style="padding:7px 4px; font-weight:bold; text-align:right;">{{ $currencySymbol }}{{ number_format($salarySheet->basic_amount, 2) }}</td>
            </tr>
            <tr style="border-bottom:1px solid #e5e7eb;">
                <td style="padding:7px 4px; color:#555;">Overtime</td>
                <td style="padding:7px 4px; color:#555; text-align:center;">{{ number_format($salarySheet->total_overtime_hours,1) }}h × {{ $currencySymbol }}{{ number_format($salarySheet->employee->overtime_rate,2) }}</td>
                <td style="padding:7px 4px; font-weight:bold; text-align:right;">{{ $currencySymbol }}{{ number_format($salarySheet->overtime_amount, 2) }}</td>
            </tr>
            @if($salarySheet->adjustment_amount != 0)
            <tr style="border-bottom:1px solid #e5e7eb;">
                <td style="padding:7px 4px; color:#555;" colspan="2">Adjustment</td>
                <td style="padding:7px 4px; font-weight:bold; text-align:right;">{{ $currencySymbol }}{{ number_format($salarySheet->adjustment_amount, 2) }}</td>
            </tr>
            @endif
            @if($salarySheet->advance_deducted > 0)
            <tr style="border-bottom:1px solid #e5e7eb;">
                <td style="padding:7px 4px; color:#dc2626;" colspan="2">Advance Deducted</td>
                <td style="padding:7px 4px; font-weight:bold; text-align:right; color:#dc2626;">-{{ $currencySymbol }}{{ number_format($salarySheet->advance_deducted, 2) }}</td>
            </tr>
            @endif
            <tr style="background:#1e40af; color:#fff;">
                <td style="padding:10px 4px; font-weight:bold; font-size:15px;" colspan="2">NET SALARY</td>
                <td style="padding:10px 4px; font-weight:bold; font-size:15px; text-align:right;">{{ $currencySymbol }}{{ number_format($salarySheet->net_salary, 2) }}</td>
            </tr>
        </table>

        {{-- Payment Status --}}
        <table style="width:100%; border-collapse:collapse; font-size:13px; margin-bottom:20px;">
            <tr>
                <td style="padding:5px 4px; color:#555;">Paid Amount</td>
                <td style="padding:5px 4px; font-weight:bold; color:#16a34a; text-align:right;">{{ $currencySymbol }}{{ number_format($salarySheet->paid_amount, 2) }}</td>
            </tr>
            <tr>
                <td style="padding:5px 4px; color:#555;">Due Amount</td>
                <td style="padding:5px 4px; font-weight:bold; color:#ea580c; text-align:right;">{{ $currencySymbol }}{{ number_format($salarySheet->due_amount, 2) }}</td>
            </tr>
        </table>

        {{-- Signatures --}}
        <div style="display:flex; justify-content:space-between; margin-top:40px; font-size:12px; color:#555;">
            <div style="text-align:center; width:40%;">
                <div style="border-top:1px solid #333; padding-top:6px;">Employee Signature</div>
            </div>
            <div style="text-align:center; width:40%;">
                <div style="border-top:1px solid #333; padding-top:6px;">Authorized Signature</div>
            </div>
        </div>

        {{-- Footer --}}
        <p style="text-align:center; font-size:11px; color:#aaa; margin-top:20px; border-top:1px solid #eee; padding-top:8px;">
            Printed on {{ now()->format('d M Y, H:i') }} &mdash; {{ $companyName }}
        </p>
    </div>
</div>

<style>
@media print {
    body * { visibility: hidden !important; }
    .print-receipt, .print-receipt * { visibility: visible !important; }
    .print-receipt { position: fixed; top: 0; left: 0; width: 100%; }
}
</style>
@endsection
