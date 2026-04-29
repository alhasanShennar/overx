<?php

namespace App\Filament\Resources\ClientResource\RelationManagers;

use App\Models\Contract;
use App\Services\EarningPeriodService;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ContractsRelationManager extends RelationManager
{
    protected static string $relationship = 'contracts';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('amount')->numeric()->prefix('$')->nullable(),
            Forms\Components\FileUpload::make('file')->nullable()->directory('contracts'),
            Forms\Components\TextInput::make('storing_machines_no')
                ->label('Storing Machines')
                ->numeric()
                ->step(5)
                ->minValue(0)
                ->default(0),
            Forms\Components\TextInput::make('cashout_machines_no')
                ->label('Cashout Machines')
                ->numeric()
                ->step(5)
                ->minValue(0)
                ->default(0),
            Forms\Components\DatePicker::make('start_date')->nullable(),
            Forms\Components\DatePicker::make('end_date')->nullable(),
            Forms\Components\Textarea::make('notes')->nullable()->columnSpanFull(),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('amount')->money('USD')->sortable(),
                Tables\Columns\TextColumn::make('storing_machines_no')->label('Storing M.')->alignCenter(),
                Tables\Columns\TextColumn::make('cashout_machines_no')->label('Cashout M.')->alignCenter(),
                Tables\Columns\TextColumn::make('start_date')->date()->sortable(),
                Tables\Columns\TextColumn::make('end_date')->date()->sortable(),
                Tables\Columns\IconColumn::make('file')->label('File')
                    ->boolean()
                    ->trueIcon('heroicon-o-paper-clip')
                    ->falseIcon('heroicon-o-x-mark')
                    ->getStateUsing(fn($record) => !empty($record->file)),
                Tables\Columns\TextColumn::make('notes')->limit(40)->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->after(function (Contract $record) {
                        $client = $record->client;
                        $service = app(EarningPeriodService::class);

                        $start = $record->start_date
                            ? Carbon::parse($record->start_date)
                            : Carbon::today();
                        $end = $record->end_date
                            ? Carbon::parse($record->end_date)
                            : $start->copy()->addDays(29);

                        // Split the contract duration into 30-day periods
                        $periodStart = $start->copy();
                        $count = 0;

                        while ($periodStart->lte($end)) {
                            $periodEnd = $periodStart->copy()->addDays(29);
                            if ($periodEnd->gt($end)) {
                                $periodEnd = $end->copy();
                            }

                            // Skip if a period already covers this start date
                            $overlap = \App\Models\EarningPeriod::where('client_id', $client->id)
                                ->whereDate('start_date', '<=', $periodStart)
                                ->whereDate('end_date', '>=', $periodStart)
                                ->exists();

                            if (! $overlap) {
                                \App\Models\EarningPeriod::create([
                                    'client_id'        => $client->id,
                                    'start_date'       => $periodStart->copy(),
                                    'end_date'         => $periodEnd->copy(),
                                    'status'           => \App\Models\EarningPeriod::STATUS_PENDING,
                                    'total_btc_earned' => 0,
                                    'average_btc_price'=> 0,
                                    'total_revenue'    => 0,
                                ]);
                                $count++;
                            }

                            $periodStart->addDays(30);
                        }

                        Notification::make()
                            ->title("{$count} earning period(s) created from " . $start->format('M d, Y') . ' to ' . $end->format('M d, Y'))
                            ->success()
                            ->send();
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
