<?php

namespace App\Filament\Resources\ClientResource\RelationManagers;

use App\Filament\Resources\TradingContractResource;
use App\Filament\Resources\TradingEarningResource;
use App\Models\TradingContract;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class TradingContractsRelationManager extends RelationManager
{
    protected static string $relationship = 'tradingContracts';

    protected static ?string $title = 'Trading Contracts';

    protected static ?string $icon = 'heroicon-o-presentation-chart-line';

    public function form(Form $form): Form
    {
        return $form->schema(TradingContractResource::contractFormSchema(lockClient: true));
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query
                ->where(fn ($q) => $q->whereNull('end_date')->orWhere('end_date', '>=', today()))
                ->orderByDesc('start_date'))
            ->columns([
                Tables\Columns\TextColumn::make('amount')->money('USD')->sortable(),
                Tables\Columns\TextColumn::make('earning')->money('USD')->color('success'),
                Tables\Columns\TextColumn::make('roi_percent')
                    ->label('ROI')
                    ->formatStateUsing(fn ($state) => filled($state) ? number_format((float) $state, 2) . '%' : '—')
                    ->badge()
                    ->color(fn ($state) => match (true) {
                        $state > 0 => 'success',
                        $state < 0 => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('start_date')->date()->sortable(),
                Tables\Columns\TextColumn::make('end_date')->date()->placeholder('Open'),
                Tables\Columns\IconColumn::make('file')
                    ->boolean()
                    ->getStateUsing(fn (TradingContract $record) => filled($record->file)),
                Tables\Columns\TextColumn::make('trading_earnings_count')
                    ->counts('tradingEarnings')
                    ->label('Entries'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('New Trading Contract')
                    ->mutateFormDataUsing(fn (array $data) => array_merge($data, [
                        'client_id' => $this->getOwnerRecord()->id,
                    ])),
            ])
            ->actions([
                Tables\Actions\Action::make('record_earning')
                    ->label('Record Earning')
                    ->icon('heroicon-o-plus-circle')
                    ->color('success')
                    ->form(fn (TradingContract $record) => TradingEarningResource::earningFormSchema(
                        lockClient: true,
                        lockContract: true,
                        defaultClientId: $record->client_id,
                        defaultContractId: $record->id,
                    ))
                    ->action(function (TradingContract $record, array $data) {
                        $record->tradingEarnings()->create([
                            'client_id' => $record->client_id,
                            'date' => $data['date'],
                            'amount' => $data['amount'],
                            'notes' => $data['notes'] ?? null,
                        ]);

                        Notification::make()
                            ->title('Trading earning recorded.')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
