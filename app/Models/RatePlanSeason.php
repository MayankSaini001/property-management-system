<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RatePlanSeason extends Model
{
    protected $fillable = [
        'company_id',
        'rate_plan_id',
        'name',
        'start_date',
        'end_date',
    ];

    /**
     * A season covers every night from start_date to end_date (both dates included).
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function ratePlan(): BelongsTo
    {
        return $this->belongsTo(RatePlan::class);
    }

    public function prices(): HasMany
    {
        return $this->hasMany(RatePlanSeasonPrice::class, 'season_id');
    }
}