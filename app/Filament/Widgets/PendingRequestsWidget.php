<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
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
                Transaction::with(['client.user', 'earningPeriod'])
                    ->where('status', 'pending')
                    ->latest('requested_at')
            )
            ->columns([
                Tables\Columns\TextColumn::make('client.user.name')->label('Client'),
                Tables\Columns\TextColumn::make('type')->badge()
                    ->color(fn($state) => match ($state) {
                        'cashout' => 'danger',
                        'store' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('btc_amount')->numeric(8)->label('BTC'),
                Tables\Columns\TextColumn::make('fiat_amount')->money('USD')->label('Value'),
                Tables\Columns\TextColumn::make('earningPeriod.start_date')->label('Period Start')->date(),
                Tables\Columns\TextColumn::make('earningPeriod.end_date')->label('Period End')->date(),
                Tables\Columns\TextColumn::make('requested_at')->dateTime()->label('Requested'),
            ]);
    }
}
