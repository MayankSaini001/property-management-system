<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;

class PaymentPolicy
{
    /**
     * Company Admin can list the payments of their own company.
     */
    public function viewAny(User $user): bool
    {
        return $user->isCompanyAdmin() && $user->company_id !== null;
    }

    /**
     * Company Admin can void only payments of their own company.
     * Payments are never deleted, a wrong one is voided with a reason.
     */
    public function void(User $user, Payment $payment): bool
    {
        return $user->isCompanyAdmin()
            && $user->company_id !== null
            && (int) $user->company_id === (int) $payment->company_id;
    }
}
