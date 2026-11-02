@php
    $field = 'rounded-xl border-slate-200 bg-slate-50 text-sm text-slate-700 shadow-none focus:border-indigo-400 focus:bg-white focus:ring-2 focus:ring-indigo-100';
    $th = 'px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-indigo-900/60';
    $softBtn = 'rounded-lg px-3 py-1.5 text-sm font-medium transition';
    $filtersOn = request()->filled('q') || request()->filled('status') || request()->filled('property_id');

    $statusStyles = [
        'confirmed' => 'bg-sky-50 text-sky-700 ring-sky-200',
        'checked_in' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'checked_out' => 'bg-slate-100 text-slate-600 ring-slate-200',
        'cancelled' => 'bg-rose-50 text-rose-700 ring-rose-200',
        'no_show' => 'bg-amber-50 text-amber-700 ring-amber-200',
    ];
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Reservations</h2>
    </x-slot>

    <div class="min-h-[75vh] bg-gradient-to-b from-indigo-50 via-slate-50 to-white py-10">
        <div class="w-full space-y-6 px-6 lg:px-8">

            {{-- Page heading --}}
            <div class="flex flex-wrap items-center justify-between gap-4 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
                <div class="flex items-center gap-4">
                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </span>
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900">All Reservations</h3>
                        <p class="text-sm text-slate-500">Bookings of {{ auth()->user()->company->name }}</p>
                    </div>
                </div>

                <a href="{{ route('company.reservations.create') }}"
                    class="rounded-xl bg-gradient-to-r from-indigo-500 to-violet-500 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:from-indigo-600 hover:to-violet-600">
                    + New Reservation
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
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Total Reservations</p>
                    <p class="mt-2 text-3xl font-bold text-indigo-600">{{ $stats['total'] }}</p>
                </div>
                <div class="rounded-2xl bg-sky-50/70 p-5 ring-1 ring-sky-100">
                    <p class="text-xs font-medium uppercase tracking-wide text-sky-700/80">Upcoming</p>
                    <p class="mt-2 text-3xl font-bold text-sky-600">{{ $stats['upcoming'] }}</p>
                </div>
                <div class="rounded-2xl bg-emerald-50/70 p-5 ring-1 ring-emerald-100">
                    <p class="text-xs font-medium uppercase tracking-wide text-emerald-700/80">In House</p>
                    <p class="mt-2 text-3xl font-bold text-emerald-600">{{ $stats['in_house'] }}</p>
                </div>
                <div class="rounded-2xl bg-rose-50/70 p-5 ring-1 ring-rose-100">
                    <p class="text-xs font-medium uppercase tracking-wide text-rose-700/80">Cancelled</p>
                    <p class="mt-2 text-3xl font-bold text-rose-600">{{ $stats['cancelled'] }}</p>
                </div>
            </div>

            {{-- Live search and filters --}}
            <form id="reservation-filters" method="GET" action="{{ route('company.reservations.index') }}"
                class="flex flex-wrap items-end gap-4 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-100">

                <div class="min-w-[220px] flex-1">
                    <label for="q" class="mb-1.5 block text-xs font-medium text-slate-500">Search</label>
                    <div class="relative">
                        <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
                            fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10.5a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z" />
                        </svg>
                        <input id="q" name="q" type="search" value="{{ request('q') }}" autocomplete="off"
                            placeholder="Booking reference, guest name, email or phone" class="{{ $field }} w-full pl-9">
                    </div>
                </div>

                <div class="w-full sm:w-52">
                    <label for="property_id" class="mb-1.5 block text-xs font-medium text-slate-500">Property</label>
                    <select id="property_id" name="property_id" class="{{ $field }} w-full">
                        <option value="">All properties</option>
                        @foreach ($properties as $property)
                            <option value="{{ $property->id }}" @selected((int) request('property_id') === $property->id)>{{ $property->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="w-full sm:w-44">
                    <label for="status" class="mb-1.5 block text-xs font-medium text-slate-500">Status</label>
                    <select id="status" name="status" class="{{ $field }} w-full">
                        <option value="">All statuses</option>
                        @foreach (\App\Models\Reservation::statuses() as $value => $text)
                            <option value="{{ $value }}" @selected(request('status') === $value)>{{ $text }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="button" id="clear-reservation-filters"
                    class="rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-200">
                    Clear
                </button>

                <span id="reservation-loading" class="hidden pb-2.5 text-xs font-medium text-indigo-500">Updating...</span>
            </form>

            {{-- Results (replaced live when filters change) --}}
            <div id="reservation-results" class="transition-opacity duration-150">
                <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-100">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-100">
                            <thead class="bg-indigo-50/60">
                                <tr>
                                    <th class="{{ $th }}">Reference</th>
                                    <th class="{{ $th }}">Guest</th>
                                    <th class="{{ $th }}">Property</th>
                                    <th class="{{ $th }}">Rooms</th>
                                    <th class="{{ $th }}">Stay</th>
                                    <th class="{{ $th }}">Status</th>
                                    <th class="{{ $th }}">Actions</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-slate-100">
                                @forelse ($reservations as $reservation)
                                    <tr class="transition hover:bg-indigo-50/40">
                                        <td class="px-5 py-4">
                                            <a href="{{ route('company.reservations.show', $reservation) }}"
                                                class="font-semibold text-indigo-600 hover:text-indigo-800">
                                                {{ $reservation->booking_reference }}
                                            </a>
                                            <div class="text-xs text-slate-400">{{ \App\Models\Reservation::sources()[$reservation->source] ?? ucfirst($reservation->source) }}</div>
                                        </td>

                                        <td class="px-5 py-4 text-sm font-medium text-slate-800">
                                            {{ $reservation->guest?->fullName() ?? 'Deleted guest' }}
                                        </td>

                                        <td class="px-5 py-4 text-sm text-slate-600">{{ $reservation->property?->name }}</td>

                                        <td class="px-5 py-4">
                                            <div class="flex flex-wrap gap-1">
                                                @foreach ($reservation->items as $item)
                                                    <span class="rounded-md bg-indigo-50 px-2 py-0.5 text-xs font-semibold text-indigo-700">
                                                        {{ $item->unit?->name }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        </td>

                                        <td class="px-5 py-4 text-sm text-slate-700">
                                            {{ $reservation->check_in->format('d M') }} - {{ $reservation->check_out->format('d M Y') }}
                                            <div class="text-xs text-slate-400">{{ $reservation->nights() }} night{{ $reservation->nights() > 1 ? 's' : '' }}</div>
                                        </td>

                                        <td class="px-5 py-4">
                                            <span class="rounded-full px-2.5 py-1 text-xs font-medium ring-1 {{ $statusStyles[$reservation->status] ?? 'bg-slate-100 text-slate-600 ring-slate-200' }}">
                                                {{ \App\Models\Reservation::statuses()[$reservation->status] ?? ucfirst($reservation->status) }}
                                            </span>
                                        </td>

                                        <td class="px-5 py-4">
                                            <a href="{{ route('company.reservations.show', $reservation) }}"
                                                class="{{ $softBtn }} bg-sky-50 text-sky-700 hover:bg-sky-100">View</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-5 py-16 text-center">
                                            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-400">
                                                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10.5a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z" />
                                                </svg>
                                            </div>

                                            @if ($filtersOn)
                                                <p class="mt-3 text-sm font-medium text-slate-700">No reservations match your search.</p>
                                                <p class="text-sm text-slate-500">Try a different reference or name, or clear the filters.</p>
                                            @else
                                                <p class="mt-3 text-sm font-medium text-slate-700">No reservations yet.</p>
                                                <a href="{{ route('company.reservations.create') }}"
                                                    class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">+ Create your first reservation</a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-6">
                    {{ $reservations->links() }}
                </div>
            </div>

        </div>
    </div>

    <script>
        (function () {
            const form = document.getElementById('reservation-filters');
            const results = document.getElementById('reservation-results');
            const loading = document.getElementById('reservation-loading');
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
                        const fresh = doc.getElementById('reservation-results');

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

            form.querySelectorAll('select').forEach(function (select) {
                select.addEventListener('change', load);
            });

            search.addEventListener('input', function () {
                clearTimeout(timer);
                timer = setTimeout(load, 300);
            });

            document.getElementById('clear-reservation-filters').addEventListener('click', function () {
                search.value = '';
                form.querySelectorAll('select').forEach(function (select) {
                    select.value = '';
                });
                load();
            });
        })();
    </script>
</x-app-layout>