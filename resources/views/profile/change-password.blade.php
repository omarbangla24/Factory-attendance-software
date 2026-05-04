@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-6 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md mx-auto">

        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Change Password</h1>
            <p class="text-sm text-gray-500 mt-0.5">Update your login password</p>
        </div>

        <x-toast-notifications />

        @if ($errors->any())
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
            @foreach ($errors->all() as $error)
                <p class="text-sm text-red-600">{{ $error }}</p>
            @endforeach
        </div>
        @endif

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <form method="POST" action="{{ route('profile.change-password.update') }}" class="space-y-5">
                @csrf
                @method('PATCH')

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Current Password</label>
                    <input type="password" name="current_password" required autofocus
                           class="w-full px-3.5 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-400 focus:border-transparent @error('current_password') border-red-400 @enderror"
                           placeholder="Your current password">
                    @error('current_password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">New Password</label>
                    <input type="password" name="password" required
                           class="w-full px-3.5 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-400 focus:border-transparent @error('password') border-red-400 @enderror"
                           placeholder="Minimum 8 characters">
                    @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Confirm New Password</label>
                    <input type="password" name="password_confirmation" required
                           class="w-full px-3.5 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-400 focus:border-transparent"
                           placeholder="Re-enter new password">
                </div>

                <button type="submit"
                        class="w-full py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors">
                    Change Password
                </button>
            </form>
        </div>

    </div>
</div>
@endsection
