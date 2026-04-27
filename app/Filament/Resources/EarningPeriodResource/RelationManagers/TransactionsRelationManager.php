<?php

namespace App\Filament\Resources\EarningPeriodResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class TransactionsRelationManager extends RelationManager
{
    protected static string $relationship = 'transactions';

    public function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('type')->badge(),
                Tables\Columns\TextColumn::make('btc_amount')->numeric(8),
                Tables\Columns\TextColumn::make('fiat_amount')->money('USD'),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'completed',
                        'danger' => 'rejected',
                        'gray' => 'cancelled',
                    ]),
                Tables\Columns\TextColumn::make('requested_by'),
                Tables\Columns\TextColumn::make('processed_at')->dateTime(),
            ])
            ->actions([]);
    }
}
