<?php

namespace App\Filament\Resources\ClientResource\RelationManagers;

use App\Filament\Resources\TradingEarningResource;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class TradingEarningsRelationManager extends RelationManager
{
    protected static string $relationship = 'tradingEarnings';

    protected static ?string $title = 'Trading Earnings';

    protected static ?string $icon = 'heroicon-o-arrow-trending-up';

    public function form(Form $form): Form
    {
        return $form->schema(
            TradingEarningResource::earningFormSchema(
                lockClient: true,
                defaultClientId: $this->getOwnerRecord()->id,
            )
        );
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('tradingContract'))
            ->columns(TradingEarningResource::earningTableColumns())
            ->defaultSort('date', 'desc')
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(fn (array $data) => array_merge($data, [
                        'client_id' => $this->getOwnerRecord()->id,
                    ])),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
