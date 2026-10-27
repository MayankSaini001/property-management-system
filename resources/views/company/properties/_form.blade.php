{{-- Shared fields for Add / Edit Property. Needs $property --}}
@php
    $input = 'w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500';
    $label = 'mb-1 block text-sm font-medium text-gray-700';
    $error = 'mt-1 text-sm text-red-600';
@endphp

<div class="grid grid-cols-1 gap-5 sm:grid-cols-2">

    <div class="sm:col-span-2">
        <label for="name" class="{{ $label }}">Property Name *</label>
        <input id="name" name="name" type="text" required value="{{ old('name', $property->name) }}"
            class="{{ $input }}" placeholder="e.g. Hotel Sunrise">
        @error('name') <p class="{{ $error }}">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="type" class="{{ $label }}">Property Type *</label>
        <select id="type" name="type" required class="{{ $input }}">
            @foreach (\App\Models\Property::types() as $value => $text)
                <option value="{{ $value }}" @selected(old('type', $property->type) === $value)>{{ $text }}</option>
            @endforeach
        </select>
        @error('type') <p class="{{ $error }}">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="status" class="{{ $label }}">Status *</label>
        <select id="status" name="status" required class="{{ $input }}">
            <option value="active" @selected(old('status', $property->status) === 'active')>Active</option>
            <option value="inactive" @selected(old('status', $property->status) === 'inactive')>Inactive</option>
        </select>
        @error('status') <p class="{{ $error }}">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="email" class="{{ $label }}">Email</label>
        <input id="email" name="email" type="email" value="{{ old('email', $property->email) }}"
            class="{{ $input }}" placeholder="property@example.com">
        @error('email') <p class="{{ $error }}">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="phone" class="{{ $label }}">Phone</label>
        <input id="phone" name="phone" type="text" value="{{ old('phone', $property->phone) }}"
            class="{{ $input }}" placeholder="Property phone number">
        @error('phone') <p class="{{ $error }}">{{ $message }}</p> @enderror
    </div>

    <div class="sm:col-span-2">
        <label for="address" class="{{ $label }}">Address</label>
        <input id="address" name="address" type="text" value="{{ old('address', $property->address) }}"
            class="{{ $input }}" placeholder="Street address">
        @error('address') <p class="{{ $error }}">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="city" class="{{ $label }}">City</label>
        <input id="city" name="city" type="text" value="{{ old('city', $property->city) }}"
            class="{{ $input }}">
        @error('city') <p class="{{ $error }}">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="state" class="{{ $label }}">State / Region</label>
        <input id="state" name="state" type="text" value="{{ old('state', $property->state) }}"
            class="{{ $input }}">
        @error('state') <p class="{{ $error }}">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="country_code" class="{{ $label }}">Country</label>
        <select id="country_code" name="country_code" class="{{ $input }}">
            <option value="">— Select country —</option>
            @foreach (\App\Models\Property::countries() as $code => $name)
                <option value="{{ $code }}" @selected(old('country_code', $property->country_code) === $code)>{{ $name }}</option>
            @endforeach
        </select>
        @error('country_code') <p class="{{ $error }}">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="postal_code" class="{{ $label }}">Postal Code</label>
        <input id="postal_code" name="postal_code" type="text" value="{{ old('postal_code', $property->postal_code) }}"
            class="{{ $input }}">
        @error('postal_code') <p class="{{ $error }}">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="timezone" class="{{ $label }}">Timezone *</label>
        <select id="timezone" name="timezone" required class="{{ $input }}">
            @foreach (\App\Models\Property::timezones() as $value => $text)
                <option value="{{ $value }}" @selected(old('timezone', $property->timezone) === $value)>{{ $text }}</option>
            @endforeach
        </select>
        @error('timezone') <p class="{{ $error }}">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="currency" class="{{ $label }}">Currency *</label>
        <select id="currency" name="currency" required class="{{ $input }}">
            @foreach (\App\Models\Property::currencies() as $code => $text)
                <option value="{{ $code }}" @selected(old('currency', $property->currency) === $code)>{{ $text }}</option>
            @endforeach
        </select>
        @error('currency') <p class="{{ $error }}">{{ $message }}</p> @enderror
    </div>

</div>