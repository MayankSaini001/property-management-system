<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Properties</h2>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-7xl px-6 lg:px-8">

            {{-- Heading + Add button --}}
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">All Properties</h3>
                    <p class="text-sm text-gray-500">
                        Hotels and villas of {{ auth()->user()->company->name }}.
                    </p>
                </div>

                <a href="{{ route('company.properties.create') }}"
                    class="rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow hover:bg-indigo-700">
                    + Add Property
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
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">Property</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">Type</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">Location</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">Currency</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">Actions</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100">
                            @forelse ($properties as $property)
                                <tr>
                                    <td class="px-4 py-4">
                                        <div class="font-medium text-gray-900">{{ $property->name }}</div>
                                        @if ($property->email)
                                            <div class="text-xs text-gray-500">{{ $property->email }}</div>
                                        @endif
                                    </td>

                                    <td class="px-4 py-4">
                                        <span class="rounded-full px-2.5 py-1 text-xs font-medium
                                            {{ $property->type === 'villa' ? 'bg-amber-100 text-amber-800' : 'bg-sky-100 text-sky-800' }}">
                                            {{ \App\Models\Property::types()[$property->type] ?? ucfirst($property->type) }}
                                        </span>
                                    </td>

                                    <td class="px-4 py-4 text-sm text-gray-700">
                                        {{ collect([$property->city, $property->countryName()])->filter()->implode(', ') ?: '—' }}
                                    </td>

                                    <td class="px-4 py-4 text-sm text-gray-700">{{ $property->currency }}</td>

                                    <td class="px-4 py-4">
                                        <span class="rounded-full px-2.5 py-1 text-xs font-medium
                                            {{ $property->isActive() ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                                            {{ ucfirst($property->status) }}
                                        </span>
                                    </td>

                                    <td class="px-4 py-4">
                                        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm font-medium">
                                            @if ($property->type === 'hotel')
                                                <a href="{{ route('company.room-types.index', $property) }}"
                                                    class="text-gray-600 hover:text-gray-900">Room Types</a>
                                            @endif

                                            <a href="{{ route('company.units.index', $property) }}"
                                                class="text-gray-600 hover:text-gray-900">
                                                {{ $property->type === 'hotel' ? 'Rooms' : 'Units' }}
                                            </a>

                                            <a href="{{ route('company.properties.edit', $property) }}"
                                                class="text-indigo-600 hover:text-indigo-800">Edit</a>

                                            <form method="POST" action="{{ route('company.properties.status', $property) }}"
                                                onsubmit="return confirm('{{ $property->isActive() ? 'Deactivate' : 'Activate' }} this property?')">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit"
                                                    class="{{ $property->isActive() ? 'text-red-600 hover:text-red-800' : 'text-green-600 hover:text-green-800' }}">
                                                    {{ $property->isActive() ? 'Deactivate' : 'Activate' }}
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-12 text-center text-sm text-gray-500">
                                        No properties yet.
                                        <a href="{{ route('company.properties.create') }}"
                                            class="font-semibold text-indigo-600 hover:text-indigo-800">+ Add your first property</a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-6">
                {{ $properties->links() }}
            </div>

        </div>
    </div>
</x-app-layout>