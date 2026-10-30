@php
    $field = 'rounded-xl border-slate-200 bg-slate-50 text-sm text-slate-700 shadow-none focus:border-indigo-400 focus:bg-white focus:ring-2 focus:ring-indigo-100';
    $th = 'px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-indigo-900/60';
    $softBtn = 'rounded-lg px-3 py-1.5 text-sm font-medium transition';
    $searching = request()->filled('q');
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Guests</h2>
    </x-slot>

    <div class="min-h-[75vh] bg-gradient-to-b from-indigo-50 via-slate-50 to-white py-10">
        <div class="w-full space-y-6 px-6 lg:px-8">

            {{-- Page heading --}}
            <div class="flex flex-wrap items-center justify-between gap-4 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
                <div class="flex items-center gap-4">
                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-4.13a4 4 0 100-8 4 4 0 000 8zm6 1a3 3 0 100-6M3 11a3 3 0 106 0" />
                        </svg>
                    </span>

                    <div>
                        <h3 class="text-lg font-semibold text-slate-900">All Guests</h3>
                        <p class="text-sm text-slate-500">Guest profiles of {{ auth()->user()->company->name }}</p>
                    </div>
                </div>

                <a href="{{ route('company.guests.create') }}"
                    class="rounded-xl bg-gradient-to-r from-indigo-500 to-violet-500 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:from-indigo-600 hover:to-violet-600">
                    + Add Guest
                </a>
            </div>

            @if (session('success'))
                <div class="flex items-center gap-2 rounded-xl bg-emerald-50 px-4 py-3 text-sm text-emerald-800 ring-1 ring-emerald-100">
                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                    {{ session('success') }}
                </div>
            @endif

            {{-- Summary cards --}}
            <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-100">
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Total Guests</p>
                    <p class="mt-2 text-3xl font-bold text-indigo-600">{{ $stats['total'] }}</p>
                </div>
                <div class="rounded-2xl bg-emerald-50/70 p-5 ring-1 ring-emerald-100">
                    <p class="text-xs font-medium uppercase tracking-wide text-emerald-700/80">New This Month</p>
                    <p class="mt-2 text-3xl font-bold text-emerald-600">{{ $stats['new_this_month'] }}</p>
                </div>
                <div class="rounded-2xl bg-sky-50/70 p-5 ring-1 ring-sky-100">
                    <p class="text-xs font-medium uppercase tracking-wide text-sky-700/80">With Email</p>
                    <p class="mt-2 text-3xl font-bold text-sky-600">{{ $stats['with_email'] }}</p>
                </div>
                <div class="rounded-2xl bg-violet-50/70 p-5 ring-1 ring-violet-100">
                    <p class="text-xs font-medium uppercase tracking-wide text-violet-700/80">With Phone</p>
                    <p class="mt-2 text-3xl font-bold text-violet-600">{{ $stats['with_phone'] }}</p>
                </div>
            </div>

            {{-- Live search --}}
            <form id="guest-filters" method="GET" action="{{ route('company.guests.index') }}"
                class="flex flex-wrap items-end gap-4 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-100">

                <div class="min-w-[220px] flex-1">
                    <label for="q" class="mb-1.5 block text-xs font-medium text-slate-500">Search guests</label>
                    <div class="relative">
                        <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
                            fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10.5a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z" />
                        </svg>
                        <input id="q" name="q" type="search" value="{{ request('q') }}" autocomplete="off"
                            placeholder="Search by name, email or phone" class="{{ $field }} w-full pl-9">
                    </div>
                </div>

                <button type="button" id="clear-guest-filters"
                    class="rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-200">
                    Clear
                </button>

                <span id="guest-loading" class="hidden pb-2.5 text-xs font-medium text-indigo-500">Updating...</span>
            </form>

            {{-- Results (replaced live while typing) --}}
            <div id="guest-results" class="transition-opacity duration-150">
                <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-100">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-100">
                            <thead class="bg-indigo-50/60">
                                <tr>
                                    <th class="{{ $th }}">Guest</th>
                                    <th class="{{ $th }}">Contact</th>
                                    <th class="{{ $th }}">Location</th>
                                    <th class="{{ $th }}">Added</th>
                                    <th class="{{ $th }}">Actions</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-slate-100">
                                @forelse ($guests as $guest)
                                    <tr class="transition hover:bg-indigo-50/40">
                                        <td class="px-5 py-4">
                                            <a href="{{ route('company.guests.show', $guest) }}" class="flex items-center gap-3">
                                                <span class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-indigo-100 to-violet-100 text-sm font-bold text-indigo-600">
                                                    {{ $guest->initials() }}
                                                </span>
                                                <span class="font-semibold text-slate-900 hover:text-indigo-700">{{ $guest->fullName() }}</span>
                                            </a>
                                        </td>

                                        <td class="px-5 py-4 text-sm">
                                            <div class="text-slate-700">{{ $guest->email ?: '-' }}</div>
                                            <div class="text-xs text-slate-500">{{ $guest->phone ?: '-' }}</div>
                                        </td>

                                        <td class="px-5 py-4 text-sm text-slate-600">
                                            {{ collect([$guest->city, $guest->countryName()])->filter()->implode(', ') ?: '-' }}
                                        </td>

                                        <td class="px-5 py-4 text-sm text-slate-600">{{ $guest->created_at->format('d M Y') }}</td>

                                        <td class="px-5 py-4">
                                            <div class="flex items-center gap-2">
                                                <a href="{{ route('company.guests.show', $guest) }}"
                                                    class="{{ $softBtn }} bg-sky-50 text-sky-700 hover:bg-sky-100">View</a>
                                                <a href="{{ route('company.guests.edit', $guest) }}"
                                                    class="{{ $softBtn }} bg-indigo-50 text-indigo-700 hover:bg-indigo-100">Edit</a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-5 py-16 text-center">
                                            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-400">
                                                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10.5a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z" />
                                                </svg>
                                            </div>

                                            @if ($searching)
                                                <p class="mt-3 text-sm font-medium text-slate-700">No guests match your search.</p>
                                                <p class="text-sm text-slate-500">Try a different name, email or phone number.</p>
                                            @else
                                                <p class="mt-3 text-sm font-medium text-slate-700">No guests yet.</p>
                                                <a href="{{ route('company.guests.create') }}"
                                                    class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">+ Add your first guest</a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-6">
                    {{ $guests->links() }}
                </div>
            </div>

        </div>
    </div>

    {{-- Live search: the list updates while the admin types --}}
    <script>
        (function () {
            const form = document.getElementById('guest-filters');
            const results = document.getElementById('guest-results');
            const loading = document.getElementById('guest-loading');
            const search = document.getElementById('q');
            let timer = null;
            let controller = null;

            function buildUrl() {
                const params = new URLSearchParams(new FormData(form));

                Array.from(params.keys()).forEach(function (key) {
                    if (!params.get(key)) {
                        params.delete(key);
                    }
                });

                const query = params.toString();

                return form.action + (query ? '?' + query : '');
            }

            function load() {
                const url = buildUrl();

                if (controller) {
                    controller.abort();
                }
                controller = new AbortController();

                results.classList.add('opacity-50');
                loading.classList.remove('hidden');

                fetch(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    signal: controller.signal,
                })
                    .then(function (response) { return response.text(); })
                    .then(function (html) {
                        const doc = new DOMParser().parseFromString(html, 'text/html');
                        const fresh = doc.getElementById('guest-results');

                        if (fresh) {
                            results.innerHTML = fresh.innerHTML;
                        }

                        history.replaceState(null, '', url);
                        results.classList.remove('opacity-50');
                        loading.classList.add('hidden');
                    })
                    .catch(function (error) {
                        if (error.name !== 'AbortError') {
                            window.location = url;
                        }
                    });
            }

            form.addEventListener('submit', function (event) {
                event.preventDefault();
                load();
            });

            search.addEventListener('input', function () {
                clearTimeout(timer);
                timer = setTimeout(load, 300);
            });

            document.getElementById('clear-guest-filters').addEventListener('click', function () {
                search.value = '';
                load();
            });
        })();
    </script>
</x-app-layout>