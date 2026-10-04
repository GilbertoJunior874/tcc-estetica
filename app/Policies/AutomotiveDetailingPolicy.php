<?php

namespace App\Policies;

use App\Models\User;

class AutomotiveDetailingPolicy
{
    public function create(User $user): bool
    {
        return $user->automotiveDetailing()->doesntExist();
    }
}
