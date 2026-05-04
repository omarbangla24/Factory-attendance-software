<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-2xl text-gray-800">Employees</h2>
            <a href="{{ route('employees.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                + Add Employee
            </a>
        </div>
    </x-slot>

    <!-- Success/Error Messages -->
    @if ($message = Session::get('success'))
        <div class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4">
            {{ $message }}
        </div>
    @endif
    @if ($message = Session::get('error'))
        <div class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4">
            {{ $message }}
        </div>
    @endif

    <!-- Search & Filter -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <form method="GET" action="{{ route('employees.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Search -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Search (Name/Phone)</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search..." 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-blue-500">
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-blue-500">
                        <option value="">All</option>
                        <option value="active" @selected(request('status') === 'active')>Active</option>
                        <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                    </select>
                </div>

                <!-- Department Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Department</label>
                    <select name="department" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-blue-500">
                        <option value="">All</option>
                        @foreach ($departments as $dept)
                            @if ($dept)
                                <option value="{{ $dept }}" @selected(request('department') === $dept)>{{ $dept }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>

                <!-- Search Button -->
                <div class="flex items-end gap-2">
                    <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">
                        Search
                    </button>
                    <a href="{{ route('employees.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 text-sm font-medium">
                        Clear
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Bulk Actions -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <form id="bulkForm" class="space-y-4">
            <!-- Desktop View -->
            <div class="hidden md:block">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="px-6 py-3 text-left">
                                    <input type="checkbox" id="selectAll" class="rounded">
                                </th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Name</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Phone</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Department</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Hajira Rate</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Overtime Rate</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Status</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @forelse ($employees as $employee)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <input type="checkbox" value="{{ $employee->id }}" class="employeeCheckbox rounded">
                                    </td>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $employee->name }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $employee->phone ?? '-' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $employee->department ?? '-' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ number_format($employee->hajira_rate, 2) }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ number_format($employee->overtime_rate, 2) }}</td>
                                    <td class="px-6 py-4 text-sm">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            @if ($employee->status === 'active')
                                                bg-green-100 text-green-800
                                            @else
                                                bg-yellow-100 text-yellow-800
                                            @endif
                                        ">
                                            {{ ucfirst($employee->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        <div class="flex space-x-2">
                                            <a href="{{ route('employees.edit', $employee) }}" class="text-blue-600 hover:text-blue-900 font-medium">Edit</a>
                                            <form method="POST" action="{{ route('employees.destroy', $employee) }}" class="inline" onsubmit="return confirm('Are you sure?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900 font-medium">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-4 text-center text-gray-600">
                                        No employees found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Mobile View (Card Layout) -->
            <div class="md:hidden space-y-4">
                @forelse ($employees as $employee)
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-start gap-3">
                                <input type="checkbox" value="{{ $employee->id }}" class="employeeCheckbox rounded mt-1">
                                <div>
                                    <h3 class="font-semibold text-gray-900">{{ $employee->name }}</h3>
                                    <p class="text-sm text-gray-600">{{ $employee->phone ?? 'No phone' }}</p>
                                </div>
                            </div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if ($employee->status === 'active')
                                    bg-green-100 text-green-800
                                @else
                                    bg-yellow-100 text-yellow-800
                                @endif
                            ">
                                {{ ucfirst($employee->status) }}
                            </span>
                        </div>
                        <div class="grid grid-cols-2 gap-2 text-sm mb-3">
                            <div>
                                <span class="text-gray-600">Department:</span>
                                <p class="font-medium">{{ $employee->department ?? '-' }}</p>
                            </div>
                            <div>
                                <span class="text-gray-600">Hajira Rate:</span>
                                <p class="font-medium">{{ number_format($employee->hajira_rate, 2) }}</p>
                            </div>
                            <div>
                                <span class="text-gray-600">Overtime Rate:</span>
                                <p class="font-medium">{{ number_format($employee->overtime_rate, 2) }}</p>
                            </div>
                            <div>
                                <span class="text-gray-600">Joined:</span>
                                <p class="font-medium">{{ $employee->joining_date->format('M d, Y') }}</p>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <a href="{{ route('employees.edit', $employee) }}" class="flex-1 text-center px-3 py-2 bg-blue-600 text-white text-sm font-medium rounded hover:bg-blue-700">Edit</a>
                            <form method="POST" action="{{ route('employees.destroy', $employee) }}" class="flex-1" onsubmit="return confirm('Are you sure?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full px-3 py-2 bg-red-600 text-white text-sm font-medium rounded hover:bg-red-700">Delete</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <p class="text-center text-gray-600 py-8">No employees found.</p>
                @endforelse
            </div>

            <!-- Bulk Action Controls -->
            <div id="bulkControls" class="hidden bg-gray-50 border-t pt-4 mt-4">
                <p class="text-sm text-gray-600 mb-4"><span id="selectedCount">0</span> employee(s) selected</p>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Bulk Update Hajira Rate -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Update Hajira Rate</label>
                        <div class="flex gap-2">
                            <input type="number" id="hajiraRate" step="0.01" min="0" placeholder="Rate" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            <button type="button" onclick="bulkUpdateRates('hajira')" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">
                                Update
                            </button>
                        </div>
                    </div>

                    <!-- Bulk Update Overtime Rate -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Update Overtime Rate</label>
                        <div class="flex gap-2">
                            <input type="number" id="overtimeRate" step="0.01" min="0" placeholder="Rate" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            <button type="button" onclick="bulkUpdateRates('overtime')" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">
                                Update
                            </button>
                        </div>
                    </div>

                    <!-- Bulk Update Status -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Update Status</label>
                        <div class="flex gap-2">
                            <select id="statusSelect" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                <option value="">Select Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                            <button type="button" onclick="bulkUpdateStatus()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">
                                Update
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Pagination -->
    <div class="flex justify-center">
        {{ $employees->links() }}
    </div>

    <script>
        // Checkbox selection logic
        const selectAllCheckbox = document.getElementById('selectAll');
        const employeeCheckboxes = document.querySelectorAll('.employeeCheckbox');
        const bulkControls = document.getElementById('bulkControls');
        const selectedCountSpan = document.getElementById('selectedCount');

        function updateBulkControls() {
            const checkedCount = document.querySelectorAll('.employeeCheckbox:checked').length;
            selectedCountSpan.textContent = checkedCount;
            bulkControls.classList.toggle('hidden', checkedCount === 0);
        }

        selectAllCheckbox?.addEventListener('change', function() {
            employeeCheckboxes.forEach(checkbox => checkbox.checked = this.checked);
            updateBulkControls();
        });

        employeeCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', updateBulkControls);
        });

        function getSelectedIds() {
            return Array.from(document.querySelectorAll('.employeeCheckbox:checked')).map(cb => cb.value);
        }

        function bulkUpdateRates(type) {
            const selectedIds = getSelectedIds();
            if (selectedIds.length === 0) {
                alert('Please select at least one employee');
                return;
            }

            const hajiraRate = document.getElementById('hajiraRate').value;
            const overtimeRate = document.getElementById('overtimeRate').value;

            if (!hajiraRate && !overtimeRate) {
                alert('Please enter at least one rate');
                return;
            }

            const formData = new FormData();
            selectedIds.forEach(id => formData.append('employee_ids[]', id));
            if (hajiraRate) formData.append('hajira_rate', hajiraRate);
            if (overtimeRate) formData.append('overtime_rate', overtimeRate);
            formData.append('_token', '{{ csrf_token() }}');

            fetch('{{ route("employees.bulk-update-rates") }}', {
                method: 'POST',
                body: formData
            }).then(response => {
                if (response.ok) location.reload();
                else alert('Error updating rates');
            });
        }

        function bulkUpdateStatus() {
            const selectedIds = getSelectedIds();
            if (selectedIds.length === 0) {
                alert('Please select at least one employee');
                return;
            }

            const status = document.getElementById('statusSelect').value;
            if (!status) {
                alert('Please select a status');
                return;
            }

            const formData = new FormData();
            selectedIds.forEach(id => formData.append('employee_ids[]', id));
            formData.append('status', status);
            formData.append('_token', '{{ csrf_token() }}');

            fetch('{{ route("employees.bulk-update-status") }}', {
                method: 'POST',
                body: formData
            }).then(response => {
                if (response.ok) location.reload();
                else alert('Error updating status');
            });
        }
    </script>
</x-app-layout>
