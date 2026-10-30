<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Guest extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'date_of_birth',
        'address',
        'city',
        'state',
        'country_code',
        'postal_code',
        'preferences',
        'notes',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    public function fullName(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    public function initials(): string
    {
        return strtoupper(
            mb_substr((string) $this->first_name, 0, 1) . mb_substr((string) $this->last_name, 0, 1)
        );
    }

    public function countryName(): ?string
    {
        return Property::countries()[$this->country_code] ?? $this->country_code;
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}