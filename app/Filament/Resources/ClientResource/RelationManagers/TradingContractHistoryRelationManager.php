<?php

namespace App\Filament\Resources\ClientResource\RelationManagers;

use App\Models\TradingContract;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class TradingContractHistoryRelationManager extends RelationManager
{
    protected static string $relationship = 'tradingContracts';

    protected static ?string $title = 'Trading Contract History';

    protected static ?string $icon = 'heroicon-o-archive-box';

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
            ->modifyQueryUsing(fn ($query) => $query
                ->whereNotNull('end_date')
                ->where('end_date', '<', today())
                ->orderByDesc('end_date'))
            ->columns([
                Tables\Columns\TextColumn::make('amount')->money('USD'),
                Tables\Columns\TextColumn::make('earning')->money('USD')->color('success'),
                Tables\Columns\TextColumn::make('roi_percent')
                    ->label('ROI')
                    ->formatStateUsing(fn ($state) => filled($state) ? number_format((float) $state, 2) . '%' : '—'),
                Tables\Columns\TextColumn::make('start_date')->date(),
                Tables\Columns\TextColumn::make('end_date')->date(),
                Tables\Columns\IconColumn::make('file')
                    ->boolean()
                    ->getStateUsing(fn (TradingContract $record) => filled($record->file)),
            ])
            ->actions([]);
    }
}
