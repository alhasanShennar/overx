<?php

namespace App\Policies;

use App\Models\EarningPeriod;
use App\Models\User;

class EarningPeriodPolicy
{
    public function view(User $user, EarningPeriod $earningPeriod): bool
    {
        return $user->isAdmin() || $user->client?->id === $earningPeriod->client_id;
    }

    public function requestAction(User $user, EarningPeriod $earningPeriod): bool
    {
        if ($user->client?->id !== $earningPeriod->client_id) {
            return false;
        }

        return $earningPeriod->isEligibleForRequest();
    }
}
