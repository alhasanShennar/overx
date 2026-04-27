<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientResource\Pages;
use App\Filament\Resources\ClientResource\RelationManagers;
use App\Models\Client;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;

class ClientResource extends Resource
{
    protected static ?string $model = Client::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'User Management';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Grid::make(2)->schema([
                Forms\Components\Section::make('User Account')
                    ->relationship('user')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Full Name')->required(),
                        Forms\Components\TextInput::make('email')
                            ->label('Email')->email()->required(),
                        Forms\Components\TextInput::make('password')
                            ->label('Password')->password()
                            ->dehydrateStateUsing(fn($state) => Hash::make($state))
                            ->dehydrated(fn($state) => filled($state))
                            ->required(fn(string $context) => $context === 'create'),
                    ]),

                Forms\Components\Section::make('Client Details')->schema([
                    PhoneInput::make('phone')->nullable(),
                    Forms\Components\FileUpload::make('passport')
                        ->directory('clients/passports')
                        ->nullable(),
                    Forms\Components\TextInput::make('current_storing_machines')
                        ->numeric()->default(0)->minValue(0),
                    Forms\Components\TextInput::make('current_cashout_machines')
                        ->numeric()->default(0)->minValue(0),
                ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Client Name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email')->searchable(),
                Tables\Columns\TextColumn::make('phone'),
                Tables\Columns\TextColumn::make('current_storing_machines')
                    ->label('Storing'),
                Tables\Columns\TextColumn::make('current_cashout_machines')
                    ->label('Cashout'),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ContractsRelationManager::class,
            RelationManagers\EarningPeriodsRelationManager::class,
            RelationManagers\EarningsRelationManager::class,
            RelationManagers\TransactionsRelationManager::class,
            RelationManagers\CashoutsRelationManager::class,
            RelationManagers\StoredEarningsRelationManager::class,
            RelationManagers\CashoutDetailsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClients::route('/'),
            'create' => Pages\CreateClient::route('/create'),
            'edit' => Pages\EditClient::route('/{record}/edit'),
        ];
    }
}
