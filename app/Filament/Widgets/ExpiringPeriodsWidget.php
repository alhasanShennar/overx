<?php

namespace App\Filament\Widgets;

use App\Models\EarningPeriod;
use App\Services\EarningPeriodService;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class ExpiringPeriodsWidget extends BaseWidget
{
    protected static ?int $sort = 10;
    protected int|string|array $columnSpan = 1;
    protected static ?string $heading = 'Periods Ending Within 7 Days';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => EarningPeriod::with(['client.user'])
                    ->where('status', EarningPeriod::STATUS_PENDING)
                    ->whereBetween('end_date', [Carbon::today(), Carbon::today()->addDays(7)])
                    ->orderBy('end_date')
            )
            ->columns([
                Tables\Columns\TextColumn::make('client.user.name')->label('Client'),
                Tables\Columns\TextColumn::make('start_date')->date()->label('Start'),
                Tables\Columns\TextColumn::make('end_date')->date()->label('End')
                    ->color(fn (EarningPeriod $record) => Carbon::parse($record->end_date)->isToday() ? 'danger' : 'warning'),
                Tables\Columns\TextColumn::make('total_btc_earned')->numeric(8)->label('BTC Earned'),
                Tables\Columns\TextColumn::make('total_revenue')->money('USD')->label('Revenue'),
            ])
            ->actions([
                Tables\Actions\Action::make('mark_completed')
                    ->label('Mark Completed')
                    ->icon('heroicon-o-check-circle')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->action(function (EarningPeriod $record) {
                        $record->update(['status' => EarningPeriod::STATUS_COMPLETED]);
                        Notification::make()->title('Period marked as completed.')->success()->send();
                    }),
            ]);
    }
}
