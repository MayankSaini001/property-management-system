<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
    ];

    protected $casts = [
        'check_in' => 'date',
        'check_out' => 'date',
    ];

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class)->withTrashed();
    }
}