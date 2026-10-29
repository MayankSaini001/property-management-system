@php
    $isHotel = $property->type === 'hotel';
    $statusStyles = [
        'available' => 'bg-green-50 text-green-800',
        'out_of_service' => 'bg-gray-100 text-gray-700',
        'maintenance' => 'bg-amber-50 text-amber-800',
    ];
    $filtersOn = request()->filled('q') || request()->filled('status') || request()->filled('room_type_id');
    $field = 'rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500';
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">
            {{ $isHotel ? 'Rooms' : 'Villa Units' }} - {{ $property->name }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-7xl px-6 lg:px-8">

            <div class="mb-6 flex items-center justify-between">
                <div>
                    <a href="{{ route('company.properties.index') }}"
                        class="text-sm font-medium text-indigo-600 hover:text-indigo-800">&larr; Back to Properties</a>
                    <h3 class="mt-2 text-lg font-semibold text-gray-900">
                        {{ $isHotel ? 'All Rooms' : 'All Villa Units' }}
                    </h3>
                    <p class="text-sm text-gray-500">
                        {{ $isHotel ? 'Individual rooms of' : 'Bookable units of' }} {{ $property->name }}.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('company.blocks.index', $property) }}"
                        class="rounded-lg bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm ring-1 ring-gray-200 hover:bg-gray-50">
                        Maintenance &amp; Blocks
                    </a>

                    <a href="{{ route('company.units.create', $property) }}"
                        class="rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow hover:bg-indigo-700">
                        {{ $isHotel ? '+ Add Room' : '+ Add Villa Unit' }}
                    </a>
                </div>
            </div>

            @if (session('success'))
                <div class="mb-6 rounded-lg bg-green-50 px-4 py-3 text-sm text-green-800 ring-1 ring-green-200">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-800 ring-1 ring-red-200">
                    {{ $errors->first() }}
                </div>
            @endif

            {{-- Search and filters --}}
            <form method="GET" action="{{ route('company.units.index', $property) }}"
                class="mb-6 flex flex-wrap items-end gap-3 rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-100">

                <div>
                    <label for="q" class="mb-1 block text-xs font-medium text-gray-600">Search</label>
                    <input id="q" name="q" type="text" value="{{ request('q') }}"
                        placeholder="{{ $isHotel ? 'Room number or name' : 'Unit name' }}" class="{{ $field }}">
                </div>

                @if ($isHotel)
                    <div>
                        <label for="room_type_id" class="mb-1 block text-xs font-medium text-gray-600">Room Type</label>
                        <select id="room_type_id" name="room_type_id" class="{{ $field }}">
                            <option value="">All room types</option>
                            @foreach ($roomTypes as $roomType)
                                <option value="{{ $roomType->id }}" @selected((int) request('room_type_id') === $roomType->id)>
                                    {{ $roomType->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div>
                    <label for="status" class="mb-1 block text-xs font-medium text-gray-600">Status</label>
                    <select id="status" name="status" class="{{ $field }}">
                        <option value="">All statuses</option>
                        @foreach (\App\Models\Unit::statuses() as $value => $text)
                            <option value="{{ $value }}" @selected(request('status') === $value)>{{ $text }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="submit"
                    class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-700">
                    Filter
                </button>

                @if ($filtersOn)
                    <a href="{{ route('company.units.index', $property) }}"
                        class="text-sm font-medium text-gray-600 underline hover:text-gray-900">Reset</a>
                @endif
            </form>

            <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-100">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">{{ $isHotel ? 'Room' : 'Unit' }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">Type</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">Room Type</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">Capacity</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">Actions</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100">
                            @forelse ($units as $unit)
                                <tr>
                                    <td class="px-4 py-4">
                                        <div class="flex items-center gap-2">
                                            <span class="font-medium text-gray-900">{{ $unit->name }}</span>
                                            @if ($unit->blocked_now > 0)
                                                <span class="rounded-full bg-purple-100 px-2 py-0.5 text-xs font-medium text-purple-800">Blocked today</span>
                                            @endif
                                        </div>
                                        @if ($unit->notes)
                                            <div class="max-w-xs truncate text-xs text-gray-500">{{ $unit->notes }}</div>
                                        @endif
                                    </td>

                                    <td class="px-4 py-4">
                                        <span class="rounded-full px-2.5 py-1 text-xs font-medium
                                            {{ $unit->unit_type === 'villa' ? 'bg-amber-100 text-amber-800' : 'bg-sky-100 text-sky-800' }}">
                                            {{ \App\Models\Unit::unitTypes()[$unit->unit_type] ?? ucfirst($unit->unit_type) }}
                                        </span>
                                    </td>

                                    <td class="px-4 py-4 text-sm text-gray-700">{{ $unit->roomType?->name ?? '-' }}</td>

                                    <td class="px-4 py-4 text-sm text-gray-700">
                                        {{ $unit->capacity }}
                                        <span class="text-xs text-gray-500">({{ $unit->max_adults }}A + {{ $unit->max_children }}C)</span>
                                    </td>

                                    <td class="px-4 py-4">
                                        <form method="POST" action="{{ route('company.units.status', $unit) }}">
                                            @csrf
                                            @method('PATCH')
                                            <select name="status" onchange="this.form.submit()"
                                                class="rounded-lg border-gray-300 py-1 text-xs font-medium {{ $statusStyles[$unit->status] ?? '' }}">
                                                @foreach (\App\Models\Unit::statuses() as $value => $text)
                                                    <option value="{{ $value }}" @selected($unit->status === $value)>{{ $text }}</option>
                                                @endforeach
                                            </select>
                                        </form>
                                    </td>

                                    <td class="px-4 py-4">
                                        <div class="flex items-center gap-4 text-sm font-medium">
                                            <a href="{{ route('company.units.edit', $unit) }}"
                                                class="text-indigo-600 hover:text-indigo-800">Edit</a>
                                            <a href="{{ route('company.blocks.create', [$property, 'unit' => $unit->id]) }}"
                                                class="text-gray-600 hover:text-gray-900">Block dates</a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-12 text-center text-sm text-gray-500">
                                        @if ($filtersOn)
                                            {{ $isHotel ? 'No rooms match your filters.' : 'No villa units match your filters.' }}
                                            <a href="{{ route('company.units.index', $property) }}"
                                                class="font-semibold text-indigo-600 hover:text-indigo-800">Reset filters</a>
                                        @else
                                            {{ $isHotel ? 'No rooms yet.' : 'No villa units yet.' }}
                                            <a href="{{ route('company.units.create', $property) }}"
                                                class="font-semibold text-indigo-600 hover:text-indigo-800">
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
</x-app-layout>