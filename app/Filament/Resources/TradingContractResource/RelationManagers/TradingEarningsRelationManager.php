<?php

namespace App\Filament\Resources\TradingContractResource\RelationManagers;

use App\Filament\Resources\TradingEarningResource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class TradingEarningsRelationManager extends RelationManager
{
    protected static string $relationship = 'tradingEarnings';

    protected static ?string $title = 'Trading Earnings';

    public function form(Form $form): Form
    {
        return $form->schema(TradingEarningResource::earningFormSchema(
            lockClient: true,
            lockContract: true,
            defaultContractId: $this->getOwnerRecord()->id,
            defaultClientId: $this->getOwnerRecord()->client_id,
        ));
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns(TradingEarningResource::earningTableColumns())
            ->defaultSort('date', 'desc')
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['client_id'] = $this->getOwnerRecord()->client_id;
                        $data['trading_contract_id'] = $this->getOwnerRecord()->id;

                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
