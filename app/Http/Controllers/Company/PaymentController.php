<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Property;
use App\Models\Reservation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PaymentController extends Controller
{
    /**
     * Statuses that can receive payments.
     */
    private const PAYABLE_STATUSES = ['confirmed', 'checked_in', 'checked_out'];

    /**
     * List the payments of the logged-in Company Admin's company, with live search and filters.
     */
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Payment::class);

        $companyId = $request->user()->company_id;

        $payments = Payment::where('company_id', $companyId)
            ->with(['reservation.guest', 'property', 'creator'])
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%' . trim($request->input('q')) . '%';

                $query->where(function ($inner) use ($term) {
                    $inner->where('receipt_number', 'like', $term)
                        ->orWhere('reference', 'like', $term)
                        ->orWhereHas('reservation', function ($reservation) use ($term) {
                            $reservation->where('booking_reference', 'like', $term)
                                ->orWhereHas('guest', function ($guest) use ($term) {
                                    $guest->where('first_name', 'like', $term)
                                        ->orWhere('last_name', 'like', $term)
                                        ->orWhereRaw("CONCAT(first_name, ' ', last_name) like ?", [$term]);
                                });
                        });
                });
            })
            ->when(
                $request->filled('method') && array_key_exists($request->input('method'), Payment::methods()),
                function ($query) use ($request) {
                    $query->where('method', $request->input('method'));
                }
            )
            ->when($request->input('status') === 'voided', function ($query) {
                $query->whereNotNull('voided_at');
            })
            ->when($request->input('status') === 'received', function ($query) {
                $query->whereNull('voided_at');
            })
            ->when($request->filled('property_id'), function ($query) use ($request) {
                $query->where('property_id', (int) $request->input('property_id'));
            })
            ->orderByDesc('paid_on')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $properties = Property::where('company_id', $companyId)->orderBy('name')->get();

        // Summary numbers for the cards (not affected by the filters). Grouped by currency.
        $valid = function () use ($companyId) {
            return Payment::where('company_id', $companyId)->where('type', 'payment')->whereNull('voided_at');
        };

        $totals = function ($query) {
            return $query->selectRaw('currency, SUM(amount) as total')
                ->groupBy('currency')
                ->pluck('total', 'currency');
        };

        $stats = [
            'received' => $totals($valid()),
            'today' => $totals($valid()->whereDate('paid_on', now()->toDateString())),
            'month' => $totals($valid()->whereBetween('paid_on', [
                now()->startOfMonth()->toDateString(),
                now()->toDateString(),
            ])),
            'voided' => Payment::where('company_id', $companyId)->whereNotNull('voided_at')->count(),
        ];

        return view('company.payments.index', compact('payments', 'properties', 'stats'));
    }

    /**
     * Record a payment (deposit or part payment) for a reservation.
     *
     * The reservation row is locked and the balance is checked again, so two staff members
     * can never receive more than the balance together.
     */
    public function store(Request $request, Reservation $reservation): RedirectResponse
    {
        Gate::authorize('update', $reservation);

        if (! in_array($reservation->status, self::PAYABLE_STATUSES, true)) {
            return back()->withErrors([
                'amount' => 'Payments cannot be added to a cancelled or no-show reservation.',
            ]);
        }

        $today = now($reservation->property?->timezone ?? config('app.timezone'))->toDateString();

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01', 'max:9999999.99'],
            'method' => ['required', Rule::in(array_keys(Payment::methods()))],
            'paid_on' => ['required', 'date', 'before_or_equal:' . $today],
            'reference' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [
            'paid_on.before_or_equal' => 'The payment date cannot be in the future.',
        ]);

        $amountCents = (int) round(((float) $validated['amount']) * 100);

        $payment = DB::transaction(function () use ($request, $reservation, $validated, $amountCents) {
            $fresh = Reservation::lockForUpdate()->find($reservation->id);

            if ($fresh->totalCents() <= 0) {
                throw ValidationException::withMessages([
                    'amount' => 'This reservation has no price, so no payment can be added.',
                ]);
            }

            $balance = $fresh->balanceCents();

            if ($amountCents > $balance) {
                throw ValidationException::withMessages([
                    'amount' => 'The amount is more than the balance (' . $fresh->currency . ' ' . number_format($balance / 100, 2) . ').',
                ]);
            }

            // company, property, currency and user come from the server, never from the form.
            return Payment::create([
                'company_id' => $fresh->company_id,
                'property_id' => $fresh->property_id,
                'reservation_id' => $fresh->id,
                'receipt_number' => $this->makeReceiptNumber(),
                'type' => 'payment',
                'amount' => number_format($amountCents / 100, 2, '.', ''),
                'currency' => $fresh->currency,
                'method' => $validated['method'],
                'paid_on' => $validated['paid_on'],
                'reference' => $validated['reference'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'created_by' => $request->user()->id,
            ]);
        });

        return redirect()
            ->route('company.reservations.show', $reservation)
            ->with('success', 'Payment of ' . $payment->currency . ' ' . number_format((float) $payment->amount, 2)
                . ' recorded. Receipt ' . $payment->receipt_number . '.');
    }

    /**
     * Void a payment that was entered by mistake. The money no longer counts as received.
     */
    public function void(Request $request, Payment $payment): RedirectResponse
    {
        Gate::authorize('void', $payment);

        $validated = $request->validate([
            'void_reason' => ['required', 'string', 'max:500'],
        ], [
            'void_reason.required' => 'Please give a reason for voiding the payment.',
        ]);

        $done = DB::transaction(function () use ($request, $payment, $validated) {
            $fresh = Payment::lockForUpdate()->find($payment->id);

            if (! $fresh || $fresh->voided_at !== null) {
                return false;
            }

            $fresh->update([
                'voided_at' => now(),
                'void_reason' => $validated['void_reason'],
                'voided_by' => $request->user()->id,
            ]);

            return true;
        });

        if (! $done) {
            return back()->withErrors(['void_reason' => 'This payment is already voided.']);
        }

        return back()->with('success', 'Payment ' . $payment->receipt_number . ' voided.');
    }

    /**
     * Unique receipt number, for example RC-261105-K3P9X.
     */
    private function makeReceiptNumber(): string
    {
        do {
            $number = 'RC-' . now()->format('ymd') . '-' . strtoupper(Str::random(5));
        } while (Payment::where('receipt_number', $number)->exists());

        return $number;
    }
}
