<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\RequiresAdminPermission;
use App\Filament\Resources\TradingEarningResource\Pages;
use App\Models\Client;
use App\Models\TradingContract;
use App\Models\TradingEarning;
use App\Support\AdminPermission;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TradingEarningResource extends Resource
{
    use RequiresAdminPermission;

    protected static ?string $model = TradingEarning::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-trending-up';

    protected static ?string $navigationGroup = 'Trading';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Trading Earnings';

    protected static function adminPermission(): ?string
    {
        return AdminPermission::VIEW_TRADING_EARNINGS;
    }

    public static function form(Form $form): Form
    {
        return $form->schema(static::earningFormSchema());
    }

    /**
     * @return array<int, Forms\Components\Component>
     */
    public static function earningFormSchema(
        bool $lockClient = false,
        bool $lockContract = false,
        ?int $defaultClientId = null,
        ?int $defaultContractId = null,
    ): array {
        return [
            Forms\Components\Section::make('Earning Entry')
                ->description('Record profit or loss for a trading contract. The contract total updates automatically.')
                ->schema([
                    Forms\Components\Select::make('client_id')
                        ->label('Client')
                        ->options(fn () => Client::with('user')->get()->pluck('user.name', 'id'))
                        ->default($defaultClientId)
                        ->searchable()
                        ->required()
                        ->live()
                        ->disabled($lockClient)
                        ->dehydrated()
                        ->afterStateUpdated(fn (Forms\Set $set) => $set('trading_contract_id', null)),

                    Forms\Components\Select::make('trading_contract_id')
                        ->label('Trading Contract')
                        ->options(function (Get $get) use ($defaultClientId) {
                            $clientId = $get('client_id') ?: $defaultClientId;

                            if (! $clientId) {
                                return [];
                            }

                            return TradingContract::query()
                                ->where('client_id', $clientId)
                                ->orderByDesc('start_date')
                                ->get()
                                ->mapWithKeys(fn (TradingContract $contract) => [
                                    $contract->id => sprintf(
                                        '%s · $%s · %s',
                                        $contract->period_label,
                                        number_format((float) $contract->amount, 2),
                                        $contract->status,
                                    ),
                                ]);
                        })
                        ->default($defaultContractId)
                        ->searchable()
                        ->required()
                        ->disabled($lockContract)
                        ->dehydrated(),

                    Forms\Components\DatePicker::make('date')
                        ->default(today())
                        ->required()
                        ->native(false),

                    Forms\Components\TextInput::make('amount')
                        ->label('Earning Amount')
                        ->numeric()
                        ->prefix('$')
                        ->required()
                        ->helperText('Use negative values for losses.'),

                    Forms\Components\Textarea::make('notes')
                        ->rows(3)
                        ->columnSpanFull(),
                ])
                ->columns(2),
        ];
    }

    /**
     * @return array<int, Tables\Columns\Column>
     */
    public static function earningTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('date')->date()->sortable(),
            Tables\Columns\TextColumn::make('amount')
                ->money('USD')
                ->sortable()
                ->color(fn ($state) => (float) $state < 0 ? 'danger' : 'success'),
            Tables\Columns\TextColumn::make('tradingContract.period_label')
                ->label('Contract')
                ->toggleable(),
            Tables\Columns\TextColumn::make('notes')
                ->limit(50)
                ->toggleable(),
            Tables\Columns\TextColumn::make('created_at')
                ->since()
                ->toggleable(isToggledHiddenByDefault: true),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['client.user', 'tradingContract']))
            ->columns([
                Tables\Columns\TextColumn::make('client.user.name')
                    ->label('Client')
                    ->searchable()
                    ->sortable(),

                ...static::earningTableColumns(),
            ])
            ->defaultSort('date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('client_id')
                    ->label('Client')
                    ->options(fn () => Client::with('user')->get()->pluck('user.name', 'id'))
                    ->searchable(),

                Tables\Filters\SelectFilter::make('trading_contract_id')
                    ->label('Contract')
                    ->options(fn () => TradingContract::with('client.user')
                        ->get()
                        ->mapWithKeys(fn (TradingContract $contract) => [
                            $contract->id => ($contract->client->user->name ?? 'Client') . ' · ' . $contract->period_label,
                        ])),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTradingEarnings::route('/'),
            'create' => Pages\CreateTradingEarning::route('/create'),
            'edit' => Pages\EditTradingEarning::route('/{record}/edit'),
        ];
    }
}
