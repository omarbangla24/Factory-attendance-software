<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800">Edit Advance</h2>
    </x-slot>

    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-lg shadow p-6">
            <form method="POST" action="{{ route('advances.update', $advance) }}">
                @csrf
                @method('PUT')

                <!-- Employee Selection -->
                <div class="mb-6">
                    <label for="employee_id" class="block text-sm font-medium text-gray-700 mb-2">Employee *</label>
                    <select id="employee_id" name="employee_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg @error('employee_id') border-red-500 @enderror">
                        @foreach ($employees as $employee)
                            <option value="{{ $employee->id }}" @selected($advance->employee_id == $employee->id)>
                                {{ $employee->name }} ({{ $employee->department }})
                            </option>
                        @endforeach
                    </select>
                    @error('employee_id')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Date -->
                <div class="mb-6">
                    <label for="date" class="block text-sm font-medium text-gray-700 mb-2">Date *</label>
                    <input type="date" id="date" name="date" value="{{ old('date', $advance->date->format('Y-m-d')) }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg @error('date') border-red-500 @enderror">
                    @error('date')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Amount -->
                <div class="mb-6">
                    <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">Amount *</label>
                    <input type="number" id="amount" name="amount" value="{{ old('amount', $advance->amount) }}" placeholder="0.00" step="0.01" min="0.01" required class="w-full px-4 py-2 border border-gray-300 rounded-lg @error('amount') border-red-500 @enderror">
                    @error('amount')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Reason -->
                <div class="mb-6">
                    <label for="reason" class="block text-sm font-medium text-gray-700 mb-2">Reason</label>
                    <input type="text" id="reason" name="reason" value="{{ old('reason', $advance->reason) }}" placeholder="Optional reason for advance" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    @error('reason')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Note -->
                <div class="mb-6">
                    <label for="note" class="block text-sm font-medium text-gray-700 mb-2">Note</label>
                    <textarea id="note" name="note" rows="3" placeholder="Optional additional notes" class="w-full px-4 py-2 border border-gray-300 rounded-lg">{{ old('note', $advance->note) }}</textarea>
                    @error('note')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Buttons -->
                <div class="flex gap-3">
                    <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
                        Update Advance
                    </button>
                    <a href="{{ route('advances.show', $advance) }}" class="flex-1 px-4 py-2 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400 text-center font-medium">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
