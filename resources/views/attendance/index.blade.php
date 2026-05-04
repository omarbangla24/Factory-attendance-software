<x-app-layout>
<div x-data="hajiraApp()" x-init="loadData()" x-cloak>
@php $cols = $attendanceColumns ?? ['absent','one','one_half','overtime','note']; @endphp

    {{-- ══ Tab Bar + Controls ══ --}}
    <div class="sticky top-0 z-30 bg-white border-b shadow-sm">
        <div class="max-w-6xl mx-auto px-4">

            {{-- Tabs --}}
            <div class="flex gap-0 border-b -mb-px">
                <button @click="tab='daily'"
                        :class="tab==='daily' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-400 hover:text-gray-600'"
                        class="px-5 py-3 text-sm font-medium border-b-2 transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    Daily Entry
                </button>
                <button @click="tab='calendar'; loadCalendar()"
                        :class="tab==='calendar' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-400 hover:text-gray-600'"
                        class="px-5 py-3 text-sm font-medium border-b-2 transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    Calendar View
                </button>
            </div>

            {{-- Daily Entry Controls --}}
            <div x-show="tab==='daily'" class="py-2.5 flex flex-wrap gap-2 items-center">
                {{-- Date nav --}}
                <div class="flex items-center gap-1 bg-gray-50 rounded-lg border border-gray-200 p-0.5">
                    <button @click="prevDate()" class="p-1.5 rounded-md hover:bg-white hover:shadow-sm text-gray-500 transition-all">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <input type="date" x-model="date" @change="loadData()"
                           class="text-sm font-medium bg-transparent border-0 focus:ring-0 px-1 py-1 text-gray-700 w-34">
                    <button @click="nextDate()" class="p-1.5 rounded-md hover:bg-white hover:shadow-sm text-gray-500 transition-all">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>

                <input type="text" x-model="search" @input.debounce.400ms="loadData()" placeholder="Search..."
                       class="text-sm border border-gray-200 rounded-lg px-3 py-1.5 focus:ring-2 focus:ring-blue-400 focus:border-transparent w-36 bg-gray-50">

                <select x-model="dept" @change="loadData()"
                        class="text-sm border border-gray-200 rounded-lg px-3 py-1.5 focus:ring-2 focus:ring-blue-400 focus:border-transparent bg-gray-50 min-w-36">
                    <option value="">All Depts</option>
                    <template x-for="d in departments" :key="d">
                        <option :value="d" x-text="d"></option>
                    </template>
                </select>

                {{-- Bulk --}}
                <div class="flex gap-1 items-center">
                    <span class="text-xs text-gray-300">|</span>
                    @if(in_array('absent',   $cols)) <button @click="bulkSet('absent')"   class="text-xs px-2.5 py-1.5 rounded-lg bg-red-50   text-red-600   hover:bg-red-100   border border-red-100   transition-colors">All Absent</button> @endif
                    @if(in_array('one',      $cols)) <button @click="bulkSet('one')"      class="text-xs px-2.5 py-1.5 rounded-lg bg-green-50 text-green-600 hover:bg-green-100 border border-green-100 transition-colors">All 1</button> @endif
                    @if(in_array('one_half', $cols)) <button @click="bulkSet('one_half')" class="text-xs px-2.5 py-1.5 rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-100 border border-amber-100 transition-colors">All 1.5</button> @endif
                    @if(in_array('overtime', $cols)) <button @click="bulkSet('clear_ot')" class="text-xs px-2.5 py-1.5 rounded-lg bg-gray-50  text-gray-500  hover:bg-gray-100  border border-gray-200  transition-colors">Clear OT</button> @endif
                </div>

                {{-- Global status --}}
                <div class="ml-auto flex items-center gap-3">
                    <template x-if="loading">
                        <span class="flex items-center gap-1 text-xs text-gray-400">
                            <svg class="animate-spin w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        </span>
                    </template>
                    <template x-if="!loading && pendingSaves > 0">
                        <span class="flex items-center gap-1 text-xs text-amber-500 font-medium">
                            <svg class="animate-spin w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            Saving
                        </span>
                    </template>
                    <template x-if="!loading && pendingSaves === 0 && allSaved">
                        <span class="flex items-center gap-1 text-xs text-emerald-500 font-medium">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 00-1.414 0L8 12.586 4.707 9.293a1 1 0 00-1.414 1.414l4 4a1 1 0 001.414 0l8-8a1 1 0 000-1.414z" clip-rule="evenodd"/></svg>
                            Saved
                        </span>
                    </template>

                    {{-- Stats pills --}}
                    <div class="hidden sm:flex items-center gap-2 text-xs">
                        <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 rounded-full font-medium" x-text="stats.present + ' P'"></span>
                        <span class="px-2 py-0.5 bg-red-50 text-red-600 rounded-full font-medium" x-text="stats.absent + ' A'"></span>
                        <span class="px-2 py-0.5 bg-blue-50 text-blue-600 rounded-full font-medium" x-text="stats.hajira + ' H'"></span>
                        <span x-show="stats.overtime > 0" class="px-2 py-0.5 bg-purple-50 text-purple-600 rounded-full font-medium" x-text="stats.overtime + 'h OT'"></span>
                    </div>
                </div>
            </div>

            {{-- Calendar Controls --}}
            <div x-show="tab==='calendar'" class="py-2.5 flex flex-wrap gap-3 items-center">
                <select x-model="cal.employeeId" @change="loadCalendar()"
                        class="text-sm border border-gray-200 rounded-lg px-3 py-1.5 focus:ring-2 focus:ring-blue-400 focus:border-transparent bg-gray-50 min-w-48">
                    <option value="">— Select Employee —</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}">{{ $emp->name }}@if($emp->department) ({{ $emp->department }})@endif</option>
                    @endforeach
                </select>

                <div class="flex items-center gap-1 bg-gray-50 rounded-lg border border-gray-200 p-0.5">
                    <button @click="calPrevMonth()" class="p-1.5 rounded-md hover:bg-white hover:shadow-sm text-gray-500 transition-all">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <span class="text-sm font-medium text-gray-700 px-2 min-w-28 text-center" x-text="calMonthLabel()"></span>
                    <button @click="calNextMonth()" class="p-1.5 rounded-md hover:bg-white hover:shadow-sm text-gray-500 transition-all">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>

                <template x-if="cal.loading">
                    <svg class="animate-spin w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                </template>

                {{-- Calendar summary pills --}}
                <template x-if="cal.summary && cal.employeeId">
                    <div class="flex items-center gap-2 text-xs ml-auto">
                        <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 rounded-full font-medium" x-text="cal.summary.present + ' Present'"></span>
                        <span class="px-2 py-0.5 bg-red-50 text-red-600 rounded-full font-medium" x-text="cal.summary.absent + ' Absent'"></span>
                        <span class="px-2 py-0.5 bg-blue-50 text-blue-600 rounded-full font-medium" x-text="cal.summary.hajira + ' Hajira'"></span>
                        <span x-show="cal.summary.overtime > 0" class="px-2 py-0.5 bg-purple-50 text-purple-600 rounded-full font-medium" x-text="cal.summary.overtime + 'h OT'"></span>
                    </div>
                </template>
            </div>

        </div>
    </div>

    {{-- ══ DAILY ENTRY TAB ══ --}}
    <div x-show="tab==='daily'" class="max-w-6xl mx-auto px-4 py-4">

        {{-- Skeleton --}}
        <div x-show="loading && employees.length === 0" class="space-y-1">
            <template x-for="i in 6" :key="i">
                <div class="animate-pulse bg-gray-100 rounded-lg h-10"></div>
            </template>
        </div>

        {{-- Empty --}}
        <div x-show="!loading && employees.length === 0" class="py-16 text-center">
            <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <p class="text-sm text-gray-400">No employees found</p>
        </div>

        {{-- Desktop Table --}}
        <div x-show="employees.length > 0" class="hidden md:block bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50/80 border-b border-gray-100">
                        <th class="text-left px-4 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">Employee</th>
                        @if(in_array('absent',   $cols)) <th class="text-center px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider w-16">Absent</th> @endif
                        @if(in_array('one',      $cols)) <th class="text-center px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider w-20">1 Hajira</th> @endif
                        @if(in_array('one_half', $cols)) <th class="text-center px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider w-20">1.5 Hajira</th> @endif
                        @if(in_array('overtime', $cols)) <th class="text-center px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider w-20">OT Hrs</th> @endif
                        @if(in_array('note',     $cols)) <th class="text-left px-4 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">Note</th> @endif
                        <th class="w-7 px-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <template x-for="emp in employees" :key="emp.id">
                        <tr class="group transition-colors"
                            :class="emp.hajira_type === 'absent' ? 'bg-red-50/30 hover:bg-red-50/50' : 'hover:bg-blue-50/20'">

                            {{-- Name --}}
                            <td class="px-4 py-2.5">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold shrink-0"
                                         :class="emp.hajira_type === 'absent' ? 'bg-red-100 text-red-500' : (emp.hajira_type === 'one_half' ? 'bg-amber-100 text-amber-600' : 'bg-emerald-100 text-emerald-600')"
                                         x-text="emp.name.charAt(0).toUpperCase()">
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-800 leading-tight" x-text="emp.name"></div>
                                        <div class="text-xs text-gray-400" x-text="emp.department"></div>
                                    </div>
                                </div>
                            </td>

                            {{-- Absent --}}
                            @if(in_array('absent', $cols))
                            <td class="px-3 py-2.5 text-center">
                                <button @click="setHajiraAndSave(emp, 'absent')"
                                        :class="emp.hajira_type === 'absent'
                                            ? 'bg-red-500 text-white ring-2 ring-red-200'
                                            : 'bg-white text-gray-300 border border-gray-200 hover:border-red-300 hover:text-red-400'"
                                        class="w-8 h-8 rounded-lg text-xs font-bold transition-all">A</button>
                            </td>
                            @endif

                            {{-- 1 Hajira --}}
                            @if(in_array('one', $cols))
                            <td class="px-3 py-2.5 text-center">
                                <button @click="setHajiraAndSave(emp, 'one')"
                                        :class="emp.hajira_type === 'one'
                                            ? 'bg-emerald-500 text-white ring-2 ring-emerald-200'
                                            : 'bg-white text-gray-300 border border-gray-200 hover:border-emerald-300 hover:text-emerald-400'"
                                        class="w-8 h-8 rounded-lg text-xs font-bold transition-all">1</button>
                            </td>
                            @endif

                            {{-- 1.5 Hajira --}}
                            @if(in_array('one_half', $cols))
                            <td class="px-3 py-2.5 text-center">
                                <button @click="setHajiraAndSave(emp, 'one_half')"
                                        :class="emp.hajira_type === 'one_half'
                                            ? 'bg-amber-500 text-white ring-2 ring-amber-200'
                                            : 'bg-white text-gray-300 border border-gray-200 hover:border-amber-300 hover:text-amber-400'"
                                        class="w-8 h-8 rounded-lg text-xs font-bold transition-all">1½</button>
                            </td>
                            @endif

                            {{-- OT --}}
                            @if(in_array('overtime', $cols))
                            <td class="px-3 py-2.5 text-center">
                                <input type="number" x-model.number="emp.overtime_hours"
                                       @change="autoSave(emp)" step="0.5" min="0"
                                       class="w-14 text-center text-sm border border-gray-200 rounded-lg px-1 py-1 focus:ring-2 focus:ring-blue-400 focus:border-transparent bg-gray-50 focus:bg-white transition-colors">
                            </td>
                            @endif

                            {{-- Note --}}
                            @if(in_array('note', $cols))
                            <td class="px-4 py-2.5">
                                <input type="text" x-model="emp.note"
                                       @input.debounce.800ms="autoSave(emp)" placeholder="Add note..."
                                       class="w-full text-sm bg-transparent border-0 border-b border-transparent group-hover:border-gray-200 focus:border-blue-300 focus:ring-0 py-0.5 placeholder-gray-300 text-gray-600 transition-colors">
                            </td>
                            @endif

                            {{-- Row status --}}
                            <td class="px-2 py-2.5 text-center w-7">
                                <span x-show="emp._status === 'saving'" class="text-gray-300 inline-flex">
                                    <svg class="animate-spin w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                </span>
                                <span x-show="emp._status === 'saved'" class="text-emerald-400 inline-flex">
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 00-1.414 0L8 12.586 4.707 9.293a1 1 0 00-1.414 1.414l4 4a1 1 0 001.414 0l8-8a1 1 0 000-1.414z" clip-rule="evenodd"/></svg>
                                </span>
                                <span x-show="emp._status === 'error'" class="text-red-400 inline-flex" title="Save failed — click to retry" @click="autoSave(emp)" style="cursor:pointer">
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                                </span>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        {{-- Mobile Cards --}}
        <div x-show="employees.length > 0" class="md:hidden space-y-2">
            <template x-for="emp in employees" :key="emp.id">
                <div class="bg-white rounded-xl border shadow-sm px-4 py-3 transition-colors"
                     :class="emp.hajira_type === 'absent' ? 'border-red-200' : 'border-gray-200'">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold"
                                 :class="emp.hajira_type === 'absent' ? 'bg-red-100 text-red-500' : 'bg-emerald-100 text-emerald-600'"
                                 x-text="emp.name.charAt(0).toUpperCase()"></div>
                            <div>
                                <div class="text-sm font-semibold text-gray-800" x-text="emp.name"></div>
                                <div class="text-xs text-gray-400" x-text="emp.department"></div>
                            </div>
                            <span x-show="emp._status === 'saving'" class="text-gray-300 ml-1">
                                <svg class="animate-spin w-3 h-3" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            </span>
                            <span x-show="emp._status === 'saved'" class="text-emerald-400 ml-1">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 00-1.414 0L8 12.586 4.707 9.293a1 1 0 00-1.414 1.414l4 4a1 1 0 001.414 0l8-8a1 1 0 000-1.414z" clip-rule="evenodd"/></svg>
                            </span>
                        </div>
                        <div class="flex gap-1.5">
                            @if(in_array('absent', $cols))
                            <button @click="setHajiraAndSave(emp, 'absent')"
                                    :class="emp.hajira_type === 'absent' ? 'bg-red-500 text-white' : 'bg-gray-100 text-gray-500 hover:bg-red-50 hover:text-red-500'"
                                    class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all">A</button>
                            @endif
                            @if(in_array('one', $cols))
                            <button @click="setHajiraAndSave(emp, 'one')"
                                    :class="emp.hajira_type === 'one' ? 'bg-emerald-500 text-white' : 'bg-gray-100 text-gray-500 hover:bg-emerald-50 hover:text-emerald-500'"
                                    class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all">1</button>
                            @endif
                            @if(in_array('one_half', $cols))
                            <button @click="setHajiraAndSave(emp, 'one_half')"
                                    :class="emp.hajira_type === 'one_half' ? 'bg-amber-500 text-white' : 'bg-gray-100 text-gray-500 hover:bg-amber-50 hover:text-amber-500'"
                                    class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all">1½</button>
                            @endif
                        </div>
                    </div>
                    <div class="flex gap-2.5 items-center">
                        @if(in_array('overtime', $cols))
                        <div class="flex items-center gap-1.5">
                            <span class="text-xs text-gray-400 font-medium">OT</span>
                            <input type="number" x-model.number="emp.overtime_hours" @change="autoSave(emp)"
                                   step="0.5" min="0"
                                   class="w-14 text-center text-xs border border-gray-200 rounded-lg px-1 py-1 focus:ring-2 focus:ring-blue-400 bg-gray-50">
                        </div>
                        @endif
                        @if(in_array('note', $cols))
                        <input type="text" x-model="emp.note" @input.debounce.800ms="autoSave(emp)" placeholder="Note..."
                               class="flex-1 text-xs border border-gray-200 rounded-lg px-2.5 py-1 placeholder-gray-300 focus:ring-2 focus:ring-blue-400 focus:border-transparent bg-gray-50">
                        @endif
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- ══ CALENDAR TAB ══ --}}
    <div x-show="tab==='calendar'" class="max-w-6xl mx-auto px-4 py-4">

        {{-- No employee selected --}}
        <div x-show="!cal.employeeId" class="py-20 text-center">
            <div class="w-16 h-16 rounded-full bg-blue-50 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            </div>
            <p class="text-sm text-gray-400 font-medium">Select an employee to view their monthly attendance</p>
        </div>

        {{-- Calendar --}}
        <div x-show="cal.employeeId" class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">

            {{-- Legend --}}
            <div class="flex items-center gap-4 px-4 py-2 bg-gray-50 border-b border-gray-200 text-xs text-gray-500">
                <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-emerald-500 inline-block"></span>1 Hajira</span>
                <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-amber-500 inline-block"></span>1.5 Hajira</span>
                <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-red-500 inline-block"></span>Absent</span>
                <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-gray-200 inline-block"></span>No Data</span>
            </div>

            {{-- Calendar grid --}}
            <div class="p-4">
                {{-- Day headers (ISO: Mon–Sun) --}}
                <div class="grid grid-cols-7 mb-1">
                    <template x-for="d in ['Mon','Tue','Wed','Thu','Fri','Sat','Sun']" :key="d">
                        <div class="text-center text-xs font-semibold text-gray-400 py-2" x-text="d"></div>
                    </template>
                </div>

                {{-- Day cells --}}
                <div class="grid grid-cols-7 gap-1">
                    <template x-for="(cell, idx) in calDays()" :key="idx">
                        <div>
                            {{-- Empty cell --}}
                            <div x-show="!cell" class="h-16"></div>

                            {{-- Day cell --}}
                            <div x-show="cell"
                                 @click="cell && jumpToDay(cell.dateStr)"
                                 :title="cell && cell.attendance && cell.attendance.note ? cell.attendance.note : ''"
                                 :class="cell && cell.attendance
                                    ? (cell.attendance.hajira_type === 'one_half'
                                        ? 'bg-amber-100 border-amber-300 hover:bg-amber-200'
                                        : cell.attendance.hajira_type === 'one'
                                            ? 'bg-emerald-100 border-emerald-300 hover:bg-emerald-200'
                                            : 'bg-red-100 border-red-300 hover:bg-red-200')
                                    : (cell && cell.isFuture
                                        ? 'bg-gray-50 border-gray-100 opacity-50 cursor-default'
                                        : 'bg-white border-gray-200 hover:bg-gray-50 cursor-pointer')"
                                 class="h-16 rounded-lg border p-1.5 flex flex-col cursor-pointer transition-colors select-none">

                                {{-- Day number + OT --}}
                                <div class="flex items-start justify-between leading-none">
                                    <span :class="cell && cell.isToday
                                              ? 'bg-blue-500 text-white w-5 h-5 rounded-full flex items-center justify-center text-xs font-bold'
                                              : (cell && cell.isWeekend ? 'text-xs font-semibold text-gray-400' : 'text-xs font-semibold text-gray-600')"
                                          x-text="cell ? cell.day : ''"></span>
                                    <span x-show="cell && cell.attendance && cell.attendance.overtime_hours > 0"
                                          class="text-xs text-purple-600 font-bold"
                                          x-text="cell && cell.attendance ? '+' + cell.attendance.overtime_hours : ''"></span>
                                </div>

                                {{-- Status badge --}}
                                <div class="flex-1 flex items-center justify-center">
                                    <template x-if="cell && cell.attendance">
                                        <span :class="cell.attendance.hajira_type === 'one_half'
                                                ? 'bg-amber-500 text-white'
                                                : cell.attendance.hajira_type === 'one'
                                                    ? 'bg-emerald-500 text-white'
                                                    : 'bg-red-500 text-white'"
                                              class="text-xs font-bold px-1.5 py-0.5 rounded-md shadow-sm"
                                              x-text="cell.attendance.hajira_type === 'absent' ? 'A' : cell.attendance.hajira_value"></span>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    {{-- Error Toast --}}
    <div x-show="toast.show"
         x-transition:enter="transition ease-out duration-200 transform"
         x-transition:enter-start="opacity-0 translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-end="opacity-0"
         class="fixed bottom-6 right-4 z-50 pointer-events-none">
        <div class="bg-white border border-red-200 text-red-700 rounded-xl shadow-lg px-4 py-3 flex items-center gap-2.5 text-sm max-w-xs">
            <svg class="w-4 h-4 text-red-400 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
            <span x-text="toast.message"></span>
        </div>
    </div>
