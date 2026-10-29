@php
    $typeStyles = [
        'maintenance' => 'bg-amber-100 text-amber-800',
        'owner_use' => 'bg-purple-100 text-purple-800',
    ];
    $tabs = ['upcoming' => 'Current & Upcoming', 'past' => 'Past', 'all' => 'All'];
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Maintenance &amp; Blocks - {{ $property->name }}</h2>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-7xl px-6 lg:px-8">

            <div class="mb-6 flex items-center justify-between">
                <div>
                    <a href="{{ route('company.units.index', $property) }}"
                        class="text-sm font-medium text-indigo-600 hover:text-indigo-800">
                        &larr; Back to {{ $property->type === 'hotel' ? 'Rooms' : 'Units' }}
                    </a>
                    <h3 class="mt-2 text-lg font-semibold text-gray-900">Date Blocks</h3>
                    <p class="text-sm text-gray-500">
                        Blocked rooms or units cannot be booked in these dates. The day after a block ends is free for check-in.
                    </p>
                </div>

                <a href="{{ route('company.blocks.create', $property) }}"
                    class="rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow hover:bg-indigo-700">
                    + Add Block
                </a>
            </div>

            @if (session('success'))
                <div class="mb-6 rounded-lg bg-green-50 px-4 py-3 text-sm text-green-800 ring-1 ring-green-200">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Tabs --}}
            <div class="mb-4 flex gap-2">
                @foreach ($tabs as $key => $label)
                    <a href="{{ route('company.blocks.index', [$property, 'when' => $key]) }}"
                        class="rounded-lg px-3 py-1.5 text-sm font-medium
                            {{ $when === $key ? 'bg-indigo-600 text-white' : 'bg-white text-gray-600 ring-1 ring-gray-200 hover:bg-gray-50' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-100">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">{{ $property->type === 'hotel' ? 'Room' : 'Unit' }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">Block Type</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">From</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">To</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">Nights</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">Reason</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">Actions</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100">
                            @forelse ($blocks as $block)
                                <tr>
                                    <td class="px-4 py-4 font-medium text-gray-900">{{ $block->unit?->name ?? '-' }}</td>

                                    <td class="px-4 py-4">
                                        <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $typeStyles[$block->block_type] ?? 'bg-gray-100 text-gray-600' }}">
                                            {{ \App\Models\InventoryBlock::types()[$block->block_type] ?? ucfirst($block->block_type) }}
                                        </span>
                                    </td>

                                    <td class="px-4 py-4 text-sm text-gray-700">{{ $block->start_date->format('d M Y') }}</td>
                                    <td class="px-4 py-4 text-sm text-gray-700">{{ $block->end_date->format('d M Y') }}</td>
                                    <td class="px-4 py-4 text-sm text-gray-700">{{ $block->nights() }}</td>

                                    <td class="px-4 py-4 text-sm text-gray-600">
                                        <div class="max-w-xs truncate">{{ $block->reason ?: '-' }}</div>
                                    </td>

                                    <td class="px-4 py-4">
                                        <form method="POST" action="{{ route('company.blocks.destroy', $block) }}"
                                            onsubmit="return confirm('Remove this block? The dates will become available again.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-sm font-medium text-red-600 hover:text-red-800">Remove</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-12 text-center text-sm text-gray-500">
                                        No blocks here.
                                        <a href="{{ route('company.blocks.create', $property) }}"
                                            class="font-semibold text-indigo-600 hover:text-indigo-800">+ Add a block</a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-6">
                {{ $blocks->links() }}
            </div>

        </div>
    </div>
</x-app-layout>