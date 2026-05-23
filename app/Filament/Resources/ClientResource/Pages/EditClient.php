<?php

namespace App\Filament\Resources\ClientResource\Pages;

use App\Filament\Resources\ClientResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Hash;

class EditClient extends EditRecord
{
    protected static string $resource = ClientResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['user_name']  = $this->record->user?->name;
        $data['user_email'] = $this->record->user?->email;
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $updates = ['name' => $data['user_name'], 'email' => $data['user_email']];
        if (! empty($data['user_password'])) {
            $updates['password'] = Hash::make($data['user_password']);
        }
        $this->record->user?->update($updates);

        unset($data['user_name'], $data['user_email'], $data['user_password']);
        return $data;
    }
}
