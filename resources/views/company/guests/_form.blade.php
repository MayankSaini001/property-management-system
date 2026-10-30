{{-- Shared fields for Add / Edit Guest. Needs $guest --}}
@php
    $input = 'w-full rounded-xl border-slate-200 bg-slate-50 text-sm text-slate-700 shadow-none focus:border-indigo-400 focus:bg-white focus:ring-2 focus:ring-indigo-100';
    $label = 'mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500';
    $error = 'mt-1 text-sm text-rose-600';
    $section = 'mb-4 text-sm font-semibold text-indigo-700';
@endphp

<div class="space-y-8">

    {{-- Personal details --}}
    <div>
        <h4 class="{{ $section }}">Personal details</h4>
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
            <div>
                <label for="first_name" class="{{ $label }}">First Name *</label>
                <input id="first_name" name="first_name" type="text" required value="{{ old('first_name', $guest->first_name) }}"
                    class="{{ $input }}" placeholder="e.g. Rahul">
                @error('first_name') <p class="{{ $error }}">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="last_name" class="{{ $label }}">Last Name *</label>
                <input id="last_name" name="last_name" type="text" required value="{{ old('last_name', $guest->last_name) }}"
                    class="{{ $input }}" placeholder="e.g. Sharma">
                @error('last_name') <p class="{{ $error }}">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="date_of_birth" class="{{ $label }}">Date of Birth</label>
                <input id="date_of_birth" name="date_of_birth" type="date"
                    value="{{ old('date_of_birth', optional($guest->date_of_birth)->format('Y-m-d')) }}"
                    max="{{ now()->subDay()->toDateString() }}" class="{{ $input }}">
                @error('date_of_birth') <p class="{{ $error }}">{{ $message }}</p> @enderror
            </div>
        </div>
    </div>

    {{-- Contact --}}
    <div>
        <h4 class="{{ $section }}">Contact</h4>
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
            <div>
                <label for="email" class="{{ $label }}">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email', $guest->email) }}"
                    class="{{ $input }}" placeholder="guest@example.com">
                @error('email') <p class="{{ $error }}">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="phone" class="{{ $label }}">Phone</label>
                <input id="phone" name="phone" type="text" value="{{ old('phone', $guest->phone) }}"
                    class="{{ $input }}" placeholder="+91 98765 43210">
                @error('phone') <p class="{{ $error }}">{{ $message }}</p> @enderror
            </div>
        </div>
        <p class="mt-2 text-xs text-slate-500">Enter at least an email or a phone number.</p>
    </div>

    {{-- Address --}}
    <div>
        <h4 class="{{ $section }}">Address</h4>
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label for="address" class="{{ $label }}">Street Address</label>
                <input id="address" name="address" type="text" value="{{ old('address', $guest->address) }}"
                    class="{{ $input }}">
                @error('address') <p class="{{ $error }}">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="city" class="{{ $label }}">City</label>
                <input id="city" name="city" type="text" value="{{ old('city', $guest->city) }}" class="{{ $input }}">
                @error('city') <p class="{{ $error }}">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="state" class="{{ $label }}">State / Region</label>
                <input id="state" name="state" type="text" value="{{ old('state', $guest->state) }}" class="{{ $input }}">
                @error('state') <p class="{{ $error }}">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="country_code" class="{{ $label }}">Country</label>
                <select id="country_code" name="country_code" class="{{ $input }}">
                    <option value="">- Select country -</option>
                    @foreach (\App\Models\Property::countries() as $code => $name)
                        <option value="{{ $code }}" @selected(old('country_code', $guest->country_code) === $code)>{{ $name }}</option>
                    @endforeach
                </select>
                @error('country_code') <p class="{{ $error }}">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="postal_code" class="{{ $label }}">Postal Code</label>
                <input id="postal_code" name="postal_code" type="text" value="{{ old('postal_code', $guest->postal_code) }}"
                    class="{{ $input }}">
                @error('postal_code') <p class="{{ $error }}">{{ $message }}</p> @enderror
            </div>
        </div>
    </div>

    {{-- Preferences and notes --}}
    <div>
        <h4 class="{{ $section }}">Preferences and notes</h4>
        <div class="grid grid-cols-1 gap-5">
            <div>
                <label for="preferences" class="{{ $label }}">Guest Preferences</label>
                <textarea id="preferences" name="preferences" rows="3" class="{{ $input }}"
                    placeholder="e.g. Ground floor room, vegetarian meals, late check-in">{{ old('preferences', $guest->preferences) }}</textarea>
                @error('preferences') <p class="{{ $error }}">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="notes" class="{{ $label }}">Internal Notes</label>
                <textarea id="notes" name="notes" rows="3" class="{{ $input }}"
                    placeholder="Notes visible only to your staff">{{ old('notes', $guest->notes) }}</textarea>
                @error('notes') <p class="{{ $error }}">{{ $message }}</p> @enderror
            </div>
        </div>
    </div>

</div>