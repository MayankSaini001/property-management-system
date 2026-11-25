@php
    $field = 'rounded-xl border-slate-200 bg-slate-50 text-sm text-slate-700 shadow-none focus:border-indigo-400 focus:bg-white focus:ring-2 focus:ring-indigo-100';
    $th = 'px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-indigo-900/60';
    $filtersOn = request()->filled('q') || request()->filled('method') || request()->filled('status') || request()->filled('property_id');

    $money = function ($totals) {
        if ($totals->isEmpty()) {
            return '0.00';
        }

        return $totals->map(function ($amount, $currency) {
            return $currency . ' ' . number_format((float) $amount, 2);
        })->implode('  /  ');
    };
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Payments</h2>
    </x-slot>

    <div class="min-h-[75vh] bg-gradient-to-b from-indigo-50 via-slate-50 to-white py-10">
        <div class="w-full space-y-6 px-6 lg:px-8">

            {{-- Page heading --}}
            <div class="flex items-center gap-4 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
                <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                </span>
                <div>
                    <h3 class="text-lg font-semibold text-slate-900">All Payments</h3>
                    <p class="text-sm text-slate-500">Money received for the bookings of {{ auth()->user()->company->name }}. Add a payment from the reservation page.</p>
                </div>
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
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-100">
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Total Received</p>
                    <p class="mt-2 text-2xl font-bold text-indigo-600">{{ $money($stats['received']) }}</p>
                </div>
                <div class="rounded-2xl bg-emerald-50/70 p-5 ring-1 ring-emerald-100">
                    <p class="text-xs font-medium uppercase tracking-wide text-emerald-700/80">Received Today</p>
                    <p class="mt-2 text-2xl font-bold text-emerald-600">{{ $money($stats['today']) }}</p>
                </div>
                <div class="rounded-2xl bg-sky-50/70 p-5 ring-1 ring-sky-100">
                    <p class="text-xs font-medium uppercase tracking-wide text-sky-700/80">This Month</p>
                    <p class="mt-2 text-2xl font-bold text-sky-600">{{ $money($stats['month']) }}</p>
                </div>
                <div class="rounded-2xl bg-rose-50/70 p-5 ring-1 ring-rose-100">
                    <p class="text-xs font-medium uppercase tracking-wide text-rose-700/80">Voided Payments</p>
                    <p class="mt-2 text-2xl font-bold text-rose-600">{{ $stats['voided'] }}</p>
                </div>
            </div>

            {{-- Live search and filters --}}
            <form id="payment-filters" method="GET" action="{{ route('company.payments.index') }}"
                class="flex flex-wrap items-end gap-4 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-100">

                <div class="min-w-[220px] flex-1">
                    <label for="q" class="mb-1.5 block text-xs font-medium text-slate-500">Search</label>
                    <div class="relative">
                        <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
                            fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10.5a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z" />
                        </svg>
                        <input id="q" name="q" type="search" value="{{ request('q') }}" autocomplete="off"
                            placeholder="Receipt number, booking reference, guest or payment reference" class="{{ $field }} w-full pl-9">
                    </div>
                </div>

                <div class="w-full sm:w-48">
                    <label for="property_id" class="mb-1.5 block text-xs font-medium text-slate-500">Property</label>
                    <select id="property_id" name="property_id" class="{{ $field }} w-full">
                        <option value="">All properties</option>
                        @foreach ($properties as $property)
                            <option value="{{ $property->id }}" @selected((int) request('property_id') === $property->id)>{{ $property->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="w-full sm:w-44">
                    <label for="method" class="mb-1.5 block text-xs font-medium text-slate-500">Method</label>
                    <select id="method" name="method" class="{{ $field }} w-full">
                        <option value="">All methods</option>
                        @foreach (\App\Models\Payment::methods() as $value => $text)
                            <option value="{{ $value }}" @selected(request('method') === $value)>{{ $text }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="w-full sm:w-40">
                    <label for="status" class="mb-1.5 block text-xs font-medium text-slate-500">Status</label>
                    <select id="status" name="status" class="{{ $field }} w-full">
                        <option value="">All</option>
                        <option value="received" @selected(request('status') === 'received')>Received</option>
                        <option value="voided" @selected(request('status') === 'voided')>Voided</option>
                    </select>
                </div>

                <button type="button" id="clear-payment-filters"
                    class="rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-200">
                    Clear
                </button>

                <span id="payment-loading" class="hidden pb-2.5 text-xs font-medium text-indigo-500">Updating...</span>
            </form>

            {{-- Results (replaced live when filters change) --}}
            <div id="payment-results" class="transition-opacity duration-150">
                <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-100">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-100">
                            <thead class="bg-indigo-50/60">
                                <tr>
                                    <th class="{{ $th }}">Date</th>
                                    <th class="{{ $th }}">Receipt</th>
                                    <th class="{{ $th }}">Reservation</th>
                                    <th class="{{ $th }}">Method</th>
                                    <th class="{{ $th }} text-right">Amount</th>
                                    <th class="{{ $th }}">Status</th>
                                    <th class="{{ $th }}">Added By</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-slate-100">
                                @forelse ($payments as $payment)
                                    <tr class="transition hover:bg-indigo-50/40 {{ $payment->isVoided() ? 'opacity-60' : '' }}">
                                        <td class="px-5 py-4 text-sm text-slate-700">{{ $payment->paid_on->format('d M Y') }}</td>

                                        <td class="px-5 py-4 text-sm font-semibold text-slate-800">
                                            {{ $payment->receipt_number }}
                                            @if ($payment->reference)
                                                <div class="text-xs font-normal text-slate-400">Ref: {{ $payment->reference }}</div>
                                            @endif
                                        </td>

                                        <td class="px-5 py-4 text-sm">
                                            @if ($payment->reservation)
                                                <a href="{{ route('company.reservations.show', $payment->reservation) }}"
                                                    class="font-semibold text-indigo-600 hover:text-indigo-800">{{ $payment->reservation->booking_reference }}</a>
                                                <div class="text-xs text-slate-500">
                                                    {{ $payment->reservation->guest?->fullName() ?? 'Guest' }} - {{ $payment->property?->name }}
                                                </div>
                                            @else
                                                -
                                            @endif
                                        </td>

                                        <td class="px-5 py-4 text-sm text-slate-600">{{ \App\Models\Payment::methods()[$payment->method] ?? $payment->method }}</td>

                                        <td class="px-5 py-4 text-right text-sm font-bold text-slate-900 {{ $payment->isVoided() ? 'line-through' : '' }}">
                                            {{ $payment->currency }} {{ number_format((float) $payment->amount, 2) }}
                                        </td>

                                        <td class="px-5 py-4">
                                            @if ($payment->isVoided())
                                                <span class="rounded-full bg-rose-50 px-2.5 py-1 text-xs font-medium text-rose-700 ring-1 ring-rose-200"
                                                    title="{{ $payment->void_reason }}">Voided</span>
                                            @else
                                                <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700 ring-1 ring-emerald-200">Received</span>
                                            @endif
                                        </td>

                                        <td class="px-5 py-4 text-sm text-slate-600">{{ $payment->creator?->name ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-5 py-16 text-center">
                                            @if ($filtersOn)
                                                <p class="text-sm font-medium text-slate-700">No payments match your search.</p>
                                                <p class="text-sm text-slate-500">Try a different search or clear the filters.</p>
                                            @else
                                                <p class="text-sm font-medium text-slate-700">No payments yet.</p>
                                                <a href="{{ route('company.reservations.index') }}"
                                                    class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">Open a reservation to add a payment</a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-6">
                    {{ $payments->links() }}
                </div>
            </div>

        </div>
    </div>

    <script>
        (function () {
            const form = document.getElementById('payment-filters');
            const results = document.getElementById('payment-results');
            const loading = document.getElementById('payment-loading');
            const search = document.getElementById('q');
            let timer = null;
            let controller = null;

            function buildUrl() {
                const params = new URLSearchParams(new FormData(form));

                Array.from(params.keys()).forEach(function (key) {
                    if (!params.get(key)) {
                        params.delete(key);
                    }
                });

                const query = params.toString();

                return form.action + (query ? '?' + query : '');
            }

            function load() {
                const url = buildUrl();

                if (controller) {
                    controller.abort();
                }
                controller = new AbortController();

                results.classList.add('opacity-50');
                loading.classList.remove('hidden');

                fetch(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    signal: controller.signal,
                })
                    .then(function (response) { return response.text(); })
                    .then(function (html) {
                        const doc = new DOMParser().parseFromString(html, 'text/html');
                        const fresh = doc.getElementById('payment-results');

                        if (fresh) {
                            results.innerHTML = fresh.innerHTML;
                        }

                        history.replaceState(null, '', url);
                        results.classList.remove('opacity-50');
                        loading.classList.add('hidden');
                    })
                    .catch(function (error) {
                        if (error.name !== 'AbortError') {
                            window.location = url;
                        }
                    });
            }

            form.addEventListener('submit', function (event) {
                event.preventDefault();
                load();
            });

            form.querySelectorAll('select').forEach(function (select) {
                select.addEventListener('change', load);
            });

            search.addEventListener('input', function () {
                clearTimeout(timer);
                timer = setTimeout(load, 300);
            });

            document.getElementById('clear-payment-filters').addEventListener('click', function () {
                search.value = '';
                form.querySelectorAll('select').forEach(function (select) {
                    select.value = '';
                });
                load();
            });
        })();
    </script>
</x-app-layout>
