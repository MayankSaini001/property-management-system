{{-- Shared fields for Add / Edit Room Type. Needs $roomType --}}
@php
    $input = 'w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500';
    $label = 'mb-1 block text-sm font-medium text-gray-700';
    $error = 'mt-1 text-sm text-red-600';
@endphp

<div class="grid grid-cols-1 gap-5 sm:grid-cols-2">

    <div class="sm:col-span-2">
        <label for="name" class="{{ $label }}">Room Type Name *</label>
        <input id="name" name="name" type="text" required value="{{ old('name', $roomType->name) }}"
            class="{{ $input }}" placeholder="e.g. Deluxe Room">
        @error('name') <p class="{{ $error }}">{{ $message }}</p> @enderror
    </div>

    <div class="sm:col-span-2">
        <label for="description" class="{{ $label }}">Description</label>
        <textarea id="description" name="description" rows="3" class="{{ $input }}"
            placeholder="Short description of this room type">{{ old('description', $roomType->description) }}</textarea>
        @error('description') <p class="{{ $error }}">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="max_adults" class="{{ $label }}">Max Adults *</label>
        <input id="max_adults" name="max_adults" type="number" min="1" max="20" required
            value="{{ old('max_adults', $roomType->max_adults) }}" class="{{ $input }}">
        @error('max_adults') <p class="{{ $error }}">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="max_children" class="{{ $label }}">Max Children *</label>
        <input id="max_children" name="max_children" type="number" min="0" max="20" required
            value="{{ old('max_children', $roomType->max_children) }}" class="{{ $input }}">
        @error('max_children') <p class="{{ $error }}">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="capacity" class="{{ $label }}">Total Capacity *</label>
        <input id="capacity" name="capacity" type="number" min="1" max="40" required
            value="{{ old('capacity', $roomType->capacity) }}" class="{{ $input }}">
        <p class="mt-1 text-xs text-gray-500">Cannot be more than max adults plus max children.</p>
        @error('capacity') <p class="{{ $error }}">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="status" class="{{ $label }}">Status *</label>
        <select id="status" name="status" required class="{{ $input }}">
            <option value="active" @selected(old('status', $roomType->status) === 'active')>Active</option>
            <option value="inactive" @selected(old('status', $roomType->status) === 'inactive')>Inactive</option>
        </select>
        @error('status') <p class="{{ $error }}">{{ $message }}</p> @enderror
    </div>

</div>