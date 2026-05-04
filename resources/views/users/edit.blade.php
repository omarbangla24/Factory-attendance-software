<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-2xl text-gray-800">Edit User: {{ $user->name }}</h2>
            <a href="{{ route('users.index') }}" class="text-blue-600 hover:text-blue-900">← Back</a>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <!-- User Information -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">User Information</h3>
            
            <form method="POST" action="{{ route('users.update', $user) }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                    <input type="text" name="name" id="name" value="{{ $user->name }}" 
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" name="email" id="email" value="{{ $user->email }}" 
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Update User
                    </button>
                </div>
            </form>
        </div>

        <!-- Role Assignment -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Assign Role</h3>
            
            <form method="POST" action="{{ route('users.assign-role', $user) }}" class="space-y-4">
                @csrf

                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700">Select Role</label>
                    <div class="mt-2 space-y-2">
                        @foreach($roles as $role)
                            <label class="flex items-center">
                                <input type="radio" name="role" value="{{ $role->name }}" 
                                    @checked(in_array($role->name, $userRoles))
                                    class="rounded-full">
                                <span class="ml-3 text-sm text-gray-700">
                                    <strong>{{ ucfirst($role->name) }}</strong>
                                    @if($role->name === 'admin')
                                        <span class="text-xs text-gray-600"> - Full access to all features</span>
                                    @elseif($role->name === 'accountant')
                                        <span class="text-xs text-gray-600"> - Manage payroll, advances, and reports</span>
                                    @elseif($role->name === 'data_entry')
                                        <span class="text-xs text-gray-600"> - Daily attendance entry only</span>
                                    @endif
                                </span>
                            </label>
                        @endforeach
                    </div>
                    @error('role')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                        Assign Role
                    </button>
                </div>
            </form>
        </div>

        <!-- Current Permissions -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Current Permissions</h3>
            
            @if($user->roles->count() > 0)
                <div class="space-y-3">
                    <p class="text-sm text-gray-600">
                        Role: <strong class="capitalize">{{ $user->roles->first()->name }}</strong>
                    </p>
                    
                    <div>
                        <p class="text-sm font-medium text-gray-700 mb-2">Permissions ({{ $user->permissions->count() }} total):</p>
                        <div class="flex flex-wrap gap-2">
                            @forelse($user->permissions as $permission)
                                <span class="inline-block px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs">
                                    {{ $permission->name }}
                                </span>
                            @empty
                                <span class="text-gray-500 text-sm">No permissions</span>
                            @endforelse
                        </div>
                    </div>
                </div>
            @else
                <p class="text-sm text-gray-600">
                    <strong>No role assigned.</strong> Please select a role above to grant permissions.
                </p>
            @endif
        </div>

        <!-- Role Definitions (Reference) -->
        <div class="bg-blue-50 rounded-lg p-6 mt-6 border border-blue-200">
            <h4 class="text-sm font-semibold text-blue-900 mb-3">Role Definitions</h4>
            <div class="space-y-3 text-sm text-blue-800">
                <div>
                    <strong>Admin:</strong> Full access to all features including user management and settings
                </div>
                <div>
                    <strong>Accountant:</strong> Can manage employees, advances, salaries, payments, and view reports
                </div>
                <div>
                    <strong>Data Entry:</strong> Can only view employees and manage daily attendance entry
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
