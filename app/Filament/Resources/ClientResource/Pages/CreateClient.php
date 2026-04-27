<?php

namespace App\Filament\Resources\ClientResource\Pages;

use App\Filament\Resources\ClientResource;
use App\Models\Client;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CreateClient extends CreateRecord
{
    protected static string $resource = ClientResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            $userData = $data['user'] ?? [];
            $user = User::create([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => Hash::make($userData['password']),
            ]);

            return Client::create([
                'user_id' => $user->id,
                'phone' => $data['phone'] ?? null,
                'passport' => $data['passport'] ?? null,
                'current_storing_machines' => $data['current_storing_machines'] ?? 0,
                'current_cashout_machines' => $data['current_cashout_machines'] ?? 0,
            ]);
        });
    }
}
