<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReservationNight extends Model
{
    protected $fillable = [
        'company_id',
        'reservation_id',
        'reservation_item_id',
        'unit_id',
        'stay_date',
        'price',
        'price_type',
        'season_name',
    ];

    protected $casts = [
        'stay_date' => 'date',
        'price' => 'decimal:2',
    ];

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(ReservationItem::class, 'reservation_item_id');
    }
}