<?php

namespace App\Policies;

use App\Models\Property;
use App\Models\User;

class PropertyPolicy
{
    /**
     * Company Admin can list the properties of their own company.
     */
    public function viewAny(User $user): bool
    {
        return $user->isCompanyAdmin() && $user->company_id !== null;
    }

    /**
     * Company Admin can add properties to their own company.
     */
    public function create(User $user): bool
    {
        return $user->isCompanyAdmin() && $user->company_id !== null;
    }

    /**
     * A Company Admin can change only properties of their own company.
     */
    public function update(User $user, Property $property): bool
    {
        return $user->isCompanyAdmin()
            && $user->company_id !== null
            && (int) $user->company_id === (int) $property->company_id;
    }
}