</div>

<script>
function hajiraApp() {
    const TODAY = new Date().toISOString().split('T')[0];
    return {
        tab: 'daily',
        date: '{{ $date }}',
        search: '',
        dept: '',
        departments: @json($departments),
        employees: [],
        loading: false,
        pendingSaves: 0,
        allSaved: false,
        toast: { show: false, message: '' },
        stats: { present: 0, absent: 0, hajira: 0, overtime: 0 },

        cal: {
            employeeId: '',
            month: '{{ \Carbon\Carbon::today()->format('Y-m') }}',
            attendances: {},
            summary: null,
            loading: false,
        },

        // ── Daily Entry ─────────────────────────────────────────────
        async loadData() {
            this.loading = true;
            this.allSaved = false;
            try {
                const params = new URLSearchParams({ date: this.date, search: this.search, department: this.dept });
                const res = await fetch('{{ route('attendance.data') }}?' + params, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await res.json();
                this.employees = data.employees.map(e => ({ ...e, _status: null }));
                this.departments = data.departments;
                this.computeStats();
            } catch (e) {
                this.showToast('Failed to load attendance data');
            } finally {
                this.loading = false;
            }
        },

        computeStats() {
            this.stats = { present: 0, absent: 0, hajira: 0, overtime: 0 };
            this.employees.forEach(e => {
                if (e.hajira_type === 'absent') this.stats.absent++;
                else this.stats.present++;
                this.stats.hajira   = Math.round((this.stats.hajira   + (parseFloat(e.hajira_value)   || 0)) * 10) / 10;
                this.stats.overtime = Math.round((this.stats.overtime + (parseFloat(e.overtime_hours) || 0)) * 10) / 10;
            });
        },

        setHajiraAndSave(emp, type) {
            emp.hajira_type  = type;
            emp.hajira_value = type === 'absent' ? 0 : (type === 'one' ? 1 : 1.5);
            this.computeStats();
            this.autoSave(emp);
        },

        async autoSave(emp) {
            emp._status = 'saving';
            this.pendingSaves++;
            this.allSaved = false;
            try {
                const res = await fetch('{{ route('attendance.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        attendances: JSON.stringify([{
                            employee_id:    emp.id,
                            date:           this.date,
                            hajira_type:    emp.hajira_type,
                            hajira_value:   emp.hajira_value,
                            overtime_hours: emp.overtime_hours || 0,
                            note:           emp.note || ''
                        }])
                    })
                });
                const data = await res.json();
                emp._status = data.success ? 'saved' : 'error';
                if (!data.success) this.showToast(emp.name + ': Save failed');
            } catch (e) {
                emp._status = 'error';
                this.showToast('Network error — check connection');
            } finally {
                this.pendingSaves--;
                if (this.pendingSaves === 0) {
                    this.allSaved = !this.employees.some(e => e._status === 'error');
                    if (this.allSaved) {
                        setTimeout(() => {
                            this.employees.forEach(e => { if (e._status === 'saved') e._status = null; });
                            this.allSaved = false;
                        }, 2500);
                    }
                }
            }
        },

        bulkSet(action) {
            this.employees.forEach(emp => {
                if      (action === 'absent')   { emp.hajira_type = 'absent';   emp.hajira_value = 0;   }
                else if (action === 'one')       { emp.hajira_type = 'one';      emp.hajira_value = 1;   }
                else if (action === 'one_half')  { emp.hajira_type = 'one_half'; emp.hajira_value = 1.5; }
                else if (action === 'clear_ot')  { emp.overtime_hours = 0; }
            });
            this.computeStats();
            this.employees.forEach(emp => this.autoSave(emp));
        },

        prevDate() {
            const d = new Date(this.date + 'T00:00:00'); d.setDate(d.getDate() - 1);
            this.date = d.getFullYear() + '-' + String(d.getMonth()+1).padStart(2,'0') + '-' + String(d.getDate()).padStart(2,'0');
            this.loadData();
        },
        nextDate() {
            const d = new Date(this.date + 'T00:00:00'); d.setDate(d.getDate() + 1);
            this.date = d.getFullYear() + '-' + String(d.getMonth()+1).padStart(2,'0') + '-' + String(d.getDate()).padStart(2,'0');
            this.loadData();
        },

        // ── Calendar View ────────────────────────────────────────────
        async loadCalendar() {
            if (!this.cal.employeeId) return;
            this.cal.loading = true;
            try {
                const params = new URLSearchParams({ employee_id: this.cal.employeeId, month: this.cal.month });
                const res = await fetch('{{ route('attendance.calendar') }}?' + params, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await res.json();
                this.cal.attendances = data.attendances || {};
                this.cal.summary = data.summary;
            } catch (e) {
                this.showToast('Failed to load calendar data');
            } finally {
                this.cal.loading = false;
            }
        },

        calDays() {
            const [year, month] = this.cal.month.split('-').map(Number);
            const firstDow = new Date(year, month - 1, 1).getDay(); // 0=Sun
            const leading  = firstDow === 0 ? 6 : firstDow - 1;     // ISO Mon=0
            const total    = new Date(year, month, 0).getDate();
            const cells    = [];

            for (let i = 0; i < leading; i++) cells.push(null);
            for (let d = 1; d <= total; d++) {
                const dateStr  = `${year}-${String(month).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
                const dow      = new Date(year, month - 1, d).getDay();
                cells.push({
                    day:        d,
                    dateStr,
                    isToday:    dateStr === TODAY,
                    isFuture:   dateStr > TODAY,
                    isWeekend:  dow === 0 || dow === 6,
                    attendance: this.cal.attendances[dateStr] || null,
                });
            }
            return cells;
        },

        calMonthLabel() {
            const [y, m] = this.cal.month.split('-').map(Number);
            return new Date(y, m - 1, 1).toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
        },
        calPrevMonth() {
            const [y, m] = this.cal.month.split('-').map(Number);
            const d = new Date(y, m - 2, 1);
            this.cal.month = `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}`;
            this.loadCalendar();
        },
        calNextMonth() {
            const [y, m] = this.cal.month.split('-').map(Number);
            const d = new Date(y, m, 1);
            this.cal.month = `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}`;
            this.loadCalendar();
        },

        jumpToDay(dateStr) {
            this.date = dateStr;
            this.tab  = 'daily';
            this.loadData();
        },

        // ── Shared ───────────────────────────────────────────────────
        showToast(message) {
            this.toast = { show: true, message };
            setTimeout(() => this.toast.show = false, 4000);
        },
    };
}
</script>
</x-app-layout>
