@php
    $field = 'rounded-xl border-slate-200 bg-slate-50 text-sm text-slate-700 shadow-none focus:border-indigo-400 focus:bg-white focus:ring-2 focus:ring-indigo-100';
    $th = 'px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-indigo-900/60';
    $softBtn = 'rounded-lg px-3 py-1.5 text-sm font-medium transition';
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Rates and Pricing</h2>
    </x-slot>

    <div class="min-h-[75vh] bg-gradient-to-b from-indigo-50 via-slate-50 to-white py-10">
        <div class="w-full space-y-6 px-6 lg:px-8">

            {{-- Page heading --}}
            <div class="flex flex-wrap items-center justify-between gap-4 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
                <div class="flex items-center gap-4">
                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </span>
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900">Rate Plans</h3>
                        <p class="text-sm text-slate-500">Prices of rooms and villas for {{ auth()->user()->company->name }}</p>
                    </div>
                </div>

                <a href="{{ route('company.rate-plans.create') }}"
                    class="rounded-xl bg-gradient-to-r from-indigo-500 to-violet-500 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:from-indigo-600 hover:to-violet-600">
                    + New Rate Plan
                </a>
            </div>

            @if (session('success'))
                <div class="flex items-center gap-2 rounded-xl bg-emerald-50 px-4 py-3 text-sm text-emerald-800 ring-1 ring-emerald-100">
                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="rounded-xl bg-rose-50 px-4 py-3 text-sm text-rose-800 ring-1 ring-rose-100">
                    {{ $errors->first() }}
                </div>
            @endif

            {{-- Summary cards --}}
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-100">
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Rate Plans</p>
                    <p class="mt-2 text-3xl font-bold text-indigo-600">{{ $stats['total'] }}</p>
                </div>
                <div class="rounded-2xl bg-emerald-50/70 p-5 ring-1 ring-emerald-100">
                    <p class="text-xs font-medium uppercase tracking-wide text-emerald-700/80">Active</p>
                    <p class="mt-2 text-3xl font-bold text-emerald-600">{{ $stats['active'] }}</p>
                </div>
                <div class="rounded-2xl bg-sky-50/70 p-5 ring-1 ring-sky-100">
                    <p class="text-xs font-medium uppercase tracking-wide text-sky-700/80">Properties With Rates</p>
                    <p class="mt-2 text-3xl font-bold text-sky-600">{{ $stats['properties'] }}</p>
                </div>
            </div>

            {{-- Filter --}}
            <form method="GET" action="{{ route('company.rate-plans.index') }}"
                class="flex flex-wrap items-end gap-4 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-100">
                <div class="w-full sm:w-72">
                    <label for="property_id" class="mb-1.5 block text-xs font-medium text-slate-500">Property</label>
                    <select id="property_id" name="property_id" onchange="this.form.submit()" class="{{ $field }} w-full">
                        <option value="">All properties</option>
                        @foreach ($properties as $property)
                            <option value="{{ $property->id }}" @selected((int) request('property_id') === $property->id)>{{ $property->name }}</option>
                        @endforeach
                    </select>
                </div>

                @if (request()->filled('property_id'))
                    <a href="{{ route('company.rate-plans.index') }}"
                        class="rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-200">Clear</a>
                @endif
            </form>

            {{-- Rate plans --}}
            <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-100">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead class="bg-indigo-50/60">
                            <tr>
                                <th class="{{ $th }}">Rate Plan</th>
                                <th class="{{ $th }}">Property</th>
                                <th class="{{ $th }}">Meal Plan</th>
                                <th class="{{ $th }}">Prices Set</th>
                                <th class="{{ $th }}">Status</th>
                                <th class="{{ $th }}">Actions</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100">
                            @forelse ($ratePlans as $plan)
                                @php
                                    $isHotel = $plan->property?->type === 'hotel';
                                    $targetTotal = $isHotel ? $plan->property?->room_types_count : $plan->property?->units_count;
                                    $complete = $targetTotal > 0 && $plan->prices_count >= $targetTotal;
                                @endphp

                                <tr class="transition hover:bg-indigo-50/40 {{ $plan->isActive() ? '' : 'opacity-70' }}">
                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-2">
                                            <a href="{{ route('company.rate-plans.edit', $plan) }}"
                                                class="font-semibold text-slate-900 hover:text-indigo-700">{{ $plan->name }}</a>
                                            @if ($plan->is_default)
                                                <span class="rounded-full bg-indigo-50 px-2 py-0.5 text-xs font-medium text-indigo-700 ring-1 ring-indigo-200">Default</span>
                                            @endif
                                        </div>
                                        @if ($plan->description)
                                            <div class="max-w-xs truncate text-xs text-slate-500">{{ $plan->description }}</div>
                                        @endif
                                    </td>

                                    <td class="px-5 py-4 text-sm text-slate-700">
                                        {{ $plan->property?->name }}
                                        <div class="text-xs text-slate-400">{{ $plan->property?->currency }}</div>
                                    </td>

                                    <td class="px-5 py-4 text-sm text-slate-600">
                                        {{ \App\Models\RatePlan::mealPlans()[$plan->meal_plan] ?? $plan->meal_plan }}
                                    </td>

                                    <td class="px-5 py-4">
                                        <span class="rounded-full px-2.5 py-1 text-xs font-medium ring-1
                                            {{ $complete ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : 'bg-amber-50 text-amber-700 ring-amber-200' }}">
                                            {{ $plan->prices_count }} of {{ $targetTotal ?? 0 }}
                                            {{ $isHotel ? 'room types' : 'units' }}
                                        </span>
                                    </td>

                                    <td class="px-5 py-4">
                                        <span class="rounded-full px-2.5 py-1 text-xs font-medium ring-1
                                            {{ $plan->isActive() ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : 'bg-slate-100 text-slate-600 ring-slate-200' }}">
                                            {{ ucfirst($plan->status) }}
                                        </span>
                                    </td>

                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-2">
                                            <a href="{{ route('company.rate-plans.edit', $plan) }}"
                                                class="{{ $softBtn }} bg-indigo-50 text-indigo-700 hover:bg-indigo-100">Prices</a>

                                            <form method="POST" action="{{ route('company.rate-plans.status', $plan) }}"
                                                onsubmit="return confirm('{{ $plan->isActive() ? 'Deactivate' : 'Activate' }} this rate plan?')">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit"
                                                    class="{{ $softBtn }} {{ $plan->isActive() ? 'bg-rose-50 text-rose-600 hover:bg-rose-100' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }}">
                                                    {{ $plan->isActive() ? 'Deactivate' : 'Activate' }}
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-16 text-center">
                                        <p class="text-sm font-medium text-slate-700">No rate plans yet.</p>
                                        <a href="{{ route('company.rate-plans.create') }}"
                                            class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">+ Create your first rate plan</a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>