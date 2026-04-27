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
            Forms\Components\DatePicker::make('start_date')
                ->required()
                ->live()
                ->afterStateUpdated(function (Set $set, $state): void {
                    if (blank($state)) {
                        $set('end_date', null);

                        return;
                    }

                    $set('end_date', Carbon::parse($state)->addDays(30)->toDateString());
                }),
            Forms\Components\DatePicker::make('end_date')->required(),
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
                Tables\Columns\TextColumn::make('start_date')->date(),
                Tables\Columns\TextColumn::make('end_date')->date(),
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
