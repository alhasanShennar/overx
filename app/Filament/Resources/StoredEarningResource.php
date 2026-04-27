<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StoredEarningResource\Pages;
use App\Models\Client;
use App\Models\StoredEarning;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class StoredEarningResource extends Resource
{
    protected static ?string $model = StoredEarning::class;
    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $navigationGroup = 'Mining';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('btc_amount')->disabled(),
            Forms\Components\TextInput::make('revenue_amount')->disabled(),
            Forms\Components\DateTimePicker::make('stored_at')->disabled(),
            Forms\Components\Textarea::make('notes')->nullable()->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('client.user.name')
                    ->label('Client')->searchable(),
                Tables\Columns\TextColumn::make('btc_amount')->numeric(8)->label('BTC Stored'),
                Tables\Columns\TextColumn::make('revenue_amount')->money('USD')->label('Value'),
                Tables\Columns\TextColumn::make('stored_at')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('earningPeriod.start_date')
                    ->label('Period')->date(),
            ])
            ->defaultSort('stored_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('client_id')
                    ->label('Client')
                    ->options(fn() => Client::with('user')->get()->pluck('user.name', 'id'))
                    ->searchable(),
            ])
            ->actions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStoredEarnings::route('/'),
        ];
    }
}
