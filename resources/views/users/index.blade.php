@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-6 px-4 sm:px-6 lg:px-8">
    <div class="max-w-5xl mx-auto">

        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">User Management</h1>
                <p class="text-sm text-gray-500 mt-0.5">Create and manage system user accounts</p>
            </div>
            <a href="{{ route('users.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add User
            </a>
        </div>

        <x-toast-notifications />

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">User</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Role</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($users as $user)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-sm font-bold shrink-0">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $user->name }}</p>
                                    @if($user->id === auth()->id())
                                        <span class="text-xs text-blue-500 font-medium">You</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3.5 text-sm text-gray-600">{{ $user->email }}</td>
                        <td class="px-5 py-3.5">
                            @php $role = $user->roles->first(); @endphp
                            @if($role)
                                <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-semibold
                                    {{ $role->name === 'admin' ? 'bg-purple-100 text-purple-700' :
                                       ($role->name === 'accountant' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600') }}">
                                    {{ ucfirst($role->name) }}
                                </span>
                            @else
                                <span class="text-xs text-gray-400">No role</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('users.edit', $user) }}"
                                   class="text-xs px-3 py-1.5 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-100 transition-colors font-medium">
                                    Edit
                                </a>
                                @if($user->id !== auth()->id())
                                <form action="{{ route('users.destroy', $user) }}" method="POST"
                                      onsubmit="return confirm('Delete user {{ addslashes($user->name) }}? This cannot be undone.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="text-xs px-3 py-1.5 rounded-lg border border-red-200 text-red-600 hover:bg-red-50 transition-colors font-medium">
                                        Delete
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-5 py-12 text-center text-sm text-gray-400">No users found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            @if($users->hasPages())
            <div class="px-5 py-3 border-t border-gray-100 bg-gray-50">
                {{ $users->links() }}
            </div>
            @endif
        </div>

    </div>
</div>
@endsection
