@php
    $field = 'rounded-xl border-slate-200 bg-slate-50 text-sm text-slate-700 shadow-none focus:border-indigo-400 focus:bg-white focus:ring-2 focus:ring-indigo-100';
    $label = 'mb-1.5 block text-xs font-medium text-slate-500';
    $error = 'mt-1 text-sm text-rose-600';

    $defaultProperty = request('property_id', $properties->count() === 1 ? $properties->first()->id : '');
    $today = now()->toDateString();
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">New Reservation</h2>
    </x-slot>

    <div class="min-h-[75vh] bg-gradient-to-b from-indigo-50 via-slate-50 to-white py-10">
        <div class="w-full space-y-6 px-6 lg:px-8">

            <a href="{{ route('company.reservations.index') }}"
                class="text-sm font-medium text-indigo-600 hover:text-indigo-800">&larr; Back to Reservations</a>

            {{-- Page heading --}}
            <div class="flex items-center gap-4 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
                <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                </span>
                <div>
                    <h3 class="text-lg font-semibold text-slate-900">Create a Reservation</h3>
                    <p class="text-sm text-slate-500">Step 1: choose the property, dates and guests. Step 2: pick the rooms and the guest.</p>
                </div>
            </div>

            {{-- Errors from saving the reservation --}}
            @if ($errors->any())
                <div class="rounded-2xl bg-rose-50 px-5 py-4 text-sm text-rose-800 ring-1 ring-rose-100">
                    <p class="font-semibold">The reservation could not be saved:</p>
                    <ul class="mt-1 list-disc pl-5">
                        @foreach ($errors->all() as $message)
                            <li>{{ $message }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($properties->isEmpty())
                <div class="rounded-2xl bg-white px-6 py-16 text-center shadow-sm ring-1 ring-slate-100">
                    <p class="text-sm font-medium text-slate-700">You need an active property to create a reservation.</p>
                    <a href="{{ route('company.properties.create') }}"
                        class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">+ Add a property</a>
                </div>
            @else

                {{-- Step 1: search (results update automatically when all fields are filled) --}}
                <form id="reservation-search" method="GET" action="{{ route('company.reservations.create') }}"
                    class="grid grid-cols-1 items-end gap-4 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-100 md:grid-cols-2 xl:grid-cols-[2fr_1fr_1fr_0.8fr_0.8fr_auto]">

                    @if (request()->filled('guest'))
                        <input type="hidden" name="guest" value="{{ request('guest') }}">
                    @endif

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
                        <label for="adults" class="{{ $label }}">Adults per room</label>
                        <input id="adults" name="adults" type="number" min="1" max="20"
                            value="{{ request('adults', 2) }}" class="{{ $field }} w-full">
                    </div>

                    <div>
                        <label for="children" class="{{ $label }}">Children per room</label>
                        <input id="children" name="children" type="number" min="0" max="20"
                            value="{{ request('children', 0) }}" class="{{ $field }} w-full">
                    </div>

                    <div class="flex items-center gap-3">
                        <button type="submit"
                            class="rounded-xl bg-gradient-to-r from-indigo-500 to-violet-500 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:from-indigo-600 hover:to-violet-600">
                            Search
                        </button>
                        <span id="reservation-loading" class="hidden text-xs font-medium text-indigo-500">Searching...</span>
                    </div>
                </form>

                {{-- Step 2: rooms, guest and details (replaced live when the search changes) --}}
                <div id="reservation-results" class="space-y-6 transition-opacity duration-150">

                    @if (! $submitted)
                        <div class="rounded-2xl border border-dashed border-slate-200 bg-white/60 px-6 py-16 text-center">
                            <p class="text-sm font-medium text-slate-700">Select a property, dates and guests to see the available rooms.</p>
                            <p class="mt-1 text-sm text-slate-500">The rooms appear automatically once everything is filled.</p>
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
                            $groups = $search['available']->groupBy('room_type_id');
                            $selected = array_map('intval', old('unit_ids', []));
                        @endphp

                        {{-- Summary --}}
                        <div class="flex flex-wrap items-center gap-3 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-100">
                            <span class="rounded-full bg-indigo-50 px-3 py-1 text-sm font-semibold text-indigo-700">
                                {{ $search['property']->name }}
                            </span>
                            <span class="rounded-full bg-sky-50 px-3 py-1 text-sm font-medium text-sky-700">
                                {{ $search['check_in']->format('d M') }} - {{ $search['check_out']->format('d M Y') }}
                                ({{ $search['nights'] }} night{{ $search['nights'] > 1 ? 's' : '' }})
                            </span>
                            <span class="rounded-full bg-violet-50 px-3 py-1 text-sm font-medium text-violet-700">
                                {{ $search['adults'] }} adult{{ $search['adults'] > 1 ? 's' : '' }}
                                @if ($search['children'] > 0)
                                    + {{ $search['children'] }} child{{ $search['children'] > 1 ? 'ren' : '' }}
                                @endif
                                per room
                            </span>
                            <span class="rounded-full bg-emerald-50 px-3 py-1 text-sm font-medium text-emerald-700">
                                {{ $search['available']->count() }} available
                            </span>
                        </div>

                        @if ($search['available']->isEmpty())
                            <div class="rounded-2xl bg-amber-50 px-6 py-10 text-center ring-1 ring-amber-100">
                                <p class="text-sm font-semibold text-amber-800">No {{ $isHotel ? 'rooms' : 'villa units' }} are available for these dates.</p>
                                <p class="mt-1 text-sm text-amber-700">Try different dates or fewer guests. The
                                    <a href="{{ route('company.availability.index', request()->only(['property_id', 'check_in', 'check_out', 'adults', 'children'])) }}"
                                        class="font-semibold underline">Availability Search</a> shows the reasons.</p>
                            </div>
                        @else
                            <form id="booking-form" method="POST" action="{{ route('company.reservations.store') }}" class="space-y-6">
                                @csrf
                                <input type="hidden" name="property_id" value="{{ $search['property']->id }}">
                                <input type="hidden" name="check_in" value="{{ $search['check_in']->toDateString() }}">
                                <input type="hidden" name="check_out" value="{{ $search['check_out']->toDateString() }}">
                                <input type="hidden" name="adults" value="{{ $search['adults'] }}">
                                <input type="hidden" name="children" value="{{ $search['children'] }}">

                                {{-- Rooms --}}
                                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
                                    <h4 class="text-sm font-semibold text-indigo-700">
                                        Select {{ $isHotel ? 'rooms' : 'villa units' }}
                                    </h4>
                                    <p class="mt-1 text-xs text-slate-500">You can select more than one.</p>

                                    <div class="mt-4 space-y-5">
                                        @foreach ($groups as $units)
                                            @php $first = $units->first(); @endphp
                                            <div>
                                                @if ($isHotel)
                                                    <p class="mb-2 text-sm font-medium text-slate-600">
                                                        {{ $first->roomType?->name ?? 'Room' }}
                                                        <span class="text-xs text-slate-400">
                                                            (capacity {{ $first->capacity }}, {{ $units->count() }} available)
                                                        </span>
                                                    </p>
                                                @endif

                                                <div class="flex flex-wrap gap-3">
                                                    @foreach ($units as $unit)
                                                        <label class="cursor-pointer">
                                                            <input type="checkbox" name="unit_ids[]" value="{{ $unit->id }}"
                                                                class="peer sr-only" @checked(in_array($unit->id, $selected, true))>
                                                            <span class="block rounded-xl bg-indigo-50 px-5 py-2.5 text-sm font-semibold text-indigo-700 ring-1 ring-indigo-100 transition hover:bg-indigo-100 peer-checked:bg-indigo-600 peer-checked:text-white peer-checked:ring-indigo-600">
                                                                {{ $unit->name }}
                                                            </span>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                {{-- Guest and details --}}
                                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
                                    <h4 class="text-sm font-semibold text-indigo-700">Guest and booking details</h4>

                                    <div class="mt-4 grid grid-cols-1 gap-5 md:grid-cols-2">
                                        <div>
                                            <label for="guest_id" class="{{ $label }}">Guest *</label>
                                            <select id="guest_id" name="guest_id" required class="{{ $field }} w-full">
                                                <option value="">Select guest</option>
                                                @foreach ($guests as $guest)
                                                    <option value="{{ $guest->id }}" @selected((int) old('guest_id', request('guest')) === $guest->id)>
                                                        {{ $guest->fullName() }} - {{ $guest->phone ?: $guest->email }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <p class="mt-1 text-xs text-slate-500">
                                                Guest not in the list?
                                                <a href="{{ route('company.guests.create') }}" class="font-semibold text-indigo-600 hover:text-indigo-800">Add a new guest</a>
                                            </p>
                                            @error('guest_id') <p class="{{ $error }}">{{ $message }}</p> @enderror
                                        </div>

                                        <div>
                                            <label for="source" class="{{ $label }}">Booking Source *</label>
                                            <select id="source" name="source" required class="{{ $field }} w-full">
                                                @foreach (\App\Models\Reservation::sources() as $value => $text)
                                                    <option value="{{ $value }}" @selected(old('source', 'direct') === $value)>{{ $text }}</option>
                                                @endforeach
                                            </select>
                                            @error('source') <p class="{{ $error }}">{{ $message }}</p> @enderror
                                        </div>

                                        <div class="md:col-span-2">
                                            <label for="notes" class="{{ $label }}">Notes</label>
                                            <textarea id="notes" name="notes" rows="3" class="{{ $field }} w-full"
                                                placeholder="Special requests, arrival time, etc.">{{ old('notes') }}</textarea>
                                            @error('notes') <p class="{{ $error }}">{{ $message }}</p> @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between">
                                    <p class="text-xs text-slate-400">Rates and payments will be added with the Pricing and Finance modules.</p>

                                    <button type="submit"
                                        class="rounded-xl bg-gradient-to-r from-indigo-500 to-violet-500 px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:from-indigo-600 hover:to-violet-600">
                                        Confirm Reservation
                                    </button>
                                </div>
                            </form>
                        @endif
                    @endif
                </div>
            @endif

        </div>
    </div>

    <script>
        (function () {
            const form = document.getElementById('reservation-search');

            if (!form) {
                return;
            }

            const results = document.getElementById('reservation-results');
            const loading = document.getElementById('reservation-loading');
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