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
     * List the payments and refunds of the logged-in Company Admin's company, with live search and filters.
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
            ->when(in_array($request->input('type'), ['payment', 'refund'], true), function ($query) use ($request) {
                $query->where('type', $request->input('type'));
            })
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
        $notVoided = function () use ($companyId) {
            return Payment::where('company_id', $companyId)->whereNull('voided_at');
        };

        $total = function ($query) {
            return $query->selectRaw('currency, SUM(amount) as total')
                ->groupBy('currency')
                ->pluck('total', 'currency');
        };

        // Net money = payments minus refunds.
        $net = function ($query) {
            return $query->selectRaw("currency, SUM(CASE WHEN type = 'refund' THEN -amount ELSE amount END) as total")
                ->groupBy('currency')
                ->pluck('total', 'currency');
        };

        $stats = [
            'net' => $net($notVoided()),
            'refunded' => $total($notVoided()->where('type', 'refund')),
            'today' => $net($notVoided()->whereDate('paid_on', now()->toDateString())),
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

            return $this->createEntry($request, $fresh, 'payment', $amountCents, [
                'method' => $validated['method'],
                'paid_on' => $validated['paid_on'],
                'reference' => $validated['reference'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);
        });

        return redirect()
            ->route('company.reservations.show', $reservation)
            ->with('success', 'Payment of ' . $payment->currency . ' ' . number_format((float) $payment->amount, 2)
                . ' recorded. Receipt ' . $payment->receipt_number . '.');
    }

    /**
     * Record a refund. It can never be more than the money held for the booking.
     */
    public function refund(Request $request, Reservation $reservation): RedirectResponse
    {
        Gate::authorize('update', $reservation);

        $today = now($reservation->property?->timezone ?? config('app.timezone'))->toDateString();

        $validated = $request->validate([
            'refund_amount' => ['required', 'numeric', 'min:0.01', 'max:9999999.99'],
            'refund_method' => ['required', Rule::in(array_keys(Payment::methods()))],
            'refund_paid_on' => ['required', 'date', 'before_or_equal:' . $today],
            'refund_reference' => ['nullable', 'string', 'max:100'],
            'refund_reason' => ['required', 'string', 'max:500'],
        ], [
            'refund_amount.required' => 'Please enter the refund amount.',
            'refund_paid_on.before_or_equal' => 'The refund date cannot be in the future.',
            'refund_reason.required' => 'Please give the reason for the refund.',
        ]);

        $amountCents = (int) round(((float) $validated['refund_amount']) * 100);

        $refund = DB::transaction(function () use ($request, $reservation, $validated, $amountCents) {
            $fresh = Reservation::lockForUpdate()->find($reservation->id);

            $held = $fresh->paidCents();

            if ($amountCents > $held) {
                throw ValidationException::withMessages([
                    'refund_amount' => 'The refund is more than the money held for this booking (' . $fresh->currency . ' ' . number_format($held / 100, 2) . ').',
                ]);
            }

            return $this->createEntry($request, $fresh, 'refund', $amountCents, [
                'method' => $validated['refund_method'],
                'paid_on' => $validated['refund_paid_on'],
                'reference' => $validated['refund_reference'] ?? null,
                'notes' => $validated['refund_reason'],
            ]);
        });

        return redirect()
            ->route('company.reservations.show', $reservation)
            ->with('success', 'Refund of ' . $refund->currency . ' ' . number_format((float) $refund->amount, 2)
                . ' recorded. Voucher ' . $refund->receipt_number . '.');
    }

    /**
     * Void a payment or refund that was entered by mistake. It no longer counts.
     * A payment cannot be voided while the remaining money would be less than the refunds already given.
     */
    public function void(Request $request, Payment $payment): RedirectResponse
    {
        Gate::authorize('void', $payment);

        $validated = $request->validate([
            'void_reason' => ['required', 'string', 'max:500'],
        ], [
            'void_reason.required' => 'Please give a reason for voiding.',
        ]);

        $result = DB::transaction(function () use ($request, $payment, $validated) {
            // Same order everywhere: reservation first, then the payment.
            $reservation = Reservation::lockForUpdate()->find($payment->reservation_id);
            $fresh = Payment::lockForUpdate()->find($payment->id);

            if (! $fresh || $fresh->voided_at !== null) {
                return 'already';
            }

            if ($fresh->type === 'payment' && $reservation) {
                $after = $reservation->receivedCents()
                    - (int) round(((float) $fresh->amount) * 100)
                    - $reservation->refundedCents();

                if ($after < 0) {
                    return 'refund';
                }
            }

            $fresh->update([
                'voided_at' => now(),
                'void_reason' => $validated['void_reason'],
                'voided_by' => $request->user()->id,
            ]);

            return 'ok';
        });

        if ($result === 'already') {
            return back()->withErrors(['void_reason' => 'This entry is already voided.']);
        }

        if ($result === 'refund') {
            return back()->withErrors([
                'void_reason' => 'A refund has been given against this payment. Void the refund first.',
            ]);
        }

        return back()->with('success', $payment->receipt_number . ' voided.');
    }

    /**
     * Printable receipt of a payment, or voucher of a refund.
     */
    public function receipt(Payment $payment): View
    {
        Gate::authorize('view', $payment);

        $payment->load(['reservation.guest', 'reservation.company', 'property', 'creator']);

        return view('company.payments.receipt', compact('payment'));
    }

    /**
     * Save a payment or refund. Company, property, currency and user come from the server, never from the form.
     */
    private function createEntry(Request $request, Reservation $reservation, string $type, int $amountCents, array $data): Payment
    {
        return Payment::create([
            'company_id' => $reservation->company_id,
            'property_id' => $reservation->property_id,
            'reservation_id' => $reservation->id,
            'receipt_number' => $this->makeReceiptNumber($type === 'refund' ? 'RF' : 'RC'),
            'type' => $type,
            'amount' => number_format($amountCents / 100, 2, '.', ''),
            'currency' => $reservation->currency,
            'method' => $data['method'],
            'paid_on' => $data['paid_on'],
            'reference' => $data['reference'],
            'notes' => $data['notes'],
            'created_by' => $request->user()->id,
        ]);
    }

    
    /**
     * Unique number, for example RC-261105-K3P9X for a payment or RF-261105-K3P9X for a refund.
     */
    private function makeReceiptNumber(string $prefix): string
    {
        do {
            $number = $prefix . '-' . now()->format('ymd') . '-' . strtoupper(Str::random(5));
        } while (Payment::where('receipt_number', $number)->exists());

        return $number;
    }
}