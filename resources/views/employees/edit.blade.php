<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-2xl text-gray-800">Edit Employee</h2>
            <a href="{{ route('employees.index') }}" class="text-gray-600 hover:text-gray-900">← Back</a>
        </div>
    </x-slot>

    <div class="bg-white rounded-lg shadow p-6 max-w-2xl">
        <form method="POST" action="{{ route('employees.update', $employee) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Name -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Name *</label>
                <input type="text" name="name" value="{{ old('name', $employee->name) }}" required
                    class="w-full px-4 py-2 border @error('name') border-red-500 @else border-gray-300 @enderror rounded-lg focus:outline-none focus:border-blue-500">
                @error('name')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Phone -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                <input type="text" name="phone" value="{{ old('phone', $employee->phone) }}"
                    class="w-full px-4 py-2 border @error('phone') border-red-500 @else border-gray-300 @enderror rounded-lg focus:outline-none focus:border-blue-500"
                    placeholder="Optional">
                @error('phone')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Address -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                <textarea name="address" rows="3"
                    class="w-full px-4 py-2 border @error('address') border-red-500 @else border-gray-300 @enderror rounded-lg focus:outline-none focus:border-blue-500"
                    placeholder="Optional">{{ old('address', $employee->address) }}</textarea>
                @error('address')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Joining Date -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Joining Date *</label>
                <input type="date" name="joining_date" value="{{ old('joining_date', $employee->joining_date->format('Y-m-d')) }}" required
                    class="w-full px-4 py-2 border @error('joining_date') border-red-500 @else border-gray-300 @enderror rounded-lg focus:outline-none focus:border-blue-500">
                @error('joining_date')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Department -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Department</label>
                <input type="text" name="department" value="{{ old('department', $employee->department) }}"
                    class="w-full px-4 py-2 border @error('department') border-red-500 @else border-gray-300 @enderror rounded-lg focus:outline-none focus:border-blue-500"
                    placeholder="e.g., IT, HR, Finance, Sales, Operations">
                @error('department')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Hajira Rate -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Hajira Rate (Daily) *</label>
                <input type="number" name="hajira_rate" value="{{ old('hajira_rate', $employee->hajira_rate) }}" step="0.01" min="0" required
                    class="w-full px-4 py-2 border @error('hajira_rate') border-red-500 @else border-gray-300 @enderror rounded-lg focus:outline-none focus:border-blue-500">
                @error('hajira_rate')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Overtime Rate -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Overtime Rate (Hourly) *</label>
                <input type="number" name="overtime_rate" value="{{ old('overtime_rate', $employee->overtime_rate) }}" step="0.01" min="0" required
                    class="w-full px-4 py-2 border @error('overtime_rate') border-red-500 @else border-gray-300 @enderror rounded-lg focus:outline-none focus:border-blue-500">
                @error('overtime_rate')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Status -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status *</label>
                <select name="status" required
                    class="w-full px-4 py-2 border @error('status') border-red-500 @else border-gray-300 @enderror rounded-lg focus:outline-none focus:border-blue-500">
                    <option value="">Select Status</option>
                    <option value="active" @selected(old('status', $employee->status) === 'active')>Active</option>
                    <option value="inactive" @selected(old('status', $employee->status) === 'inactive')>Inactive</option>
                </select>
                @error('status')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Info Box -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <p class="text-sm text-blue-800">
                    <strong>Note:</strong> Employees with attendance or salary records cannot be deleted.
                </p>
            </div>

            <!-- Buttons -->
            <div class="flex gap-4">
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
                    Update Employee
                </button>
                <a href="{{ route('employees.index') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-medium">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</x-app-layout>
