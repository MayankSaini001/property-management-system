@php
    $isHotel = $property->type === 'hotel';
    $noun = $isHotel ? 'Room' : 'Unit';

    $statusStyles = [
        'available' => ['dot' => 'bg-emerald-500', 'select' => 'bg-emerald-50 text-emerald-700 ring-emerald-200'],
        'out_of_service' => ['dot' => 'bg-slate-400', 'select' => 'bg-slate-100 text-slate-600 ring-slate-200'],
        'maintenance' => ['dot' => 'bg-amber-500', 'select' => 'bg-amber-50 text-amber-700 ring-amber-200'],
    ];

    $field = 'rounded-xl border-slate-200 bg-slate-50 text-sm text-slate-700 shadow-none focus:border-indigo-400 focus:bg-white focus:ring-2 focus:ring-indigo-100';
    $th = 'px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-indigo-900/60';
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">
            {{ $isHotel ? 'Rooms' : 'Villa Units' }} - {{ $property->name }}
        </h2>
    </x-slot>

    <div class="min-h-[75vh] bg-gradient-to-b from-indigo-50 via-slate-50 to-white py-10">
        <div class="mx-auto max-w-none space-y-6 px-6 lg:px-8">

            {{-- Page heading --}}
            <div class="flex flex-wrap items-center justify-between gap-4 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
                <div class="flex items-center gap-4">
                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            @if ($isHotel)
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 21V5a2 2 0 012-2h8a2 2 0 012 2v16M16 9h2a2 2 0 012 2v10M3 21h18M8 7h4M8 11h4M8 15h4" />
                            @else
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l9-9 9 9M5 10v10a1 1 0 001 1h3v-6h6v6h3a1 1 0 001-1V10" />
                            @endif
                        </svg>
                    </span>

                    <div>
                        <a href="{{ route('company.properties.index') }}"
                            class="text-xs font-medium text-indigo-600 hover:text-indigo-800">&larr; Back to Properties</a>
                        <h3 class="text-lg font-semibold text-slate-900">{{ $isHotel ? 'All Rooms' : 'All Villa Units' }}</h3>
                        <p class="text-sm text-slate-500">
                            {{ $isHotel ? 'Individual rooms of' : 'Bookable units of' }} {{ $property->name }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('company.blocks.index', $property) }}"
                        class="rounded-xl bg-violet-50 px-4 py-2.5 text-sm font-semibold text-violet-700 ring-1 ring-violet-100 transition hover:bg-violet-100">
                        Maintenance &amp; Blocks
                    </a>

                    <a href="{{ route('company.units.create', $property) }}"
                        class="rounded-xl bg-gradient-to-r from-indigo-500 to-violet-500 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:from-indigo-600 hover:to-violet-600">
                        {{ $isHotel ? '+ Add Room' : '+ Add Villa Unit' }}
                    </a>
                </div>
            </div>

            @if (session('success'))
                <div class="flex items-center gap-2 rounded-xl bg-emerald-50 px-4 py-3 text-sm text-emerald-800 ring-1 ring-emerald-100">
                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="rounded-xl bg-rose-50 px-4 py-3 text-sm text-rose-800 ring-1 ring-rose-100">
                    {{ $errors->first() }}
                </div>
            @endif

            {{-- Summary cards --}}
            <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-100">
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Total {{ $isHotel ? 'Rooms' : 'Units' }}</p>
                    <p class="mt-2 text-3xl font-bold text-indigo-600">{{ $stats['total'] }}</p>
                </div>
                <div class="rounded-2xl bg-emerald-50/70 p-5 ring-1 ring-emerald-100">
                    <p class="text-xs font-medium uppercase tracking-wide text-emerald-700/80">Available</p>
                    <p class="mt-2 text-3xl font-bold text-emerald-600">{{ $stats['available'] }}</p>
                </div>
                <div class="rounded-2xl bg-amber-50/70 p-5 ring-1 ring-amber-100">
                    <p class="text-xs font-medium uppercase tracking-wide text-amber-700/80">Maintenance / Out of Service</p>
                    <p class="mt-2 text-3xl font-bold text-amber-600">{{ $stats['maintenance'] + $stats['out_of_service'] }}</p>
                </div>
                <div class="rounded-2xl bg-violet-50/70 p-5 ring-1 ring-violet-100">
                    <p class="text-xs font-medium uppercase tracking-wide text-violet-700/80">Blocked Today</p>
                    <p class="mt-2 text-3xl font-bold text-violet-600">{{ $stats['blocked_now'] }}</p>
                </div>
            </div>

            {{-- Live search and filters --}}
            <form id="unit-filters" method="GET" action="{{ route('company.units.index', $property) }}"
                class="flex flex-wrap items-end gap-4 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-100">

                <div class="min-w-[220px] flex-1">
                    <label for="q" class="mb-1.5 block text-xs font-medium text-slate-500">Search</label>
                    <div class="relative">
                        <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
                            fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10.5a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z" />
                        </svg>
                        <input id="q" name="q" type="search" value="{{ request('q') }}" autocomplete="off"
                            placeholder="{{ $isHotel ? 'Search room number or name' : 'Search unit name' }}"
                            class="{{ $field }} w-full pl-9">
                    </div>
                </div>

                @if ($isHotel)
                    <div class="w-full sm:w-52">
                        <label for="room_type_id" class="mb-1.5 block text-xs font-medium text-slate-500">Room Type</label>
                        <select id="room_type_id" name="room_type_id" class="{{ $field }} w-full">
                            <option value="">All room types</option>
                            @foreach ($roomTypes as $roomType)
                                <option value="{{ $roomType->id }}" @selected((int) request('room_type_id') === $roomType->id)>
                                    {{ $roomType->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="w-full sm:w-48">
                    <label for="status" class="mb-1.5 block text-xs font-medium text-slate-500">Status</label>
                    <select id="status" name="status" class="{{ $field }} w-full">
                        <option value="">All statuses</option>
                        @foreach (\App\Models\Unit::statuses() as $value => $text)
                            <option value="{{ $value }}" @selected(request('status') === $value)>{{ $text }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="button" id="clear-filters"
                    class="rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-200">
                    Clear
                </button>

                <span id="filter-loading" class="hidden pb-2.5 text-xs font-medium text-indigo-500">Updating...</span>
            </form>

            {{-- Results (replaced live when filters change) --}}
            <div id="unit-results" class="transition-opacity duration-150">
                <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-100">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-100">
                            <thead class="bg-indigo-50/60">
                                <tr>
                                    <th class="{{ $th }}">{{ $noun }}</th>
                                    <th class="{{ $th }}">Type</th>
                                    <th class="{{ $th }}">Room Type</th>
                                    <th class="{{ $th }}">Capacity</th>
                                    <th class="{{ $th }}">Status</th>
                                    <th class="{{ $th }}">Actions</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-slate-100">
                                @forelse ($units as $unit)
                                    @php $style = $statusStyles[$unit->status] ?? $statusStyles['available']; @endphp
                                    <tr class="transition hover:bg-indigo-50/40">
                                        <td class="px-5 py-4">
                                            <div class="flex items-center gap-3">
                                                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-50 text-sm font-bold text-indigo-600">
                                                    {{ strtoupper(mb_substr($unit->name, 0, 1)) }}
                                                </span>
                                                <div>
                                                    <div class="flex items-center gap-2">
                                                        <span class="font-semibold text-slate-900">{{ $unit->name }}</span>
                                                        @if ($unit->blocked_now > 0)
                                                            <span class="inline-flex items-center gap-1 rounded-full bg-violet-50 px-2 py-0.5 text-xs font-medium text-violet-700 ring-1 ring-violet-200">
                                                                <span class="h-1.5 w-1.5 rounded-full bg-violet-500"></span> Blocked today
                                                            </span>
                                                        @endif
                                                    </div>
                                                    @if ($unit->notes)
                                                        <div class="max-w-xs truncate text-xs text-slate-500">{{ $unit->notes }}</div>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>

                                        <td class="px-5 py-4">
                                            <span class="rounded-full px-2.5 py-1 text-xs font-medium ring-1
                                                {{ $unit->unit_type === 'villa' ? 'bg-amber-50 text-amber-700 ring-amber-200' : 'bg-sky-50 text-sky-700 ring-sky-200' }}">
                                                {{ \App\Models\Unit::unitTypes()[$unit->unit_type] ?? ucfirst($unit->unit_type) }}
                                            </span>
                                        </td>

                                        <td class="px-5 py-4 text-sm text-slate-600">{{ $unit->roomType?->name ?? '-' }}</td>

                                        <td class="px-5 py-4 text-sm text-slate-700">
                                            <span class="font-semibold">{{ $unit->capacity }}</span>
                                            <span class="text-xs text-slate-400">({{ $unit->max_adults }}A + {{ $unit->max_children }}C)</span>
                                        </td>

                                        <td class="px-5 py-4">
                                            <form method="POST" action="{{ route('company.units.status', $unit) }}"
                                                class="flex items-center gap-2">
                                                @csrf
                                                @method('PATCH')
                                                <span class="h-2 w-2 rounded-full {{ $style['dot'] }}"></span>
                                                <select name="status" onchange="this.form.submit()"
                                                    class="cursor-pointer rounded-full border-0 py-1 pl-3 pr-8 text-xs font-semibold ring-1 focus:ring-2 {{ $style['select'] }}">
                                                    @foreach (\App\Models\Unit::statuses() as $value => $text)
                                                        <option value="{{ $value }}" @selected($unit->status === $value)>{{ $text }}</option>
                                                    @endforeach
                                                </select>
                                            </form>
                                        </td>

                                        <td class="px-5 py-4">
                                            <div class="flex items-center gap-2 text-sm font-medium">
                                                <a href="{{ route('company.units.edit', $unit) }}"
                                                    class="rounded-lg bg-indigo-50 px-3 py-1.5 text-indigo-700 transition hover:bg-indigo-100">Edit</a>
                                                <a href="{{ route('company.blocks.create', [$property, 'unit' => $unit->id]) }}"
                                                    class="rounded-lg bg-violet-50 px-3 py-1.5 text-violet-700 transition hover:bg-violet-100">Block dates</a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-5 py-16 text-center">
                                            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-400">
                                                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10.5a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z" />
                                                </svg>
                                            </div>

                                            @if (request()->filled('q') || request()->filled('status') || request()->filled('room_type_id'))
                                                <p class="mt-3 text-sm font-medium text-slate-700">
                                                    {{ $isHotel ? 'No rooms match your search.' : 'No villa units match your search.' }}
                                                </p>
                                                <p class="text-sm text-slate-500">Try a different name or clear the filters.</p>
                                            @else
                                                <p class="mt-3 text-sm font-medium text-slate-700">
                                                    {{ $isHotel ? 'No rooms yet.' : 'No villa units yet.' }}
                                                </p>
                                                <a href="{{ route('company.units.create', $property) }}"
                                                    class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">
                                                    {{ $isHotel ? '+ Add your first room' : '+ Add your first villa unit' }}
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-6">
                    {{ $units->links() }}
                </div>
            </div>

        </div>
    </div>

    {{-- Live filtering: the list updates as soon as a filter changes or the admin types --}}
    <script>
        (function () {
            const form = document.getElementById('unit-filters');
            const results = document.getElementById('unit-results');
            const loading = document.getElementById('filter-loading');
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
                        const fresh = doc.getElementById('unit-results');

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

            document.getElementById('clear-filters').addEventListener('click', function () {
                search.value = '';
                form.querySelectorAll('select').forEach(function (select) {
                    select.value = '';
                });
                load();
            });
        })();
    </script>
</x-app-layout>