{{-- Shared fields for New / Edit Rate Plan. Needs $ratePlan. $properties is used only when creating. --}}
@php
    $input = 'w-full rounded-xl border-slate-200 bg-slate-50 text-sm text-slate-700 shadow-none focus:border-indigo-400 focus:bg-white focus:ring-2 focus:ring-indigo-100';
    $label = 'mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500';
    $error = 'mt-1 text-sm text-rose-600';
    $creating = isset($properties);
@endphp

<div class="grid grid-cols-1 gap-5 sm:grid-cols-2">

    @if ($creating)
        <div class="sm:col-span-2">
            <label for="property_id" class="{{ $label }}">Property *</label>
            <select id="property_id" name="property_id" required class="{{ $input }}">
                <option value="">Select property</option>
                @foreach ($properties as $property)
                    <option value="{{ $property->id }}" @selected((string) old('property_id', $ratePlan->property_id) === (string) $property->id)>
                        {{ $property->name }} ({{ \App\Models\Property::types()[$property->type] ?? $property->type }}, {{ $property->currency }})
                    </option>
                @endforeach
            </select>
            @error('property_id') <p class="{{ $error }}">{{ $message }}</p> @enderror
        </div>
    @endif

    <div>
        <label for="name" class="{{ $label }}">Rate Plan Name *</label>
        <input id="name" name="name" type="text" required value="{{ old('name', $ratePlan->name) }}"
            class="{{ $input }}" placeholder="e.g. Standard Rate">
        @error('name') <p class="{{ $error }}">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="meal_plan" class="{{ $label }}">Meal Plan *</label>
        <select id="meal_plan" name="meal_plan" required class="{{ $input }}">
            @foreach (\App\Models\RatePlan::mealPlans() as $value => $text)
                <option value="{{ $value }}" @selected(old('meal_plan', $ratePlan->meal_plan) === $value)>{{ $text }}</option>
            @endforeach
        </select>
        @error('meal_plan') <p class="{{ $error }}">{{ $message }}</p> @enderror
    </div>

    <div class="sm:col-span-2">
        <label for="description" class="{{ $label }}">Description</label>
        <textarea id="description" name="description" rows="2" class="{{ $input }}"
            placeholder="e.g. Standard price for all guests">{{ old('description', $ratePlan->description) }}</textarea>
        @error('description') <p class="{{ $error }}">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="status" class="{{ $label }}">Status *</label>
        <select id="status" name="status" required class="{{ $input }}">
            <option value="active" @selected(old('status', $ratePlan->status) === 'active')>Active</option>
            <option value="inactive" @selected(old('status', $ratePlan->status) === 'inactive')>Inactive</option>
        </select>
        @error('status') <p class="{{ $error }}">{{ $message }}</p> @enderror
    </div>

    <div class="flex items-end">
        @if ($ratePlan->exists && $ratePlan->is_default)
            <p class="rounded-xl bg-indigo-50 px-4 py-2.5 text-sm font-medium text-indigo-700 ring-1 ring-indigo-100">
                This is the default rate plan of the property.
            </p>
        @else
            <label class="inline-flex items-center gap-2 py-2.5">
                <input type="hidden" name="is_default" value="0">
                <input type="checkbox" name="is_default" value="1" @checked(old('is_default', $ratePlan->is_default))
                    class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                <span class="text-sm text-slate-700">Make this the default rate plan of the property</span>
            </label>
        @endif
    </div>

</div>