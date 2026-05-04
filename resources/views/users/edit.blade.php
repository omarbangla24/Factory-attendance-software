@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-6 px-4 sm:px-6 lg:px-8">
    <div class="max-w-lg mx-auto">

        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('users.index') }}" class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Edit User</h1>
                <p class="text-sm text-gray-500 mt-0.5">{{ $user->email }}</p>
            </div>
        </div>

        <x-toast-notifications />

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-4">
            <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-4">Account Info</h2>
            <form method="POST" action="{{ route('users.update', $user) }}" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Full Name</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                           class="w-full px-3.5 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-400 focus:border-transparent @error('name') border-red-400 @enderror">
                    @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Email Address</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                           class="w-full px-3.5 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-400 focus:border-transparent @error('email') border-red-400 @enderror">
                    @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                    <div class="space-y-2">
                        @foreach($roles as $role)
                        @php
                            $descs = [
                                'admin'      => 'Full access — users, settings, all features',
                                'accountant' => 'Payroll, advances, salaries, reports',
                                'data_entry' => 'Daily attendance entry only',
                            ];
                            $isSelected = old('role', $userRole) === $role->name;
                        @endphp
                        <label class="flex items-center gap-3 p-3 rounded-lg border cursor-pointer transition-colors
                                      {{ $isSelected ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:bg-gray-50' }}">
                            <input type="radio" name="role" value="{{ $role->name }}"
                                   {{ $isSelected ? 'checked' : '' }}
                                   class="w-4 h-4 text-blue-600">
                            <div>
                                <p class="text-sm font-medium text-gray-800">{{ ucfirst(str_replace('_', ' ', $role->name)) }}</p>
                                <p class="text-xs text-gray-500">{{ $descs[$role->name] ?? '' }}</p>
                            </div>
                        </label>
                        @endforeach
                    </div>
                    @error('role') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <button type="submit"
                        class="w-full py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors">
                    Save Changes
                </button>
            </form>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-4">
            <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-1">Reset Password</h2>
            <p class="text-xs text-gray-400 mb-4">Set a new password for this user.</p>
            <form method="POST" action="{{ route('users.reset-password', $user) }}" class="space-y-4">
                @csrf
                @method('PATCH')
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">New Password</label>
                    <input type="password" name="password" required
                           class="w-full px-3.5 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-400 focus:border-transparent"
                           placeholder="Minimum 8 characters">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Confirm Password</label>
                    <input type="password" name="password_confirmation" required
                           class="w-full px-3.5 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-400 focus:border-transparent"
                           placeholder="Re-enter new password">
                </div>
                <button type="submit"
                        class="w-full py-2.5 bg-amber-500 text-white text-sm font-semibold rounded-lg hover:bg-amber-600 transition-colors">
                    Reset Password
                </button>
            </form>
        </div>

        @if($user->id !== auth()->id())
        <div class="bg-white rounded-xl shadow-sm border-2 border-red-100 p-6">
            <h2 class="text-sm font-semibold text-red-700 uppercase tracking-wider mb-1">Danger Zone</h2>
            <p class="text-xs text-gray-500 mb-4">Permanently deletes this user account. Cannot be undone.</p>
            <form action="{{ route('users.destroy', $user) }}" method="POST"
                  onsubmit="return confirm('Delete {{ addslashes($user->name) }}? This cannot be undone.')">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="px-4 py-2 text-sm font-semibold text-red-600 border border-red-300 rounded-lg hover:bg-red-600 hover:text-white hover:border-red-600 transition-all">
                    Delete This User
                </button>
            </form>
        </div>
        @endif

    </div>
</div>
@endsection
