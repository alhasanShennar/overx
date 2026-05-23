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
            $user = User::create([
                'name'     => $data['user_name'],
                'email'    => $data['user_email'],
                'password' => Hash::make($data['user_password']),
            ]);

            return Client::create([
                'user_id'  => $user->id,
                'phone'    => $data['phone'] ?? null,
                'passport' => $data['passport'] ?? null,
            ]);
        });
    }
}
