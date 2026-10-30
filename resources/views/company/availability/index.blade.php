@php
    $field = 'rounded-xl border-slate-200 bg-slate-50 text-sm text-slate-700 shadow-none focus:border-indigo-400 focus:bg-white focus:ring-2 focus:ring-indigo-100';
    $label = 'mb-1.5 block text-xs font-medium text-slate-500';

    $defaultProperty = old('property_id', request('property_id', $properties->count() === 1 ? $properties->first()->id : ''));
    $today = now()->toDateString();
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Availability Search</h2>
    </x-slot>

    <div class="min-h-[75vh] bg-gradient-to-b from-indigo-50 via-slate-50 to-white py-10">
        <div class="w-full space-y-6 px-6 lg:px-8">

            {{-- Page heading --}}
            <div class="flex items-center gap-4 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
                <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3M4 11h16M5 5h14a1 1 0 011 1v14a1 1 0 01-1 1H5a1 1 0 01-1-1V6a1 1 0 011-1z" />
                    </svg>
                </span>
                <div>
                    <h3 class="text-lg font-semibold text-slate-900">Check Availability</h3>
                    <p class="text-sm text-slate-500">Find rooms and villas that are free for the stay dates and number of guests.</p>
                </div>
            </div>

            @if ($properties->isEmpty())
                <div class="rounded-2xl bg-white px-6 py-16 text-center shadow-sm ring-1 ring-slate-100">
                    <p class="text-sm font-medium text-slate-700">You need an active property to search availability.</p>
                    <a href="{{ route('company.properties.create') }}"
                        class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">+ Add a property</a>
                </div>
            @else

                {{-- Search form (results update automatically when all fields are filled) --}}
                <form id="availability-form" method="GET" action="{{ route('company.availability.index') }}"
                    class="grid grid-cols-1 items-end gap-4 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-100 md:grid-cols-2 xl:grid-cols-[2fr_1fr_1fr_0.7fr_0.7fr_auto]">

                    <div>
                        <label for="property_id" class="{{ $label }}">Property</label>
                        <select id="property_id" name="property_id" class="{{ $field }} w-full">
                            <option value="">Select property</option>
                            @foreach ($properties as $property)
                                <option value="{{ $property->id }}" @selected((string) $defaultProperty === (string) $property->id)>
                                    {{ $property->name }} ({{ \App\Models\Property::types()[$property->type] ?? $property->type }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="check_in" class="{{ $label }}">Check-in</label>
                        <input id="check_in" name="check_in" type="date" min="{{ $today }}"
                            value="{{ request('check_in') }}" class="{{ $field }} w-full">
                    </div>

                    <div>
                        <label for="check_out" class="{{ $label }}">Check-out</label>
                        <input id="check_out" name="check_out" type="date" min="{{ $today }}"
                            value="{{ request('check_out') }}" class="{{ $field }} w-full">
                    </div>

                    <div>
                        <label for="adults" class="{{ $label }}">Adults</label>
                        <input id="adults" name="adults" type="number" min="1" max="20"
                            value="{{ request('adults', 2) }}" class="{{ $field }} w-full">
                    </div>

                    <div>
                        <label for="children" class="{{ $label }}">Children</label>
                        <input id="children" name="children" type="number" min="0" max="20"
                            value="{{ request('children', 0) }}" class="{{ $field }} w-full">
                    </div>

                    <div class="flex items-center gap-3">
                        <button type="submit"
                            class="rounded-xl bg-gradient-to-r from-indigo-500 to-violet-500 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:from-indigo-600 hover:to-violet-600">
                            Search
                        </button>
                        <span id="availability-loading" class="hidden text-xs font-medium text-indigo-500">Searching...</span>
                    </div>
                </form>

                {{-- Results (replaced live) --}}
                <div id="availability-results" class="space-y-6 transition-opacity duration-150">

                    @if (! $submitted)
                        <div class="rounded-2xl border border-dashed border-slate-200 bg-white/60 px-6 py-16 text-center">
                            <p class="text-sm font-medium text-slate-700">Select a property, dates and guests to see available rooms.</p>
                            <p class="mt-1 text-sm text-slate-500">Results appear automatically once everything is filled.</p>
                        </div>
                    @elseif (count($searchErrors) > 0)
                        <div class="rounded-2xl bg-rose-50 px-5 py-4 text-sm text-rose-800 ring-1 ring-rose-100">
                            <p class="font-semibold">Please check the search:</p>
                            <ul class="mt-1 list-disc pl-5">
                                @foreach ($searchErrors as $message)
                                    <li>{{ $message }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @elseif ($search)
                        @php
                            $isHotel = $search['property']->type === 'hotel';
                            $availableCount = $search['available']->count();
                            $unavailableCount = $search['unavailable']->count();
                            $groups = $search['available']->groupBy('room_type_id');
                        @endphp

                        {{-- Summary --}}
                        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                            <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-100">
                                <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Stay</p>
                                <p class="mt-2 text-lg font-bold text-indigo-600">
                                    {{ $search['check_in']->format('d M') }} - {{ $search['check_out']->format('d M Y') }}
                                </p>
                            </div>
                            <div class="rounded-2xl bg-sky-50/70 p-5 ring-1 ring-sky-100">
                                <p class="text-xs font-medium uppercase tracking-wide text-sky-700/80">Nights</p>
                                <p class="mt-2 text-3xl font-bold text-sky-600">{{ $search['nights'] }}</p>
                            </div>
                            <div class="rounded-2xl bg-emerald-50/70 p-5 ring-1 ring-emerald-100">
                                <p class="text-xs font-medium uppercase tracking-wide text-emerald-700/80">Available</p>
                                <p class="mt-2 text-3xl font-bold text-emerald-600">{{ $availableCount }}</p>
                            </div>
                            <div class="rounded-2xl bg-slate-100/70 p-5 ring-1 ring-slate-200">
                                <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Not Available</p>
                                <p class="mt-2 text-3xl font-bold text-slate-600">{{ $unavailableCount }}</p>
                            </div>
                        </div>

                        <p class="text-sm text-slate-500">
                            {{ $search['property']->name }} &middot;
                            {{ $search['adults'] }} adult{{ $search['adults'] > 1 ? 's' : '' }}
                            @if ($search['children'] > 0)
                                + {{ $search['children'] }} child{{ $search['children'] > 1 ? 'ren' : '' }}
                            @endif
                            &middot; check-out day is free for the next guest
                        </p>

                        {{-- Available --}}
                        @if ($availableCount === 0)
                            <div class="rounded-2xl bg-amber-50 px-6 py-10 text-center ring-1 ring-amber-100">
                                <p class="text-sm font-semibold text-amber-800">No {{ $isHotel ? 'rooms' : 'villa units' }} are available for these dates.</p>
                                <p class="mt-1 text-sm text-amber-700">Try different dates or fewer guests. See the reasons below.</p>
                            </div>
                        @elseif ($isHotel)
                            <div class="grid grid-cols-1 gap-6 md:grid-cols-[repeat(auto-fit,minmax(340px,1fr))]">
                                @foreach ($groups as $units)
                                    @php $first = $units->first(); @endphp
                                    <div class="rounded-2xl border-t-4 border-emerald-300 bg-white p-6 shadow-sm ring-1 ring-slate-100">
                                        <div class="flex items-start justify-between gap-3">
                                            <div>
                                                <h4 class="text-base font-semibold text-slate-900">{{ $first->roomType?->name ?? 'Room' }}</h4>
                                                <p class="text-sm text-slate-500">
                                                    Capacity {{ $first->capacity }}
                                                    ({{ $first->max_adults }}A + {{ $first->max_children }}C)
                                                </p>
                                            </div>
                                            <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200">
                                                {{ $units->count() }} available
                                            </span>
                                        </div>

                                        <div class="mt-4 flex flex-wrap gap-2">
                                            @foreach ($units as $unit)
                                                <span class="rounded-lg bg-indigo-50 px-3 py-1.5 text-sm font-semibold text-indigo-700">
                                                    {{ $unit->name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="grid grid-cols-1 gap-6 md:grid-cols-[repeat(auto-fit,minmax(340px,1fr))]">
                                @foreach ($search['available'] as $unit)
                                    <div class="rounded-2xl border-t-4 border-emerald-300 bg-white p-6 shadow-sm ring-1 ring-slate-100">
                                        <div class="flex items-start justify-between gap-3">
                                            <div>
                                                <h4 class="text-base font-semibold text-slate-900">{{ $unit->name }}</h4>
                                                <p class="text-sm text-slate-500">
                                                    Capacity {{ $unit->capacity }}
                                                    ({{ $unit->max_adults }}A + {{ $unit->max_children }}C)
                                                </p>
                                            </div>
                                            <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200">
                                                Available
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <p class="text-xs text-slate-400">Rates and booking will be added with the Pricing and Reservations modules.</p>

                        {{-- Not available --}}
                        @if ($unavailableCount > 0)
                            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
                                <h4 class="text-sm font-semibold text-slate-700">Not available for these dates</h4>

                                <div class="mt-4 divide-y divide-slate-100">
                                    @foreach ($search['unavailable'] as $unit)
                                        <div class="flex flex-wrap items-center justify-between gap-2 py-3">
                                            <div class="flex items-center gap-3">
                                                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-100 text-sm font-bold text-slate-500">
                                                    {{ strtoupper(mb_substr($unit->name, 0, 1)) }}
                                                </span>
                                                <div>
                                                    <p class="text-sm font-semibold text-slate-800">{{ $unit->name }}</p>
                                                    @if ($unit->roomType)
                                                        <p class="text-xs text-slate-500">{{ $unit->roomType->name }}</p>
                                                    @endif
                                                </div>
                                            </div>

                                            <span class="rounded-full bg-rose-50 px-3 py-1 text-xs font-medium text-rose-700 ring-1 ring-rose-100">
                                                {{ $unit->reason }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endif
                </div>
            @endif

        </div>
    </div>

    <script>
        (function () {
            const form = document.getElementById('availability-form');

            if (!form) {
                return;
            }

            const results = document.getElementById('availability-results');
            const loading = document.getElementById('availability-loading');
            const checkIn = document.getElementById('check_in');
            const checkOut = document.getElementById('check_out');
            let timer = null;
            let controller = null;

            function ready() {
                return ['property_id', 'check_in', 'check_out', 'adults'].every(function (name) {
                    return form.elements[name].value;
                });
            }

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
                if (!ready()) {
                    return;
                }

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
                        const fresh = doc.getElementById('availability-results');

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

            // Check-out is always at least one day after check-in.
            function syncDates() {
                if (!checkIn.value) {
                    return;
                }

                const next = new Date(checkIn.value + 'T00:00:00Z');
                next.setUTCDate(next.getUTCDate() + 1);
                const min = next.toISOString().slice(0, 10);

                checkOut.min = min;

                if (!checkOut.value || checkOut.value < min) {
                    checkOut.value = min;
                }
            }

            function schedule(event) {
                if (event && event.target === checkIn) {
                    syncDates();
                }

                clearTimeout(timer);
                timer = setTimeout(load, 350);
            }

            form.addEventListener('input', schedule);
            form.addEventListener('change', schedule);

            form.addEventListener('submit', function (event) {
                if (ready()) {
                    event.preventDefault();
                    load();
                }
            });

            syncDates();
        })();
    </script>
</x-app-layout>