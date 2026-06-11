<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\RequiresAdminPermission;
use App\Filament\Resources\TradingPeriodResource\Pages;
use App\Models\Client;
use App\Models\TradingPeriod;
use App\Models\TradingTransaction;
use App\Services\TradingPeriodService;
use App\Support\AdminPermission;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TradingPeriodResource extends Resource
{
    use RequiresAdminPermission;

    protected static ?string $model = TradingPeriod::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'Trading';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Trading Periods';

    protected static function adminPermission(): ?string
    {
        return AdminPermission::VIEW_TRADING_PERIODS;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('period_label')->disabled()->dehydrated(false),
            Forms\Components\TextInput::make('total_earning')->prefix('$')->disabled(),
            Forms\Components\TextInput::make('status')->disabled(),
            Forms\Components\Textarea::make('notes'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['client.user', 'tradingContract']))
            ->columns([
                Tables\Columns\TextColumn::make('client.user.name')->label('Client')->searchable(),
                Tables\Columns\TextColumn::make('tradingContract.period_label')->label('Contract'),
                Tables\Columns\TextColumn::make('period_label')->label('Month')->sortable(['year', 'month']),
                Tables\Columns\TextColumn::make('total_earning')->money('USD')->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'gray' => 'pending',
                        'primary' => 'completed',
                        'warning' => 'request_pending',
                        'success' => ['stored', 'cashed_out'],
                        'danger' => 'rejected',
                    ]),
                Tables\Columns\TextColumn::make('client_decision')->badge(),
                Tables\Columns\IconColumn::make('is_locked')->boolean(),
            ])
            ->defaultSort('year', 'desc')
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
                    ->options(fn () => Client::with('user')->get()->pluck('user.name', 'id'))
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\Action::make('process_cashout')
                    ->label('Process Cashout')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->visible(fn (TradingPeriod $record) => $record->status === TradingPeriod::STATUS_REQUEST_PENDING
                        && $record->client_decision === TradingPeriod::DECISION_CASHOUT)
                    ->form([
                        Forms\Components\Select::make('cashout_details_id')
                            ->label('Cashout Method')
                            ->options(fn (TradingPeriod $record) => $record->client->cashoutDetails()
                                ->get()
                                ->mapWithKeys(fn ($d) => [$d->id => ($d->label ?: $d->type)])),
                        Forms\Components\DatePicker::make('date')->default(today())->required(),
                        Forms\Components\FileUpload::make('receipt')->directory('trading-cashout-receipts')->nullable(),
                        Forms\Components\Textarea::make('notes')->nullable(),
                    ])
                    ->action(function (TradingPeriod $record, array $data) {
                        $transaction = $record->transactions()
                            ->where('status', TradingTransaction::STATUS_PENDING)
                            ->where('type', TradingTransaction::TYPE_CASHOUT)
                            ->latest()
                            ->first();

                        if (! $transaction) {
                            Notification::make()->title('No pending cashout transaction found.')->danger()->send();

                            return;
                        }

                        app(TradingPeriodService::class)->processCashout($transaction, $data);
                        Notification::make()->title('Trading cashout processed.')->success()->send();
                    }),

                Tables\Actions\Action::make('process_store')
                    ->label('Process Store')
                    ->icon('heroicon-o-archive-box')
                    ->color('info')
                    ->visible(fn (TradingPeriod $record) => $record->status === TradingPeriod::STATUS_REQUEST_PENDING
                        && $record->client_decision === TradingPeriod::DECISION_STORE)
                    ->form([
                        Forms\Components\Textarea::make('notes')->nullable(),
                    ])
                    ->action(function (TradingPeriod $record, array $data) {
                        $transaction = $record->transactions()
                            ->where('status', TradingTransaction::STATUS_PENDING)
                            ->where('type', TradingTransaction::TYPE_STORE)
                            ->latest()
                            ->first();

                        if (! $transaction) {
                            Notification::make()->title('No pending store transaction found.')->danger()->send();

                            return;
                        }

                        app(TradingPeriodService::class)->processStore($transaction, $data);
                        Notification::make()->title('Trading earnings stored.')->success()->send();
                    }),

                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (TradingPeriod $record) => $record->status === TradingPeriod::STATUS_REQUEST_PENDING)
                    ->form([
                        Forms\Components\Textarea::make('notes')->label('Reason')->nullable(),
                    ])
                    ->requiresConfirmation()
                    ->action(function (TradingPeriod $record, array $data) {
                        $transaction = $record->transactions()
                            ->where('status', TradingTransaction::STATUS_PENDING)
                            ->latest()
                            ->first();

                        if ($transaction) {
                            app(TradingPeriodService::class)->rejectRequest($transaction, $data['notes'] ?? '');
                        }

                        Notification::make()->title('Request rejected.')->warning()->send();
                    }),

                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTradingPeriods::route('/'),
            'view' => Pages\ViewTradingPeriod::route('/{record}'),
        ];
    }
}
