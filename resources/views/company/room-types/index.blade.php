<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Room Types - {{ $property->name }}</h2>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-none px-6 lg:px-8">

            <div class="mb-6 flex items-center justify-between">
                <div>
                    <a href="{{ route('company.properties.index') }}"
                        class="text-sm font-medium text-indigo-600 hover:text-indigo-800">&larr; Back to Properties</a>
                    <h3 class="mt-2 text-lg font-semibold text-gray-900">All Room Types</h3>
                    <p class="text-sm text-gray-500">Room categories of {{ $property->name }} with capacity and occupancy limits.</p>
                </div>

                <a href="{{ route('company.room-types.create', $property) }}"
                    class="rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow hover:bg-indigo-700">
                    + Add Room Type
                </a>
            </div>

            @if (session('success'))
                <div class="mb-6 rounded-lg bg-green-50 px-4 py-3 text-sm text-green-800 ring-1 ring-green-200">
                    {{ session('success') }}
                </div>
            @endif

            <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-100">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">Room Type</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">Max Adults</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">Max Children</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">Capacity</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">Rooms</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">Actions</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100">
                            @forelse ($roomTypes as $roomType)
                                <tr>
                                    <td class="px-4 py-4">
                                        <div class="font-medium text-gray-900">{{ $roomType->name }}</div>
                                        @if ($roomType->description)
                                            <div class="max-w-xs truncate text-xs text-gray-500">{{ $roomType->description }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 text-sm text-gray-700">{{ $roomType->max_adults }}</td>
                                    <td class="px-4 py-4 text-sm text-gray-700">{{ $roomType->max_children }}</td>
                                    <td class="px-4 py-4 text-sm text-gray-700">{{ $roomType->capacity }}</td>
                                    <td class="px-4 py-4 text-sm text-gray-700">{{ $roomType->units_count }}</td>

                                    <td class="px-4 py-4">
                                        <span class="rounded-full px-2.5 py-1 text-xs font-medium
                                            {{ $roomType->isActive() ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                                            {{ ucfirst($roomType->status) }}
                                        </span>
                                    </td>

                                    <td class="px-4 py-4">
                                        <div class="flex items-center gap-4 text-sm font-medium">
                                            <a href="{{ route('company.room-types.edit', $roomType) }}"
                                                class="text-indigo-600 hover:text-indigo-800">Edit</a>

                                            <form method="POST" action="{{ route('company.room-types.status', $roomType) }}"
                                                onsubmit="return confirm('{{ $roomType->isActive() ? 'Deactivate' : 'Activate' }} this room type?')">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit"
                                                    class="{{ $roomType->isActive() ? 'text-red-600 hover:text-red-800' : 'text-green-600 hover:text-green-800' }}">
                                                    {{ $roomType->isActive() ? 'Deactivate' : 'Activate' }}
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-12 text-center text-sm text-gray-500">
                                        No room types yet.
                                        <a href="{{ route('company.room-types.create', $property) }}"
                                            class="font-semibold text-indigo-600 hover:text-indigo-800">+ Add your first room type</a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-6">
                {{ $roomTypes->links() }}
            </div>

        </div>
    </div>
</x-app-layout>