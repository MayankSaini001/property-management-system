@php
    $field = 'rounded-xl border-slate-200 bg-slate-50 text-sm text-slate-700 shadow-none focus:border-indigo-400 focus:bg-white focus:ring-2 focus:ring-indigo-100';
    $navBtn = 'rounded-xl bg-white px-3 py-2 text-sm font-medium text-slate-600 shadow-sm ring-1 ring-slate-200 transition hover:bg-indigo-50 hover:text-indigo-700';
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Booking Calendar</h2>
    </x-slot>

    <div class="min-h-[75vh] bg-gradient-to-b from-indigo-50 via-slate-50 to-white py-10">
        <div class="w-full space-y-6 px-6 lg:px-8">

            @if (! $property)
                <div class="rounded-2xl bg-white px-6 py-16 text-center shadow-sm ring-1 ring-slate-100">
                    <p class="text-sm font-medium text-slate-700">Add a property to see the booking calendar.</p>
                    <a href="{{ route('company.properties.create') }}"
                        class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">+ Add a property</a>
                </div>
            @else
                @php
                    $colWidth = $view === 'day' ? 'min-w-[340px]' : ($view === 'week' ? 'min-w-[120px]' : 'min-w-[46px]');
                    $statusStyles = [
                        'confirmed' => 'bg-sky-100 text-sky-800 ring-sky-200 hover:bg-sky-200',
                        'checked_in' => 'bg-emerald-100 text-emerald-800 ring-emerald-200 hover:bg-emerald-200',
                        'checked_out' => 'bg-slate-200 text-slate-600 ring-slate-300 hover:bg-slate-300',
                    ];
                    $blockStyles = [
                        'maintenance' => 'bg-amber-100 text-amber-800 ring-amber-200',
                        'owner_use' => 'bg-violet-100 text-violet-800 ring-violet-200',
                    ];
                    $tabs = ['day' => 'Day', 'week' => 'Week', 'month' => 'Month'];
                    $link = fn ($params) => route('company.calendar.index', array_merge([
                        'property_id' => $property->id,
                        'view' => $view,
                        'date' => $start->toDateString(),
                    ], $params));
                @endphp

                {{-- Heading and controls --}}
                <div class="space-y-4 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3M4 11h16M5 5h14a1 1 0 011 1v14a1 1 0 01-1 1H5a1 1 0 01-1-1V6a1 1 0 011-1z" />
                                </svg>
                            </span>
                            <div>
                                <h3 class="text-lg font-semibold text-slate-900">{{ $label }}</h3>
                                <p class="text-sm text-slate-500">{{ $property->name }} - each column is one night</p>
                            </div>
                        </div>

                        <a href="{{ route('company.reservations.create', ['property_id' => $property->id]) }}"
                            class="rounded-xl bg-gradient-to-r from-indigo-500 to-violet-500 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:from-indigo-600 hover:to-violet-600">
                            + New Reservation
                        </a>
                    </div>

                    <form method="GET" action="{{ route('company.calendar.index') }}" class="flex flex-wrap items-end gap-4">
                        <input type="hidden" name="view" value="{{ $view }}">

                        <div class="w-full sm:w-64">
                            <label for="property_id" class="mb-1.5 block text-xs font-medium text-slate-500">Property</label>
                            <select id="property_id" name="property_id" onchange="this.form.submit()" class="{{ $field }} w-full">
                                @foreach ($properties as $item)
                                    <option value="{{ $item->id }}" @selected($item->id === $property->id)>{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="date" class="mb-1.5 block text-xs font-medium text-slate-500">Go to date</label>
                            <input id="date" name="date" type="date" value="{{ $start->toDateString() }}"
                                onchange="this.form.submit()" class="{{ $field }}">
                        </div>

                        <div class="flex items-center gap-2">
                            <a href="{{ $link(['date' => $prev->toDateString()]) }}" class="{{ $navBtn }}">&larr; Previous</a>
                            <a href="{{ $link(['date' => $today->toDateString()]) }}" class="{{ $navBtn }}">Today</a>
                            <a href="{{ $link(['date' => $next->toDateString()]) }}" class="{{ $navBtn }}">Next &rarr;</a>
                        </div>

                        <div class="ml-auto inline-flex rounded-xl bg-slate-100 p-1">
                            @foreach ($tabs as $key => $text)
                                <a href="{{ $link(['view' => $key]) }}"
                                    class="rounded-lg px-4 py-1.5 text-sm font-medium transition
                                        {{ $view === $key ? 'bg-indigo-500 text-white shadow-sm' : 'text-slate-600 hover:bg-white' }}">
                                    {{ $text }}
                                </a>
                            @endforeach
                        </div>
                    </form>
                </div>

                {{-- Summary cards --}}
                <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                    <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-100">
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-500">{{ $property->type === 'hotel' ? 'Rooms' : 'Units' }}</p>
                        <p class="mt-2 text-3xl font-bold text-indigo-600">{{ $stats['rooms'] }}</p>
                    </div>
                    <div class="rounded-2xl bg-sky-50/70 p-5 ring-1 ring-sky-100">
                        <p class="text-xs font-medium uppercase tracking-wide text-sky-700/80">Reservations</p>
                        <p class="mt-2 text-3xl font-bold text-sky-600">{{ $stats['reservations'] }}</p>
                    </div>
                    <div class="rounded-2xl bg-emerald-50/70 p-5 ring-1 ring-emerald-100">
                        <p class="text-xs font-medium uppercase tracking-wide text-emerald-700/80">Booked Nights</p>
                        <p class="mt-2 text-3xl font-bold text-emerald-600">{{ $stats['booked_nights'] }}</p>
                    </div>
                    <div class="rounded-2xl bg-violet-50/70 p-5 ring-1 ring-violet-100">
                        <p class="text-xs font-medium uppercase tracking-wide text-violet-700/80">Occupancy</p>
                        <p class="mt-2 text-3xl font-bold text-violet-600">{{ $stats['occupancy'] }}%</p>
                    </div>
                </div>

                {{-- Legend --}}
                <div class="flex flex-wrap items-center gap-3 text-xs text-slate-600">
                    <span class="rounded-full bg-sky-100 px-3 py-1 font-medium text-sky-800">Confirmed</span>
                    <span class="rounded-full bg-emerald-100 px-3 py-1 font-medium text-emerald-800">Checked in</span>
                    <span class="rounded-full bg-slate-200 px-3 py-1 font-medium text-slate-600">Checked out</span>
                    <span class="rounded-full bg-amber-100 px-3 py-1 font-medium text-amber-800">Maintenance</span>
                    <span class="rounded-full bg-violet-100 px-3 py-1 font-medium text-violet-800">Owner use</span>
                    <span class="text-slate-400">Click a free cell (+) to start a booking for that night.</span>
                </div>

                {{-- Calendar --}}
                @if (count($rows) === 0)
                    <div class="rounded-2xl bg-white px-6 py-16 text-center shadow-sm ring-1 ring-slate-100">
                        <p class="text-sm font-medium text-slate-700">This property has no rooms or units yet.</p>
                        <a href="{{ route('company.units.create', $property) }}"
                            class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">+ Add a {{ $property->type === 'hotel' ? 'room' : 'villa unit' }}</a>
                    </div>
                @else
                    <div class="overflow-x-auto rounded-2xl bg-white shadow-sm ring-1 ring-slate-100">
                        <table class="min-w-full border-separate border-spacing-0 text-sm">
                            <thead>
                                <tr>
                                    <th class="sticky left-0 z-20 min-w-[170px] border-b border-r border-slate-100 bg-indigo-50 px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-indigo-900/60">
                                        {{ $property->type === 'hotel' ? 'Room' : 'Unit' }}
                                    </th>

                                    @foreach ($days as $day)
                                        @php
                                            $isToday = $day->equalTo($today);
                                            $isWeekend = $day->isWeekend();
                                        @endphp
                                        <th class="{{ $colWidth }} border-b border-slate-100 px-1 py-2 text-center
                                            {{ $isToday ? 'bg-indigo-500 text-white' : ($isWeekend ? 'bg-slate-100 text-slate-500' : 'bg-indigo-50 text-indigo-900/70') }}">
                                            <div class="text-[11px] font-medium uppercase">{{ $day->format('D') }}</div>
                                            <div class="text-base font-bold">{{ $day->format('d') }}</div>
                                            @if ($view !== 'month')
                                                <div class="text-[11px]">{{ $day->format('M') }}</div>
                                            @endif
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($rows as $row)
                                    <tr>
                                        <td class="sticky left-0 z-10 border-b border-r border-slate-100 bg-white px-4 py-3">
                                            <p class="font-semibold text-slate-900">{{ $row['unit']->name }}</p>
                                            <p class="text-xs text-slate-400">
                                                {{ $row['unit']->roomType?->name ?? 'Villa unit' }} - cap {{ $row['unit']->capacity }}
                                            </p>
                                        </td>

                                        @php $col = 0; @endphp

                                        @foreach ($row['cells'] as $cell)
                                            @php
                                                $day = $days[$col];
                                                $col += $cell['span'];
                                                $isToday = $day->equalTo($today);
                                                $isWeekend = $day->isWeekend();
                                            @endphp

                                            <td colspan="{{ $cell['span'] }}"
                                                class="border-b border-slate-100 p-1
                                                    {{ $cell['type'] === 'empty' ? ($isToday ? 'bg-indigo-50/60' : ($isWeekend ? 'bg-slate-50' : '')) : '' }}">

                                                @if ($cell['type'] === 'reservation')
                                                    @php
                                                        $item = $cell['item'];
                                                        $reservation = $item->reservation;
                                                        $guestName = $reservation->guest?->fullName() ?? 'Guest';
                                                    @endphp
                                                    <a href="{{ route('company.reservations.show', $reservation) }}"
                                                        title="{{ $reservation->booking_reference }} - {{ $guestName }} - {{ $item->check_in->format('d M') }} to {{ $item->check_out->format('d M Y') }}"
                                                        class="block h-10 truncate px-2 py-2 text-xs font-semibold ring-1 transition
                                                            {{ $statusStyles[$reservation->status] ?? 'bg-slate-100 text-slate-600 ring-slate-200' }}
                                                            {{ $cell['clipped_left'] ? 'rounded-l-none' : 'rounded-l-lg' }}
                                                            {{ $cell['clipped_right'] ? 'rounded-r-none' : 'rounded-r-lg' }}">
                                                        {{ $cell['clipped_left'] ? '< ' : '' }}{{ $guestName }}{{ $cell['clipped_right'] ? ' >' : '' }}
                                                    </a>
                                                @elseif ($cell['type'] === 'block')
                                                    @php $block = $cell['block']; @endphp
                                                    <div title="{{ \App\Models\InventoryBlock::types()[$block->block_type] ?? 'Blocked' }}: {{ $block->start_date->format('d M') }} to {{ $block->end_date->format('d M Y') }}{{ $block->reason ? ' - ' . $block->reason : '' }}"
                                                        class="h-10 truncate px-2 py-2 text-xs font-semibold ring-1
                                                            {{ $blockStyles[$block->block_type] ?? 'bg-slate-100 text-slate-600 ring-slate-200' }}
                                                            {{ $cell['clipped_left'] ? 'rounded-l-none' : 'rounded-l-lg' }}
                                                            {{ $cell['clipped_right'] ? 'rounded-r-none' : 'rounded-r-lg' }}">
                                                        {{ \App\Models\InventoryBlock::types()[$block->block_type] ?? 'Blocked' }}
                                                    </div>
                                                @elseif ($day->gte($today))
                                                    <a href="{{ route('company.reservations.create', [
                                                            'property_id' => $property->id,
                                                            'check_in' => $day->toDateString(),
                                                            'check_out' => $day->copy()->addDay()->toDateString(),
                                                            'adults' => 2,
                                                            'children' => 0,
                                                        ]) }}"
                                                        title="Book {{ $row['unit']->name }} for {{ $day->format('d M') }}"
                                                        class="flex h-10 items-center justify-center rounded-lg text-slate-300 transition hover:bg-indigo-50 hover:text-indigo-600">
                                                        +
                                                    </a>
                                                @else
                                                    <div class="h-10"></div>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            @endif

        </div>
    </div>
</x-app-layout>