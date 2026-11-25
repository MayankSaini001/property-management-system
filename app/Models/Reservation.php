<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reservation extends Model
{
    use SoftDeletes;

    /**
     * Statuses that keep the rooms blocked for the reserved dates.
     */
    public const ACTIVE_STATUSES = ['confirmed', 'checked_in'];

    protected $fillable = [
        'company_id',
        'property_id',
        'guest_id',
        'rate_plan_id',
        'booking_reference',
        'status',
        'source',
        'check_in',
        'check_out',
        'adults',
        'children',
        'currency',
        'total_amount',
        'notes',
        'checked_in_at',
        'checked_out_at',
        'no_show_at',
        'cancelled_at',
        'cancellation_reason',
        'created_by',
    ];

    protected $casts = [
        'check_in' => 'date',
        'check_out' => 'date',
        'checked_in_at' => 'datetime',
        'checked_out_at' => 'datetime',
        'no_show_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'total_amount' => 'decimal:2',
    ];

    public static function statuses(): array
    {
        return [
            'confirmed' => 'Confirmed',
            'checked_in' => 'Checked In',
            'checked_out' => 'Checked Out',
            'cancelled' => 'Cancelled',
            'no_show' => 'No Show',
        ];
    }

    public static function sources(): array
    {
        return [
            'direct' => 'Direct',
            'walk_in' => 'Walk-in',
            'phone' => 'Phone',
            'email' => 'Email',
            'other' => 'Other',
        ];
    }

    public function nights(): int
    {
        return (int) $this->check_in->diffInDays($this->check_out);
    }

    public static function paymentStatuses(): array
    {
        return [
            'not_priced' => 'No price',
            'unpaid' => 'Unpaid',
            'partial' => 'Partially paid',
            'paid' => 'Paid',
        ];
    }

    /**
     * Total of the booking in minor units (paise / cents).
     */
    public function totalCents(): int
    {
        return (int) round(((float) $this->total_amount) * 100);
    }

    /**
     * Money received so far, without voided payments, in minor units.
     * Uses the "paid_total" number when the list has already loaded it, so the list stays fast.
     */
    public function paidCents(): int
    {
        $paid = array_key_exists('paid_total', $this->attributes)
            ? $this->attributes['paid_total']
            : $this->payments()->where('type', 'payment')->whereNull('voided_at')->sum('amount');

        return (int) round(((float) $paid) * 100);
    }

    public function balanceCents(): int
    {
        return max(0, $this->totalCents() - $this->paidCents());
    }

    public function paymentStatus(): string
    {
        $total = $this->totalCents();

        if ($total <= 0) {
            return 'not_priced';
        }

        $paid = $this->paidCents();

        if ($paid <= 0) {
            return 'unpaid';
        }

        return $paid < $total ? 'partial' : 'paid';
    }

    public function isCancellable(): bool
    {
        return $this->status === 'confirmed';
    }

    public function canCheckIn(): bool
    {
        return $this->status === 'confirmed';
    }

    public function canMarkNoShow(): bool
    {
        return $this->status === 'confirmed';
    }

    public function canCheckOut(): bool
    {
        return $this->status === 'checked_in';
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class)->withTrashed();
    }

    public function ratePlan(): BelongsTo
    {
        return $this->belongsTo(RatePlan::class)->withTrashed();
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class)->withTrashed();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ReservationItem::class);
    }

    public function nightPrices(): HasMany
    {
        return $this->hasMany(ReservationNight::class)->orderBy('stay_date');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->orderByDesc('paid_on')->orderByDesc('id');
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(ReservationStatusHistory::class)->orderBy('id');
    }
}
