<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Models\Client;
use App\Models\Transaction;
use App\Services\EarningPeriodService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;
    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';
    protected static ?string $navigationGroup = 'Mining';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('type')->disabled(),
            Forms\Components\TextInput::make('status')->disabled(),
            Forms\Components\Textarea::make('notes'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('client.user.name')
                    ->label('Client')->searchable(),
                Tables\Columns\TextColumn::make('earningPeriod.start_date')
                    ->label('Period Start')->date(),
                Tables\Columns\TextColumn::make('type')->badge()
                    ->color(fn($state) => match ($state) {
                        'cashout' => 'danger',
                        'store' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('btc_amount')->numeric(8)->label('BTC'),
                Tables\Columns\TextColumn::make('fiat_amount')->money('USD')->label('Fiat'),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'completed',
                        'danger' => 'rejected',
                        'gray' => 'cancelled',
                    ]),
                Tables\Columns\TextColumn::make('requested_by'),
                Tables\Columns\TextColumn::make('requested_at')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('processed_at')->dateTime()->toggleable(),
            ])
            ->defaultSort('requested_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'completed' => 'Completed',
                        'rejected' => 'Rejected',
                        'cancelled' => 'Cancelled',
                    ]),
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'cashout' => 'Cashout',
                        'store' => 'Store',
                        'adjustment' => 'Adjustment',
                    ]),
                Tables\Filters\SelectFilter::make('client_id')
                    ->label('Client')
                    ->options(fn() => Client::with('user')->get()->pluck('user.name', 'id'))
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\Action::make('approve_cashout')
                    ->label('Process Cashout')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->visible(
                        fn(Transaction $record) =>
                        $record->status === 'pending' && $record->type === 'cashout'
                    )
                    ->form([
                        Forms\Components\Select::make('cashout_details_id')
                            ->label('Cashout Method')
                            ->options(function (Transaction $record) {
                                return $record->client->cashoutDetails()
                                    ->get()
                                    ->mapWithKeys(fn($d) => [$d->id => ($d->label ?: $d->type)]);
                            })
                            ->nullable(),
                        Forms\Components\DatePicker::make('date')->default(today())->required(),
                        Forms\Components\TextInput::make('amount')->numeric()->prefix('$')->nullable(),
                        Forms\Components\FileUpload::make('receipt')->directory('cashout-receipts')->nullable(),
                        Forms\Components\Textarea::make('notes')->nullable(),
                    ])
                    ->action(function (Transaction $record, array $data) {
                        app(EarningPeriodService::class)->processCashout($record, $data);
                        Notification::make()->title('Cashout processed.')->success()->send();
                    }),

                Tables\Actions\Action::make('approve_store')
                    ->label('Process Store')
                    ->icon('heroicon-o-archive-box')
                    ->color('info')
                    ->visible(
                        fn(Transaction $record) =>
                        $record->status === 'pending' && $record->type === 'store'
                    )
                    ->form([
                        Forms\Components\Textarea::make('notes')->nullable(),
                    ])
                    ->action(function (Transaction $record, array $data) {
                        app(EarningPeriodService::class)->processStore($record, $data);
                        Notification::make()->title('Store processed.')->success()->send();
                    }),

                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn(Transaction $record) => $record->status === 'pending')
                    ->form([
                        Forms\Components\Textarea::make('notes')->label('Rejection Reason')->nullable(),
                    ])
                    ->requiresConfirmation()
                    ->action(function (Transaction $record, array $data) {
                        app(EarningPeriodService::class)->rejectRequest($record, $data['notes'] ?? '');
                        Notification::make()->title('Transaction rejected.')->warning()->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransactions::route('/'),
        ];
    }
}
