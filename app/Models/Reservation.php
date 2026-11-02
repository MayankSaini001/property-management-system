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
        'cancelled_at',
        'cancellation_reason',
        'created_by',
    ];

    protected $casts = [
        'check_in' => 'date',
        'check_out' => 'date',
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

    public function isCancellable(): bool
    {
        return $this->status === 'confirmed';
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class)->withTrashed();
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
}