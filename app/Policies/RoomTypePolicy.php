<?php

namespace App\Policies;

use App\Models\Property;
use App\Models\RoomType;
use App\Models\User;

class RoomTypePolicy
{
    /**
     * Company Admin can list room types of their own company's property.
     */
    public function viewAny(User $user, Property $property): bool
    {
        return $this->ownsProperty($user, $property);
    }

    /**
     * Company Admin can add room types to their own company's property.
     */
    public function create(User $user, Property $property): bool
    {
        return $this->ownsProperty($user, $property);
    }

    /**
     * Company Admin can change only room types of their own company.
     */
    public function update(User $user, RoomType $roomType): bool
    {
        return $user->isCompanyAdmin()
            && $user->company_id !== null
            && (int) $user->company_id === (int) $roomType->company_id;
    }

    private function ownsProperty(User $user, Property $property): bool
    {
        return $user->isCompanyAdmin()
            && $user->company_id !== null
            && (int) $user->company_id === (int) $property->company_id;
    }
}