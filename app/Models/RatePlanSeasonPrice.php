<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RatePlanSeasonPrice extends Model
{
    protected $fillable = [
        'company_id',
        'season_id',
        'room_type_id',
        'unit_id',
        'weekday_price',
        'weekend_price',
    ];

    protected $casts = [
        'weekday_price' => 'decimal:2',
        'weekend_price' => 'decimal:2',
    ];

    public function season(): BelongsTo
    {
        return $this->belongsTo(RatePlanSeason::class, 'season_id');
    }
}