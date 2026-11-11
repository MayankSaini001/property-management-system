<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RatePlanPrice extends Model
{
    protected $fillable = [
        'company_id',
        'rate_plan_id',
        'room_type_id',
        'unit_id',
        'weekday_price',
        'weekend_price',
    ];

    protected $casts = [
        'weekday_price' => 'decimal:2',
        'weekend_price' => 'decimal:2',
    ];

    public function ratePlan(): BelongsTo
    {
        return $this->belongsTo(RatePlan::class);
    }

    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class)->withTrashed();
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class)->withTrashed();
    }
}