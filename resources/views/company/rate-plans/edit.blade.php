@php
    $th = 'px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-indigo-900/60';
    $priceInput = 'w-36 rounded-xl border-slate-200 bg-slate-50 text-sm text-slate-700 shadow-none focus:border-indigo-400 focus:bg-white focus:ring-2 focus:ring-indigo-100';
    $weekendNames = collect(\App\Models\RatePlan::WEEKEND_DAYS)->map(function ($day) {
        return [0 => 'Sunday', 1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday'][$day];
    })->implode(' and ');
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Rate Plan - {{ $ratePlan->name }}</h2>
    </x-slot>

    <div class="min-h-[75vh] bg-gradient-to-b from-indigo-50 via-slate-50 to-white py-10">
        <div class="mx-auto max-w-5xl space-y-6 px-6">

            <a href="{{ route('company.rate-plans.index') }}"
                class="text-sm font-medium text-indigo-600 hover:text-indigo-800">&larr; Back to Rate Plans</a>

            @if (session('success'))
                <div class="flex items-center gap-2 rounded-xl bg-emerald-50 px-4 py-3 text-sm text-emerald-800 ring-1 ring-emerald-100">
                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="rounded-xl bg-rose-50 px-4 py-3 text-sm text-rose-800 ring-1 ring-rose-100">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $message)
                            <li>{{ $message }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Header --}}
            <div class="flex flex-wrap items-center justify-between gap-4 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="text-xl font-semibold text-slate-900">{{ $ratePlan->name }}</h3>
                        @if ($ratePlan->is_default)
                            <span class="rounded-full bg-indigo-50 px-2 py-0.5 text-xs font-medium text-indigo-700 ring-1 ring-indigo-200">Default</span>
                        @endif
                    </div>
                    <p class="text-sm text-slate-500">
                        {{ $property->name }} ({{ \App\Models\Property::types()[$property->type] ?? $property->type }}) - prices in {{ $property->currency }}
                    </p>
                </div>

                <span class="rounded-full px-3 py-1 text-sm font-medium ring-1
                    {{ $ratePlan->isActive() ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : 'bg-slate-100 text-slate-600 ring-slate-200' }}">
                    {{ ucfirst($ratePlan->status) }}
                </span>
            </div>

            {{-- Base prices --}}
            <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-100">
                <div class="px-6 pt-6">
                    <h4 class="text-sm font-semibold text-indigo-700">
                        Base prices per night ({{ $isHotel ? 'per room type' : 'per villa unit' }})
                    </h4>
                    <p class="mt-1 text-sm text-slate-500">
                        The weekend price is used for {{ $weekendNames }} nights. Leave both prices empty if this plan should not be used for that {{ $isHotel ? 'room type' : 'unit' }}.
                    </p>
                </div>

                @if ($targets->isEmpty())
                    <div class="px-6 py-12 text-center">
                        <p class="text-sm font-medium text-slate-700">
                            {{ $isHotel ? 'This hotel has no room types yet.' : 'This villa has no units yet.' }}
                        </p>
                        <a href="{{ $isHotel ? route('company.room-types.create', $property) : route('company.units.create', $property) }}"
                            class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">
                            {{ $isHotel ? '+ Add a room type' : '+ Add a villa unit' }}
                        </a>
                    </div>
                @else
                    <form method="POST" action="{{ route('company.rate-plans.prices', $ratePlan) }}">
                        @csrf
                        @method('PUT')

                        <div class="mt-4 overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-100">
                                <thead class="bg-indigo-50/60">
                                    <tr>
                                        <th class="{{ $th }}">{{ $isHotel ? 'Room Type' : 'Villa Unit' }}</th>
                                        <th class="{{ $th }}">Capacity</th>
                                        <th class="{{ $th }}">Weekday ({{ $property->currency }})</th>
                                        <th class="{{ $th }}">Weekend ({{ $property->currency }})</th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-slate-100">
                                    @foreach ($targets as $target)
                                        @php $price = $prices->get($target->id); @endphp

                                        <tr>
                                            <td class="px-5 py-4 font-semibold text-slate-900">{{ $target->name }}</td>
                                            <td class="px-5 py-4 text-sm text-slate-600">{{ $target->capacity }}</td>
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
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="flex items-center justify-between border-t border-slate-100 px-6 py-5">
                            <p class="text-xs text-slate-400">Same price for every day? Enter the same number in both columns.</p>

                            <button type="submit"
                                class="rounded-xl bg-gradient-to-r from-indigo-500 to-violet-500 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:from-indigo-600 hover:to-violet-600">
                                Save Prices
                            </button>
                        </div>
                    </form>
                @endif
            </div>

            {{-- Details --}}
            <div class="rounded-2xl bg-white p-8 shadow-sm ring-1 ring-slate-100">
                <h4 class="mb-5 text-sm font-semibold text-indigo-700">Rate plan details</h4>

                <form method="POST" action="{{ route('company.rate-plans.update', $ratePlan) }}">
                    @csrf
                    @method('PUT')

                    @include('company.rate-plans._form')

                    <div class="mt-8 flex items-center justify-end border-t border-slate-100 pt-6">
                        <button type="submit"
                            class="rounded-xl bg-indigo-50 px-6 py-2.5 text-sm font-semibold text-indigo-700 ring-1 ring-indigo-100 transition hover:bg-indigo-100">
                            Update Details
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>