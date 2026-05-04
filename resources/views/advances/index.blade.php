<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-2xl text-gray-800">Advances</h2>
            <a href="{{ route('advances.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                + Add Advance
            </a>
        </div>
    </x-slot>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
            <div class="text-sm text-gray-600 mb-1">Total Advance</div>
            <div class="text-3xl font-bold text-blue-600">{{ number_format($totals['total_advance'], 2) }}</div>
        </div>
        <div class="bg-yellow-50 rounded-lg p-4 border border-yellow-200">
            <div class="text-sm text-gray-600 mb-1">Total Deducted</div>
            <div class="text-3xl font-bold text-yellow-600">{{ number_format($totals['total_deducted'], 2) }}</div>
        </div>
        <div class="bg-green-50 rounded-lg p-4 border border-green-200">
            <div class="text-sm text-gray-600 mb-1">Remaining Balance</div>
            <div class="text-3xl font-bold text-green-600">{{ number_format($totals['remaining_balance'], 2) }}</div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-4 md:p-6 mb-6">
        <form method="GET" action="{{ route('advances.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Search Employee</label>
                <input type="text" name="search" value="{{ $search }}" placeholder="Employee name..." class="w-full px-3 py-2 border border-gray-300 rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">From Date</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">To Date</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Filter
                </button>
                <a href="{{ route('advances.index') }}" class="flex-1 px-4 py-2 bg-gray-400 text-white rounded-lg hover:bg-gray-500 text-center">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Table for Desktop / Cards for Mobile -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <!-- Mobile View (Cards) -->
        <div class="md:hidden">
            @forelse ($advances as $advance)
                <div class="border-b p-4">
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <h3 class="font-semibold text-gray-900">{{ $advance->employee->name }}</h3>
                            <p class="text-sm text-gray-600">{{ $advance->date->format('M d, Y') }}</p>
                        </div>
                        <span class="text-lg font-bold text-blue-600">{{ number_format($advance->amount, 2) }}</span>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-2 mb-3 text-sm">
                        <div>
                            <span class="text-gray-600">Deducted:</span>
                            <span class="font-semibold text-yellow-600">{{ number_format($advance->deductions->sum('amount'), 2) }}</span>
                        </div>
                        <div>
                            <span class="text-gray-600">Balance:</span>
                            <span class="font-semibold text-green-600">{{ number_format($advance->amount - $advance->deductions->sum('amount'), 2) }}</span>
                        </div>
                    </div>
                    
                    @if ($advance->reason)
                        <p class="text-xs text-gray-600 mb-2">{{ $advance->reason }}</p>
                    @endif
                    
                    <div class="flex gap-2">
                        <a href="{{ route('advances.show', $advance) }}" class="flex-1 text-center px-3 py-1 bg-blue-100 text-blue-600 rounded text-sm hover:bg-blue-200">View</a>
                        @if ($advance->deductions->count() == 0)
                            <a href="{{ route('advances.edit', $advance) }}" class="flex-1 text-center px-3 py-1 bg-green-100 text-green-600 rounded text-sm hover:bg-green-200">Edit</a>
                            <form method="POST" action="{{ route('advances.destroy', $advance) }}" class="flex-1" onsubmit="return confirm('Delete this advance?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full px-3 py-1 bg-red-100 text-red-600 rounded text-sm hover:bg-red-200">Delete</button>
                            </form>
                        @else
                            <button disabled class="flex-1 px-3 py-1 bg-gray-100 text-gray-400 rounded text-sm cursor-not-allowed">Edit</button>
                            <button disabled class="flex-1 px-3 py-1 bg-gray-100 text-gray-400 rounded text-sm cursor-not-allowed">Delete</button>
                        @endif
                    </div>
                </div>
            @empty
                <div class="p-6 text-center text-gray-600">
                    No advances found.
                </div>
            @endforelse
        </div>

        <!-- Desktop View (Table) -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Employee</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Date</th>
                        <th class="px-6 py-3 text-right text-sm font-semibold text-gray-900">Amount</th>
                        <th class="px-6 py-3 text-right text-sm font-semibold text-gray-900">Deducted</th>
                        <th class="px-6 py-3 text-right text-sm font-semibold text-gray-900">Balance</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Reason</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse ($advances as $advance)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $advance->employee->name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $advance->date->format('M d, Y') }}</td>
                            <td class="px-6 py-4 text-sm text-right font-semibold text-blue-600">{{ number_format($advance->amount, 2) }}</td>
                            <td class="px-6 py-4 text-sm text-right font-semibold text-yellow-600">{{ number_format($advance->deductions->sum('amount'), 2) }}</td>
                            <td class="px-6 py-4 text-sm text-right font-semibold text-green-600">{{ number_format($advance->amount - $advance->deductions->sum('amount'), 2) }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $advance->reason ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm">
                                <div class="flex space-x-2">
                                    <a href="{{ route('advances.show', $advance) }}" class="text-blue-600 hover:text-blue-900">View</a>
                                    @if ($advance->deductions->count() == 0)
                                        <a href="{{ route('advances.edit', $advance) }}" class="text-green-600 hover:text-green-900">Edit</a>
                                        <form method="POST" action="{{ route('advances.destroy', $advance) }}" class="inline" onsubmit="return confirm('Delete this advance?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                        </form>
                                    @else
                                        <span class="text-gray-400 cursor-not-allowed">Edit</span>
                                        <span class="text-gray-400 cursor-not-allowed">Delete</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-600">
                                No advances found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if ($advances->hasPages())
            <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                {{ $advances->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
