<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'HajiraPayroll') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    </head>
    <body class="font-sans antialiased bg-gray-50">
        <div class="flex h-screen overflow-hidden" x-data="{ sidebarOpen: false, desktopSidebarOpen: localStorage.getItem('sidebarOpen') !== 'false' }" x-init="$watch('desktopSidebarOpen', v => localStorage.setItem('sidebarOpen', v))">
            <!-- Sidebar (Hidden on mobile, visible on desktop) -->
            <aside x-show="desktopSidebarOpen" x-transition:enter="transition-all duration-200" x-transition:enter-start="w-0 opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition-all duration-200" x-transition:leave-end="w-0 opacity-0" class="hidden md:flex md:w-64 bg-gradient-to-b from-blue-900 to-blue-800 text-white flex-col shrink-0">
                <!-- Logo -->
                <div class="p-6 border-b border-blue-700">
                    <h1 class="text-2xl font-bold">Hajira</h1>
                    <p class="text-xs text-blue-200">Payroll System</p>
                </div>

                <!-- Navigation Menu -->
                <nav class="flex-1 overflow-y-auto p-4 space-y-2">
                    <a href="{{ route('dashboard') }}" class="flex items-center px-4 py-3 rounded-lg hover:bg-blue-700 transition {{ request()->routeIs('dashboard') ? 'bg-blue-700' : '' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-3m0 0l7-4 7 4M5 9v10a1 1 0 001 1h12a1 1 0 001-1V9m-9 4l4-2m-9 0l4 2"></path>
                        </svg>
                        Dashboard
                    </a>

                    <a href="{{ route('employees.index') }}" class="flex items-center px-4 py-3 rounded-lg hover:bg-blue-700 transition {{ request()->routeIs('employees.*') ? 'bg-blue-700' : '' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.856-1.487M15 10a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        Employees
                    </a>

                    <a href="{{ route('attendance.index') }}" class="flex items-center px-4 py-3 rounded-lg hover:bg-blue-700 transition {{ request()->routeIs('attendance.*') ? 'bg-blue-700' : '' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Daily Hajira
                    </a>

                    <a href="{{ route('advances.index') }}" class="flex items-center px-4 py-3 rounded-lg hover:bg-blue-700 transition {{ request()->routeIs('advances.*') ? 'bg-blue-700' : '' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Advances
                    </a>

                    <a href="{{ route('salaries.index') }}" class="flex items-center px-4 py-3 rounded-lg hover:bg-blue-700 transition {{ request()->routeIs('salaries.*') ? 'bg-blue-700' : '' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Salary
                    </a>

                    <a href="{{ route('payments.index') }}" class="flex items-center px-4 py-3 rounded-lg hover:bg-blue-700 transition {{ request()->routeIs('payments.*') ? 'bg-blue-700' : '' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m7 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Payments
                    </a>

                    <a href="{{ route('reports.index') }}" class="flex items-center px-4 py-3 rounded-lg hover:bg-blue-700 transition {{ request()->routeIs('reports.*') ? 'bg-blue-700' : '' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        Reports
                    </a>

                    <a href="{{ route('settings.index') }}" class="flex items-center px-4 py-3 rounded-lg hover:bg-blue-700 transition {{ request()->routeIs('settings.*') ? 'bg-blue-700' : '' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        Settings
                    </a>
                </nav>

                <!-- User Profile -->
                <div class="p-4 border-t border-blue-700">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-blue-400 rounded-full flex items-center justify-center text-sm font-bold">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </div>
                        <div class="ml-3 flex-1">
                            <p class="text-sm font-semibold">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-blue-200">{{ auth()->user()->role }}</p>
                        </div>
                    </div>
                </div>
            </aside>

            <!-- Main Content -->
            <div class="flex-1 flex flex-col overflow-hidden">
                <!-- Top Navigation (Mobile + Desktop) -->
                <header class="bg-white shadow-sm sticky top-0 z-40">
                    <div class="flex items-center justify-between h-16 px-4 sm:px-6 lg:px-8">
                        <!-- Desktop Sidebar Toggle -->
                        <button @click="desktopSidebarOpen = !desktopSidebarOpen" class="hidden md:inline-flex items-center justify-center p-2 rounded-md text-gray-500 hover:bg-gray-100 hover:text-gray-700 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                            </svg>
                        </button>
                        <!-- Mobile Menu Button -->
                        <button @click="sidebarOpen = !sidebarOpen" class="md:hidden inline-flex items-center justify-center p-2 rounded-md text-gray-700 hover:bg-gray-100">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                            </svg>
                        </button>

                        <!-- Logo (Mobile only) -->
                        <h1 class="md:hidden text-xl font-bold text-blue-900">Hajira</h1>

                        <!-- User Dropdown -->
                        <div class="ml-auto flex items-center space-x-4">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="text-gray-700 hover:text-gray-900 text-sm font-medium">
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </header>

                <!-- Mobile Sidebar -->
                <div x-show="sidebarOpen" class="md:hidden fixed inset-0 z-30 bg-black bg-opacity-50" @click="sidebarOpen = false"></div>
                <div x-show="sidebarOpen" class="md:hidden fixed inset-y-0 left-0 z-30 w-64 bg-gradient-to-b from-blue-900 to-blue-800 text-white overflow-y-auto">
                    <div class="p-6 border-b border-blue-700">
                        <h1 class="text-2xl font-bold">Hajira</h1>
                    </div>
                    <nav class="p-4 space-y-2">
                        <a href="{{ route('dashboard') }}" class="flex items-center px-4 py-3 rounded-lg hover:bg-blue-700 transition">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-3m0 0l7-4 7 4M5 9v10a1 1 0 001 1h12a1 1 0 001-1V9m-9 4l4-2m-9 0l4 2"></path>
                            </svg>
                            Dashboard
                        </a>
                        <a href="{{ route('employees.index') }}" class="flex items-center px-4 py-3 rounded-lg hover:bg-blue-700 transition">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.856-1.487M15 10a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            Employees
                        </a>
                        <a href="{{ route('attendance.index') }}" class="flex items-center px-4 py-3 rounded-lg hover:bg-blue-700 transition">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Daily Hajira
                        </a>
                    </nav>
                </div>

                <!-- Page Content -->
                <main class="flex-1 overflow-y-auto">
                    @isset($header)
                        <div class="bg-white shadow-sm border-b">
                            <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
                                {{ $header }}
                            </div>
                        </div>
                    @endisset

                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        @hasSection('content')
                            @yield('content')
                        @else
                            {{ $slot }}
                        @endif
                    </div>
                </main>
            </div>
        </div>

        <!-- Toast Notifications -->
        <x-toast-notifications />
    </body>
</html>
