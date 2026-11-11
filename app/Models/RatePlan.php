<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RatePlan extends Model
{
    use SoftDeletes;

    /**
     * Nights that use the weekend price (Carbon dayOfWeek: 5 = Friday, 6 = Saturday).
     * The night of a date is the night that starts on that date.
     */
    public const WEEKEND_DAYS = [5, 6];

    protected $fillable = [
        'company_id',
        'property_id',
        'name',
        'meal_plan',
        'description',
        'status',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public static function mealPlans(): array
    {
        return [
            'room_only' => 'Room only',
            'breakfast' => 'Bed and breakfast',
            'half_board' => 'Half board',
            'full_board' => 'Full board',
        ];
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class)->withTrashed();
    }

    public function prices(): HasMany
    {
        return $this->hasMany(RatePlanPrice::class);
    }
}