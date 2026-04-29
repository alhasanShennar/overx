<?php

namespace App\Filament\Resources\ClientResource\RelationManagers;

use App\Models\EarningPeriod;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class EarningsRelationManager extends RelationManager
{
    protected static string $relationship = 'earnings';

    public function form(Form $form): Form
    {
        $clientId = $this->getOwnerRecord()->id;

        return $form->schema([
            Forms\Components\DatePicker::make('date')
                ->required()
                ->default(today())
                ->live()
                ->afterStateUpdated(function ($state, Forms\Set $set) use ($clientId) {
                    if (! $state) return;
                    $period = EarningPeriod::where('client_id', $clientId)
                        ->whereDate('start_date', '<=', $state)
                        ->whereDate('end_date', '>=', $state)
                        ->first();
                    $set('earning_period_id', $period?->id);
                }),
            Forms\Components\Select::make('earning_period_id')
                ->label('Earning Period')
                ->options(
                    EarningPeriod::where('client_id', $clientId)
                        ->orderBy('start_date')
                        ->get()
                        ->mapWithKeys(fn($p) => [
                            $p->id => $p->start_date->format('M d, Y') . ' → ' . $p->end_date->format('M d, Y') . ' [' . $p->status . ']'
                        ])
                )
                ->default(
                    EarningPeriod::where('client_id', $clientId)
                        ->whereDate('start_date', '<=', today())
                        ->whereDate('end_date', '>=', today())
                        ->value('id')
                )
                ->required()
                ->searchable()
                ->helperText('Auto-selected based on date'),
            Forms\Components\TextInput::make('btc_earned')
                ->numeric()->required()->step('0.00000001')
                ->live(onBlur: true)
                ->afterStateUpdated(fn(Forms\Get $get, Forms\Set $set) => $this->calculateRevenue($get, $set)),
            Forms\Components\TextInput::make('btc_price')
                ->numeric()->required()->prefix('$')
                ->live(onBlur: true)
                ->afterStateUpdated(fn(Forms\Get $get, Forms\Set $set) => $this->calculateRevenue($get, $set)),
            Forms\Components\TextInput::make('revenue')
                ->label('Revenue (USD)')
                ->numeric()
                ->prefix('$')
                ->disabled()
                ->dehydrated(false)
                ->helperText('Auto-calculated: BTC Earned × BTC Price'),
            Forms\Components\Textarea::make('additional_notes')->nullable(),
        ])->columns(2);
    }

    protected function calculateRevenue(Forms\Get $get, Forms\Set $set): void
    {
        $btc = (float) $get('btc_earned');
        $price = (float) $get('btc_price');

        $set('revenue', $btc > 0 && $price > 0 ? round($btc * $price, 2) : null);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date')->date()->sortable(),
                Tables\Columns\TextColumn::make('earningPeriod.start_date')
                    ->label('Period Start')->date(),
                Tables\Columns\TextColumn::make('btc_earned')->numeric(8),
                Tables\Columns\TextColumn::make('btc_price')->money('USD'),
                Tables\Columns\TextColumn::make('revenue')->money('USD'),
            ])
            ->defaultSort('date', 'desc')
            ->headerActions([Tables\Actions\CreateAction::make()])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }
}
