<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Property extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'name',
        'slug',
        'type',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'country_code',
        'postal_code',
        'timezone',
        'currency',
        'status',
    ];

    /**
     * Allowed property types.
     */
    public static function types(): array
    {
        return [
            'hotel' => 'Hotel',
            'villa' => 'Villa',
        ];
    }

    /**
     * Allowed currencies (code => label). Add more here when needed.
     */
    public static function currencies(): array
    {
        return [
            'INR' => 'INR — Indian Rupee',
            'GBP' => 'GBP — British Pound',
            'EUR' => 'EUR — Euro',
            'USD' => 'USD — US Dollar',
            'AED' => 'AED — UAE Dirham',
        ];
    }

    /**
     * Allowed timezones (value => label). Add more here when needed.
     */
    public static function timezones(): array
    {
        return [
            'Asia/Kolkata' => 'Asia/Kolkata',
            'Europe/London' => 'Europe/London',
            'Europe/Paris' => 'Europe/Paris',
            'Europe/Madrid' => 'Europe/Madrid',
            'America/New_York' => 'America/New_York',
            'Asia/Dubai' => 'Asia/Dubai',
        ];
    }

    /**
     * Allowed countries (ISO code => name). Add more here when needed.
     */
    public static function countries(): array
    {
        return [
            'IN' => 'India',
            'GB' => 'United Kingdom',
            'FR' => 'France',
            'ES' => 'Spain',
            'IT' => 'Italy',
            'DE' => 'Germany',
            'US' => 'United States',
            'AE' => 'United Arab Emirates',
        ];
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function countryName(): ?string
    {
        return static::countries()[$this->country_code] ?? $this->country_code;
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

        public function roomTypes(): HasMany
    {
        return $this->hasMany(RoomType::class);
    }

    public function units(): HasMany
    {
        return $this->hasMany(Unit::class);
    }

        public function inventoryBlocks(): HasMany
    {
        return $this->hasMany(InventoryBlock::class);
    }
}