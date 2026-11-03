<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Guest;
use App\Models\Property;
use App\Models\Reservation;
use App\Models\ReservationItem;
use App\Models\Unit;
use App\Services\AvailabilityService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ReservationController extends Controller
{
    private const MAX_NIGHTS = 60;

    public function __construct(private AvailabilityService $availability)
    {
    }

    /**
     * List reservations of the logged-in Company Admin's company, with live search and filters.
     */
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Reservation::class);

        $companyId = $request->user()->company_id;

        $reservations = Reservation::where('company_id', $companyId)
            ->with(['guest', 'property', 'items.unit'])
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%' . trim($request->input('q')) . '%';

                $query->where(function ($inner) use ($term) {
                    $inner->where('booking_reference', 'like', $term)
                        ->orWhereHas('guest', function ($guest) use ($term) {
                            $guest->where('first_name', 'like', $term)
                                ->orWhere('last_name', 'like', $term)
                                ->orWhereRaw("CONCAT(first_name, ' ', last_name) like ?", [$term])
                                ->orWhere('phone', 'like', $term)
                                ->orWhere('email', 'like', $term);
                        });
                });
            })
            ->when(
                $request->filled('status') && array_key_exists($request->input('status'), Reservation::statuses()),
                function ($query) use ($request) {
                    $query->where('status', $request->input('status'));
                }
            )
            ->when($request->filled('property_id'), function ($query) use ($request) {
                $query->where('property_id', (int) $request->input('property_id'));
            })
            ->orderByDesc('check_in')
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        $properties = Property::where('company_id', $companyId)->orderBy('name')->get();

        $today = now()->toDateString();

        // Summary numbers for the cards (not affected by the filters).
        $stats = [
            'total' => Reservation::where('company_id', $companyId)->count(),
            'upcoming' => Reservation::where('company_id', $companyId)
                ->where('status', 'confirmed')
                ->whereDate('check_in', '>=', $today)
                ->count(),
            'in_house' => Reservation::where('company_id', $companyId)->where('status', 'checked_in')->count(),
            'cancelled' => Reservation::where('company_id', $companyId)->where('status', 'cancelled')->count(),
        ];

        return view('company.reservations.index', compact('reservations', 'properties', 'stats'));
    }

    /**
     * Show the New Reservation page: search form, then the available rooms to book.
     */
    public function create(Request $request): View
    {
        Gate::authorize('create', Reservation::class);

        $companyId = $request->user()->company_id;

        $properties = $request->user()
            ->company
            ->properties()
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $guests = Guest::where('company_id', $companyId)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->limit(500)
            ->get();

        $submitted = $request->filled('property_id')
            || $request->filled('check_in')
            || $request->filled('check_out');

        $search = null;
        $searchErrors = [];

        if ($submitted) {
            $property = $properties->firstWhere('id', (int) $request->input('property_id'));
            $today = now($property?->timezone ?? config('app.timezone'))->toDateString();

            $validator = Validator::make($request->all(), [
                'property_id' => ['required', Rule::in($properties->pluck('id')->all())],
                'check_in' => ['required', 'date', 'after_or_equal:' . $today],
                'check_out' => ['required', 'date', 'after:check_in'],
                'adults' => ['required', 'integer', 'min:1', 'max:20'],
                'children' => ['nullable', 'integer', 'min:0', 'max:20'],
            ]);

            $validator->after(function ($validator) use ($request) {
                if ($validator->errors()->isEmpty()) {
                    $nights = Carbon::parse($request->input('check_in'))
                        ->diffInDays(Carbon::parse($request->input('check_out')));

                    if ($nights > self::MAX_NIGHTS) {
                        $validator->errors()->add('check_out', 'A reservation can be for up to ' . self::MAX_NIGHTS . ' nights.');
                    }
                }
            });

            if ($validator->fails()) {
                $searchErrors = $validator->errors()->all();
            } else {
                $search = $this->availability->search(
                    $property,
                    $request->input('check_in'),
                    $request->input('check_out'),
                    (int) $request->input('adults'),
                    (int) $request->input('children', 0)
                );
            }
        }

        return view('company.reservations.create', compact(
            'properties',
            'guests',
            'search',
            'searchErrors',
            'submitted'
        ));
    }

    /**
     * Save a new reservation.
     *
     * The selected rooms are locked inside a database transaction and checked again,
     * so two people can never book the same room for overlapping dates.
     */
    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Reservation::class);

        $companyId = $request->user()->company_id;

        $properties = $request->user()
            ->company
            ->properties()
            ->where('status', 'active')
            ->get();

        $property = $properties->firstWhere('id', (int) $request->input('property_id'));
        $today = now($property?->timezone ?? config('app.timezone'))->toDateString();

        $validated = $request->validate([
            'property_id' => ['required', Rule::in($properties->pluck('id')->all())],
            'guest_id' => [
                'required',
                Rule::exists('guests', 'id')
                    ->where('company_id', $companyId)
                    ->whereNull('deleted_at'),
            ],
            'check_in' => ['required', 'date', 'after_or_equal:' . $today],
            'check_out' => ['required', 'date', 'after:check_in'],
            'adults' => ['required', 'integer', 'min:1', 'max:20'],
            'children' => ['nullable', 'integer', 'min:0', 'max:20'],
            'unit_ids' => ['required', 'array', 'min:1', 'max:10'],
            'unit_ids.*' => ['integer', 'distinct'],
            'source' => ['required', Rule::in(array_keys(Reservation::sources()))],
            'notes' => ['nullable', 'string', 'max:2000'],
        ], [
            'unit_ids.required' => 'Please select at least one room or unit.',
            'unit_ids.min' => 'Please select at least one room or unit.',
        ]);

        $checkIn = $validated['check_in'];
        $checkOut = $validated['check_out'];
        $adults = (int) $validated['adults'];
        $children = (int) ($validated['children'] ?? 0);
        $unitIds = array_map('intval', $validated['unit_ids']);

        if (Carbon::parse($checkIn)->diffInDays(Carbon::parse($checkOut)) > self::MAX_NIGHTS) {
            throw ValidationException::withMessages([
                'check_out' => 'A reservation can be for up to ' . self::MAX_NIGHTS . ' nights.',
            ]);
        }

        $reservation = DB::transaction(function () use (
            $request, $property, $validated, $checkIn, $checkOut, $adults, $children, $unitIds
        ) {
            // 1. Lock the selected rooms, always in the same order, so parallel bookings wait for each other.
            $units = Unit::where('property_id', $property->id)
                ->whereIn('id', $unitIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            if ($units->count() !== count($unitIds)) {
                throw ValidationException::withMessages([
                    'unit_ids' => 'Some of the selected rooms are not valid for this property.',
                ]);
            }

            // 2. Check every room again with the latest data, now that it is locked.
            foreach ($units as $unit) {
                $stillFree = $this->availability
                    ->availableQuery($property, $checkIn, $checkOut, $adults, $children)
                    ->whereKey($unit->id)
                    ->exists();

                if (! $stillFree) {
                    throw ValidationException::withMessages([
                        'unit_ids' => 'Room ' . $unit->name . ' is no longer available for these dates. Please search again.',
                    ]);
                }
            }

            // 3. Save the reservation and its rooms.
            // company_id, property and created_by come from the server, never from the form.
            $reservation = Reservation::create([
                'company_id' => $property->company_id,
                'property_id' => $property->id,
                'guest_id' => $validated['guest_id'],
                'booking_reference' => $this->makeReference(),
                'status' => 'confirmed',
                'source' => $validated['source'],
                'check_in' => $checkIn,
                'check_out' => $checkOut,
                'adults' => $adults * $units->count(),
                'children' => $children * $units->count(),
                'currency' => $property->currency,
                'total_amount' => 0,
                'notes' => $validated['notes'] ?? null,
                'created_by' => $request->user()->id,
            ]);

            foreach ($units as $unit) {
                ReservationItem::create([
                    'company_id' => $property->company_id,
                    'reservation_id' => $reservation->id,
                    'unit_id' => $unit->id,
                    'check_in' => $checkIn,
                    'check_out' => $checkOut,
                    'adults' => $adults,
                    'children' => $children,
                ]);
            }

            $reservation->statusHistories()->create([
                'company_id' => $property->company_id,
                'old_status' => null,
                'new_status' => 'confirmed',
                'note' => 'Reservation created',
                'changed_by' => $request->user()->id,
            ]);

            return $reservation;
        });

        return redirect()
            ->route('company.reservations.show', $reservation)
            ->with('success', 'Reservation ' . $reservation->booking_reference . ' created successfully.');
    }

    /**
     * Show one reservation.
     */
    public function show(Reservation $reservation): View
    {
        Gate::authorize('view', $reservation);

        $reservation->load(['guest', 'property', 'creator', 'items.unit.roomType', 'statusHistories.user']);

        return view('company.reservations.show', compact('reservation'));
    }

    /**
     * Check the guest in. Allowed from the check-in date until before the check-out date.
     */
    public function checkIn(Request $request, Reservation $reservation): RedirectResponse
    {
        Gate::authorize('update', $reservation);

        $today = $this->today($reservation);

        if ($today < $reservation->check_in->toDateString()) {
            return back()->withErrors([
                'status' => 'Check-in is possible only from the check-in date (' . $reservation->check_in->format('d M Y') . ').',
            ]);
        }

        if ($today >= $reservation->check_out->toDateString()) {
            return back()->withErrors([
                'status' => 'The check-out date of this reservation has already passed.',
            ]);
        }

        $done = $this->changeStatus($request, $reservation, 'confirmed', 'checked_in', [
            'checked_in_at' => now(),
        ]);

        if (! $done) {
            return back()->withErrors(['status' => 'Only a confirmed reservation can be checked in.']);
        }

        return redirect()
            ->route('company.reservations.show', $reservation)
            ->with('success', 'Guest checked in successfully.');
    }

    /**
     * Check the guest out. The rooms become available again for the remaining dates.
     */
    public function checkOut(Request $request, Reservation $reservation): RedirectResponse
    {
        Gate::authorize('update', $reservation);

        $done = $this->changeStatus($request, $reservation, 'checked_in', 'checked_out', [
            'checked_out_at' => now(),
        ]);

        if (! $done) {
            return back()->withErrors(['status' => 'Only a checked-in reservation can be checked out.']);
        }

        return redirect()
            ->route('company.reservations.show', $reservation)
            ->with('success', 'Guest checked out successfully.');
    }

    /**
     * Mark a confirmed reservation as no-show. Allowed only from the check-in date.
     */
    public function noShow(Request $request, Reservation $reservation): RedirectResponse
    {
        Gate::authorize('update', $reservation);

        if ($this->today($reservation) < $reservation->check_in->toDateString()) {
            return back()->withErrors([
                'status' => 'A reservation can be marked as no-show only from the check-in date (' . $reservation->check_in->format('d M Y') . ').',
            ]);
        }

        $done = $this->changeStatus($request, $reservation, 'confirmed', 'no_show', [
            'no_show_at' => now(),
        ]);

        if (! $done) {
            return back()->withErrors(['status' => 'Only a confirmed reservation can be marked as no-show.']);
        }

        return redirect()
            ->route('company.reservations.show', $reservation)
            ->with('success', 'Reservation marked as no-show. The rooms are available again.');
    }

    /**
     * Cancel a reservation. Its rooms become available again for those dates.
     */
    public function cancel(Request $request, Reservation $reservation): RedirectResponse
    {
        Gate::authorize('update', $reservation);

        $validated = $request->validate([
            'cancellation_reason' => ['nullable', 'string', 'max:500'],
        ]);

        $done = $this->changeStatus($request, $reservation, 'confirmed', 'cancelled', [
            'cancelled_at' => now(),
            'cancellation_reason' => $validated['cancellation_reason'] ?? null,
        ], $validated['cancellation_reason'] ?? null);

        if (! $done) {
            return back()->withErrors(['cancellation_reason' => 'Only a confirmed reservation can be cancelled.']);
        }

        return redirect()
            ->route('company.reservations.show', $reservation)
            ->with('success', 'Reservation cancelled. The rooms are available again.');
    }

    /**
     * Move a reservation from one status to another.
     *
     * The row is locked first and the current status is checked again, so a double click
     * or two staff members can never apply the same change twice. Every change is written
     * to the status history.
     */
    private function changeStatus(
        Request $request,
        Reservation $reservation,
        string $from,
        string $to,
        array $extra = [],
        ?string $note = null
    ): bool {
        return DB::transaction(function () use ($request, $reservation, $from, $to, $extra, $note) {
            $fresh = Reservation::lockForUpdate()->find($reservation->id);

            if (! $fresh || $fresh->status !== $from) {
                return false;
            }

            $fresh->update(['status' => $to] + $extra);

            $fresh->statusHistories()->create([
                'company_id' => $fresh->company_id,
                'old_status' => $from,
                'new_status' => $to,
                'note' => $note,
                'changed_by' => $request->user()->id,
            ]);

            return true;
        });
    }

    /**
     * Today's date in the property's timezone.
     */
    private function today(Reservation $reservation): string
    {
        return now($reservation->property?->timezone ?? config('app.timezone'))->toDateString();
    }

    /**
     * Unique booking reference, for example BK-261029-A7K2P.
     */
    private function makeReference(): string
    {
        do {
            $reference = 'BK-' . now()->format('ymd') . '-' . strtoupper(Str::random(5));
        } while (Reservation::withTrashed()->where('booking_reference', $reference)->exists());

        return $reference;
    }
}