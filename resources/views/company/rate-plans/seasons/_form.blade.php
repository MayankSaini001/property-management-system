{{-- Shared fields for Add / Edit Season. Needs $ratePlan, $season, $property, $isHotel, $targets, $prices --}}
@php
    $input = 'w-full rounded-xl border-slate-200 bg-slate-50 text-sm text-slate-700 shadow-none focus:border-indigo-400 focus:bg-white focus:ring-2 focus:ring-indigo-100';
    $priceInput = 'w-36 rounded-xl border-slate-200 bg-slate-50 text-sm text-slate-700 shadow-none focus:border-indigo-400 focus:bg-white focus:ring-2 focus:ring-indigo-100';
    $label = 'mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500';
    $error = 'mt-1 text-sm text-rose-600';
    $th = 'px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-indigo-900/60';
@endphp

<div class="space-y-8">

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
        <div class="sm:col-span-3">
            <label for="name" class="{{ $label }}">Season Name *</label>
            <input id="name" name="name" type="text" required value="{{ old('name', $season->name) }}"
                class="{{ $input }}" placeholder="e.g. Diwali, Winter Holidays, Peak Season">
            @error('name') <p class="{{ $error }}">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="start_date" class="{{ $label }}">From Date *</label>
            <input id="start_date" name="start_date" type="date" required
                value="{{ old('start_date', optional($season->start_date)->format('Y-m-d')) }}" class="{{ $input }}">
            @error('start_date') <p class="{{ $error }}">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="end_date" class="{{ $label }}">To Date (included) *</label>
            <input id="end_date" name="end_date" type="date" required
                value="{{ old('end_date', optional($season->end_date)->format('Y-m-d')) }}" class="{{ $input }}">
            <p class="mt-1 text-xs text-slate-500">The night of this date also uses the season price.</p>
            @error('end_date') <p class="{{ $error }}">{{ $message }}</p> @enderror
        </div>
    </div>

    <div>
        <h4 class="text-sm font-semibold text-indigo-700">
            Season prices per night ({{ $isHotel ? 'per room type' : 'per villa unit' }}) in {{ $property->currency }}
        </h4>
        <p class="mt-1 text-sm text-slate-500">
            Leave both prices empty for a {{ $isHotel ? 'room type' : 'unit' }} that should keep its base price during this season.
        </p>

        @error('prices') <p class="mt-2 {{ $error }}">{{ $message }}</p> @enderror

        <div class="mt-4 overflow-x-auto rounded-xl ring-1 ring-slate-100">
            <table class="min-w-full divide-y divide-slate-100">
                <thead class="bg-indigo-50/60">
                    <tr>
                        <th class="{{ $th }}">{{ $isHotel ? 'Room Type' : 'Villa Unit' }}</th>
                        <th class="{{ $th }}">Weekday</th>
                        <th class="{{ $th }}">Weekend</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    @forelse ($targets as $target)
                        @php $price = $prices->get($target->id); @endphp
                        <tr>
                            <td class="px-5 py-4 font-semibold text-slate-900">{{ $target->name }}</td>
                            <td class="px-5 py-4">
                                <input type="number" step="0.01" min="0" max="9999999.99"
                                    name="prices[{{ $target->id }}][weekday]"
                                    value="{{ old('prices.' . $target->id . '.weekday', $price?->weekday_price) }}"
                                    class="{{ $priceInput }}" placeholder="0.00">
                            </td>
                            <td class="px-5 py-4">
                                <input type="number" step="0.01" min="0" max="9999999.99"
                                    name="prices[{{ $target->id }}][weekend]"
                                    value="{{ old('prices.' . $target->id . '.weekend', $price?->weekend_price) }}"
                                    class="{{ $priceInput }}" placeholder="0.00">
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-5 py-8 text-center text-sm text-slate-500">
                                {{ $isHotel ? 'This hotel has no room types yet.' : 'This villa has no units yet.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>