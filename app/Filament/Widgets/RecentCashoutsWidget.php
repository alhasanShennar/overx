<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\CashoutResource;
use App\Models\Cashout;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentCashoutsWidget extends BaseWidget
{
    protected static ?int $sort = 4;
    protected int|string|array $columnSpan = 1;
    protected static ?string $heading = 'Recent Cashouts';

    public function table(Table $table): Table
    {
        return $table
            ->query(Cashout::with(['client.user', 'cashoutDetail'])->latest())
            ->defaultPaginationPageOption(10)
            ->columns([
                Tables\Columns\TextColumn::make('client.user.name')->label('Client'),
                Tables\Columns\TextColumn::make('amount')->money('USD'),
                Tables\Columns\TextColumn::make('btc_amount')->numeric(8)->label('BTC'),
                Tables\Columns\TextColumn::make('date')->date(),
                Tables\Columns\TextColumn::make('status')->badge()
                    ->color(fn ($state) => match ($state) {
                        'pending'   => 'warning',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default     => 'gray',
                    }),
                Tables\Columns\TextColumn::make('cashoutDetail.label')->label('Method'),
            ])
            ->actions([
                Tables\Actions\Action::make('edit')
                    ->url(fn (Cashout $record) => CashoutResource::getUrl('edit', ['record' => $record]))
                    ->icon('heroicon-o-pencil-square')
                    ->label('Edit'),
            ]);
    }
}
