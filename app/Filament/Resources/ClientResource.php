<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientResource\Pages;
use App\Filament\Resources\ClientResource\RelationManagers;
use App\Mail\ClientWelcomeMail;
use App\Models\Client;
use App\Models\Earning;
use App\Models\EarningPeriod;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Form;
use Illuminate\Validation\Rule;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
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
                    ->schema([
                        Forms\Components\TextInput::make('user_name')
                            ->label('Full Name')->required(),
                        Forms\Components\TextInput::make('user_email')
                            ->label('Email')->email()->required()
                            ->rules(fn ($livewire) => [
                                'required', 'email',
                                Rule::unique('users', 'email')
                                    ->ignore($livewire->record?->user_id),
                            ]),
                        Forms\Components\TextInput::make('user_password')
                            ->label('Password')->password()
                            ->required(fn(string $context) => $context === 'create'),
                    ]),

                Forms\Components\Section::make('Client Details')->schema([
                    PhoneInput::make('phone')->nullable(),
                    Forms\Components\FileUpload::make('passport')
                        ->directory('clients/passports')
                        ->nullable(),
                    Forms\Components\TextInput::make('current_storing_machines')
                        ->numeric()->disabled()->dehydrated(false)
                        ->helperText('Auto-calculated from contracts'),
                    Forms\Components\TextInput::make('current_cashout_machines')
                        ->numeric()->disabled()->dehydrated(false)
                        ->helperText('Auto-calculated from contracts'),
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
                Tables\Actions\Action::make('add_earning')
                    ->label('Add Earning')
                    ->icon('heroicon-m-plus-circle')
                    ->color('success')
                    ->button()
                    ->size('sm')
                    ->form(function (Client $record): array {
                        return [
                            Forms\Components\DatePicker::make('date')
                                ->default(today())
                                ->required()
                                ->live()
                                ->afterStateUpdated(function ($state, Forms\Set $set) use ($record) {
                                    if (! $state) return;
                                    $period = EarningPeriod::where('client_id', $record->id)
                                        ->whereDate('start_date', '<=', $state)
                                        ->whereDate('end_date', '>=', $state)
                                        ->first();
                                    $set('earning_period_id', $period?->id);
                                }),
                            Forms\Components\Select::make('earning_period_id')
                                ->label('Earning Period')
                                ->options(
                                    EarningPeriod::where('client_id', $record->id)
                                        ->orderBy('start_date')
                                        ->get()
                                        ->mapWithKeys(fn($p) => [
                                            $p->id => $p->start_date->format('M d, Y') . ' → ' . $p->end_date->format('M d, Y') . ' [' . $p->status . ']'
                                        ])
                                )
                                ->default(
                                    EarningPeriod::where('client_id', $record->id)
                                        ->whereDate('start_date', '<=', today())
                                        ->whereDate('end_date', '>=', today())
                                        ->value('id')
                                )
                                ->required()
                                ->helperText('Auto-selected based on date'),
                            Forms\Components\TextInput::make('btc_earned')
                                ->label('BTC Earned')
                                ->numeric()
                                ->step(0.00000001)
                                ->required(),
                            Forms\Components\TextInput::make('btc_price')
                                ->label('BTC Price (USD)')
                                ->numeric()
                                ->prefix('$')
                                ->required(),
                            Forms\Components\Textarea::make('additional_notes')
                                ->label('Notes')
                                ->nullable(),
                        ];
                    })
                    ->action(function (Client $record, array $data) {
                        Earning::create([
                            'client_id'        => $record->id,
                            'earning_period_id' => $data['earning_period_id'],
                            'date'             => $data['date'],
                            'btc_earned'       => $data['btc_earned'],
                            'btc_price'        => $data['btc_price'],
                            'additional_notes' => $data['additional_notes'] ?? null,
                        ]);

                        Notification::make()
                            ->title('Earning added successfully.')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('send_welcome')
                    ->label('Send Welcome')
                    ->icon('heroicon-m-envelope')
                    ->color('info')
                    ->button()
                    ->size('sm')
                    ->visible(fn (Client $record) => $record->welcome_sent_at === null)
                    ->requiresConfirmation()
                    ->modalHeading('Send Welcome Email')
                    ->modalDescription(fn (Client $record) => 'This will generate a new password for ' . $record->user->name . ' and send their login credentials by email. Continue?')
                    ->modalSubmitActionLabel('Yes, send it')
                    ->action(function (Client $record): void {
                        $plainPassword = Str::password(12, symbols: false);

                        $record->user->update([
                            'password' => Hash::make($plainPassword),
                        ]);

                        Mail::to($record->user->email)
                            ->send(new ClientWelcomeMail($record, $plainPassword));

                        $record->update(['welcome_sent_at' => now()]);

                        Notification::make()
                            ->title('Welcome email sent to ' . $record->user->email)
                            ->success()
                            ->send();
                    })
                    ->failureNotificationTitle('Failed to send email — check mail configuration.')
                    ->after(fn () => null),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ContractsRelationManager::class,
            RelationManagers\ContractHistoryRelationManager::class,
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
