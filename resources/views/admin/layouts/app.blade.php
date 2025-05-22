<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-100">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - Pageman @yield('title')</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:100,200,300,400,500,600,700,800,900&display=swap"
        rel="stylesheet" />

    {{-- Assuming the main application's app.css (compiled with Tailwind) is used --}}
    {{-- If Vite is used in the main app: --}}
    {{-- @vite(['resources/css/app.css', 'resources/js/app.js']) --}}
    {{-- Or link to your compiled asset if the main app uses a different name --}}
    {{-- For development, you might use a CDN if the app doesn't have Tailwind yet --}}
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        /* Minimal global styles, prefer utility classes */
        body {
            font-family: 'Inter', sans-serif;
        }

        [x-cloak] {
            display: none !important;
        }
    </style>

    @stack('styles')
</head>

<body class="font-sans antialiased text-slate-900">
    <div id="app">
        @hasSection('is_login_page')
            {{-- Special layout for login/auth pages --}}
            <div class="flex flex-col items-center justify-center min-h-screen px-4 py-12 bg-slate-100">
                @yield('content')
            </div>
        @else
            {{-- Main Admin Layout with Sidebar and Header --}}
            <div class="flex min-h-screen bg-slate-100">
                <aside x-data="{ open: true }"
                    class="fixed inset-y-0 left-0 z-30 flex flex-col w-64 bg-slate-800 text-slate-100 transition-all duration-300 ease-in-out sm:static sm:inset-auto"
                    :class="{ '-translate-x-full sm:translate-x-0': !open }">
                    <div class="flex items-center justify-between h-16 flex-shrink-0 border-b border-slate-700 px-4">
                        <a href="{{ route(config('pageman.admin.dashboard_route', 'pageman.admin.dashboard')) }}"
                            class="text-xl font-semibold text-white">
                            Pageman
                        </a>
                        <button @click="open = !open" class="sm:hidden text-slate-300 hover:text-white">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    <div class="flex-grow overflow-y-auto">
                        <nav class="px-2 py-4 space-y-1">
                            <a href="{{ route(config('pageman.admin.dashboard_route', 'pageman.admin.dashboard')) }}"
                                class="group flex items-center px-2 py-2.5 text-sm font-medium rounded-md {{ request()->routeIs(config('pageman.admin.dashboard_route', 'pageman.admin.dashboard')) ? 'bg-slate-900 text-white' : 'text-slate-300 hover:bg-slate-700 hover:text-white' }} transition-colors">
                                <svg class="mr-3 h-6 w-6 flex-shrink-0 text-slate-400 group-hover:text-slate-300"
                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h7.5" />
                                </svg>
                                Dashboard
                            </a>
                            {{-- Add more navigation links here as your CMS grows --}}
                            {{-- Example:
                            <a href="#"
                               class="group flex items-center px-2 py-2.5 text-sm font-medium rounded-md text-slate-300 hover:bg-slate-700 hover:text-white transition-colors">
                                <svg class="mr-3 h-6 w-6 flex-shrink-0 text-slate-400 group-hover:text-slate-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                </svg>
                                Pages
                            </a>
                            --}}
                        </nav>
                    </div>
                </aside>

                <div class="flex flex-col flex-1">
                    <header x-data="{ sidebarOpen: false }" class="bg-white shadow-sm sticky top-0 z-20">
                        <div class="mx-auto max-w-full px-4 sm:px-6 lg:px-8"> {{-- Changed max-w-7xl to max-w-full --}}
                            <div class="flex h-16 items-center justify-between">
                                <div class="flex items-center">
                                    <button @click="sidebarOpen = true" x-data="{}" x-init="$dispatch('alpine-sidebar-open', { open: sidebarOpen })"
                                        @alpine-sidebar-open.window="document.querySelector('aside[x-data]').__x.$data.open = true"
                                        class="sm:hidden mr-3 text-slate-500 hover:text-slate-700">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 6h16M4 12h16M4 18h16"></path>
                                        </svg>
                                    </button>
                                    <h1 class="text-xl font-semibold text-slate-800">@yield('header-title', 'Admin Panel')</h1>
                                </div>
                                <div class="ml-auto flex items-center">
                                    @auth('web')
                                        {{-- Specify the guard if not default --}}
                                        <span class="text-sm text-slate-600 mr-4">
                                            Welcome, {{ Auth::guard('web')->user()->name }}!
                                        </span>
                                        <form method="POST"
                                            action="{{ route(config('pageman.auth.logout_route', 'pageman.auth.logout')) }}">
                                            @csrf
                                            <button type="submit"
                                                class="text-sm font-medium text-blue-600 hover:text-blue-500 focus:outline-none focus:underline">
                                                Logout
                                            </button>
                                        </form>
                                    @endauth
                                </div>
                            </div>
                        </div>
                    </header>

                    <main class="flex-1 p-6">
                        @yield('content')
                    </main>

                    <footer class="py-4 px-6 border-t border-slate-200 bg-white text-center text-sm text-slate-500">
                        Pageman &copy; {{ date('Y') }}. All rights reserved.
                    </footer>
                </div>
            </div>
        @endif
    </div>

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @stack('scripts')
</body>

</html>
