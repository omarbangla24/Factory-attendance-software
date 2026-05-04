@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-6 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto">
        <!-- Back Button -->
        <div class="mb-6">
            <a href="{{ route('activity-logs.index') }}" class="inline-flex items-center text-blue-600 hover:text-blue-800">
                ← Back to Activity Logs
            </a>
        </div>

        <!-- Header -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <div class="flex justify-between items-start">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Activity Details</h1>
                    <p class="mt-2 text-sm text-gray-600">
                        ID: <span class="font-mono text-gray-900">{{ $activity->id }}</span>
                    </p>
                </div>
                <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full {{ $activity->description === 'created' ? 'bg-green-100 text-green-800' : ($activity->description === 'deleted' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800') }}">
                    {{ ucfirst($activity->description) }}
                </span>
            </div>
        </div>

        <!-- Basic Information -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Left Column -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h2>
                <dl class="space-y-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">User</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $activity->causer?->name ?? 'System' }}
                            @if($activity->causer)
                                <span class="text-gray-500">({{ $activity->causer->email }})</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Action</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ ucfirst($activity->description) }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Model</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ str_replace('App\\Models\\', '', $activity->subject_type) }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Record ID</dt>
                        <dd class="mt-1 text-sm font-mono text-gray-900">{{ $activity->subject_id }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Timestamp</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $activity->created_at->format('M d, Y H:i:s') }}
                        </dd>
                    </div>
                </dl>
            </div>

            <!-- Right Column -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">IP & Browser</h2>
                <dl class="space-y-4">
                    @if($activity->properties && isset($activity->properties['ip_address']))
                        <div>
                            <dt class="text-sm font-medium text-gray-500">IP Address</dt>
                            <dd class="mt-1 text-sm font-mono text-gray-900">{{ $activity->properties['ip_address'] }}</dd>
                        </div>
                    @endif
                    @if($activity->properties && isset($activity->properties['user_agent']))
                        <div>
                            <dt class="text-sm font-medium text-gray-500">User Agent</dt>
                            <dd class="mt-1 text-xs text-gray-900 break-all">{{ $activity->properties['user_agent'] }}</dd>
                        </div>
                    @endif
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Log Name</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $activity->log_name ?? 'default' }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Changes Section -->
        @if($activity->properties && isset($activity->properties['attributes']) && count($activity->properties['attributes']) > 0)
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Changed Fields</h2>
                
                @php
                    $attributes = $activity->properties['attributes'] ?? [];
                    $old = $activity->properties['old'] ?? [];
                @endphp

                <div class="space-y-4">
                    @foreach($attributes as $key => $newValue)
                        @php $oldValue = $old[$key] ?? 'N/A'; @endphp
                        @if($oldValue !== $newValue)
                            <div class="border border-gray-200 rounded-lg p-4">
                                <p class="text-sm font-semibold text-gray-900 mb-3">{{ ucwords(str_replace('_', ' ', $key)) }}</p>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="bg-red-50 p-3 rounded">
                                        <p class="text-xs text-gray-500 mb-1">Previous Value</p>
                                        <p class="text-sm font-mono text-gray-900 break-all">
                                            @if(is_null($oldValue) || $oldValue === '')
                                                <em class="text-gray-500">Empty</em>
                                            @else
                                                {{ is_array($oldValue) ? json_encode($oldValue) : $oldValue }}
                                            @endif
                                        </p>
                                    </div>
                                    <div class="bg-green-50 p-3 rounded">
                                        <p class="text-xs text-gray-500 mb-1">New Value</p>
                                        <p class="text-sm font-mono text-gray-900 break-all">
                                            @if(is_null($newValue) || $newValue === '')
                                                <em class="text-gray-500">Empty</em>
                                            @else
                                                {{ is_array($newValue) ? json_encode($newValue) : $newValue }}
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Raw Data (for debugging) -->
        <div class="bg-white rounded-lg shadow p-6">
            <details>
                <summary class="cursor-pointer font-semibold text-gray-900 hover:text-gray-700">
                    Raw Properties (JSON)
                </summary>
                <pre class="mt-4 bg-gray-50 p-4 rounded text-xs overflow-auto text-gray-900">{{ json_encode($activity->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
            </details>
        </div>
    </div>
</div>
@endsection
