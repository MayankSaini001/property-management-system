<?php

namespace App\Policies;

use App\Models\Property;
use App\Models\Unit;
use App\Models\User;

class UnitPolicy
{
    /**
     * Company Admin can list units of their own company's property.
     */
    public function viewAny(User $user, Property $property): bool
    {
        return $this->ownsProperty($user, $property);
    }

    /**
     * Company Admin can add units to their own company's property.
     */
    public function create(User $user, Property $property): bool
    {
        return $this->ownsProperty($user, $property);
    }

    /**
     * Company Admin can change only units of their own company.
     */
    public function update(User $user, Unit $unit): bool
    {
        return $user->isCompanyAdmin()
            && $user->company_id !== null
            && (int) $user->company_id === (int) $unit->company_id;
    }

    private function ownsProperty(User $user, Property $property): bool
    {
        return $user->isCompanyAdmin()
            && $user->company_id !== null
            && (int) $user->company_id === (int) $property->company_id;
    }
}