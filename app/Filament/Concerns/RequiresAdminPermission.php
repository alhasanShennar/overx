<?php

namespace App\Filament\Concerns;

trait RequiresAdminPermission
{
    abstract protected static function adminPermission(): ?string;

    public static function canAccess(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        $permission = static::adminPermission();

        return $permission ? $user->can($permission) : true;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }
}
