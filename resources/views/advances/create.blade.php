<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800">Add Advance</h2>
    </x-slot>

    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-lg shadow p-6">
            <form method="POST" action="{{ route('advances.store') }}">
                @csrf

                <!-- Employee Selection -->
                <div class="mb-6">
                    <label for="employee_id" class="block text-sm font-medium text-gray-700 mb-2">Employee *</label>
                    <select id="employee_id" name="employee_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg @error('employee_id') border-red-500 @enderror" onchange="updateEmployeeBalance()">
                        <option value="">-- Select Employee --</option>
                        @foreach ($employees as $employee)
                            <option value="{{ $employee->id }}" @selected(old('employee_id') == $employee->id)>
                                {{ $employee->name }} ({{ $employee->department }})
                            </option>
                        @endforeach
                    </select>
                    @error('employee_id')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Employee Balance Info -->
                <div id="balance-info" class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg hidden">
                    <div class="grid grid-cols-3 gap-4 text-sm">
                        <div>
                            <span class="text-gray-600">Total Advance:</span>
                            <span id="total-advance" class="block font-bold text-lg text-blue-600">0.00</span>
                        </div>
                        <div>
                            <span class="text-gray-600">Total Deducted:</span>
                            <span id="total-deducted" class="block font-bold text-lg text-yellow-600">0.00</span>
                        </div>
                        <div>
                            <span class="text-gray-600">Remaining Balance:</span>
                            <span id="remaining-balance" class="block font-bold text-lg text-green-600">0.00</span>
                        </div>
                    </div>
                </div>

                <!-- Date -->
                <div class="mb-6">
                    <label for="date" class="block text-sm font-medium text-gray-700 mb-2">Date *</label>
                    <input type="date" id="date" name="date" value="{{ old('date', now()->format('Y-m-d')) }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg @error('date') border-red-500 @enderror">
                    @error('date')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Amount -->
                <div class="mb-6">
                    <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">Amount *</label>
                    <input type="number" id="amount" name="amount" value="{{ old('amount') }}" placeholder="0.00" step="0.01" min="0.01" required class="w-full px-4 py-2 border border-gray-300 rounded-lg @error('amount') border-red-500 @enderror">
                    @error('amount')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Reason -->
                <div class="mb-6">
                    <label for="reason" class="block text-sm font-medium text-gray-700 mb-2">Reason</label>
                    <input type="text" id="reason" name="reason" value="{{ old('reason') }}" placeholder="Optional reason for advance" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    @error('reason')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Note -->
                <div class="mb-6">
                    <label for="note" class="block text-sm font-medium text-gray-700 mb-2">Note</label>
                    <textarea id="note" name="note" rows="3" placeholder="Optional additional notes" class="w-full px-4 py-2 border border-gray-300 rounded-lg">{{ old('note') }}</textarea>
                    @error('note')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Buttons -->
                <div class="flex gap-3">
                    <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
                        Create Advance
                    </button>
                    <a href="{{ route('advances.index') }}" class="flex-1 px-4 py-2 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400 text-center font-medium">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        function updateEmployeeBalance() {
            const employeeId = document.getElementById('employee_id').value;
            const balanceInfo = document.getElementById('balance-info');
            
            if (!employeeId) {
                balanceInfo.classList.add('hidden');
                return;
            }

            // Fetch balance from API
            fetch(`/api/employees/${employeeId}/advance-balance`, {
                headers: {
                    'Authorization': `Bearer {{ auth()->user()->createToken('api')->plainTextToken ?? '' }}`,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('total-advance').textContent = parseFloat(data.total_advance).toFixed(2);
                document.getElementById('total-deducted').textContent = parseFloat(data.total_deducted).toFixed(2);
                document.getElementById('remaining-balance').textContent = parseFloat(data.remaining_balance).toFixed(2);
                balanceInfo.classList.remove('hidden');
            })
            .catch(() => {
                balanceInfo.classList.add('hidden');
            });
        }
    </script>
</x-app-layout>
