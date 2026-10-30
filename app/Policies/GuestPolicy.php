<?php

namespace App\Policies;

use App\Models\Guest;
use App\Models\User;

class GuestPolicy
{
    /**
     * Company Admin can list the guests of their own company.
     */
    public function viewAny(User $user): bool
    {
        return $user->isCompanyAdmin() && $user->company_id !== null;
    }

    /**
     * Company Admin can add guests to their own company.
     */
    public function create(User $user): bool
    {
        return $user->isCompanyAdmin() && $user->company_id !== null;
    }

    /**
     * Company Admin can open only guests of their own company.
     */
    public function view(User $user, Guest $guest): bool
    {
        return $this->sameCompany($user, $guest);
    }

    /**
     * Company Admin can change only guests of their own company.
     */
    public function update(User $user, Guest $guest): bool
    {
        return $this->sameCompany($user, $guest);
    }

    private function sameCompany(User $user, Guest $guest): bool
    {
        return $user->isCompanyAdmin()
            && $user->company_id !== null
            && (int) $user->company_id === (int) $guest->company_id;
    }
}