<?php

namespace App\Policies;

use App\Enums\DetailingStatus;
use App\Models\AutomotiveDetailing;
use App\Models\User;

class AutomotiveDetailingPolicy
{
    public function create(User $user): bool
    {
        return ! $user->isAdmin() && $user->automotiveDetailing()->doesntExist();
    }

    public function update(User $user, AutomotiveDetailing $detailing): bool
    {
        return $detailing->user_id === $user->id
            && $detailing->status !== DetailingStatus::Inactive;
    }
}
