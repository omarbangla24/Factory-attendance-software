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
                <h1 class="text-2xl font-bold text-gray-900">Add New User</h1>
                <p class="text-sm text-gray-500 mt-0.5">Create a login account for a staff member</p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-5">
            <form method="POST" action="{{ route('users.store') }}" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Full Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required autofocus
                           class="w-full px-3.5 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-400 focus:border-transparent @error('name') border-red-400 @enderror"
                           placeholder="e.g. Ahmed Hassan">
                    @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Email Address <span class="text-red-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                           class="w-full px-3.5 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-400 focus:border-transparent @error('email') border-red-400 @enderror"
                           placeholder="e.g. ahmed@company.com">
                    @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Password <span class="text-red-500">*</span></label>
                    <input type="password" name="password" required
                           class="w-full px-3.5 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-400 focus:border-transparent @error('password') border-red-400 @enderror"
                           placeholder="Minimum 8 characters">
                    @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Confirm Password <span class="text-red-500">*</span></label>
                    <input type="password" name="password_confirmation" required
                           class="w-full px-3.5 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-400 focus:border-transparent"
                           placeholder="Re-enter password">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Role <span class="text-red-500">*</span></label>
                    <div class="space-y-2">
                        @foreach($roles as $role)
                        @php
                            $colors = [
                                'admin'      => 'border-purple-200 bg-purple-50 peer-checked:border-purple-500',
                                'accountant' => 'border-blue-200 bg-blue-50 peer-checked:border-blue-500',
                                'data_entry' => 'border-gray-200 bg-gray-50 peer-checked:border-gray-500',
                            ];
                            $descs = [
                                'admin'      => 'Full access — users, settings, all features',
                                'accountant' => 'Payroll, advances, salaries, reports',
                                'data_entry' => 'Daily attendance entry only',
                            ];
                        @endphp
                        <label class="flex items-center gap-3 p-3 rounded-lg border cursor-pointer transition-colors hover:bg-gray-50
                                      {{ old('role') === $role->name ? 'border-blue-500 bg-blue-50' : 'border-gray-200' }}">
                            <input type="radio" name="role" value="{{ $role->name }}"
                                   {{ old('role') === $role->name ? 'checked' : '' }}
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

                <div class="flex gap-3 pt-2">
                    <button type="submit"
                            class="flex-1 py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors">
                        Create User
                    </button>
                    <a href="{{ route('users.index') }}"
                       class="flex-1 py-2.5 text-center border border-gray-200 text-gray-600 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors">
                        Cancel
                    </a>
                </div>
            </form>
        </div>

    </div>
</div>
@endsection
