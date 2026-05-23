<?php

namespace App\Filament\Resources\ClientResource\RelationManagers;

use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ContractHistoryRelationManager extends RelationManager
{
    protected static string $relationship = 'contracts';

    protected static ?string $title = 'Contract History';

    public function isReadOnly(): bool
    {
        return true;
    }

    public function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->whereNotNull('end_date')->where('end_date', '<', today()))
            ->columns([
                Tables\Columns\TextColumn::make('amount')->money('USD')->sortable(),
                Tables\Columns\TextColumn::make('storing_machines_no')
                    ->label('Storing M.')
                    ->alignCenter()
                    ->formatStateUsing(fn ($state, $record) => $state . ' (' . $record->storing_machines_currency . ')'),
                Tables\Columns\TextColumn::make('cashout_machines_no')->label('Cashout M.')->alignCenter(),
                Tables\Columns\TextColumn::make('start_date')->date()->sortable(),
                Tables\Columns\TextColumn::make('end_date')->date()->sortable(),
                Tables\Columns\IconColumn::make('file')->label('File')
                    ->boolean()
                    ->trueIcon('heroicon-o-paper-clip')
                    ->falseIcon('heroicon-o-x-mark')
                    ->getStateUsing(fn ($record) => ! empty($record->file)),
            ])
            ->defaultSort('end_date', 'desc')
            ->actions([]);
    }
}
