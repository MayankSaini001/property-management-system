<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReservationItem extends Model
{
    protected $fillable = [
        'company_id',
        'reservation_id',
        'unit_id',
        'check_in',
        'check_out',
        'adults',
        'children',
        'total_amount',
    ];

    protected $casts = [
        'check_in' => 'date',
        'check_out' => 'date',
        'total_amount' => 'decimal:2',
    ];

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class)->withTrashed();
    }

    public function nights(): HasMany
    {
        return $this->hasMany(ReservationNight::class)->orderBy('stay_date');
    }
}