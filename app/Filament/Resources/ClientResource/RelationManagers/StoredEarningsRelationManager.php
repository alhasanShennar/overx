<?php

namespace App\Filament\Resources\ClientResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class StoredEarningsRelationManager extends RelationManager
{
    protected static string $relationship = 'storedEarnings';

    public function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('btc_amount')->numeric(8)->label('BTC'),
                Tables\Columns\TextColumn::make('revenue_amount')->money('USD')->label('Revenue'),
                Tables\Columns\TextColumn::make('stored_at')->dateTime()->label('Stored At'),
                Tables\Columns\TextColumn::make('earningPeriod.start_date')
                    ->label('Period')->date(),
            ])
            ->defaultSort('stored_at', 'desc')
            ->actions([]);
    }
}
