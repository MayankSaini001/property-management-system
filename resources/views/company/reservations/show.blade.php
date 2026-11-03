@php
    $statusStyles = [
        'confirmed' => 'bg-sky-50 text-sky-700 ring-sky-200',
        'checked_in' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'checked_out' => 'bg-slate-100 text-slate-600 ring-slate-200',
        'cancelled' => 'bg-rose-50 text-rose-700 ring-rose-200',
        'no_show' => 'bg-amber-50 text-amber-700 ring-amber-200',
    ];
    $th = 'px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-indigo-900/60';
    $dt = 'text-xs uppercase tracking-wide text-slate-400';
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Reservation {{ $reservation->booking_reference }}</h2>
    </x-slot>

    <div class="min-h-[75vh] bg-gradient-to-b from-indigo-50 via-slate-50 to-white py-10">
        <div class="w-full space-y-6 px-6 lg:px-8">

            <a href="{{ route('company.reservations.index') }}"
                class="text-sm font-medium text-indigo-600 hover:text-indigo-800">&larr; Back to Reservations</a>

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

            {{-- Header --}}
            <div class="flex flex-wrap items-center justify-between gap-4 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Booking reference</p>
                    <h3 class="text-2xl font-bold text-slate-900">{{ $reservation->booking_reference }}</h3>
                    <p class="mt-1 text-sm text-slate-500">
                        Created {{ $reservation->created_at->format('d M Y, H:i') }}
                        @if ($reservation->creator)
                            by {{ $reservation->creator->name }}
                        @endif
                    </p>
                </div>

                <span class="rounded-full px-4 py-1.5 text-sm font-semibold ring-1 {{ $statusStyles[$reservation->status] ?? 'bg-slate-100 text-slate-600 ring-slate-200' }}">
                    {{ \App\Models\Reservation::statuses()[$reservation->status] ?? ucfirst($reservation->status) }}
                </span>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

                {{-- Left: stay, guest, property --}}
                <div class="space-y-6 lg:col-span-1">
                    <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
                        <h4 class="text-sm font-semibold text-indigo-700">Stay</h4>
                        <dl class="mt-4 space-y-3 text-sm">
                            <div>
                                <dt class="{{ $dt }}">Check-in</dt>
                                <dd class="text-slate-800">{{ $reservation->check_in->format('l, d M Y') }}</dd>
                            </div>
                            <div>
                                <dt class="{{ $dt }}">Check-out</dt>
                                <dd class="text-slate-800">{{ $reservation->check_out->format('l, d M Y') }}</dd>
                            </div>
                            <div>
                                <dt class="{{ $dt }}">Nights</dt>
                                <dd class="text-slate-800">{{ $reservation->nights() }}</dd>
                            </div>
                            <div>
                                <dt class="{{ $dt }}">Guests</dt>
                                <dd class="text-slate-800">
                                    {{ $reservation->adults }} adult{{ $reservation->adults > 1 ? 's' : '' }}
                                    @if ($reservation->children > 0)
                                        + {{ $reservation->children }} child{{ $reservation->children > 1 ? 'ren' : '' }}
                                    @endif
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
                        <h4 class="text-sm font-semibold text-indigo-700">Guest</h4>
                        <dl class="mt-4 space-y-3 text-sm">
                            <div>
                                <dt class="{{ $dt }}">Name</dt>
                                <dd class="text-slate-800">
                                    @if ($reservation->guest && ! $reservation->guest->trashed())
                                        <a href="{{ route('company.guests.show', $reservation->guest) }}"
                                            class="font-semibold text-indigo-600 hover:text-indigo-800">{{ $reservation->guest->fullName() }}</a>
                                    @else
                                        {{ $reservation->guest?->fullName() ?? 'Deleted guest' }}
                                    @endif
                                </dd>
                            </div>
                            <div>
                                <dt class="{{ $dt }}">Email</dt>
                                <dd class="text-slate-800">{{ $reservation->guest?->email ?: '-' }}</dd>
                            </div>
                            <div>
                                <dt class="{{ $dt }}">Phone</dt>
                                <dd class="text-slate-800">{{ $reservation->guest?->phone ?: '-' }}</dd>
                            </div>
                        </dl>
                    </div>

                    <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
                        <h4 class="text-sm font-semibold text-indigo-700">Booking</h4>
                        <dl class="mt-4 space-y-3 text-sm">
                            <div>
                                <dt class="{{ $dt }}">Property</dt>
                                <dd class="text-slate-800">{{ $reservation->property?->name }}</dd>
                            </div>
                            <div>
                                <dt class="{{ $dt }}">Source</dt>
                                <dd class="text-slate-800">{{ \App\Models\Reservation::sources()[$reservation->source] ?? ucfirst($reservation->source) }}</dd>
                            </div>
                            <div>
                                <dt class="{{ $dt }}">Total amount</dt>
                                <dd class="text-slate-800">{{ $reservation->currency }} {{ number_format((float) $reservation->total_amount, 2) }}
                                    <span class="text-xs text-slate-400">(pricing comes later)</span></dd>
                            </div>
                        </dl>
                    </div>
                </div>

                {{-- Right: rooms, notes, cancel --}}
                <div class="space-y-6 lg:col-span-2">
                    <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-100">
                        <div class="px-6 pt-6">
                            <h4 class="text-sm font-semibold text-indigo-700">Rooms</h4>
                        </div>
                        <div class="mt-4 overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-100">
                                <thead class="bg-indigo-50/60">
                                    <tr>
                                        <th class="{{ $th }}">Room / Unit</th>
                                        <th class="{{ $th }}">Room Type</th>
                                        <th class="{{ $th }}">Dates</th>
                                        <th class="{{ $th }}">Guests</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach ($reservation->items as $item)
                                        <tr>
                                            <td class="px-5 py-4 font-semibold text-slate-900">{{ $item->unit?->name }}</td>
                                            <td class="px-5 py-4 text-sm text-slate-600">{{ $item->unit?->roomType?->name ?? '-' }}</td>
                                            <td class="px-5 py-4 text-sm text-slate-600">
                                                {{ $item->check_in->format('d M') }} - {{ $item->check_out->format('d M Y') }}
                                            </td>
                                            <td class="px-5 py-4 text-sm text-slate-600">
                                                {{ $item->adults }}A + {{ $item->children }}C
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="rounded-2xl bg-amber-50/60 p-6 ring-1 ring-amber-100">
                        <h4 class="text-sm font-semibold text-amber-700">Notes</h4>
                        <p class="mt-3 whitespace-pre-line text-sm text-slate-700">{{ $reservation->notes ?: 'No notes added.' }}</p>
                    </div>

                    @if ($reservation->status === 'cancelled')
                        <div class="rounded-2xl bg-rose-50/70 p-6 ring-1 ring-rose-100">
                            <h4 class="text-sm font-semibold text-rose-700">Cancelled</h4>
                            <p class="mt-2 text-sm text-slate-700">
                                Cancelled on {{ $reservation->cancelled_at?->format('d M Y, H:i') }}.
                            </p>
                            <p class="mt-1 text-sm text-slate-600">
                                Reason: {{ $reservation->cancellation_reason ?: 'Not given' }}
                            </p>
                        </div>
                    @endif

                    @if ($reservation->status === 'no_show')
                        <div class="rounded-2xl bg-amber-50/70 p-6 ring-1 ring-amber-100">
                            <h4 class="text-sm font-semibold text-amber-700">No Show</h4>
                            <p class="mt-2 text-sm text-slate-700">
                                Marked as no-show on {{ $reservation->no_show_at?->format('d M Y, H:i') }}. The rooms were released.
                            </p>
                        </div>
                    @endif

                    {{-- Front desk actions --}}
                    @if ($reservation->canCheckIn())
                        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
                            <h4 class="text-sm font-semibold text-indigo-700">Front desk</h4>
                            <p class="mt-1 text-sm text-slate-500">Check the guest in when they arrive, or mark the booking as no-show if they do not come.</p>

                            <div class="mt-4 flex flex-wrap gap-3">
                                <form method="POST" action="{{ route('company.reservations.check-in', $reservation) }}"
                                    onsubmit="return confirm('Check this guest in?')">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                        class="rounded-xl bg-emerald-50 px-5 py-2.5 text-sm font-semibold text-emerald-700 ring-1 ring-emerald-100 transition hover:bg-emerald-100">
                                        Check In
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('company.reservations.no-show', $reservation) }}"
                                    onsubmit="return confirm('Mark this reservation as no-show? The rooms will be released.')">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                        class="rounded-xl bg-amber-50 px-5 py-2.5 text-sm font-semibold text-amber-700 ring-1 ring-amber-100 transition hover:bg-amber-100">
                                        Mark as No-show
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endif

                    @if ($reservation->canCheckOut())
                        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
                            <h4 class="text-sm font-semibold text-indigo-700">Front desk</h4>
                            <p class="mt-1 text-sm text-slate-500">
                                Guest checked in on {{ $reservation->checked_in_at?->format('d M Y, H:i') }}.
                                Check the guest out when they leave.
                            </p>

                            <form method="POST" action="{{ route('company.reservations.check-out', $reservation) }}" class="mt-4"
                                onsubmit="return confirm('Check this guest out?')">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                    class="rounded-xl bg-indigo-50 px-5 py-2.5 text-sm font-semibold text-indigo-700 ring-1 ring-indigo-100 transition hover:bg-indigo-100">
                                    Check Out
                                </button>
                            </form>
                        </div>
                    @endif

                    @if ($reservation->status === 'checked_out')
                        <div class="rounded-2xl bg-slate-50 p-6 ring-1 ring-slate-200">
                            <h4 class="text-sm font-semibold text-slate-700">Stay completed</h4>
                            <p class="mt-2 text-sm text-slate-600">
                                Checked in {{ $reservation->checked_in_at?->format('d M Y, H:i') }}
                                and checked out {{ $reservation->checked_out_at?->format('d M Y, H:i') }}.
                            </p>
                        </div>
                    @endif

                    @if ($reservation->isCancellable())
                        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
                            <h4 class="text-sm font-semibold text-rose-700">Cancel reservation</h4>
                            <p class="mt-1 text-sm text-slate-500">The rooms become available again for these dates.</p>

                            <form method="POST" action="{{ route('company.reservations.cancel', $reservation) }}" class="mt-4 space-y-3"
                                onsubmit="return confirm('Cancel this reservation?')">
                                @csrf
                                @method('PATCH')

                                <textarea name="cancellation_reason" rows="2" placeholder="Reason (optional)"
                                    class="w-full rounded-xl border-slate-200 bg-slate-50 text-sm text-slate-700 shadow-none focus:border-rose-300 focus:bg-white focus:ring-2 focus:ring-rose-100">{{ old('cancellation_reason') }}</textarea>

                                <button type="submit"
                                    class="rounded-xl bg-rose-50 px-5 py-2.5 text-sm font-semibold text-rose-600 ring-1 ring-rose-100 transition hover:bg-rose-100">
                                    Cancel Reservation
                                </button>
                            </form>
                        </div>
                    @endif

                    {{-- Status history --}}
                    <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
                        <h4 class="text-sm font-semibold text-indigo-700">Status history</h4>

                        <ol class="mt-5 space-y-5">
                            @forelse ($reservation->statusHistories as $history)
                                <li class="flex gap-4">
                                    <span class="mt-1 h-3 w-3 shrink-0 rounded-full ring-4
                                        {{ match ($history->new_status) {
                                            'confirmed' => 'bg-sky-500 ring-sky-100',
                                            'checked_in' => 'bg-emerald-500 ring-emerald-100',
                                            'checked_out' => 'bg-slate-400 ring-slate-100',
                                            'cancelled' => 'bg-rose-500 ring-rose-100',
                                            'no_show' => 'bg-amber-500 ring-amber-100',
                                            default => 'bg-slate-400 ring-slate-100',
                                        } }}"></span>

                                    <div>
                                        <p class="text-sm font-semibold text-slate-800">
                                            @if ($history->old_status)
                                                {{ \App\Models\Reservation::statuses()[$history->old_status] ?? $history->old_status }}
                                                &rarr;
                                            @endif
                                            {{ \App\Models\Reservation::statuses()[$history->new_status] ?? $history->new_status }}
                                        </p>
                                        <p class="text-xs text-slate-500">
                                            {{ $history->created_at->format('d M Y, H:i') }}
                                            @if ($history->user)
                                                by {{ $history->user->name }}
                                            @endif
                                        </p>
                                        @if ($history->note)
                                            <p class="mt-1 text-sm text-slate-600">{{ $history->note }}</p>
                                        @endif
                                    </div>
                                </li>
                            @empty
                                <li class="text-sm text-slate-500">No history yet.</li>
                            @endforelse
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>