<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EarningPeriodResource\Pages;
use App\Filament\Resources\EarningPeriodResource\RelationManagers;
use App\Models\Client;
use App\Models\EarningPeriod;
use App\Models\Transaction;
use App\Services\EarningPeriodService;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EarningPeriodResource extends Resource
{
    protected static ?string $model = EarningPeriod::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationGroup = 'Mining';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationLabel = 'Earning Periods';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('client_id')
                ->label('Client')
                ->options(fn() => Client::with('user')->get()->pluck('user.name', 'id'))
                ->required()->searchable(),
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
            Forms\Components\Textarea::make('notes')->nullable()->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('client.user.name')
                    ->label('Client')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('start_date')->date()->sortable(),
                Tables\Columns\TextColumn::make('end_date')->date(),
                Tables\Columns\TextColumn::make('total_btc_earned')
                    ->numeric(8)->label('Total BTC'),
                Tables\Columns\TextColumn::make('total_revenue')
                    ->money('USD')->label('Revenue'),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'gray' => 'pending',
                        'primary' => 'completed',
                        'warning' => 'request_pending',
                        'success' => ['stored', 'cashed_out'],
                        'danger' => 'rejected',
                    ]),
                Tables\Columns\TextColumn::make('client_decision')->label('Decision')->badge(),
                Tables\Columns\IconColumn::make('is_locked')->boolean()->label('Locked'),
                Tables\Columns\TextColumn::make('requested_at')->dateTime()->label('Requested')->toggleable(),
            ])
            ->defaultSort('start_date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'completed' => 'Completed',
                        'request_pending' => 'Request Pending',
                        'stored' => 'Stored',
                        'cashed_out' => 'Cashed Out',
                        'rejected' => 'Rejected',
                    ]),
                Tables\Filters\SelectFilter::make('client_id')
                    ->label('Client')
                    ->options(fn() => Client::with('user')->get()->pluck('user.name', 'id'))
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn(EarningPeriod $record) => ! $record->is_locked),

                // Mark as completed (30-day period ended)
                Tables\Actions\Action::make('mark_completed')
                    ->label('Mark Completed')
                    ->icon('heroicon-o-check-circle')
                    ->color('primary')
                    ->visible(fn(EarningPeriod $record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->action(function (EarningPeriod $record) {
                        $record->update(['status' => EarningPeriod::STATUS_COMPLETED]);
                        Notification::make()->title('Period marked as completed.')->success()->send();
                    }),

                // Approve cashout request
                Tables\Actions\Action::make('approve_cashout')
                    ->label('Approve Cashout')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->visible(
                        fn(EarningPeriod $record) =>
                        $record->status === EarningPeriod::STATUS_REQUEST_PENDING &&
                            $record->client_decision === EarningPeriod::DECISION_CASHOUT
                    )
                    ->form([
                        Forms\Components\Select::make('cashout_details_id')
                            ->label('Cashout Method')
                            ->options(function (EarningPeriod $record) {
                                return $record->client->cashoutDetails()
                                    ->get()
                                    ->mapWithKeys(fn($d) => [$d->id => ($d->label ?: $d->type) . ' — ' . ($d->crypto_wallet_address ?? $d->bank_name ?? '')]);
                            })
                            ->nullable(),
                        Forms\Components\DatePicker::make('date')->default(today())->required(),
                        Forms\Components\TextInput::make('amount')
                            ->numeric()->prefix('$')
                            ->helperText('Leave blank to use period total revenue.')
                            ->nullable(),
                        Forms\Components\FileUpload::make('receipt')
                            ->directory('cashout-receipts')->nullable(),
                        Forms\Components\Textarea::make('notes')->nullable(),
                    ])
                    ->action(function (EarningPeriod $record, array $data) {
                        $transaction = $record->transactions()
                            ->where('type', 'cashout')
                            ->where('status', 'pending')
                            ->latest()
                            ->first();

                        if (! $transaction) {
                            // Admin-initiated cashout (no prior client request)
                            $transaction = Transaction::create([
                                'client_id' => $record->client_id,
                                'earning_period_id' => $record->id,
                                'type' => Transaction::TYPE_CASHOUT,
                                'btc_amount' => $record->total_btc_earned,
                                'fiat_amount' => $record->total_revenue,
                                'status' => Transaction::STATUS_PENDING,
                                'requested_by' => 'admin',
                                'requested_at' => now(),
                            ]);
                        }

                        app(EarningPeriodService::class)->processCashout($transaction, $data);
                        Notification::make()->title('Cashout processed successfully.')->success()->send();
                    }),

                // Approve store request
                Tables\Actions\Action::make('approve_store')
                    ->label('Approve Store')
                    ->icon('heroicon-o-archive-box')
                    ->color('info')
                    ->visible(
                        fn(EarningPeriod $record) =>
                        $record->status === EarningPeriod::STATUS_REQUEST_PENDING &&
                            $record->client_decision === EarningPeriod::DECISION_STORE
                    )
                    ->form([
                        Forms\Components\Textarea::make('notes')->nullable(),
                    ])
                    ->action(function (EarningPeriod $record, array $data) {
                        $transaction = $record->transactions()
                            ->where('type', 'store')
                            ->where('status', 'pending')
                            ->latest()
                            ->first();

                        if (! $transaction) {
                            $transaction = Transaction::create([
                                'client_id' => $record->client_id,
                                'earning_period_id' => $record->id,
                                'type' => Transaction::TYPE_STORE,
                                'btc_amount' => $record->total_btc_earned,
                                'fiat_amount' => $record->total_revenue,
                                'status' => Transaction::STATUS_PENDING,
                                'requested_by' => 'admin',
                                'requested_at' => now(),
                            ]);
                        }

                        app(EarningPeriodService::class)->processStore($transaction, $data);
                        Notification::make()->title('Store processed successfully.')->success()->send();
                    }),

                // Reject request
                Tables\Actions\Action::make('reject_request')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(
                        fn(EarningPeriod $record) =>
                        $record->status === EarningPeriod::STATUS_REQUEST_PENDING
                    )
                    ->form([
                        Forms\Components\Textarea::make('notes')
                            ->label('Rejection Reason')->nullable(),
                    ])
                    ->requiresConfirmation()
                    ->action(function (EarningPeriod $record, array $data) {
                        $transaction = $record->transactions()
                            ->where('status', 'pending')
                            ->latest()
                            ->first();

                        if ($transaction) {
                            app(EarningPeriodService::class)->rejectRequest($transaction, $data['notes'] ?? '');
                        } else {
                            $record->update([
                                'status' => EarningPeriod::STATUS_REJECTED,
                                'processed_at' => now(),
                            ]);
                        }
                        Notification::make()->title('Request rejected.')->warning()->send();
                    }),
            ])
            ->bulkActions([]);
    }

    public static function getRelationManagers(): array
    {
        return [
            RelationManagers\EarningsRelationManager::class,
            RelationManagers\TransactionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEarningPeriods::route('/'),
            'create' => Pages\CreateEarningPeriod::route('/create'),
            'edit' => Pages\EditEarningPeriod::route('/{record}/edit'),
        ];
    }
}
