<?php

namespace App\Filament\Resources\ClientResource\RelationManagers;

use App\Models\EarningPeriod;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class EarningPeriodsRelationManager extends RelationManager
{
    protected static string $relationship = 'earningPeriods';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('month_selector')
                ->label('Period Month')
                ->options(function (): array {
                    $options = [];
                    for ($i = -6; $i <= 12; $i++) {
                        $month = Carbon::today()->addMonths($i)->startOfMonth();
                        $options[$month->toDateString()] = $month->format('F Y');
                    }
                    return $options;
                })
                ->default(fn () => Carbon::today()->startOfMonth()->toDateString())
                ->required()
                ->live()
                ->dehydrated(false)
                ->afterStateHydrated(function ($component, $record): void {
                    if ($record?->start_date) {
                        $component->state($record->start_date->startOfMonth()->toDateString());
                    }
                })
                ->afterStateUpdated(function (Set $set, $state, string $operation): void {
                    if (blank($state)) {
                        return;
                    }
                    $month = Carbon::parse($state);
                    $start = ($operation === 'create' && $month->isSameMonth(Carbon::today()))
                        ? Carbon::today()
                        : $month->copy()->startOfMonth();
                    $set('start_date', $start->toDateString());
                    $set('end_date', $month->copy()->endOfMonth()->toDateString());
                }),
            Forms\Components\Hidden::make('start_date')
                ->default(fn () => Carbon::today()->toDateString()),
            Forms\Components\Hidden::make('end_date')
                ->default(fn () => Carbon::today()->endOfMonth()->toDateString()),
            Forms\Components\Select::make('status')
                ->options([
                    'pending' => 'Pending',
                    'completed' => 'Completed',
                    'request_pending' => 'Request Pending',
                    'stored' => 'Stored',
                    'cashed_out' => 'Cashed Out',
                    'rejected' => 'Rejected',
                ])->required(),
            Forms\Components\Textarea::make('notes')->nullable(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('period_label')->label('Period'),
                Tables\Columns\TextColumn::make('total_btc_earned')->numeric(8)->label('BTC Earned'),
                Tables\Columns\TextColumn::make('total_revenue')->money('USD')->label('Revenue'),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'primary' => 'completed',
                        'danger' => 'request_pending',
                        'success' => ['stored', 'cashed_out'],
                        'gray' => 'rejected',
                    ]),
                Tables\Columns\TextColumn::make('client_decision')->label('Decision'),
                Tables\Columns\IconColumn::make('is_locked')->boolean()->label('Locked'),
            ])
            ->defaultSort('start_date', 'desc')
            ->headerActions([Tables\Actions\CreateAction::make()])
            ->actions([Tables\Actions\EditAction::make()]);
    }
}
