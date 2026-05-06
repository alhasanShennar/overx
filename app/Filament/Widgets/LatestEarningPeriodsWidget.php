<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\EarningPeriodResource;
use App\Models\EarningPeriod;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestEarningPeriodsWidget extends BaseWidget
{
    protected static ?int $sort = 13;
    protected int|string|array $columnSpan = 1;
    protected static ?string $heading = 'Latest Earning Periods';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => EarningPeriod::with(['client.user'])
                    ->whereNotIn('status', [EarningPeriod::STATUS_PENDING])
                    ->latest('end_date')
            )
            ->defaultPaginationPageOption(8)
            ->columns([
                Tables\Columns\TextColumn::make('client.user.name')->label('Client'),
                Tables\Columns\TextColumn::make('start_date')->date()->label('Start'),
                Tables\Columns\TextColumn::make('end_date')->date()->label('End'),
                Tables\Columns\TextColumn::make('total_btc_earned')->numeric(8)->label('BTC'),
                Tables\Columns\TextColumn::make('total_revenue')->money('USD')->label('Revenue'),
                Tables\Columns\TextColumn::make('status')->badge()
                    ->color(fn ($state) => match ($state) {
                        'pending'          => 'gray',
                        'completed'        => 'primary',
                        'request_pending'  => 'warning',
                        'stored'           => 'success',
                        'cashed_out'       => 'success',
                        'rejected'         => 'danger',
                        default            => 'gray',
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('edit')
                    ->url(fn (EarningPeriod $record) => EarningPeriodResource::getUrl('edit', ['record' => $record]))
                    ->icon('heroicon-o-pencil-square')
                    ->label('Edit'),
            ]);
    }
}
