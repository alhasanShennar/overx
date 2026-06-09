<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\CashoutResource;
use App\Models\EarningPeriod;
use App\Services\EarningPeriodService;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class PendingRequestsWidget extends BaseWidget
{
    protected static ?int $sort = 9;
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
                Tables\Columns\TextColumn::make('period_label')->label('Period'),
                Tables\Columns\TextColumn::make('requested_at')->dateTime()->label('Requested'),
            ])
            ->actions([
                Tables\Actions\Action::make('approve_cashout')
                    ->label('Review Cashout')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->visible(fn (EarningPeriod $record) => $record->client_decision === EarningPeriod::DECISION_CASHOUT)
                    ->url(function (EarningPeriod $record) {
                        $transaction = $record->transactions()
                            ->where('type', 'cashout')
                            ->where('status', 'pending')
                            ->latest()
                            ->first();

                        if ($transaction) {
                            app(\App\Services\CashoutApprovalService::class)->ensurePendingCashout($transaction);
                        }

                        return CashoutResource::getUrl('index');
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
