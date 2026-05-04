<?php

namespace App\Filament\Widgets;

use App\Models\EarningPeriod;
use App\Services\EarningPeriodService;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class PendingRequestsWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    protected int|string|array $columnSpan = 'full';
    protected static ?string $heading = 'Pending Client Requests';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                EarningPeriod::with(['client.user', 'client.cashoutDetails'])
                    ->where('status', EarningPeriod::STATUS_REQUEST_PENDING)
                    ->latest('requested_at')
            )
            ->columns([
                Tables\Columns\TextColumn::make('client.user.name')->label('Client')->searchable(),
                Tables\Columns\TextColumn::make('client_decision')->label('Decision')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'cashout' => 'danger',
                        'store'   => 'success',
                        default   => 'gray',
                    }),
                Tables\Columns\TextColumn::make('total_btc_earned')->numeric(8)->label('BTC'),
                Tables\Columns\TextColumn::make('total_revenue')->money('USD')->label('Revenue'),
                Tables\Columns\TextColumn::make('start_date')->date()->label('Period Start'),
                Tables\Columns\TextColumn::make('end_date')->date()->label('Period End'),
                Tables\Columns\TextColumn::make('requested_at')->dateTime()->label('Requested'),
            ])
            ->actions([
                Tables\Actions\Action::make('approve_cashout')
                    ->label('Approve Cashout')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->visible(fn (EarningPeriod $record) => $record->client_decision === EarningPeriod::DECISION_CASHOUT)
                    ->form([
                        Forms\Components\Select::make('cashout_details_id')
                            ->label('Cashout Method')
                            ->options(fn (EarningPeriod $record) => $record->client->cashoutDetails()
                                ->get()
                                ->mapWithKeys(fn ($d) => [
                                    $d->id => ($d->label ?: $d->type) . ' — ' . ($d->crypto_wallet_address ?? $d->bank_name ?? ''),
                                ]))
                            ->nullable(),
                        Forms\Components\DatePicker::make('date')->default(today())->required(),
                        Forms\Components\TextInput::make('amount')
                            ->numeric()->prefix('$')
                            ->helperText('Leave blank to use period revenue.')
                            ->nullable(),
                        Forms\Components\FileUpload::make('receipt')
                            ->directory('cashout-receipts')->nullable(),
                        Forms\Components\Textarea::make('notes')->nullable(),
                    ])
                    ->action(function (EarningPeriod $record, array $data) {
                        app(EarningPeriodService::class)->approveCashout($record, $data);
                        Notification::make()->title('Cashout approved successfully.')->success()->send();
                    }),

                Tables\Actions\Action::make('approve_store')
                    ->label('Approve Store')
                    ->icon('heroicon-o-archive-box')
                    ->color('info')
                    ->visible(fn (EarningPeriod $record) => $record->client_decision === EarningPeriod::DECISION_STORE)
                    ->form([
                        Forms\Components\Textarea::make('notes')->nullable(),
                    ])
                    ->requiresConfirmation()
                    ->action(function (EarningPeriod $record, array $data) {
                        app(EarningPeriodService::class)->approveStore($record, $data);
                        Notification::make()->title('Store approved successfully.')->success()->send();
                    }),

                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->form([
                        Forms\Components\Textarea::make('notes')->label('Rejection Reason')->nullable(),
                    ])
                    ->requiresConfirmation()
                    ->action(function (EarningPeriod $record, array $data) {
                        app(EarningPeriodService::class)->rejectPeriodRequest($record, $data['notes'] ?? '');
                        Notification::make()->title('Request rejected.')->warning()->send();
                    }),
            ]);
    }
}
