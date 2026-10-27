<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'baseNEXA') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="flex min-h-screen">

            <!-- Left: brand panel (desktop only) -->
            <div class="relative hidden overflow-hidden bg-gradient-to-br from-indigo-700 via-indigo-600 to-violet-600 p-12 text-white lg:flex lg:w-1/2 lg:flex-col lg:justify-between">

                <!-- Decorative circles -->
                <div class="absolute -right-24 -top-24 h-72 w-72 rounded-full bg-white/10"></div>
                <div class="absolute -bottom-32 -left-20 h-96 w-96 rounded-full bg-white/10"></div>

                <!-- Brand -->
                <div class="relative flex items-center gap-3">
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-white text-xl font-extrabold text-indigo-700 shadow-lg">B</span>
                    <span class="text-2xl font-bold tracking-tight">base<span class="text-indigo-200">NEXA</span></span>
                </div>

                <!-- Headline -->
                <div class="relative">
                    <h1 class="text-4xl font-extrabold leading-tight">
                        Manage all your hotels<br>and villas in one place.
                    </h1>
                    <p class="mt-4 max-w-md text-lg text-indigo-100">
                        Bookings, rooms, guests, payments and reports — one simple platform for every property.
                    </p>

                    <ul class="mt-8 space-y-3 text-indigo-50">
                        <li class="flex items-center gap-3">
                            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-white/20 text-sm">✓</span>
                            Multi-company, multi-property
                        </li>
                        <li class="flex items-center gap-3">
                            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-white/20 text-sm">✓</span>
                            Live availability, no double bookings
                        </li>
                        <li class="flex items-center gap-3">
                            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-white/20 text-sm">✓</span>
                            Secure, role-based access
                        </li>
                    </ul>
                </div>

                <p class="relative text-sm text-indigo-200">
                    &copy; {{ date('Y') }} baseNEXA by Baseline. All rights reserved.
                </p>
            </div>

            <!-- Right: form area -->
            <div class="flex w-full items-center justify-center bg-gray-50 px-6 py-12 lg:w-1/2">
                <div class="w-full max-w-md">

                    <!-- Brand (mobile only) -->
                    <div class="mb-8 flex items-center justify-center gap-3 lg:hidden">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-600 to-violet-600 text-lg font-extrabold text-white shadow">B</span>
                        <span class="text-2xl font-bold tracking-tight text-gray-900">base<span class="text-indigo-600">NEXA</span></span>
                    </div>

                    <div class="rounded-2xl bg-white p-8 shadow-xl ring-1 ring-gray-100">
                        {{ $slot }}
                    </div>

                    <p class="mt-6 text-center text-xs text-gray-400 lg:hidden">
                        &copy; {{ date('Y') }} baseNEXA by Baseline
                    </p>
                </div>
            </div>

        </div>
    </body>
</html>