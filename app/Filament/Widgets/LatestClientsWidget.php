<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ClientResource;
use App\Models\Client;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestClientsWidget extends BaseWidget
{
    protected static ?int $sort = 5;
    protected int|string|array $columnSpan = 1;
    protected static ?string $heading = 'Latest Clients';

    public function table(Table $table): Table
    {
        return $table
            ->query(Client::with('user')->latest())
            ->defaultPaginationPageOption(8)
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->label('Name'),
                Tables\Columns\TextColumn::make('user.email')->label('Email'),
                Tables\Columns\TextColumn::make('phone')->label('Phone'),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->label('Joined'),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->url(fn (Client $record) => ClientResource::getUrl('edit', ['record' => $record]))
                    ->icon('heroicon-o-eye')
                    ->label('View'),
            ]);
    }
}
