<?php

namespace App\Filament\Resources\ClientResource\RelationManagers;

use App\Models\Currency;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class CashoutDetailsRelationManager extends RelationManager
{
    protected static string $relationship = 'cashoutDetails';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('label')->nullable(),
            Forms\Components\Select::make('type')
                ->options(['crypto' => 'Crypto', 'bank' => 'Bank'])
                ->required()
                ->live(),
            Forms\Components\Toggle::make('is_default'),
            Forms\Components\TextInput::make('crypto_wallet_address')
                ->visible(fn(Forms\Get $get) => $get('type') === 'crypto'),
            Forms\Components\TextInput::make('crypto_network')
                ->visible(fn(Forms\Get $get) => $get('type') === 'crypto'),
            Forms\Components\TextInput::make('account_holder')
                ->visible(fn(Forms\Get $get) => $get('type') === 'bank'),
            Forms\Components\TextInput::make('bank_name')
                ->visible(fn(Forms\Get $get) => $get('type') === 'bank'),
            Forms\Components\TextInput::make('swift_code')
                ->visible(fn(Forms\Get $get) => $get('type') === 'bank'),
            Forms\Components\TextInput::make('iban')
                ->visible(fn(Forms\Get $get) => $get('type') === 'bank'),
            Forms\Components\Select::make('currency_id')
                ->label('Currency')
                ->options(Currency::pluck('name', 'id'))
                ->nullable()
                ->visible(fn(Forms\Get $get) => $get('type') === 'bank'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('label'),
                Tables\Columns\TextColumn::make('type')->badge(),
                Tables\Columns\IconColumn::make('is_default')->boolean()->label('Default'),
                Tables\Columns\TextColumn::make('currency.code')->label('Currency'),
            ])
            ->headerActions([Tables\Actions\CreateAction::make()])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }
}
