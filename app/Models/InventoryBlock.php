<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryBlock extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'property_id',
        'unit_id',
        'block_type',
        'start_date',
        'end_date',
        'reason',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Allowed block types.
     */
    public static function types(): array
    {
        return [
            'maintenance' => 'Maintenance',
            'owner_use' => 'Owner Use',
        ];
    }

    /**
     * Blocks that touch at least one night of a stay.
     *
     * A block covers the nights start_date ... end_date (both dates included).
     * A stay covers the nights checkIn ... (checkOut - 1), so the check-out day is free.
     */
    public function scopeOverlapping($query, $checkIn, $checkOut)
    {
        return $query
            ->whereDate('start_date', '<', $checkOut)
            ->whereDate('end_date', '>=', $checkIn);
    }

    public function nights(): int
    {
        return $this->start_date->diffInDays($this->end_date) + 1;
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}