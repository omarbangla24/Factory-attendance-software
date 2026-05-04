<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-2xl text-gray-800">Advance Details</h2>
            <div class="flex gap-2">
                @if ($advance->deductions->count() == 0)
                    <a href="{{ route('advances.edit', $advance) }}" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                        Edit
                    </a>
                    <form method="POST" action="{{ route('advances.destroy', $advance) }}" class="inline" onsubmit="return confirm('Delete this advance?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700">
                            Delete
                        </button>
                    </form>
                @endif
                <a href="{{ route('advances.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700">
                    Back to List
                </a>
            </div>
        </div>
    </x-slot>

    <div class="max-w-3xl mx-auto">
        <!-- Main Details -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Employee & Date -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="font-semibold text-lg text-gray-800 mb-4">Basic Information</h3>
                <div class="space-y-3">
                    <div>
                        <span class="text-sm text-gray-600">Employee</span>
                        <p class="font-medium text-gray-900">{{ $advance->employee->name }}</p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-600">Department</span>
                        <p class="font-medium text-gray-900">{{ $advance->employee->department }}</p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-600">Advance Date</span>
                        <p class="font-medium text-gray-900">{{ $advance->date->format('M d, Y') }}</p>
                    </div>
                    @if ($advance->reason)
                        <div>
                            <span class="text-sm text-gray-600">Reason</span>
                            <p class="font-medium text-gray-900">{{ $advance->reason }}</p>
                        </div>
                    @endif
                    @if ($advance->note)
                        <div>
                            <span class="text-sm text-gray-600">Note</span>
                            <p class="font-medium text-gray-900">{{ $advance->note }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Financial Summary -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="font-semibold text-lg text-gray-800 mb-4">Financial Summary</h3>
                <div class="space-y-4">
                    <div class="p-3 bg-blue-50 rounded-lg border border-blue-200">
                        <span class="text-sm text-gray-600">Advance Amount</span>
                        <p class="font-bold text-2xl text-blue-600">{{ number_format($advance->amount, 2) }}</p>
                    </div>
                    <div class="p-3 bg-yellow-50 rounded-lg border border-yellow-200">
                        <span class="text-sm text-gray-600">Total Deducted</span>
                        <p class="font-bold text-2xl text-yellow-600">{{ number_format($deductedAmount, 2) }}</p>
                    </div>
                    <div class="p-3 bg-green-50 rounded-lg border border-green-200">
                        <span class="text-sm text-gray-600">Remaining Balance</span>
                        <p class="font-bold text-2xl text-green-600">{{ number_format($remainingAmount, 2) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Deductions Table -->
        @if ($advance->deductions->count() > 0)
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="font-semibold text-lg text-gray-800">Deduction History</h3>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Salary Sheet</th>
                                <th class="px-6 py-3 text-right text-sm font-semibold text-gray-900">Deducted Amount</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @foreach ($advance->deductions as $deduction)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        @if ($deduction->salarySheet)
                                            <a href="{{ route('salary-sheets.show', $deduction->salarySheet) }}" class="text-blue-600 hover:text-blue-900">
                                                {{ $deduction->salarySheet->month }}
                                            </a>
                                        @else
                                            <span class="text-gray-600">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-right font-semibold text-yellow-600">{{ number_format($deduction->amount, 2) }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        @if ($deduction->salarySheet)
                                            {{ $deduction->salarySheet->created_at->format('M d, Y') }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            <tr class="bg-yellow-50">
                                <td class="px-6 py-4 text-sm font-semibold text-gray-900">Total Deducted</td>
                                <td class="px-6 py-4 text-sm text-right font-bold text-yellow-600">{{ number_format($advance->deductions->sum('amount'), 2) }}</td>
                                <td class="px-6 py-4 text-sm"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="bg-blue-50 rounded-lg p-4 border border-blue-200 text-center">
                <p class="text-blue-800">No deductions yet. This advance can be edited or deleted.</p>
            </div>
        @endif

        <!-- Metadata -->
        <div class="bg-white rounded-lg shadow p-6 mt-6">
            <h3 class="font-semibold text-lg text-gray-800 mb-4">Metadata</h3>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="text-gray-600">Created By</span>
                    <p class="font-medium text-gray-900">{{ $advance->createdBy?->name ?? 'System' }}</p>
                </div>
                <div>
                    <span class="text-gray-600">Created At</span>
                    <p class="font-medium text-gray-900">{{ $advance->created_at->format('M d, Y h:i A') }}</p>
                </div>
                <div>
                    <span class="text-gray-600">Last Updated</span>
                    <p class="font-medium text-gray-900">{{ $advance->updated_at->format('M d, Y h:i A') }}</p>
                </div>
                <div>
                    <span class="text-gray-600">Status</span>
                    <p class="font-medium">
                        @if ($advance->deductions->count() > 0)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                Partially Deducted
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Active
                            </span>
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
