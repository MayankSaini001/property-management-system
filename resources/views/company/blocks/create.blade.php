@php
    $input = 'w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500';
    $label = 'mb-1 block text-sm font-medium text-gray-700';
    $error = 'mt-1 text-sm text-red-600';
    $isHotel = $property->type === 'hotel';
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Add Block - {{ $property->name }}</h2>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-3xl px-6">
            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100">

                <form method="POST" action="{{ route('company.blocks.store', $property) }}">
                    @csrf

                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">

                        <div>
                            <label for="unit_id" class="{{ $label }}">{{ $isHotel ? 'Room *' : 'Villa Unit *' }}</label>
                            <select id="unit_id" name="unit_id" required class="{{ $input }}">
                                <option value="">- Select -</option>
                                @foreach ($units as $unit)
                                    <option value="{{ $unit->id }}" @selected((int) old('unit_id', $block->unit_id) === $unit->id)>
                                        {{ $unit->name }}{{ $unit->roomType ? ' (' . $unit->roomType->name . ')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('unit_id') <p class="{{ $error }}">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="block_type" class="{{ $label }}">Block Type *</label>
                            <select id="block_type" name="block_type" required class="{{ $input }}">
                                @foreach (\App\Models\InventoryBlock::types() as $value => $text)
                                    <option value="{{ $value }}" @selected(old('block_type', $block->block_type) === $value)>{{ $text }}</option>
                                @endforeach
                            </select>
                            @error('block_type') <p class="{{ $error }}">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="start_date" class="{{ $label }}">From Date *</label>
                            <input id="start_date" name="start_date" type="date" required
                                min="{{ now($property->timezone)->toDateString() }}"
                                value="{{ old('start_date') }}" class="{{ $input }}">
                            @error('start_date') <p class="{{ $error }}">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="end_date" class="{{ $label }}">To Date (included) *</label>
                            <input id="end_date" name="end_date" type="date" required
                                min="{{ now($property->timezone)->toDateString() }}"
                                value="{{ old('end_date') }}" class="{{ $input }}">
                            <p class="mt-1 text-xs text-gray-500">The night of this date is also blocked. Check-in is possible from the next day.</p>
                            @error('end_date') <p class="{{ $error }}">{{ $message }}</p> @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label for="reason" class="{{ $label }}">Reason</label>
                            <textarea id="reason" name="reason" rows="3" class="{{ $input }}"
                                placeholder="e.g. AC repair, painting, owner stay">{{ old('reason') }}</textarea>
                            @error('reason') <p class="{{ $error }}">{{ $message }}</p> @enderror
                        </div>

                    </div>

                    <div class="mt-8 flex items-center justify-between">
                        <a href="{{ route('company.blocks.index', $property) }}"
                            class="text-sm font-medium text-gray-600 underline hover:text-gray-900">
                            Cancel
                        </a>

                        <button type="submit"
                            class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow hover:bg-indigo-700">
                            Save Block
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>