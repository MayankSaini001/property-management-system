<?php

namespace App\Policies;

use App\Models\InventoryBlock;
use App\Models\Property;
use App\Models\User;

class InventoryBlockPolicy
{
    /**
     * Company Admin can list blocks of their own company's property.
     */
    public function viewAny(User $user, Property $property): bool
    {
        return $this->ownsProperty($user, $property);
    }

    /**
     * Company Admin can add blocks to their own company's property.
     */
    public function create(User $user, Property $property): bool
    {
        return $this->ownsProperty($user, $property);
    }

    /**
     * Company Admin can remove only blocks of their own company.
     */
    public function delete(User $user, InventoryBlock $block): bool
    {
        return $user->isCompanyAdmin()
            && $user->company_id !== null
            && (int) $user->company_id === (int) $block->company_id;
    }

    private function ownsProperty(User $user, Property $property): bool
    {
        return $user->isCompanyAdmin()
            && $user->company_id !== null
            && (int) $user->company_id === (int) $property->company_id;
    }
}