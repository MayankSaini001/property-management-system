@php
    $isHotel = $property->type === 'hotel';
    $statusStyles = [
        'available' => 'bg-green-50 text-green-800',
        'out_of_service' => 'bg-gray-100 text-gray-700',
        'maintenance' => 'bg-amber-50 text-amber-800',
    ];
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

                <a href="{{ route('company.units.create', $property) }}"
                    class="rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow hover:bg-indigo-700">
                    {{ $isHotel ? '+ Add Room' : '+ Add Villa Unit' }}
                </a>
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
                                        <div class="font-medium text-gray-900">{{ $unit->name }}</div>
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
                                        <a href="{{ route('company.units.edit', $unit) }}"
                                            class="text-sm font-medium text-indigo-600 hover:text-indigo-800">Edit</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-12 text-center text-sm text-gray-500">
                                        {{ $isHotel ? 'No rooms yet.' : 'No villa units yet.' }}
                                        <a href="{{ route('company.units.create', $property) }}"
                                            class="font-semibold text-indigo-600 hover:text-indigo-800">
                                            {{ $isHotel ? '+ Add your first room' : '+ Add your first villa unit' }}
                                        </a>
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