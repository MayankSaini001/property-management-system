@php
    $location = collect([$guest->city, $guest->state, $guest->countryName()])->filter()->implode(', ');
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Guest - {{ $guest->fullName() }}</h2>
    </x-slot>

    <div class="min-h-[75vh] bg-gradient-to-b from-indigo-50 via-slate-50 to-white py-10">
        <div class="w-full space-y-6 px-6 lg:px-8">

            <a href="{{ route('company.guests.index') }}"
                class="text-sm font-medium text-indigo-600 hover:text-indigo-800">&larr; Back to Guests</a>

            @if (session('success'))
                <div class="flex items-center gap-2 rounded-xl bg-emerald-50 px-4 py-3 text-sm text-emerald-800 ring-1 ring-emerald-100">
                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                    {{ session('success') }}
                </div>
            @endif

            {{-- Profile header --}}
            <div class="flex flex-wrap items-center justify-between gap-4 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
                <div class="flex items-center gap-4">
                    <span class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-100 to-violet-100 text-xl font-bold text-indigo-600">
                        {{ $guest->initials() }}
                    </span>
                    <div>
                        <h3 class="text-xl font-semibold text-slate-900">{{ $guest->fullName() }}</h3>
                        <p class="text-sm text-slate-500">Guest since {{ $guest->created_at->format('d M Y') }}</p>
                    </div>
                </div>

                <a href="{{ route('company.guests.edit', $guest) }}"
                    class="rounded-xl bg-indigo-50 px-4 py-2.5 text-sm font-semibold text-indigo-700 ring-1 ring-indigo-100 transition hover:bg-indigo-100">
                    Edit Guest
                </a>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

                {{-- Contact and address --}}
                <div class="space-y-6 lg:col-span-1">
                    <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
                        <h4 class="text-sm font-semibold text-indigo-700">Contact</h4>
                        <dl class="mt-4 space-y-3 text-sm">
                            <div>
                                <dt class="text-xs uppercase tracking-wide text-slate-400">Email</dt>
                                <dd class="text-slate-800">{{ $guest->email ?: '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs uppercase tracking-wide text-slate-400">Phone</dt>
                                <dd class="text-slate-800">{{ $guest->phone ?: '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs uppercase tracking-wide text-slate-400">Date of Birth</dt>
                                <dd class="text-slate-800">{{ $guest->date_of_birth ? $guest->date_of_birth->format('d M Y') : '-' }}</dd>
                            </div>
                        </dl>
                    </div>

                    <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
                        <h4 class="text-sm font-semibold text-indigo-700">Address</h4>
                        <dl class="mt-4 space-y-3 text-sm">
                            <div>
                                <dt class="text-xs uppercase tracking-wide text-slate-400">Street</dt>
                                <dd class="text-slate-800">{{ $guest->address ?: '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs uppercase tracking-wide text-slate-400">City / Country</dt>
                                <dd class="text-slate-800">{{ $location ?: '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs uppercase tracking-wide text-slate-400">Postal Code</dt>
                                <dd class="text-slate-800">{{ $guest->postal_code ?: '-' }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                {{-- Preferences, notes and reservation history --}}
                <div class="space-y-6 lg:col-span-2">
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div class="rounded-2xl bg-violet-50/60 p-6 ring-1 ring-violet-100">
                            <h4 class="text-sm font-semibold text-violet-700">Preferences</h4>
                            <p class="mt-3 whitespace-pre-line text-sm text-slate-700">{{ $guest->preferences ?: 'No preferences added.' }}</p>
                        </div>

                        <div class="rounded-2xl bg-amber-50/60 p-6 ring-1 ring-amber-100">
                            <h4 class="text-sm font-semibold text-amber-700">Internal Notes</h4>
                            <p class="mt-3 whitespace-pre-line text-sm text-slate-700">{{ $guest->notes ?: 'No notes added.' }}</p>
                        </div>
                    </div>

                    <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
                        <h4 class="text-sm font-semibold text-indigo-700">Reservation history</h4>
                        <div class="mt-6 rounded-xl border border-dashed border-slate-200 px-6 py-10 text-center">
                            <p class="text-sm font-medium text-slate-700">No reservations yet.</p>
                            <p class="mt-1 text-sm text-slate-500">This guest's bookings will appear here once reservations are added.</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>