<?php

namespace App\Policies;

use App\Models\CashoutDetail;
use App\Models\User;

class CashoutDetailPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isClient();
    }

    public function view(User $user, CashoutDetail $cashoutDetail): bool
    {
        return $user->client?->id === $cashoutDetail->client_id;
    }

    public function create(User $user): bool
    {
        return $user->isClient();
    }

    public function update(User $user, CashoutDetail $cashoutDetail): bool
    {
        return $user->client?->id === $cashoutDetail->client_id;
    }

    public function delete(User $user, CashoutDetail $cashoutDetail): bool
    {
        return $user->client?->id === $cashoutDetail->client_id;
    }
}
