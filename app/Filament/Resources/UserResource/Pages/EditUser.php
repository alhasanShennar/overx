<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\Admin;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['is_admin'] = $this->record->isAdmin();
        $data['permission_names'] = $this->record->getPermissionNames()->toArray();

        return $data;
    }

    protected function afterSave(): void
    {
        $this->record->syncPermissions($this->data['permission_names'] ?? []);
        $this->syncAdminAccess((bool) ($this->data['is_admin'] ?? false));
    }

    private function syncAdminAccess(bool $isAdmin): void
    {
        if ($isAdmin) {
            Admin::firstOrCreate(['user_id' => $this->record->id]);

            return;
        }

        if (! $this->record->isSuperAdmin()) {
            $this->record->admin()?->delete();
        }
    }
}
