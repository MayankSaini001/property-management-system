<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Unit extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'property_id',
        'room_type_id',
        'name',
        'unit_type',
        'max_adults',
        'max_children',
        'capacity',
        'status',
        'notes',
    ];

    /**
     * Allowed unit types.
     */
    public static function unitTypes(): array
    {
        return [
            'room' => 'Hotel Room',
            'villa' => 'Villa Unit',
        ];
    }

    /**
     * Allowed unit statuses.
     */
    public static function statuses(): array
    {
        return [
            'available' => 'Available',
            'out_of_service' => 'Out of Service',
            'maintenance' => 'Maintenance',
        ];
    }

    /**
     * Only available units can be sold in bookings.
     */
    public function isBookable(): bool
    {
        return $this->status === 'available';
    }

    /**
     * Units whose status allows selling.
     */
    public function scopeBookable($query)
    {
        return $query->where('status', 'available');
    }

    /**
     * Units without any maintenance / owner-use block in the stay dates.
     * Used by the availability search (check-out day is free).
     */
    public function scopeFreeBetween($query, $checkIn, $checkOut)
    {
        return $query->whereDoesntHave('blocks', function ($block) use ($checkIn, $checkOut) {
            $block->overlapping($checkIn, $checkOut);
        });
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }

    public function blocks(): HasMany
    {
        return $this->hasMany(InventoryBlock::class);
    }
}