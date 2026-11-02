<?php

namespace App\Policies;

use App\Models\Reservation;
use App\Models\User;

class ReservationPolicy
{
    /**
     * Company Admin can list the reservations of their own company.
     */
    public function viewAny(User $user): bool
    {
        return $user->isCompanyAdmin() && $user->company_id !== null;
    }

    /**
     * Company Admin can create reservations for their own company.
     */
    public function create(User $user): bool
    {
        return $user->isCompanyAdmin() && $user->company_id !== null;
    }

    /**
     * Company Admin can open only reservations of their own company.
     */
    public function view(User $user, Reservation $reservation): bool
    {
        return $this->sameCompany($user, $reservation);
    }

    /**
     * Company Admin can change only reservations of their own company.
     */
    public function update(User $user, Reservation $reservation): bool
    {
        return $this->sameCompany($user, $reservation);
    }

    private function sameCompany(User $user, Reservation $reservation): bool
    {
        return $user->isCompanyAdmin()
            && $user->company_id !== null
            && (int) $user->company_id === (int) $reservation->company_id;
    }
}