@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-6 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Activity Log</h1>
            <p class="mt-2 text-sm text-gray-600">Track all system changes and user actions</p>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow mb-6 p-6">
            <form method="GET" action="{{ route('activity-logs.index') }}" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                    <!-- Model Filter -->
                    <div>
                        <label for="model" class="block text-sm font-medium text-gray-700">Model</label>
                        <select name="model" id="model" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                            <option value="">All Models</option>
                            <option value="employee" {{ request('model') === 'employee' ? 'selected' : '' }}>Employee</option>
                            <option value="attendance" {{ request('model') === 'attendance' ? 'selected' : '' }}>Attendance</option>
                            <option value="advance" {{ request('model') === 'advance' ? 'selected' : '' }}>Advance</option>
                            <option value="salary_sheet" {{ request('model') === 'salary_sheet' ? 'selected' : '' }}>Salary Sheet</option>
                            <option value="salary_payment" {{ request('model') === 'salary_payment' ? 'selected' : '' }}>Salary Payment</option>
                        </select>
                    </div>

                    <!-- Action Filter -->
                    <div>
                        <label for="action" class="block text-sm font-medium text-gray-700">Action</label>
                        <select name="action" id="action" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                            <option value="">All Actions</option>
                            <option value="created" {{ request('action') === 'created' ? 'selected' : '' }}>Created</option>
                            <option value="updated" {{ request('action') === 'updated' ? 'selected' : '' }}>Updated</option>
                            <option value="deleted" {{ request('action') === 'deleted' ? 'selected' : '' }}>Deleted</option>
                        </select>
                    </div>

                    <!-- From Date -->
                    <div>
                        <label for="from_date" class="block text-sm font-medium text-gray-700">From Date</label>
                        <input type="date" name="from_date" id="from_date" value="{{ request('from_date') }}" 
                               class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                    </div>

                    <!-- To Date -->
                    <div>
                        <label for="to_date" class="block text-sm font-medium text-gray-700">To Date</label>
                        <input type="date" name="to_date" id="to_date" value="{{ request('to_date') }}" 
                               class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                    </div>

                    <!-- Actions -->
                    <div class="flex items-end gap-2">
                        <button type="submit" class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-700">
                            Filter
                        </button>
                        <a href="{{ route('activity-logs.index') }}" class="flex-1 bg-gray-300 text-gray-700 px-4 py-2 rounded-md text-sm font-medium hover:bg-gray-400 text-center">
                            Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Mobile View (Cards) -->
        <div class="space-y-4 md:hidden">
            @forelse($logs as $log)
                <div class="bg-white rounded-lg shadow p-4 border-l-4 {{ $log->description === 'created' ? 'border-green-500' : ($log->description === 'deleted' ? 'border-red-500' : 'border-blue-500') }}">
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <p class="font-semibold text-gray-900">{{ str_replace('App\\Models\\', '', $log->subject_type) }}</p>
                            <p class="text-sm text-gray-600">{{ $log->description }}</p>
                        </div>
                        <span class="text-xs px-2 py-1 rounded-full {{ $log->description === 'created' ? 'bg-green-100 text-green-800' : ($log->description === 'deleted' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800') }}">
                            {{ ucfirst($log->description) }}
                        </span>
                    </div>
                    
                    <div class="text-sm text-gray-600 mb-2">
                        <p><strong>User:</strong> {{ $log->causer?->name ?? 'System' }}</p>
                        <p><strong>Time:</strong> {{ $log->created_at->format('M d, Y H:i') }}</p>
                    </div>

                    @if($log->properties)
                        @if(isset($log->properties['attributes']) && count($log->properties['attributes']) > 0)
                            <details class="mt-3">
                                <summary class="cursor-pointer text-sm text-blue-600 font-medium">View Changes</summary>
                                <div class="mt-2 text-xs space-y-1 bg-gray-50 p-2 rounded">
                                    @php
                                        $attributes = $log->properties['attributes'] ?? [];
                                        $old = $log->properties['old'] ?? [];
                                    @endphp
                                    @foreach($attributes as $key => $newValue)
                                        @php $oldValue = $old[$key] ?? 'N/A'; @endphp
                                        @if($oldValue !== $newValue)
                                            <p class="text-gray-700">
                                                <strong>{{ $key }}:</strong> 
                                                <span class="text-red-600">{{ $oldValue }}</span> 
                                                → 
                                                <span class="text-green-600">{{ $newValue }}</span>
                                            </p>
                                        @endif
                                    @endforeach
                                </div>
                            </details>
                        @endif
                    @endif

                    <a href="{{ route('activity-logs.show', $log) }}" class="text-sm text-blue-600 hover:text-blue-800 mt-3 inline-block">
                        View Details →
                    </a>
                </div>
            @empty
                <div class="bg-white rounded-lg shadow p-6 text-center">
                    <p class="text-gray-600">No activity logs found</p>
                </div>
            @endforelse
        </div>

        <!-- Desktop View (Table) -->
        <div class="hidden md:block bg-white rounded-lg shadow overflow-hidden">
            @forelse($logs as $log)
                @if($loop->first)
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Model</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Changes</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                @endif
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $log->causer?->name ?? 'System' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $log->description === 'created' ? 'bg-green-100 text-green-800' : ($log->description === 'deleted' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800') }}">
                                        {{ ucfirst($log->description) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ str_replace('App\\Models\\', '', $log->subject_type) }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    @if($log->properties && isset($log->properties['attributes']))
                                        @php
                                            $count = count($log->properties['attributes']);
                                        @endphp
                                        {{ $count }} field{{ $count > 1 ? 's' : '' }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $log->created_at->format('M d, Y H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <a href="{{ route('activity-logs.show', $log) }}" class="text-blue-600 hover:text-blue-900">View</a>
                                </td>
                            </tr>
                @if($loop->last)
                        </tbody>
                    </table>
                @endif
            @empty
                <div class="p-6 text-center text-gray-600">
                    <p>No activity logs found</p>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $logs->links() }}
        </div>
    </div>
</div>
@endsection
