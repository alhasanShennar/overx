<?php

namespace App\Filament\Widgets;

use App\Models\Cashout;
use App\Models\Transaction;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentCashoutsWidget extends BaseWidget
{
    protected static ?int $sort = 3;
    protected int|string|array $columnSpan = 'full';
    protected static ?string $heading = 'Recent Cashouts';

    public function table(Table $table): Table
    {
        return $table
            ->query(Cashout::with(['client.user', 'cashoutDetail'])->latest()->limit(10))
            ->columns([
                Tables\Columns\TextColumn::make('client.user.name')->label('Client'),
                Tables\Columns\TextColumn::make('amount')->money('USD'),
                Tables\Columns\TextColumn::make('btc_amount')->numeric(8)->label('BTC'),
                Tables\Columns\TextColumn::make('date')->date(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors(['warning' => 'pending', 'success' => 'completed', 'danger' => 'cancelled']),
                Tables\Columns\TextColumn::make('cashoutDetail.label')->label('Method'),
            ]);
    }
}
