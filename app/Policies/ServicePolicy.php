<?php

namespace App\Policies;

use App\Models\Service;
use App\Models\User;

class ServicePolicy
{
    public function create(User $user): bool
    {
        return $user->automotiveDetailing !== null
            && $user->can('update', $user->automotiveDetailing);
    }

    public function update(User $user, Service $service): bool
    {
        return $user->can('update', $service->automotiveDetailing);
    }

    public function delete(User $user, Service $service): bool
    {
        return $this->update($user, $service);
    }
}
