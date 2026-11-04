@php
    $card = 'rounded-xl border border-neutral-200 bg-white p-6 shadow-sm';
    $title = 'text-lg font-semibold text-neutral-800';
    $link = 'flex items-center gap-2 text-sm text-neutral-700 transition hover:text-blue-700';

    $arrivalsTotal = $arrivalsRemaining + $arrivalsDone;
    $departuresTotal = $departuresRemaining + $departuresDone;
    $arrivalsPercent = $arrivalsTotal > 0 ? $arrivalsDone / $arrivalsTotal * 100 : 0;
    $departuresPercent = $departuresTotal > 0 ? $departuresDone / $departuresTotal * 100 : 0;
    $roomsPercent = $roomStats['total'] > 0 ? $roomStats['available'] / $roomStats['total'] * 100 : 0;
    $emailPercent = $guestStats['total'] > 0 ? $guestStats['with_email'] / $guestStats['total'] * 100 : 0;
    $propertyParams = $selected ? ['property_id' => $selected->id] : [];
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Dashboard</h2>
    </x-slot>

    <div class="min-h-[80vh] bg-neutral-100 py-8">
        <div class="w-full space-y-5 px-6 lg:px-8">

            {{-- Welcome and property filter --}}
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h3 class="text-2xl font-semibold text-neutral-800">Welcome back, {{ auth()->user()->name }}</h3>
                    <p class="text-sm text-neutral-500">{{ $today->format('l, d F Y') }}</p>
                </div>

                <form method="GET" action="{{ route('dashboard') }}" class="flex items-center gap-3">
                    <label for="property_id" class="text-sm text-neutral-500">Property</label>
                    <select id="property_id" name="property_id" onchange="this.form.submit()"
                        class="rounded-lg border-neutral-300 bg-white text-sm text-neutral-700 shadow-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        <option value="">All properties</option>
                        @foreach ($properties as $property)
                            <option value="{{ $property->id }}" @selected($selected && $selected->id === $property->id)>{{ $property->name }}</option>
                        @endforeach
                    </select>
                </form>
            </div>

            {{-- Row 1 --}}
            <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">

                {{-- Company and quick actions --}}
                <div class="space-y-5">
                    <div class="{{ $card }}">
                        <div class="flex items-center gap-4">
                            <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-neutral-800 text-xl font-bold text-white">
                                {{ strtoupper(mb_substr($company->name, 0, 1)) }}
                            </span>
                            <h3 class="text-xl font-semibold leading-tight text-neutral-800">{{ $company->name }}</h3>
                        </div>

                        <div class="mt-5 flex items-center gap-2 rounded-lg bg-neutral-50 px-3 py-2 text-sm text-neutral-600">
                            <span class="h-2 w-2 rounded-full bg-green-500"></span>
                            {{ $properties->where('status', 'active')->count() }} active of {{ $properties->count() }} properties
                        </div>

                        <a href="{{ route('company.properties.index') }}"
                            class="mt-4 inline-block text-sm font-medium text-neutral-800 underline hover:text-blue-700">Manage properties</a>
                    </div>

                    <div class="{{ $card }}">
                        <h3 class="{{ $title }}">Quick actions</h3>
                        <div class="mt-4 grid grid-cols-2 gap-3">
                            <a href="{{ route('company.reservations.create', $propertyParams) }}"
                                class="rounded-lg border border-neutral-200 px-3 py-3 text-center text-sm font-medium text-neutral-700 transition hover:border-blue-300 hover:bg-blue-50 hover:text-blue-700">New reservation</a>
                            <a href="{{ route('company.availability.index', $propertyParams) }}"
                                class="rounded-lg border border-neutral-200 px-3 py-3 text-center text-sm font-medium text-neutral-700 transition hover:border-blue-300 hover:bg-blue-50 hover:text-blue-700">Availability</a>
                            <a href="{{ route('company.calendar.index', $propertyParams) }}"
                                class="rounded-lg border border-neutral-200 px-3 py-3 text-center text-sm font-medium text-neutral-700 transition hover:border-blue-300 hover:bg-blue-50 hover:text-blue-700">Calendar</a>
                            <a href="{{ route('company.guests.create') }}"
                                class="rounded-lg border border-neutral-200 px-3 py-3 text-center text-sm font-medium text-neutral-700 transition hover:border-blue-300 hover:bg-blue-50 hover:text-blue-700">Add guest</a>
                        </div>
                    </div>
                </div>

                {{-- Reservations --}}
                <div class="{{ $card }} flex flex-col">
                    <h3 class="{{ $title }}">Reservations</h3>

                    <div class="mt-5 space-y-5">
                        <div class="flex items-center gap-5">
                            <x-dashboard-ring :value="$arrivalsRemaining" :percent="$arrivalsPercent" color="#2b6cb0" />
                            <div class="flex items-center gap-2 text-sm text-neutral-600">
                                <span class="h-2.5 w-2.5 rounded-sm bg-blue-600"></span>
                                Arrivals remaining today
                            </div>
                        </div>

                        <div class="flex items-center gap-5">
                            <x-dashboard-ring :value="$departuresRemaining" :percent="$departuresPercent" color="#2b6cb0" />
                            <div class="flex items-center gap-2 text-sm text-neutral-600">
                                <span class="h-2.5 w-2.5 rounded-sm bg-blue-600"></span>
                                Departures remaining today
                            </div>
                        </div>
                    </div>

                    <div class="mt-auto space-y-2 pt-6">
                        <a href="{{ route('company.calendar.index', $propertyParams) }}" class="{{ $link }}">Reservation calendar</a>
                        <a href="{{ route('company.reservations.index') }}" class="{{ $link }}">All reservations</a>
                    </div>
                </div>

                {{-- Rooms --}}
                <div class="{{ $card }} flex flex-col">
                    <h3 class="{{ $title }}">Rooms and units</h3>

                    <div class="mt-5 flex items-center gap-5">
                        <x-dashboard-ring :value="$roomStats['available']" :percent="$roomsPercent" color="#2f855a" track="#f6ad55" />

                        <div class="space-y-2 text-sm text-neutral-600">
                            <div class="flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-sm bg-green-700"></span> Available</div>
                            <div class="flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-sm bg-orange-400"></span> Not available ({{ $roomStats['total'] - $roomStats['available'] }})</div>
                        </div>
                    </div>

                    <dl class="mt-5 space-y-2 text-sm">
                        <div class="flex justify-between"><dt class="text-neutral-500">Total</dt><dd class="font-medium text-neutral-800">{{ $roomStats['total'] }}</dd></div>
                        <div class="flex justify-between"><dt class="text-neutral-500">Maintenance</dt><dd class="font-medium text-neutral-800">{{ $roomStats['maintenance'] }}</dd></div>
                        <div class="flex justify-between"><dt class="text-neutral-500">Out of service</dt><dd class="font-medium text-neutral-800">{{ $roomStats['out_of_service'] }}</dd></div>
                        <div class="flex justify-between"><dt class="text-neutral-500">Blocked today</dt><dd class="font-medium text-neutral-800">{{ $roomStats['blocked_today'] }}</dd></div>
                    </dl>

                    <div class="mt-auto pt-6">
                        <a href="{{ route('company.properties.index') }}" class="{{ $link }}">Rooms and blocks</a>
                    </div>
                </div>

                {{-- Guests --}}
                <div class="{{ $card }} flex flex-col">
                    <h3 class="{{ $title }}">Guests</h3>

                    <div class="mt-5 flex items-center gap-5">
                        <x-dashboard-ring :value="round($emailPercent)" suffix="%" :percent="$emailPercent" color="#2b6cb0" track="#4a5568" />

                        <div class="space-y-2 text-sm text-neutral-600">
                            <div class="flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-sm bg-blue-600"></span> Profiles with email</div>
                            <div class="flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-sm bg-neutral-600"></span> Without email</div>
                        </div>
                    </div>

                    <dl class="mt-5 space-y-2 text-sm">
                        <div class="flex justify-between"><dt class="text-neutral-500">Total guests</dt><dd class="font-medium text-neutral-800">{{ $guestStats['total'] }}</dd></div>
                        <div class="flex justify-between"><dt class="text-neutral-500">New this month</dt><dd class="font-medium text-neutral-800">{{ $guestStats['new_this_month'] }}</dd></div>
                        <div class="flex justify-between"><dt class="text-neutral-500">With phone</dt><dd class="font-medium text-neutral-800">{{ $guestStats['with_phone'] }}</dd></div>
                    </dl>

                    <div class="mt-auto pt-6">
                        <a href="{{ route('company.guests.index') }}" class="{{ $link }}">Guest profiles</a>
                    </div>
                </div>
            </div>

            {{-- Row 2 --}}
            <div class="grid grid-cols-1 gap-5 xl:grid-cols-3">

                {{-- Occupancy --}}
                <div class="{{ $card }} xl:col-span-2">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <h3 class="{{ $title }}">Occupancy - next 7 days</h3>

                        <div class="flex flex-wrap items-center gap-4 text-xs text-neutral-600">
                            <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-sm bg-slate-300"></span> Occupied rooms</span>
                            <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-sm bg-slate-700"></span> Arrivals</span>
                            <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-sm bg-orange-400"></span> Departures</span>
                            <span class="flex items-center gap-1.5"><span class="h-0.5 w-4 bg-green-700"></span> Occupancy %</span>
                        </div>
                    </div>

                    @php
                        $w = 760;
                        $h = 280;
                        $padL = 38;
                        $padR = 44;
                        $padT = 14;
                        $padB = 44;
                        $plotW = $w - $padL - $padR;
                        $plotH = $h - $padT - $padB;
                        $n = count($series);
                        $slot = $plotW / $n;

                        $peak = collect($series)->max(function ($s) {
                            return max($s['arrivals'], $s['departures'], $s['occupied']);
                        });
                        $maxCount = max(4, (int) (ceil($peak / 4) * 4));

                        $barW = 15;
                        $points = [];
                    @endphp

                    <svg viewBox="0 0 {{ $w }} {{ $h }}" class="mt-4 h-auto w-full" role="img" aria-label="Occupancy for the next 7 days">
                        @foreach ([0, 1, 2, 3, 4] as $g)
                            @php $y = $padT + $plotH - ($plotH * $g / 4); @endphp
                            <line x1="{{ $padL }}" x2="{{ $w - $padR }}" y1="{{ $y }}" y2="{{ $y }}" stroke="#e5e7eb" stroke-width="1" />
                            <text x="{{ $padL - 8 }}" y="{{ $y + 4 }}" text-anchor="end" font-size="11" fill="#6b7280">{{ (int) round($maxCount * $g / 4) }}</text>
                            <text x="{{ $w - $padR + 8 }}" y="{{ $y + 4 }}" font-size="11" fill="#6b7280">{{ $g * 25 }}%</text>
                        @endforeach

                        @foreach ($series as $i => $s)
                            @php
                                $cx = $padL + $slot * ($i + 0.5);
                                $base = $padT + $plotH;
                                $hOcc = $s['occupied'] / $maxCount * $plotH;
                                $hArr = $s['arrivals'] / $maxCount * $plotH;
                                $hDep = $s['departures'] / $maxCount * $plotH;
                                $points[] = round($cx, 1) . ',' . round($base - $s['occupancy'] / 100 * $plotH, 1);
                            @endphp

                            <rect x="{{ round($cx - $barW * 1.5 - 2, 1) }}" y="{{ round($base - $hOcc, 1) }}" width="{{ $barW }}" height="{{ round($hOcc, 1) }}" rx="2" fill="#cbd5e1" />
                            <rect x="{{ round($cx - $barW / 2, 1) }}" y="{{ round($base - $hArr, 1) }}" width="{{ $barW }}" height="{{ round($hArr, 1) }}" rx="2" fill="#334155" />
                            <rect x="{{ round($cx + $barW / 2 + 2, 1) }}" y="{{ round($base - $hDep, 1) }}" width="{{ $barW }}" height="{{ round($hDep, 1) }}" rx="2" fill="#fb923c" />

                            <text x="{{ round($cx, 1) }}" y="{{ $base + 18 }}" text-anchor="middle" font-size="11" fill="#6b7280">{{ $s['date']->format('D') }}</text>
                            <text x="{{ round($cx, 1) }}" y="{{ $base + 33 }}" text-anchor="middle" font-size="11" fill="#9ca3af">{{ $s['date']->format('d/m') }}</text>
                        @endforeach

                        <polyline points="{{ implode(' ', $points) }}" fill="none" stroke="#2f855a" stroke-width="2.5" stroke-linejoin="round" stroke-linecap="round" />

                        @foreach ($points as $point)
                            @php [$px, $py] = explode(',', $point); @endphp
                            <circle cx="{{ $px }}" cy="{{ $py }}" r="3.5" fill="#fff" stroke="#2f855a" stroke-width="2" />
                        @endforeach
                    </svg>

                    <div class="mt-4 overflow-x-auto">
                        <table class="w-full min-w-[560px] text-sm">
                            <thead>
                                <tr class="border-b border-neutral-200 text-xs text-neutral-500">
                                    <th class="py-2 pr-4 text-left font-medium"></th>
                                    @foreach ($series as $s)
                                        <th class="px-2 py-2 text-center font-medium">
                                            {{ $s['date']->format('D') }}<br>{{ $s['date']->format('d/m') }}
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-neutral-100">
                                <tr>
                                    <td class="py-2 pr-4 font-medium text-neutral-800">Arrivals</td>
                                    @foreach ($series as $s)
                                        <td class="px-2 py-2 text-center font-semibold text-blue-700">{{ $s['arrivals'] }}</td>
                                    @endforeach
                                </tr>
                                <tr>
                                    <td class="py-2 pr-4 font-medium text-neutral-800">Departures</td>
                                    @foreach ($series as $s)
                                        <td class="px-2 py-2 text-center text-neutral-700">{{ $s['departures'] }}</td>
                                    @endforeach
                                </tr>
                                <tr>
                                    <td class="py-2 pr-4 font-medium text-neutral-800">Stayovers</td>
                                    @foreach ($series as $s)
                                        <td class="px-2 py-2 text-center text-neutral-700">{{ $s['stayovers'] }}</td>
                                    @endforeach
                                </tr>
                                <tr>
                                    <td class="py-2 pr-4 font-medium text-neutral-800">Guests</td>
                                    @foreach ($series as $s)
                                        <td class="px-2 py-2 text-center text-neutral-700">{{ $s['guests'] }}</td>
                                    @endforeach
                                </tr>
                                <tr>
                                    <td class="py-2 pr-4 font-medium text-neutral-800">Occupancy</td>
                                    @foreach ($series as $s)
                                        <td class="px-2 py-2 text-center text-neutral-700">{{ $s['occupancy'] }}%</td>
                                    @endforeach
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Front desk today --}}
                <div class="space-y-5">
                    <div class="{{ $card }}">
                        <div class="flex items-center justify-between">
                            <h3 class="{{ $title }}">Arrivals today</h3>
                            <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">{{ $arrivalsRemaining }} left</span>
                        </div>

                        <ul class="mt-4 divide-y divide-neutral-100">
                            @forelse ($todayArrivals as $reservation)
                                <li class="py-3">
                                    <a href="{{ route('company.reservations.show', $reservation) }}" class="group block">
                                        <p class="text-sm font-semibold text-neutral-800 group-hover:text-blue-700">
                                            {{ $reservation->guest?->fullName() ?? 'Guest' }}
                                        </p>
                                        <p class="text-xs text-neutral-500">
                                            {{ $reservation->booking_reference }} -
                                            {{ $reservation->items->map(fn ($item) => $item->unit?->name)->filter()->implode(', ') }}
                                            - {{ $reservation->nights() }} night{{ $reservation->nights() > 1 ? 's' : '' }}
                                        </p>
                                    </a>
                                </li>
                            @empty
                                <li class="py-4 text-sm text-neutral-500">No arrivals left today.</li>
                            @endforelse
                        </ul>
                    </div>

                    <div class="{{ $card }}">
                        <div class="flex items-center justify-between">
                            <h3 class="{{ $title }}">Departures today</h3>
                            <span class="rounded-full bg-orange-50 px-3 py-1 text-xs font-semibold text-orange-700">{{ $departuresRemaining }} left</span>
                        </div>

                        <ul class="mt-4 divide-y divide-neutral-100">
                            @forelse ($todayDepartures as $reservation)
                                <li class="py-3">
                                    <a href="{{ route('company.reservations.show', $reservation) }}" class="group block">
                                        <p class="text-sm font-semibold text-neutral-800 group-hover:text-blue-700">
                                            {{ $reservation->guest?->fullName() ?? 'Guest' }}
                                        </p>
                                        <p class="text-xs text-neutral-500">
                                            {{ $reservation->booking_reference }} -
                                            {{ $reservation->items->map(fn ($item) => $item->unit?->name)->filter()->implode(', ') }}
                                        </p>
                                    </a>
                                </li>
                            @empty
                                <li class="py-4 text-sm text-neutral-500">No departures left today.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>