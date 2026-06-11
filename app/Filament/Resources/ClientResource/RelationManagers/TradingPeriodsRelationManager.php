<?php

namespace App\Filament\Resources\ClientResource\RelationManagers;

use App\Models\TradingPeriod;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class TradingPeriodsRelationManager extends RelationManager
{
    protected static string $relationship = 'tradingPeriods';

    protected static ?string $title = 'Trading Periods';

    protected static ?string $icon = 'heroicon-o-calendar-days';

    public function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('tradingContract')->orderByDesc('year')->orderByDesc('month'))
            ->columns([
                Tables\Columns\TextColumn::make('tradingContract.period_label')->label('Contract'),
                Tables\Columns\TextColumn::make('period_label')->label('Month'),
                Tables\Columns\TextColumn::make('total_earning')->money('USD'),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'gray' => 'pending',
                        'primary' => 'completed',
                        'warning' => 'request_pending',
                        'success' => ['stored', 'cashed_out'],
                        'danger' => 'rejected',
                    ]),
                Tables\Columns\TextColumn::make('client_decision')->badge(),
            ])
            ->actions([]);
    }
}
