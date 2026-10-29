@php
    $isHotel = $property->type === 'hotel';

    $typeStyles = [
        'maintenance' => 'bg-amber-50 text-amber-700 ring-amber-200',
        'owner_use' => 'bg-violet-50 text-violet-700 ring-violet-200',
    ];
    $tabs = ['upcoming' => 'Current & Upcoming', 'past' => 'Past', 'all' => 'All'];
    $th = 'px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-indigo-900/60';
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Maintenance &amp; Blocks - {{ $property->name }}</h2>
    </x-slot>

    <div class="min-h-[75vh] bg-gradient-to-b from-violet-50 via-slate-50 to-white py-10">
        <div class="mx-auto max-w-none space-y-6 px-6 lg:px-8">

            {{-- Page heading --}}
            <div class="flex flex-wrap items-center justify-between gap-4 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
                <div class="flex items-center gap-4">
                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-violet-50 text-violet-600">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3M4 11h16M5 5h14a1 1 0 011 1v14a1 1 0 01-1 1H5a1 1 0 01-1-1V6a1 1 0 011-1z" />
                        </svg>
                    </span>

                    <div>
                        <a href="{{ route('company.units.index', $property) }}"
                            class="text-xs font-medium text-indigo-600 hover:text-indigo-800">
                            &larr; Back to {{ $isHotel ? 'Rooms' : 'Units' }}
                        </a>
                        <h3 class="text-lg font-semibold text-slate-900">Date Blocks</h3>
                        <p class="text-sm text-slate-500">
                            Blocked {{ $isHotel ? 'rooms' : 'units' }} cannot be booked in these dates. The day after a block ends is free for check-in.
                        </p>
                    </div>
                </div>

                <a href="{{ route('company.blocks.create', $property) }}"
                    class="rounded-xl bg-gradient-to-r from-indigo-500 to-violet-500 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:from-indigo-600 hover:to-violet-600">
                    + Add Block
                </a>
            </div>

            @if (session('success'))
                <div class="flex items-center gap-2 rounded-xl bg-emerald-50 px-4 py-3 text-sm text-emerald-800 ring-1 ring-emerald-100">
                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                    {{ session('success') }}
                </div>
            @endif

            {{-- Tabs --}}
            <div class="inline-flex rounded-xl bg-white p-1 shadow-sm ring-1 ring-slate-100">
                @foreach ($tabs as $key => $label)
                    <a href="{{ route('company.blocks.index', [$property, 'when' => $key]) }}"
                        class="rounded-lg px-4 py-2 text-sm font-medium transition
                            {{ $when === $key ? 'bg-indigo-500 text-white shadow-sm' : 'text-slate-600 hover:bg-indigo-50' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-100">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead class="bg-indigo-50/60">
                            <tr>
                                <th class="{{ $th }}">{{ $isHotel ? 'Room' : 'Unit' }}</th>
                                <th class="{{ $th }}">Block Type</th>
                                <th class="{{ $th }}">From</th>
                                <th class="{{ $th }}">To</th>
                                <th class="{{ $th }}">Nights</th>
                                <th class="{{ $th }}">State</th>
                                <th class="{{ $th }}">Reason</th>
                                <th class="{{ $th }}">Actions</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100">
                            @forelse ($blocks as $block)
                                @php
                                    $start = $block->start_date->toDateString();
                                    $end = $block->end_date->toDateString();

                                    if ($end < $today) {
                                        $state = ['Completed', 'bg-slate-100 text-slate-600 ring-slate-200'];
                                    } elseif ($start > $today) {
                                        $state = ['Upcoming', 'bg-sky-50 text-sky-700 ring-sky-200'];
                                    } else {
                                        $state = ['Active now', 'bg-emerald-50 text-emerald-700 ring-emerald-200'];
                                    }
                                @endphp

                                <tr class="transition hover:bg-indigo-50/40">
                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-3">
                                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-50 text-sm font-bold text-indigo-600">
                                                {{ strtoupper(mb_substr($block->unit?->name ?? '?', 0, 1)) }}
                                            </span>
                                            <span class="font-semibold text-slate-900">{{ $block->unit?->name ?? '-' }}</span>
                                        </div>
                                    </td>

                                    <td class="px-5 py-4">
                                        <span class="rounded-full px-2.5 py-1 text-xs font-medium ring-1 {{ $typeStyles[$block->block_type] ?? 'bg-slate-100 text-slate-600 ring-slate-200' }}">
                                            {{ \App\Models\InventoryBlock::types()[$block->block_type] ?? ucfirst($block->block_type) }}
                                        </span>
                                    </td>

                                    <td class="px-5 py-4 text-sm text-slate-700">{{ $block->start_date->format('d M Y') }}</td>
                                    <td class="px-5 py-4 text-sm text-slate-700">{{ $block->end_date->format('d M Y') }}</td>
                                    <td class="px-5 py-4 text-sm font-semibold text-slate-700">{{ $block->nights() }}</td>

                                    <td class="px-5 py-4">
                                        <span class="rounded-full px-2.5 py-1 text-xs font-medium ring-1 {{ $state[1] }}">{{ $state[0] }}</span>
                                    </td>

                                    <td class="px-5 py-4 text-sm text-slate-600">
                                        <div class="max-w-xs truncate">{{ $block->reason ?: '-' }}</div>
                                    </td>

                                    <td class="px-5 py-4">
                                        <form method="POST" action="{{ route('company.blocks.destroy', $block) }}"
                                            onsubmit="return confirm('Remove this block? The dates will become available again.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="rounded-lg bg-rose-50 px-3 py-1.5 text-sm font-medium text-rose-600 transition hover:bg-rose-100">
                                                Remove
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-5 py-16 text-center">
                                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-violet-50 text-violet-400">
                                            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3M4 11h16M5 5h14a1 1 0 011 1v14a1 1 0 01-1 1H5a1 1 0 01-1-1V6a1 1 0 011-1z" />
                                            </svg>
                                        </div>
                                        <p class="mt-3 text-sm font-medium text-slate-700">No blocks here.</p>
                                        <a href="{{ route('company.blocks.create', $property) }}"
                                            class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">+ Add a block</a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div>
                {{ $blocks->links() }}
            </div>

        </div>
    </div>
</x-app-layout>