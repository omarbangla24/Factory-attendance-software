@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-6 px-4 sm:px-6 lg:px-8">
    <div class="max-w-2xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">System Settings</h1>
            <p class="mt-2 text-sm text-gray-600">Configure company information and system defaults</p>
        </div>

        <!-- Toast Notifications -->
        <x-toast-notifications />

        <!-- Settings Form -->
        <div class="bg-white rounded-lg shadow p-6 md:p-8">
            <form method="POST" action="{{ route('settings.update') }}" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Company Information Section -->
                <div class="border-b border-gray-200 pb-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-6">Company Information</h2>

                    <!-- Company Name -->
                    <div class="mb-6">
                        <label for="company_name" class="block text-sm font-medium text-gray-700 mb-2">
                            Company Name
                            <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="company_name" id="company_name" 
                               value="{{ old('company_name', $settings['company_name']) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('company_name') border-red-500 @enderror"
                               placeholder="Enter company name">
                        @error('company_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Company Phone -->
                    <div class="mb-6">
                        <label for="company_phone" class="block text-sm font-medium text-gray-700 mb-2">
                            Company Phone
                        </label>
                        <input type="tel" name="company_phone" id="company_phone"
                               value="{{ old('company_phone', $settings['company_phone']) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('company_phone') border-red-500 @enderror"
                               placeholder="Enter company phone number">
                        @error('company_phone')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Company Address -->
                    <div>
                        <label for="company_address" class="block text-sm font-medium text-gray-700 mb-2">
                            Company Address
                        </label>
                        <textarea name="company_address" id="company_address" rows="3"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('company_address') border-red-500 @enderror"
                                  placeholder="Enter company address">{{ old('company_address', $settings['company_address']) }}</textarea>
                        @error('company_address')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Currency Section -->
                <div class="border-b border-gray-200 pb-6 pt-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-6">Currency</h2>

                    <div class="mb-4">
                        <label for="currency_code" class="block text-sm font-medium text-gray-700 mb-2">Currency</label>
                        <select name="currency_code" id="currency_code"
                                onchange="syncCurrencySymbol(this)"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                            @php
                                $currencies = [
                                    'BDT' => ['symbol' => '৳',   'label' => 'BDT — Bangladeshi Taka (৳)'],
                                    'USD' => ['symbol' => '$',   'label' => 'USD — US Dollar ($)'],
                                    'EUR' => ['symbol' => '€',   'label' => 'EUR — Euro (€)'],
                                    'GBP' => ['symbol' => '£',   'label' => 'GBP — British Pound (£)'],
                                    'INR' => ['symbol' => '₹',   'label' => 'INR — Indian Rupee (₹)'],
                                    'PKR' => ['symbol' => '₨',   'label' => 'PKR — Pakistani Rupee (₨)'],
                                    'AED' => ['symbol' => 'د.إ', 'label' => 'AED — UAE Dirham (د.إ)'],
                                    'SAR' => ['symbol' => '﷼',   'label' => 'SAR — Saudi Riyal (﷼)'],
                                    'MYR' => ['symbol' => 'RM',  'label' => 'MYR — Malaysian Ringgit (RM)'],
                                    'SGD' => ['symbol' => 'S$',  'label' => 'SGD — Singapore Dollar (S$)'],
                                ];
                            @endphp
                            @foreach($currencies as $code => $info)
                                <option value="{{ $code }}"
                                        data-symbol="{{ $info['symbol'] }}"
                                        {{ old('currency_code', $settings['currency_code']) === $code ? 'selected' : '' }}>
                                    {{ $info['label'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="currency_symbol" class="block text-sm font-medium text-gray-700 mb-2">
                            Currency Symbol
                            <span class="text-xs text-gray-400 font-normal ml-1">— auto-filled, or customize manually</span>
                        </label>
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-10 bg-blue-50 border border-blue-200 rounded-lg flex items-center justify-center text-lg font-bold text-blue-700" id="currencyPreview">
                                {{ old('currency_symbol', $settings['currency_symbol']) }}
                            </div>
                            <input type="text" name="currency_symbol" id="currency_symbol"
                                   value="{{ old('currency_symbol', $settings['currency_symbol']) }}"
                                   class="w-32 px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-center text-lg font-bold"
                                   placeholder="৳" maxlength="5">
                        </div>
                    </div>
                </div>

                <!-- System Defaults Section -->
                <div class="border-b border-gray-200 pb-6 pt-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-6">System Defaults</h2>

                    <!-- Default Overtime Rate -->
                    <div class="mb-6">
                        <label for="default_overtime_rate" class="block text-sm font-medium text-gray-700 mb-2">
                            Default Overtime Rate (per hour)
                            <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="default_overtime_rate" id="default_overtime_rate"
                               value="{{ old('default_overtime_rate', $settings['default_overtime_rate']) }}"
                               step="0.01" min="0"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('default_overtime_rate') border-red-500 @enderror"
                               placeholder="0.00">
                        <p class="mt-1 text-sm text-gray-500">Applied to new employees by default</p>
                        @error('default_overtime_rate')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Salary Auto Deduction -->
                    <div>
                        <label class="flex items-center">
                            <input type="hidden" name="salary_auto_deduction" value="0">
                            <input type="checkbox" name="salary_auto_deduction" value="1"
                                   {{ old('salary_auto_deduction', $settings['salary_auto_deduction']) ? 'checked' : '' }}
                                   class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                            <span class="ml-3 text-sm text-gray-700">
                                <strong>Auto-deduct advances from salary</strong>
                                <br>
                                <span class="text-gray-500">When checked, advance balance will be automatically deducted during salary generation</span>
                            </span>
                        </label>
                    </div>
                </div>

                <!-- Attendance Columns Section -->
                <div class="border-b border-gray-200 pb-6 pt-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-1">Daily Hajira Columns</h2>
                    <p class="text-sm text-gray-500 mb-5">Choose which columns appear in the Daily Hajira entry screen. At least one must be selected.</p>

                    @php
                        $colOptions = [
                            'absent'    => ['label' => 'Absent (A)',      'desc' => 'Mark employee as absent',           'color' => 'red'],
                            'one'       => ['label' => '1 Hajira',        'desc' => 'Full day present (×1 rate)',         'color' => 'emerald'],
                            'one_half'  => ['label' => '1.5 Hajira',      'desc' => 'Overtime day present (×1.5 rate)',   'color' => 'amber'],
                            'overtime'  => ['label' => 'OT Hours',        'desc' => 'Extra overtime hours input',         'color' => 'purple'],
                            'note'      => ['label' => 'Note / Remarks',  'desc' => 'Free-text note per employee',        'color' => 'blue'],
                        ];
                        $activeColumns = old('attendance_columns', $settings['attendance_columns']) ?? ['absent','one','one_half','overtime','note'];
                    @endphp

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach($colOptions as $val => $opt)
                        @php $active = in_array($val, $activeColumns); @endphp
                        <label class="flex items-center gap-3 p-3 rounded-lg border cursor-pointer transition-colors
                               {{ $active ? 'bg-' . $opt['color'] . '-50 border-' . $opt['color'] . '-200' : 'bg-gray-50 border-gray-200 hover:bg-gray-100' }}">
                            <input type="checkbox" name="attendance_columns[]" value="{{ $val }}"
                                   {{ $active ? 'checked' : '' }}
                                   class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                            <div>
                                <p class="text-sm font-medium text-gray-800">{{ $opt['label'] }}</p>
                                <p class="text-xs text-gray-400">{{ $opt['desc'] }}</p>
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>

                <!-- Buttons -->
                <div class="flex gap-3 pt-6 border-t border-gray-200">
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Save Settings
                    </button>
                    <a href="{{ route('dashboard') }}" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-medium">
                        Cancel
                    </a>
                </div>
            </form>
        </div>

        <!-- Info Box -->
        <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
            <h3 class="font-semibold text-blue-900 mb-2">💡 Tips</h3>
            <ul class="text-sm text-blue-800 space-y-2 list-disc list-inside">
                <li>Company information will appear on printed reports and salary sheets</li>
                <li>Default overtime rate is used for new employees</li>
                <li>Existing employee rates won't be affected by changing the default</li>
                <li>Enable auto-deduction to automatically deduct advance balance from salary</li>
            </ul>
        </div>

        <!-- Danger Zone -->
        <div class="mt-8 bg-white border-2 border-red-200 rounded-lg overflow-hidden" x-data="dangerZone()">
            <div class="px-6 py-4 bg-red-50 border-b border-red-200 flex items-center gap-2">
                <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <h2 class="text-base font-semibold text-red-700">Danger Zone</h2>
                <span class="text-xs text-red-400 ml-1">— These actions are irreversible</span>
            </div>

            <div class="divide-y divide-red-100">
                @foreach([
                    ['type' => 'attendance', 'label' => 'Attendance Data',          'desc' => 'Clears all daily hajira records',                         'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
                    ['type' => 'advances',   'label' => 'Advances & Deductions',    'desc' => 'Clears all advance records and deduction entries',         'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                    ['type' => 'salaries',   'label' => 'Salary Sheets & Payments', 'desc' => 'Clears all salary sheets, payments and deduction entries', 'icon' => 'M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z'],
                    ['type' => 'all',        'label' => 'All Transactional Data',   'desc' => 'Clears attendance, advances, salaries and activity log',   'icon' => 'M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16'],
                ] as $item)
                <div class="flex items-center justify-between px-6 py-4 hover:bg-red-50/50 transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-red-100 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-800">{{ $item['label'] }}</p>
                            <p class="text-xs text-gray-400">{{ $item['desc'] }}</p>
                        </div>
                    </div>
                    <button type="button"
                            @click="open('{{ $item['type'] }}', '{{ $item['label'] }}')"
                            class="px-4 py-1.5 text-xs font-semibold text-red-600 border border-red-300 rounded-lg hover:bg-red-600 hover:text-white hover:border-red-600 transition-all">
                        Clear
                    </button>
                </div>
                @endforeach
            </div>

            <!-- Confirm Modal -->
            <div x-show="modal.show" x-cloak
                 class="fixed inset-0 z-50 flex items-center justify-center p-4"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100">
                <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="cancel()"></div>
                <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100">

                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-semibold text-gray-900">Clear <span x-text="modal.label"></span>?</h3>
                            <p class="text-xs text-gray-500">This action cannot be undone.</p>
                        </div>
                    </div>

                    <p class="text-sm text-gray-600 mb-4">
                        Type <strong class="text-red-600 select-none">DELETE</strong> to confirm.
                    </p>

                    <input type="text" x-model="modal.confirm" placeholder="Type DELETE here..."
                           class="w-full px-3 py-2 border rounded-lg text-sm mb-4 focus:ring-2 focus:border-transparent transition-all"
                           :class="modal.confirm === 'DELETE' ? 'border-red-400 focus:ring-red-300' : 'border-gray-300 focus:ring-gray-300'"
                           @keydown.escape="cancel()">

                    <div class="flex gap-2">
                        <form :action="`{{ url('settings/data') }}/${modal.type}`" method="POST" class="flex-1">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    :disabled="modal.confirm !== 'DELETE'"
                                    class="w-full py-2 rounded-lg text-sm font-semibold transition-all"
                                    :class="modal.confirm === 'DELETE'
                                        ? 'bg-red-600 text-white hover:bg-red-700 cursor-pointer'
                                        : 'bg-gray-100 text-gray-400 cursor-not-allowed'">
                                Yes, Clear Data
                            </button>
                        </form>
                        <button type="button" @click="cancel()"
                                class="flex-1 py-2 rounded-lg text-sm font-semibold border border-gray-200 text-gray-600 hover:bg-gray-50 transition-colors">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
function dangerZone() {
    return {
        modal: { show: false, type: '', label: '', confirm: '' },
        open(type, label) {
            this.modal = { show: true, type, label, confirm: '' };
        },
        cancel() {
            this.modal.show = false;
            this.modal.confirm = '';
        }
    };
}

function syncCurrencySymbol(select) {
    const opt = select.options[select.selectedIndex];
    const sym = opt.getAttribute('data-symbol') || '';
    const input = document.getElementById('currency_symbol');
    const preview = document.getElementById('currencyPreview');
    if (sym) { input.value = sym; preview.textContent = sym; }
}

document.getElementById('currency_symbol').addEventListener('input', function () {
    document.getElementById('currencyPreview').textContent = this.value || '?';
});
</script>
@endsection
