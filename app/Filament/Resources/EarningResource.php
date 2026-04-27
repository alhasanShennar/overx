<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EarningResource\Pages;
use App\Models\Client;
use App\Models\Earning;
use App\Models\EarningPeriod;
use App\Services\EarningPeriodService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EarningResource extends Resource
{
    protected static ?string $model = Earning::class;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Mining';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('client_id')
                ->label('Client')
                ->options(fn() => Client::with('user')->get()->pluck('user.name', 'id'))
                ->required()
                ->searchable()
                ->live()
                ->afterStateUpdated(fn(Forms\Set $set) => $set('earning_period_id', null)),

            Forms\Components\DatePicker::make('date')
                ->required()
                ->default(today())
                ->live()
                ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set, $state) {
                    static::autoAssignPeriod($get, $set, $state);
                }),

            Forms\Components\Select::make('earning_period_id')
                ->label('Earning Period')
                ->options(function (Forms\Get $get) {
                    $clientId = $get('client_id');
                    if (! $clientId) return [];
                    return EarningPeriod::where('client_id', $clientId)
                        ->whereIn('status', ['pending', 'completed'])
                        ->get()
                        ->mapWithKeys(fn($p) => [
                            $p->id => $p->start_date->format('Y-m-d') . ' → ' . $p->end_date->format('Y-m-d') . ' (' . $p->status . ')'
                        ]);
                })
                ->required()
                ->searchable()
                ->helperText('Auto-assigned based on client and date. You can override.'),

            Forms\Components\TextInput::make('btc_earned')
                ->label('BTC Earned')
                ->numeric()->required()->step('0.00000001')
                ->default('0.0029')
                ->live(onBlur: true)
                ->afterStateUpdated(fn(Forms\Get $get, Forms\Set $set) => static::calculateRevenue($get, $set)),

            Forms\Components\TextInput::make('btc_price')
                ->label('BTC Price (USD)')
                ->numeric()->required()->prefix('$')
                ->live(onBlur: true)
                ->afterStateUpdated(fn(Forms\Get $get, Forms\Set $set) => static::calculateRevenue($get, $set)),

            Forms\Components\TextInput::make('revenue')
                ->label('Revenue (USD)')
                ->numeric()->prefix('$')
                ->disabled()
                ->dehydrated(false)
                ->helperText('Auto-calculated: BTC Earned × BTC Price'),

            Forms\Components\Textarea::make('additional_notes')->nullable()->columnSpanFull(),
        ])->columns(2);
    }

    protected static function calculateRevenue(Forms\Get $get, Forms\Set $set): void
    {
        $btc = (float) $get('btc_earned');
        $price = (float) $get('btc_price');
        if ($btc > 0 && $price > 0) {
            $set('revenue', round($btc * $price, 2));
        }
    }

    protected static function autoAssignPeriod(Forms\Get $get, Forms\Set $set, $date): void
    {
        $clientId = $get('client_id');
        if (! $clientId || ! $date) return;

        $client = Client::find($clientId);
        if (! $client) return;

        $service = app(EarningPeriodService::class);
        $period = $service->resolveEarningPeriodForDate($client, \Carbon\Carbon::parse($date));
        $set('earning_period_id', $period->id);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('client.user.name')
                    ->label('Client')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('date')->date()->sortable(),
                Tables\Columns\TextColumn::make('btc_earned')->numeric(8)->label('BTC'),
                Tables\Columns\TextColumn::make('btc_price')->money('USD')->label('BTC Price'),
                Tables\Columns\TextColumn::make('revenue')->money('USD'),
                Tables\Columns\TextColumn::make('earningPeriod.start_date')
                    ->label('Period')->date(),
            ])
            ->defaultSort('date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('client_id')
                    ->label('Client')
                    ->options(fn() => Client::with('user')->get()->pluck('user.name', 'id'))
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEarnings::route('/'),
            'create' => Pages\CreateEarning::route('/create'),
            'edit' => Pages\EditEarning::route('/{record}/edit'),
        ];
    }
}
