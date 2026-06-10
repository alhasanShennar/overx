<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\RequiresAdminPermission;
use App\Filament\Resources\TradingContractResource\Pages;
use App\Filament\Resources\TradingContractResource\RelationManagers;
use App\Models\Client;
use App\Models\TradingContract;
use App\Support\AdminPermission;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TradingContractResource extends Resource
{
    use RequiresAdminPermission;

    protected static ?string $model = TradingContract::class;

    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';

    protected static ?string $navigationGroup = 'Trading';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Trading Contracts';

    protected static function adminPermission(): ?string
    {
        return AdminPermission::VIEW_TRADING_CONTRACTS;
    }

    public static function form(Form $form): Form
    {
        return $form->schema(static::contractFormSchema());
    }

    /**
     * @return array<int, Forms\Components\Component>
     */
    public static function contractFormSchema(bool $lockClient = false): array
    {
        return [
            Forms\Components\Section::make('Contract Details')
                ->description('Trading contracts are separate from mining contracts and do not affect machine counts or earning periods.')
                ->schema([
                    Forms\Components\Select::make('client_id')
                        ->label('Client')
                        ->options(fn () => Client::with('user')->get()->pluck('user.name', 'id'))
                        ->searchable()
                        ->preload()
                        ->required()
                        ->disabled($lockClient)
                        ->dehydrated(),

                    Forms\Components\TextInput::make('amount')
                        ->label('Contract Amount')
                        ->numeric()
                        ->prefix('$')
                        ->required()
                        ->minValue(0),

                    Forms\Components\DatePicker::make('start_date')
                        ->label('Start Date')
                        ->native(false)
                        ->default(today()),

                    Forms\Components\DatePicker::make('end_date')
                        ->label('End Date')
                        ->native(false)
                        ->after('start_date')
                        ->helperText('Leave empty for an open-ended contract.'),

                    Forms\Components\FileUpload::make('file')
                        ->label('Contract File')
                        ->directory('trading-contracts')
                        ->acceptedFileTypes(['application/pdf', 'image/*'])
                        ->maxSize(10240)
                        ->downloadable()
                        ->openable()
                        ->columnSpanFull(),
                ])
                ->columns(2),

            Forms\Components\Section::make('Performance')
                ->schema([
                    Forms\Components\TextInput::make('earning')
                        ->label('Total Earning')
                        ->numeric()
                        ->prefix('$')
                        ->disabled()
                        ->dehydrated(false)
                        ->helperText('Auto-calculated from trading earning entries. Use the Earnings tab to record profits.'),

                    Forms\Components\Placeholder::make('roi_preview')
                        ->label('Return on Investment')
                        ->content(function (?TradingContract $record) {
                            if (! $record || ! $record->roi_percent) {
                                return '—';
                            }

                            return number_format($record->roi_percent, 2) . '%';
                        }),
                ])
                ->columns(2)
                ->visibleOn('edit'),

            Forms\Components\Section::make('Notes')
                ->schema([
                    Forms\Components\Textarea::make('notes')
                        ->rows(4)
                        ->columnSpanFull(),
                ])
                ->collapsed(),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('client.user'))
            ->columns([
                Tables\Columns\TextColumn::make('client.user.name')
                    ->label('Client')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount')
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('earning')
                    ->money('USD')
                    ->sortable()
                    ->color('success'),

                Tables\Columns\TextColumn::make('roi_percent')
                    ->label('ROI')
                    ->formatStateUsing(fn ($state) => filled($state) ? number_format((float) $state, 2) . '%' : '—')
                    ->badge()
                    ->color(fn ($state) => match (true) {
                        $state > 0 => 'success',
                        $state < 0 => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('period_label')
                    ->label('Period')
                    ->toggleable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'active',
                        'gray' => 'expired',
                    ]),

                Tables\Columns\IconColumn::make('file')
                    ->label('File')
                    ->boolean()
                    ->getStateUsing(fn (TradingContract $record) => filled($record->file)),

                Tables\Columns\TextColumn::make('trading_earnings_count')
                    ->counts('tradingEarnings')
                    ->label('Entries')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('start_date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'expired' => 'Expired',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (($data['value'] ?? null) === 'active') {
                            $query->where(fn ($q) => $q->whereNull('end_date')->orWhere('end_date', '>=', today()));
                        }

                        if (($data['value'] ?? null) === 'expired') {
                            $query->whereNotNull('end_date')->where('end_date', '<', today());
                        }
                    }),

                Tables\Filters\SelectFilter::make('client_id')
                    ->label('Client')
                    ->options(fn () => Client::with('user')->get()->pluck('user.name', 'id'))
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\TradingEarningsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTradingContracts::route('/'),
            'create' => Pages\CreateTradingContract::route('/create'),
            'view' => Pages\ViewTradingContract::route('/{record}'),
            'edit' => Pages\EditTradingContract::route('/{record}/edit'),
        ];
    }
}
