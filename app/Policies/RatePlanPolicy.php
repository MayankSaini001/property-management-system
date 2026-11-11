<?php

namespace App\Policies;

use App\Models\RatePlan;
use App\Models\User;

class RatePlanPolicy
{
    /**
     * Company Admin can list the rate plans of their own company.
     */
    public function viewAny(User $user): bool
    {
        return $user->isCompanyAdmin() && $user->company_id !== null;
    }

    /**
     * Company Admin can create rate plans for their own company.
     */
    public function create(User $user): bool
    {
        return $user->isCompanyAdmin() && $user->company_id !== null;
    }

    /**
     * Company Admin can change only rate plans and prices of their own company.
     */
    public function update(User $user, RatePlan $ratePlan): bool
    {
        return $user->isCompanyAdmin()
            && $user->company_id !== null
            && (int) $user->company_id === (int) $ratePlan->company_id;
    }
}