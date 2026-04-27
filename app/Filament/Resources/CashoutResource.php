<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CashoutResource\Pages;
use App\Models\Cashout;
use App\Models\Client;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CashoutResource extends Resource
{
    protected static ?string $model = Cashout::class;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Mining';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('amount')->numeric()->prefix('$')->disabled(),
            Forms\Components\TextInput::make('btc_amount')->numeric()->disabled(),
            Forms\Components\DatePicker::make('date'),
            Forms\Components\Select::make('status')
                ->options(['pending' => 'Pending', 'completed' => 'Completed', 'cancelled' => 'Cancelled']),
            Forms\Components\FileUpload::make('receipt')->directory('cashout-receipts')->nullable(),
            Forms\Components\Textarea::make('notes')->nullable()->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('client.user.name')
                    ->label('Client')->searchable(),
                Tables\Columns\TextColumn::make('amount')->money('USD'),
                Tables\Columns\TextColumn::make('btc_amount')->numeric(8),
                Tables\Columns\TextColumn::make('date')->date()->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'completed',
                        'danger' => 'cancelled',
                    ]),
                Tables\Columns\TextColumn::make('cashoutDetail.label')->label('Method'),
                Tables\Columns\IconColumn::make('receipt')
                    ->boolean()->label('Receipt')->getStateUsing(fn($record) => (bool) $record->receipt),
            ])
            ->defaultSort('date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(['pending' => 'Pending', 'completed' => 'Completed', 'cancelled' => 'Cancelled']),
                Tables\Filters\SelectFilter::make('client_id')
                    ->label('Client')
                    ->options(fn() => Client::with('user')->get()->pluck('user.name', 'id'))
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCashouts::route('/'),
            'edit' => Pages\EditCashout::route('/{record}/edit'),
        ];
    }
}
