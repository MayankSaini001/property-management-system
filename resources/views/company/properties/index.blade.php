@php
    $field = 'rounded-xl border-slate-200 bg-slate-50 text-sm text-slate-700 shadow-none focus:border-indigo-400 focus:bg-white focus:ring-2 focus:ring-indigo-100';
    $softBtn = 'rounded-lg px-3 py-1.5 text-sm font-medium transition';
    $filtersOn = request()->filled('q') || request()->filled('type') || request()->filled('status');
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Properties</h2>
    </x-slot>

    <div class="min-h-[75vh] bg-gradient-to-b from-indigo-50 via-slate-50 to-white py-10">
        <div class="w-full space-y-6 px-6 lg:px-8">

            {{-- Page heading --}}
            <div class="flex flex-wrap items-center justify-between gap-4 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
                <div class="flex items-center gap-4">
                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 21V5a2 2 0 012-2h8a2 2 0 012 2v16M16 9h2a2 2 0 012 2v10M3 21h18M8 7h4M8 11h4M8 15h4" />
                        </svg>
                    </span>

                    <div>
                        <h3 class="text-lg font-semibold text-slate-900">All Properties</h3>
                        <p class="text-sm text-slate-500">
                            Hotels and villas of {{ auth()->user()->company->name }}
                        </p>
                    </div>
                </div>

                <a href="{{ route('company.properties.create') }}"
                    class="rounded-xl bg-gradient-to-r from-indigo-500 to-violet-500 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:from-indigo-600 hover:to-violet-600">
                    + Add Property
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
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Total Properties</p>
                    <p class="mt-2 text-3xl font-bold text-indigo-600">{{ $stats['total'] }}</p>
                </div>
                <div class="rounded-2xl bg-sky-50/70 p-5 ring-1 ring-sky-100">
                    <p class="text-xs font-medium uppercase tracking-wide text-sky-700/80">Hotels</p>
                    <p class="mt-2 text-3xl font-bold text-sky-600">{{ $stats['hotels'] }}</p>
                </div>
                <div class="rounded-2xl bg-amber-50/70 p-5 ring-1 ring-amber-100">
                    <p class="text-xs font-medium uppercase tracking-wide text-amber-700/80">Villas</p>
                    <p class="mt-2 text-3xl font-bold text-amber-600">{{ $stats['villas'] }}</p>
                </div>
                <div class="rounded-2xl bg-emerald-50/70 p-5 ring-1 ring-emerald-100">
                    <p class="text-xs font-medium uppercase tracking-wide text-emerald-700/80">Active</p>
                    <p class="mt-2 text-3xl font-bold text-emerald-600">{{ $stats['active'] }}</p>
                </div>
            </div>

            {{-- Live search and filters --}}
            <form id="property-filters" method="GET" action="{{ route('company.properties.index') }}"
                class="flex flex-wrap items-end gap-4 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-100">

                <div class="min-w-[220px] flex-1">
                    <label for="q" class="mb-1.5 block text-xs font-medium text-slate-500">Search</label>
                    <div class="relative">
                        <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
                            fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10.5a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z" />
                        </svg>
                        <input id="q" name="q" type="search" value="{{ request('q') }}" autocomplete="off"
                            placeholder="Search by name, city or email" class="{{ $field }} w-full pl-9">
                    </div>
                </div>

                <div class="w-full sm:w-44">
                    <label for="type" class="mb-1.5 block text-xs font-medium text-slate-500">Type</label>
                    <select id="type" name="type" class="{{ $field }} w-full">
                        <option value="">All types</option>
                        @foreach (\App\Models\Property::types() as $value => $text)
                            <option value="{{ $value }}" @selected(request('type') === $value)>{{ $text }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="w-full sm:w-44">
                    <label for="status" class="mb-1.5 block text-xs font-medium text-slate-500">Status</label>
                    <select id="status" name="status" class="{{ $field }} w-full">
                        <option value="">All statuses</option>
                        <option value="active" @selected(request('status') === 'active')>Active</option>
                        <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                    </select>
                </div>

                <button type="button" id="clear-property-filters"
                    class="rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-200">
                    Clear
                </button>

                <span id="property-loading" class="hidden pb-2.5 text-xs font-medium text-indigo-500">Updating...</span>
            </form>

            {{-- Results (replaced live when filters change) --}}
            <div id="property-results" class="transition-opacity duration-150">

                @if ($properties->isEmpty())
                    <div class="rounded-2xl bg-white px-6 py-16 text-center shadow-sm ring-1 ring-slate-100">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-400">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 21V5a2 2 0 012-2h8a2 2 0 012 2v16M16 9h2a2 2 0 012 2v10M3 21h18M8 7h4M8 11h4M8 15h4" />
                            </svg>
                        </div>

                        @if ($filtersOn)
                            <p class="mt-3 text-sm font-medium text-slate-700">No properties match your search.</p>
                            <p class="text-sm text-slate-500">Try a different name or clear the filters.</p>
                        @else
                            <p class="mt-3 text-sm font-medium text-slate-700">No properties yet.</p>
                            <a href="{{ route('company.properties.create') }}"
                                class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">+ Add your first property</a>
                        @endif
                    </div>
                @else
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-[repeat(auto-fit,minmax(380px,1fr))]">
                        @foreach ($properties as $property)
                            @php
                                $isHotel = $property->type === 'hotel';
                                $location = collect([$property->city, $property->countryName()])->filter()->implode(', ');
                            @endphp

                            <div class="flex flex-col rounded-2xl border-t-4 bg-white p-6 shadow-sm ring-1 ring-slate-100 transition hover:-translate-y-0.5 hover:shadow-md
                                {{ $isHotel ? 'border-sky-300' : 'border-amber-300' }}
                                {{ $property->isActive() ? '' : 'opacity-75' }}">

                                {{-- Top: icon, name, status --}}
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex items-center gap-3">
                                        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl
                                            {{ $isHotel ? 'bg-sky-50 text-sky-600' : 'bg-amber-50 text-amber-600' }}">
                                            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                                @if ($isHotel)
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 21V5a2 2 0 012-2h8a2 2 0 012 2v16M16 9h2a2 2 0 012 2v10M3 21h18M8 7h4M8 11h4M8 15h4" />
                                                @else
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l9-9 9 9M5 10v10a1 1 0 001 1h3v-6h6v6h3a1 1 0 001-1V10" />
                                                @endif
                                            </svg>
                                        </span>

                                        <div class="min-w-0">
                                            <h4 class="truncate text-base font-semibold text-slate-900">{{ $property->name }}</h4>
                                            <span class="mt-1 inline-block rounded-full px-2.5 py-0.5 text-xs font-medium ring-1
                                                {{ $isHotel ? 'bg-sky-50 text-sky-700 ring-sky-200' : 'bg-amber-50 text-amber-700 ring-amber-200' }}">
                                                {{ \App\Models\Property::types()[$property->type] ?? ucfirst($property->type) }}
                                            </span>
                                        </div>
                                    </div>

                                    <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium ring-1
                                        {{ $property->isActive() ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : 'bg-slate-100 text-slate-600 ring-slate-200' }}">
                                        <span class="h-1.5 w-1.5 rounded-full {{ $property->isActive() ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                        {{ ucfirst($property->status) }}
                                    </span>
                                </div>

                                {{-- Details --}}
                                <div class="mt-5 space-y-2 text-sm text-slate-600">
                                    <div class="flex items-center gap-2">
                                        <svg class="h-4 w-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        <span class="truncate">{{ $location ?: 'Location not added' }}</span>
                                    </div>

                                    @if ($property->email)
                                        <div class="flex items-center gap-2">
                                            <svg class="h-4 w-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                            </svg>
                                            <span class="truncate">{{ $property->email }}</span>
                                        </div>
                                    @endif

                                    @if ($property->phone)
                                        <div class="flex items-center gap-2">
                                            <svg class="h-4 w-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                            </svg>
                                            <span class="truncate">{{ $property->phone }}</span>
                                        </div>
                                    @endif
                                </div>

                                {{-- Mini stats --}}
                                <div class="mt-5 grid grid-cols-3 gap-2">
                                    <div class="rounded-xl bg-indigo-50/60 px-3 py-2 text-center">
                                        <p class="text-lg font-bold text-indigo-600">{{ $property->units_count }}</p>
                                        <p class="text-xs text-slate-500">{{ $isHotel ? 'Rooms' : 'Units' }}</p>
                                    </div>
                                    <div class="rounded-xl bg-violet-50/60 px-3 py-2 text-center">
                                        <p class="text-lg font-bold text-violet-600">{{ $isHotel ? $property->room_types_count : '-' }}</p>
                                        <p class="text-xs text-slate-500">Room Types</p>
                                    </div>
                                    <div class="rounded-xl bg-slate-50 px-3 py-2 text-center">
                                        <p class="text-lg font-bold text-slate-700">{{ $property->currency }}</p>
                                        <p class="text-xs text-slate-500">Currency</p>
                                    </div>
                                </div>

                                {{-- Actions --}}
                                <div class="mt-5 flex flex-wrap items-center gap-2 border-t border-slate-100 pt-4">
                                    @if ($isHotel)
                                        <a href="{{ route('company.room-types.index', $property) }}"
                                            class="{{ $softBtn }} bg-violet-50 text-violet-700 hover:bg-violet-100">Room Types</a>
                                    @endif

                                    <a href="{{ route('company.units.index', $property) }}"
                                        class="{{ $softBtn }} bg-sky-50 text-sky-700 hover:bg-sky-100">
                                        {{ $isHotel ? 'Rooms' : 'Units' }}
                                    </a>

                                    <a href="{{ route('company.properties.edit', $property) }}"
                                        class="{{ $softBtn }} bg-indigo-50 text-indigo-700 hover:bg-indigo-100">Edit</a>

                                    <form method="POST" action="{{ route('company.properties.status', $property) }}"
                                        onsubmit="return confirm('{{ $property->isActive() ? 'Deactivate' : 'Activate' }} this property?')">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                            class="{{ $softBtn }} {{ $property->isActive() ? 'bg-rose-50 text-rose-600 hover:bg-rose-100' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }}">
                                            {{ $property->isActive() ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-6">
                        {{ $properties->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>

    {{-- Live filtering: the list updates as soon as a filter changes or the admin types --}}
    <script>
        (function () {
            const form = document.getElementById('property-filters');
            const results = document.getElementById('property-results');
            const loading = document.getElementById('property-loading');
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
                        const fresh = doc.getElementById('property-results');

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

            document.getElementById('clear-property-filters').addEventListener('click', function () {
                search.value = '';
                form.querySelectorAll('select').forEach(function (select) {
                    select.value = '';
                });
                load();
            });
        })();
    </script>
</x-app-layout>