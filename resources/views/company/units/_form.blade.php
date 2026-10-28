{{-- Shared fields for Add / Edit Room or Villa Unit. Needs $property, $unit, $roomTypes --}}
@php
    $isHotel = $property->type === 'hotel';
    $input = 'w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500';
    $label = 'mb-1 block text-sm font-medium text-gray-700';
    $error = 'mt-1 text-sm text-red-600';
@endphp

<div class="grid grid-cols-1 gap-5 sm:grid-cols-2">

    <div class="{{ $isHotel ? '' : 'sm:col-span-2' }}">
        <label for="name" class="{{ $label }}">{{ $isHotel ? 'Room Number / Name *' : 'Villa Unit Name *' }}</label>
        <input id="name" name="name" type="text" required value="{{ old('name', $unit->name) }}"
            class="{{ $input }}" placeholder="{{ $isHotel ? 'e.g. 101' : 'e.g. Villa Paradise' }}">
        @error('name') <p class="{{ $error }}">{{ $message }}</p> @enderror
    </div>

    @if ($isHotel)
        <div>
            <label for="room_type_id" class="{{ $label }}">Room Type *</label>
            <select id="room_type_id" name="room_type_id" required class="{{ $input }}">
                <option value="">- Select room type -</option>
                @foreach ($roomTypes as $roomType)
                    <option value="{{ $roomType->id }}"
                        @selected((int) old('room_type_id', $unit->room_type_id) === $roomType->id)>
                        {{ $roomType->name }} (capacity {{ $roomType->capacity }}){{ $roomType->isActive() ? '' : ' - inactive' }}
                    </option>
                @endforeach
            </select>
            <p class="mt-1 text-xs text-gray-500">Capacity and occupancy limits come from the room type.</p>
            @error('room_type_id') <p class="{{ $error }}">{{ $message }}</p> @enderror
        </div>
    @else
        <div>
            <label for="max_adults" class="{{ $label }}">Max Adults *</label>
            <input id="max_adults" name="max_adults" type="number" min="1" max="20" required
                value="{{ old('max_adults', $unit->max_adults) }}" class="{{ $input }}">
            @error('max_adults') <p class="{{ $error }}">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="max_children" class="{{ $label }}">Max Children *</label>
            <input id="max_children" name="max_children" type="number" min="0" max="20" required
                value="{{ old('max_children', $unit->max_children) }}" class="{{ $input }}">
            @error('max_children') <p class="{{ $error }}">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="capacity" class="{{ $label }}">Total Capacity *</label>
            <input id="capacity" name="capacity" type="number" min="1" max="40" required
                value="{{ old('capacity', $unit->capacity) }}" class="{{ $input }}">
            <p class="mt-1 text-xs text-gray-500">Cannot be more than max adults plus max children.</p>
            @error('capacity') <p class="{{ $error }}">{{ $message }}</p> @enderror
        </div>
    @endif

    <div>
        <label for="status" class="{{ $label }}">Status *</label>
        <select id="status" name="status" required class="{{ $input }}">
            @foreach (\App\Models\Unit::statuses() as $value => $text)
                <option value="{{ $value }}" @selected(old('status', $unit->status) === $value)>{{ $text }}</option>
            @endforeach
        </select>
        @error('status') <p class="{{ $error }}">{{ $message }}</p> @enderror
    </div>

    <div class="sm:col-span-2">
        <label for="notes" class="{{ $label }}">Notes</label>
        <textarea id="notes" name="notes" rows="3" class="{{ $input }}"
            placeholder="Internal notes (floor, view, special features...)">{{ old('notes', $unit->notes) }}</textarea>
        @error('notes') <p class="{{ $error }}">{{ $message }}</p> @enderror
    </div>

</div>